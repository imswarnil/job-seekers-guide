<?php
/**
 * Dodo Payments settings: API key, mode, webhook secret. Stored via WP
 * options — never hardcoded, never committed. Self-hosters fill these in
 * through wp-admin, independent of any infrastructure-specific config.
 */

namespace JSL\Payments;

defined( 'ABSPATH' ) || exit;

class Settings {

	const OPTION_API_KEY        = 'jsl_dodo_api_key';
	const OPTION_MODE           = 'jsl_dodo_mode';
	const OPTION_WEBHOOK_SECRET = 'jsl_dodo_webhook_secret';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function register_menu() {
		add_options_page(
			__( 'Dodo Payments', 'job-seekers-lms' ),
			__( 'Dodo Payments', 'job-seekers-lms' ),
			'manage_options',
			'jsl-dodo-payments',
			array( __CLASS__, 'render' )
		);
	}

	public static function register_settings() {
		register_setting( 'jsl_dodo_settings', self::OPTION_API_KEY, array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'jsl_dodo_settings', self::OPTION_MODE, array( 'sanitize_callback' => array( __CLASS__, 'sanitize_mode' ) ) );
		register_setting( 'jsl_dodo_settings', self::OPTION_WEBHOOK_SECRET, array( 'sanitize_callback' => 'sanitize_text_field' ) );
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
		return rest_url( 'jsl/v1/dodo-webhook' );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Dodo Payments', 'job-seekers-lms' ); ?></h1>
			<p><?php esc_html_e( 'Create products in your Dodo Payments dashboard first, then paste each Product ID into the matching course.', 'job-seekers-lms' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'jsl_dodo_settings' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="jsl_dodo_mode"><?php esc_html_e( 'Mode', 'job-seekers-lms' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( self::OPTION_MODE ); ?>" id="jsl_dodo_mode">
								<option value="test" <?php selected( self::mode(), 'test' ); ?>><?php esc_html_e( 'Test', 'job-seekers-lms' ); ?></option>
								<option value="live" <?php selected( self::mode(), 'live' ); ?>><?php esc_html_e( 'Live', 'job-seekers-lms' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="jsl_dodo_api_key"><?php esc_html_e( 'API Key', 'job-seekers-lms' ); ?></label></th>
						<td><input type="password" class="regular-text" autocomplete="off" name="<?php echo esc_attr( self::OPTION_API_KEY ); ?>" id="jsl_dodo_api_key" value="<?php echo esc_attr( self::api_key() ); ?>"></td>
					</tr>
					<tr>
						<th><label for="jsl_dodo_webhook_secret"><?php esc_html_e( 'Webhook Secret', 'job-seekers-lms' ); ?></label></th>
						<td><input type="password" class="regular-text" autocomplete="off" name="<?php echo esc_attr( self::OPTION_WEBHOOK_SECRET ); ?>" id="jsl_dodo_webhook_secret" value="<?php echo esc_attr( self::webhook_secret() ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Webhook URL', 'job-seekers-lms' ); ?></th>
						<td><code><?php echo esc_html( self::webhook_url() ); ?></code>
							<p class="description"><?php esc_html_e( 'Register this URL in Dodo Payments → Developer → Webhooks, subscribed to payment.succeeded.', 'job-seekers-lms' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
