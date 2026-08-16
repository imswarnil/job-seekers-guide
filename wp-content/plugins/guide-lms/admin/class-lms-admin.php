<?php
/**
 * Turns wp-admin into an LMS-first workspace.
 *
 * Everything authors need lives in the LMS console (admin.php?page=guide-lms).
 * This class removes the surfaces that compete with it:
 * - the Posts menu (this is not a blog)
 * - the Courses / Lessons / Learning Paths CPT list tables — those post types
 *   are registered with show_in_menu => false, and this class also intercepts
 *   direct hits on edit.php/post.php/post-new.php for them so nobody lands in
 *   the classic or block editor by URL
 * - admin-bar noise
 *
 * Learner-facing roles (subscribers) never see wp-admin at all — their home
 * is /my-learning/.
 */

namespace Guide\Admin;

defined( 'ABSPATH' ) || exit;

class Lms_Admin {

	/** Post types that are authored exclusively inside the console. */
	const CONSOLE_POST_TYPES = array( 'course', 'lesson', 'learning_path' );

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ), 100 );
		add_action( 'admin_menu', array( __CLASS__, 'add_updates_link' ), 30 );
		add_action( 'load-index.php', array( __CLASS__, 'redirect_dashboard' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'trim_admin_bar' ), 999 );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'strip_dashboard_widgets' ), 100 );
		add_action( 'admin_init', array( __CLASS__, 'keep_learners_out' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect_native_editors' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'hide_admin_bar_for_learners' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_block_editor' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'hide_screen_clutter' ) );
	}

	/**
	 * Strip menus that have no place in an LMS admin. Courses/Lessons/Paths
	 * are already absent (show_in_menu => false at registration).
	 *
	 * Tools is trimmed rather than removed. It was removed entirely, which also
	 * took Site Health and Export with it — on a self-hosted site those are the
	 * two screens you least want hidden: one tells you when PHP or the database
	 * is falling behind, the other is how you get your data out. Neither is
	 * clutter; both are how the site stays yours.
	 */
	public static function remove_menus() {
		global $menu, $submenu;

		// Only meaningful once wp-admin has built the menu. Guarding keeps this
		// safe when it is invoked outside a real admin request (WP-CLI, tests).
		if ( ! is_array( $menu ) ) {
			return;
		}

		remove_menu_page( 'edit.php' );          // Posts.

		// Comments stays: learners can now discuss lessons, and a moderation
		// queue you cannot reach is a moderation queue that does not exist.

		// Nothing to trim — and calling remove_submenu_page() against a menu
		// that was never registered warns.
		if ( empty( $submenu['tools.php'] ) ) {
			return;
		}

		// Keep Tools, minus the parts that only apply to a blog.
		remove_submenu_page( 'tools.php', 'import.php' );

		if ( empty( $submenu['tools.php'] ) ) {
			remove_menu_page( 'tools.php' );
			return;
		}

		// If everything worth keeping is gone, drop the empty menu too.
		$keep = array( 'site-health.php', 'export.php', 'export-personal-data.php', 'erase-personal-data.php' );
		$kept = false;
		foreach ( $submenu['tools.php'] as $item ) {
			if ( in_array( $item[2], $keep, true ) ) {
				$kept = true;
				break;
			}
		}

		if ( ! $kept ) {
			remove_menu_page( 'tools.php' );
		}
	}

	/**
	 * Surface pending core/plugin/theme updates inside the LMS menu.
	 *
	 * The Dashboard redirects to the console, so the usual place an admin
	 * notices "3 updates available" never gets looked at. An LMS running an
	 * outdated WordPress is the single most likely way this site gets
	 * compromised, so the count follows them into the menu they do use.
	 */
	public static function add_updates_link() {
		$updates = wp_get_update_data();
		$count   = isset( $updates['counts']['total'] ) ? (int) $updates['counts']['total'] : 0;

		if ( ! $count || ! current_user_can( 'update_core' ) ) {
			return;
		}

		add_submenu_page(
			Console::SLUG,
			__( 'Updates', 'guide-lms' ),
			sprintf(
				/* translators: %s: number of pending updates. */
				__( 'Updates %s', 'guide-lms' ),
				'<span class="update-plugins count-' . (int) $count . '"><span class="update-count">' . number_format_i18n( $count ) . '</span></span>'
			),
			'update_core',
			'update-core.php'
		);
	}

	public static function redirect_dashboard() {
		if ( current_user_can( 'edit_posts' ) ) {
			wp_safe_redirect( self::console_url() );
			exit;
		}
	}

	/**
	 * Sending an author to post.php?post=123 for a course/lesson/path would
	 * drop them into an editor the console is meant to replace. Bounce those
	 * requests into the console, deep-linking to the right course when we can.
	 */
	public static function redirect_native_editors() {
		if ( wp_doing_ajax() ) {
			return;
		}

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$post_type = '';

		if ( 'post.php' === $pagenow ) {
			$post_id   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			$post_type = $post_id ? (string) get_post_type( $post_id ) : '';
		} else {
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
		}

		if ( ! in_array( $post_type, self::CONSOLE_POST_TYPES, true ) ) {
			return;
		}

		wp_safe_redirect( self::console_deep_link( $post_type, isset( $post_id ) ? $post_id : 0 ) );
		exit;
	}

	/**
	 * Console hash route for a given LMS object.
	 */
	private static function console_deep_link( string $post_type, int $post_id ): string {
		if ( 'course' === $post_type && $post_id ) {
			return self::console_url( '#/courses/' . $post_id );
		}

		if ( 'lesson' === $post_type && $post_id ) {
			$course_id = (int) get_post_meta( $post_id, 'jsl_course_id', true );
			if ( $course_id ) {
				return self::console_url( '#/courses/' . $course_id . '/lessons/' . $post_id );
			}
			return self::console_url( '#/courses' );
		}

		if ( 'learning_path' === $post_type ) {
			return $post_id ? self::console_url( '#/paths/' . $post_id ) : self::console_url( '#/paths' );
		}

		return self::console_url( '#/courses' );
	}

	public static function console_url( string $fragment = '' ): string {
		return admin_url( 'admin.php?page=' . Console::SLUG ) . $fragment;
	}

	/**
	 * Belt-and-braces: even if something reaches an LMS post type's edit
	 * screen, don't boot the block editor for it.
	 */
	public static function disable_block_editor( $use, $post_type ) {
		return in_array( $post_type, self::CONSOLE_POST_TYPES, true ) ? false : $use;
	}

	public static function trim_admin_bar( $bar ) {
		foreach ( array( 'new-post', 'wp-logo', 'search', 'comments', 'new-content' ) as $node ) {
			$bar->remove_node( $node );
		}
	}

	public static function strip_dashboard_widgets() {
		// dashboard_site_health deliberately kept: it is the one widget that
		// tells a self-hoster something they need to act on.
		foreach ( array( 'dashboard_primary', 'dashboard_quick_press', 'dashboard_activity', 'dashboard_right_now' ) as $widget ) {
			remove_meta_box( $widget, 'dashboard', 'normal' );
			remove_meta_box( $widget, 'dashboard', 'side' );
		}
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}

	/**
	 * Hide the "Screen Options"/"Help" tabs on the console screen — they
	 * control nothing there and just add chrome.
	 */
	public static function hide_screen_clutter() {
		$screen = get_current_screen();
		if ( $screen && 'toplevel_page_' . Console::SLUG === $screen->id ) {
			echo '<style>#screen-meta,#screen-meta-links{display:none!important}</style>';
		}
	}

	/**
	 * Subscribers (learners) have nothing to do in wp-admin — send them to
	 * their learning dashboard instead.
	 */
	public static function keep_learners_out() {
		if ( wp_doing_ajax() || current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Sponsors are not learners — sending them to a learning dashboard
		// would be confusing and would hide the only screen they need.
		if ( class_exists( 'Guide\\Sponsors\\Sponsorship' ) && \Guide\Sponsors\Sponsorship::is_sponsor() ) {
			wp_safe_redirect( \Guide\Sponsors\Sponsor_Portal::url() );
			exit;
		}

		wp_safe_redirect( home_url( '/my-learning/' ) );
		exit;
	}

	public static function hide_admin_bar_for_learners( $show ) {
		return current_user_can( 'edit_posts' ) ? $show : false;
	}
}
