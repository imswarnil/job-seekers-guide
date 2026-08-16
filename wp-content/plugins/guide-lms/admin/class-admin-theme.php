<?php
/**
 * wp-admin reskin: applies the Guide design tokens across the admin — menu,
 * admin bar, buttons, forms, notices — plus a matching login screen.
 *
 * Colour and type only, no resets and no layout changes, so core screens and
 * other plugins' pages keep working. The console's own screens get real Bulma
 * from console.min.css, which is scoped under `.guide-admin` precisely so it
 * cannot reach the rest of wp-admin.
 */

namespace Guide\Admin;

defined( 'ABSPATH' ) || exit;

class Admin_Theme {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_login' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'login_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'login_text' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
	}

	const FONTS_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap';

	public static function enqueue() {
		wp_enqueue_style( 'guide-admin-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-admin-skin',
			GUIDE_PLUGIN_URL . 'admin/assets/css/admin-skin.min.css',
			array( 'guide-admin-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/admin-skin.min.css' )
		);
	}

	/**
	 * The scoped Bulma build. Only loaded on the console's own screens —
	 * 392 KB of framework has no business on every admin page, and nothing in
	 * it applies outside `.guide-admin` anyway.
	 */
	public static function enqueue_console() {
		wp_enqueue_style( 'guide-admin-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-console',
			GUIDE_PLUGIN_URL . 'admin/assets/css/console.min.css',
			array( 'guide-admin-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/console.min.css' )
		);
	}

	public static function enqueue_login() {
		wp_enqueue_style( 'guide-login-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-login',
			GUIDE_PLUGIN_URL . 'admin/assets/css/login.min.css',
			array( 'guide-login-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/login.min.css' )
		);
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
