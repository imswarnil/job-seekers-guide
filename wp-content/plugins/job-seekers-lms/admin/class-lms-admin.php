<?php
/**
 * Turns wp-admin into an LMS-first workspace.
 *
 * Everything authors need lives in the LMS console (admin.php?page=jsl-lms).
 * This class removes the surfaces that compete with it:
 * - blog menus (Posts, Comments) and low-value menus (Tools)
 * - the Courses / Lessons / Learning Paths CPT list tables — those post types
 *   are registered with show_in_menu => false, and this class also intercepts
 *   direct hits on edit.php/post.php/post-new.php for them so nobody lands in
 *   the classic or block editor by URL
 * - admin-bar noise
 *
 * Learner-facing roles (subscribers) never see wp-admin at all — their home
 * is /my-learning/.
 */

namespace JSL\Admin;

defined( 'ABSPATH' ) || exit;

class Lms_Admin {

	/** Post types that are authored exclusively inside the console. */
	const CONSOLE_POST_TYPES = array( 'course', 'lesson', 'learning_path' );

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ), 100 );
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
	 */
	public static function remove_menus() {
		remove_menu_page( 'edit.php' );          // Posts.
		remove_menu_page( 'edit-comments.php' ); // Comments.
		remove_menu_page( 'tools.php' );         // Tools.
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
		foreach ( array( 'dashboard_primary', 'dashboard_quick_press', 'dashboard_activity', 'dashboard_right_now', 'dashboard_site_health' ) as $widget ) {
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
		wp_safe_redirect( home_url( '/my-learning/' ) );
		exit;
	}

	public static function hide_admin_bar_for_learners( $show ) {
		return current_user_can( 'edit_posts' ) ? $show : false;
	}
}
