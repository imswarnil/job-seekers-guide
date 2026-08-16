<?php
/**
 * Unload the parts of WordPress this site does not use.
 *
 * Two reasons, and the second matters more than the first:
 *
 *   1. Weight. Emoji scripts, block-editor stylesheets, jQuery Migrate and
 *      oEmbed run on every page load for an audience largely on cheap phones
 *      and slow connections.
 *
 *   2. Attack surface. Every endpoint that exists is an endpoint that can have
 *      a vulnerability disclosed against it. The REST discovery link, RSD,
 *      Windows Live Writer and oEmbed discovery all advertise capabilities this
 *      site has no use for, and each is a thing a scanner probes.
 *
 * Nothing here removes anything the platform actually relies on. The front end
 * uses no blocks, no comments RSS, no post feeds, no emoji, and no jQuery.
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Trim {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'strip_head' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_unused' ), 100 );
		add_action( 'wp_footer', array( __CLASS__, 'dequeue_late' ), 0 );

		// Emoji: a script, a stylesheet and a DNS prefetch on every page, for a
		// feature every browser has supported natively for years.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );
		add_filter( 'tiny_mce_plugins', array( __CLASS__, 'remove_emoji_tinymce' ) );

		// jQuery Migrate: a compatibility shim for code written before 2016.
		add_action( 'wp_default_scripts', array( __CLASS__, 'drop_jquery_migrate' ) );

		// Feeds. This is an LMS; there is no blog to subscribe to, and a feed
		// is one more place content can leak from.
		foreach ( array( 'do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'kill_feed' ), 1 );
		}
		add_action( 'do_feed_rss2_comments', array( __CLASS__, 'kill_feed' ), 1 );
		add_action( 'do_feed_atom_comments', array( __CLASS__, 'kill_feed' ), 1 );

		// Heartbeat outside the editor is a request every 15 seconds for
		// nothing. Keep it where it is genuinely used.
		add_filter( 'heartbeat_settings', array( __CLASS__, 'slow_heartbeat' ) );
	}

	/**
	 * Remove the discovery links and generator tags in <head>.
	 *
	 * Each one either advertises a capability this site does not offer, or
	 * tells a scanner what version to try exploits for.
	 */
	public static function strip_head() {
		remove_action( 'wp_head', 'rsd_link' );                                  // Really Simple Discovery (XML-RPC).
		remove_action( 'wp_head', 'wlwmanifest_link' );                          // Windows Live Writer, retired in 2017.
		remove_action( 'wp_head', 'wp_generator' );                              // WordPress version.
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		// oEmbed: lets any site embed this one, and adds a discovery endpoint.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		add_filter( 'embed_oembed_discover', '__return_false' );

		// The REST discovery link and Link header. The API still works — this
		// only stops it being advertised to every visitor and every scanner.
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );

		// X-Pingback header.
		add_filter( 'wp_headers', array( __CLASS__, 'strip_pingback' ) );
	}

	public static function strip_pingback( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	public static function remove_emoji_tinymce( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

	public static function drop_jquery_migrate( $scripts ) {
		if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
			return;
		}

		$scripts->registered['jquery']->deps = array_diff(
			$scripts->registered['jquery']->deps,
			array( 'jquery-migrate' )
		);
	}

	/**
	 * Drop front-end stylesheets for features this theme does not use.
	 *
	 * The block library alone is over 100 KB, and this is a classic theme with
	 * no blocks on the front end at all.
	 */
	public static function dequeue_unused() {
		if ( is_admin() ) {
			return;
		}

		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles' ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}

		// Only the comment form needs this, and only where discussion is open.
		if ( ! is_singular() || ! comments_open() ) {
			wp_dequeue_script( 'comment-reply' );
		}
	}

	/**
	 * jQuery is not used by any front-end script this theme ships.
	 *
	 * Dropped late so a plugin that genuinely depends on it still gets it —
	 * the check is "did anything else ask for it", not "assume nothing did".
	 */
	public static function dequeue_late() {
		if ( is_admin() || ! wp_script_is( 'jquery', 'enqueued' ) ) {
			return;
		}

		global $wp_scripts;

		foreach ( (array) $wp_scripts->queue as $handle ) {
			if ( 'jquery' === $handle || empty( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			if ( in_array( 'jquery', (array) $wp_scripts->registered[ $handle ]->deps, true ) ) {
				return; // Something needs it. Leave it alone.
			}
		}

		wp_dequeue_script( 'jquery' );
	}

	public static function kill_feed() {
		wp_die(
			esc_html__( 'No feeds here — this is a course platform, not a blog.', 'guide-lms' ),
			esc_html__( 'Not available', 'guide-lms' ),
			array( 'response' => 410 )
		);
	}

	public static function slow_heartbeat( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}
}
