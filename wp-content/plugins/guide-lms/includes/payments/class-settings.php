<?php
/**
 * Dodo Payments settings: API key, mode, webhook secret. Stored via WP
 * options — never hardcoded, never committed. Self-hosters fill these in
 * through wp-admin, independent of any infrastructure-specific config.
 */

namespace Guide\Payments;

defined( 'ABSPATH' ) || exit;

class Settings {

	const OPTION_API_KEY        = 'jsl_dodo_api_key';
	const OPTION_MODE           = 'jsl_dodo_mode';
	const OPTION_WEBHOOK_SECRET = 'jsl_dodo_webhook_secret';

	/**
	 * These options are edited on the LMS → Settings screen (Payments tab),
	 * which registers them with the Settings API. This class is now just the
	 * accessor + sanitizer for them, so there is no second place to look.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'redirect_legacy_page' ) );
	}

	/**
	 * Anyone with the old Settings → Dodo Payments URL bookmarked lands on
	 * the new tab instead of a "page not found".
	 */
	public static function redirect_legacy_page() {
		if ( isset( $_GET['page'] ) && 'guide-dodo-payments' === $_GET['page'] ) {
			wp_safe_redirect( \Guide\Admin\Settings_Page::url( 'payments' ) );
			exit;
		}
	}

	public static function sanitize_mode( $value ) {
		return in_array( $value, array( 'test', 'live' ), true ) ? $value : 'test';
	}

	public static function api_key() {
		return get_option( self::OPTION_API_KEY, '' );
	}

	public static function mode() {
		return get_option( self::OPTION_MODE, 'test' );
	}

	public static function webhook_secret() {
		return get_option( self::OPTION_WEBHOOK_SECRET, '' );
	}

	public static function base_url() {
		return 'live' === self::mode() ? 'https://live.dodopayments.com' : 'https://test.dodopayments.com';
	}

	public static function webhook_url() {
		return rest_url( 'guide/v1/dodo-webhook' );
	}

}
