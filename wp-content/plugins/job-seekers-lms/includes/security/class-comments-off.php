<?php
/**
 * This site has no comments anywhere — courses, lessons, paths, pages.
 * Closes them, hides existing ones, and removes every comment UI surface
 * from wp-admin and the admin bar.
 */

namespace JSL\Security;

defined( 'ABSPATH' ) || exit;

class Comments_Off {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'remove_support' ), 100 );
		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'comments_array', '__return_empty_array', 20 );
		add_filter( 'comments_pre_query', array( __CLASS__, 'short_circuit_queries' ), 10, 2 );

		add_action( 'admin_menu', array( __CLASS__, 'remove_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect_comment_screens' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'remove_admin_bar_node' ), 999 );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_dashboard_widget' ) );
	}

	public static function remove_support() {
		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}

	public static function short_circuit_queries( $comments, $query ) {
		// Let admin screens that genuinely need counts still work; front-end never does.
		return is_admin() ? $comments : array();
	}

	public static function remove_admin_menu() {
		remove_menu_page( 'edit-comments.php' );
	}

	public static function redirect_comment_screens() {
		global $pagenow;
		if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=jsl-lms' ) );
			exit;
		}
	}

	public static function remove_admin_bar_node( $bar ) {
		$bar->remove_node( 'comments' );
	}

	public static function remove_dashboard_widget() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
}
