<?php
/**
 * Turns wp-admin into an LMS-first workspace:
 * - the Dashboard redirects to the LMS console
 * - blog surfaces (Posts, "+New Post", post widgets) are removed
 * - admin-bar noise is trimmed
 *
 * Learner-facing roles (subscribers) are kept out of wp-admin entirely
 * and their admin bar hidden — their home is /my-learning/.
 */

namespace JSL\Admin;

defined( 'ABSPATH' ) || exit;

class Lms_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'remove_blog_menus' ), 100 );
		add_action( 'load-index.php', array( __CLASS__, 'redirect_dashboard' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'trim_admin_bar' ), 999 );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'strip_dashboard_widgets' ), 100 );
		add_action( 'admin_init', array( __CLASS__, 'keep_learners_out' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'hide_admin_bar_for_learners' ) );
	}

	public static function remove_blog_menus() {
		remove_menu_page( 'edit.php' ); // Posts.
	}

	public static function redirect_dashboard() {
		if ( current_user_can( 'edit_posts' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=jsl-lms' ) );
			exit;
		}
	}

	public static function trim_admin_bar( $bar ) {
		$bar->remove_node( 'new-post' );
		$bar->remove_node( 'wp-logo' );
		$bar->remove_node( 'search' );
	}

	public static function strip_dashboard_widgets() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		remove_meta_box( 'welcome-panel', 'dashboard', 'normal' );
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
