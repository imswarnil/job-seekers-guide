<?php
/**
 * Plugin Name: Guide Bootstrap
 * Description: Keeps the site pointed at the Guide theme and plugin after a rename, so a deploy cannot leave the front end blank.
 * Version: 1.0.0
 *
 * ---------------------------------------------------------------------------
 * Why this exists, written down because it cost an afternoon.
 *
 * The theme and plugin were renamed — job-seekers-theme became guide-wp-theme,
 * job-seekers-lms became guide-lms — so that each could be distributed on its
 * own. The rename travelled to production in a deploy. What did *not* travel is
 * the database, and the database is where WordPress records which theme is
 * active and which plugins are switched on, by directory path.
 *
 * So production pulled the new code and immediately pointed at two directories
 * that no longer existed:
 *
 *   · The active theme was missing, so every front-end request rendered
 *     nothing at all — HTTP 200, zero bytes.
 *   · The active plugin path was missing, so the LMS silently did not load:
 *     no course rewrite rules, no security headers, no access control.
 *
 * A regular plugin cannot repair that, because a regular plugin is exactly the
 * thing that is not loading. Must-use plugins load unconditionally, before the
 * active_plugins list is consulted and regardless of what it says, which makes
 * this the only place the repair can live.
 *
 * It is deliberately conservative: it only ever acts when the recorded name
 * points at something that does not exist. On a healthy site every check is a
 * cheap comparison against an option and nothing is written.
 */

defined( 'ABSPATH' ) || exit;

const GUIDE_BOOTSTRAP_THEME  = 'guide-wp-theme';
const GUIDE_BOOTSTRAP_PLUGIN = 'guide-lms/guide-lms.php';

/**
 * Repair the active theme.
 *
 * Runs on `setup_theme`, which is early enough that the corrected theme is used
 * for this very request rather than the next one — the difference between a
 * visitor seeing the site and a visitor seeing a blank page.
 */
add_action(
	'setup_theme',
	static function () {
		$stylesheet = get_option( 'stylesheet' );

		if ( GUIDE_BOOTSTRAP_THEME === $stylesheet ) {
			return;
		}

		// Only intervene when the recorded theme is genuinely gone. Somebody
		// deliberately running a different, working theme is none of our
		// business.
		if ( $stylesheet && is_dir( WP_CONTENT_DIR . '/themes/' . $stylesheet ) ) {
			return;
		}

		if ( ! is_dir( WP_CONTENT_DIR . '/themes/' . GUIDE_BOOTSTRAP_THEME ) ) {
			return;
		}

		update_option( 'template', GUIDE_BOOTSTRAP_THEME );
		update_option( 'stylesheet', GUIDE_BOOTSTRAP_THEME );

		// switch_theme() is not available this early, and calling it here would
		// fire theme-switch hooks before the theme is loaded. Writing the two
		// options is what switch_theme() does that matters.
	},
	1
);

/**
 * Repair the active plugin list.
 *
 * The change lands from the next request onwards — the list for this request
 * was read before must-use plugins ran. One extra page load is a small price
 * for a site that heals itself.
 */
add_action(
	'plugins_loaded',
	static function () {
		// Two cheap operations on a healthy site: reading an option that is
		// already in memory, and one in_array(). No gate needed, and a gate
		// would mean the repair only ever ran once — which is precisely wrong
		// for something whose whole job is to fix a state that can recur.
		$active = (array) get_option( 'active_plugins', array() );

		if ( in_array( GUIDE_BOOTSTRAP_PLUGIN, $active, true ) ) {
			return;
		}

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . GUIDE_BOOTSTRAP_PLUGIN ) ) {
			return;
		}

		// Drop any plugin path that no longer exists — including this plugin's
		// own former name — so the list does not grow a tail of dead entries.
		$active = array_values(
			array_filter(
				$active,
				static function ( $path ) {
					// Must be a non-empty string before it is a path. A
					// corrupted or partially-unserialised option can yield
					// false or an array here, and WP_PLUGIN_DIR . '/' . false
					// is just the plugins directory, which exists — so a
					// bare file_exists() would happily keep the junk.
					if ( ! is_string( $path ) || '' === $path ) {
						return false;
					}

					return file_exists( WP_PLUGIN_DIR . '/' . $path );
				}
			)
		);

		$active[] = GUIDE_BOOTSTRAP_PLUGIN;

		update_option( 'active_plugins', $active );
	},
	1
);
