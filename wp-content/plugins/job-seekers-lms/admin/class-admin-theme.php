<?php
/**
 * wp-admin reskin: applies the LMS design system (ink slate + emerald
 * signal) across the whole admin — menu, admin bar, buttons, forms,
 * tables, notices — plus a matching login screen. Pure CSS overrides,
 * no markup changes, so core screens keep working.
 */

namespace JSL\Admin;

defined( 'ABSPATH' ) || exit;

class Admin_Theme {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_login' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'login_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'login_text' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
	}

	const FONTS_URL = 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap';

	public static function enqueue() {
		wp_enqueue_style( 'jsl-admin-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style( 'jsl-admin-theme', JSL_PLUGIN_URL . 'admin/assets/css/admin-theme.css', array( 'jsl-admin-fonts' ), JSL_VERSION );
	}

	public static function enqueue_login() {
		wp_enqueue_style( 'jsl-login-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style( 'jsl-login-theme', JSL_PLUGIN_URL . 'admin/assets/css/login-theme.css', array( 'jsl-login-fonts' ), JSL_VERSION );
	}

	public static function login_url() {
		return home_url( '/' );
	}

	public static function login_text() {
		return get_bloginfo( 'name' );
	}

	public static function footer_text() {
		return esc_html( get_bloginfo( 'name' ) ) . ' — powered by the Job Seekers LMS.';
	}
}
