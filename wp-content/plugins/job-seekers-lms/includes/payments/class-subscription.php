<?php
/**
 * Platform subscription — one payment unlocks every course on the site.
 *
 * Sits alongside per-course purchases rather than replacing them: a learner
 * can buy a single course outright, or subscribe and get all of them. Both
 * end up as rows in wp_jsl_enrollments, and JSL\Access\Access is the only
 * thing that reads them.
 *
 * The grant is time-boxed (expires_at). Renewals come in as webhooks and
 * simply push the expiry out; a cancellation flips the row to 'cancelled'
 * but leaves progress alone.
 */

namespace JSL\Payments;

use JSL\Enrollment\Enrollment;

defined( 'ABSPATH' ) || exit;

class Subscription {

	const OPTION_ENABLED     = 'jsl_subscription_enabled';
	const OPTION_PRODUCT_ID  = 'jsl_subscription_product_id';
	const OPTION_PRICE_LABEL = 'jsl_subscription_price_label';
	const OPTION_PERIOD      = 'jsl_subscription_period_days';
	const OPTION_BLURB       = 'jsl_subscription_blurb';

	/** Grace window added to each period so a late renewal webhook can't lock someone out mid-lesson. */
	const GRACE_DAYS = 2;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false ) && '' !== self::product_id();
	}

	public static function product_id(): string {
		return (string) get_option( self::OPTION_PRODUCT_ID, '' );
	}

	public static function price_label(): string {
		return (string) get_option( self::OPTION_PRICE_LABEL, '' );
	}

	public static function blurb(): string {
		return (string) get_option( self::OPTION_BLURB, '' );
	}

	public static function period_days(): int {
		$days = (int) get_option( self::OPTION_PERIOD, 30 );
		return $days > 0 ? $days : 30;
	}

	public static function register_routes() {
		register_rest_route(
			'jsl/v1',
			'/subscribe',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_subscribe' ),
				'permission_callback' => 'is_user_logged_in',
			)
		);
	}

	/**
	 * Start a subscription checkout for the current user.
	 */
	public static function handle_subscribe( \WP_REST_Request $request ) {
		if ( ! self::is_enabled() ) {
			return new \WP_REST_Response( array( 'error' => __( 'Subscriptions are not available.', 'job-seekers-lms' ) ), 404 );
		}

		$user_id = get_current_user_id();

		if ( Enrollment::has_platform_subscription( $user_id ) ) {
			return new \WP_REST_Response( array( 'already_subscribed' => true ), 200 );
		}

		$session = self::create_session( $user_id );

		if ( is_wp_error( $session ) ) {
			return new \WP_REST_Response( array( 'error' => $session->get_error_message() ), 502 );
		}

		return new \WP_REST_Response( array( 'checkout_url' => $session['checkout_url'] ), 200 );
	}

	/**
	 * @return array{checkout_url:string}|\WP_Error
	 */
	public static function create_session( int $user_id ) {
		if ( ! Settings::api_key() ) {
			return new \WP_Error( 'jsl_no_api_key', __( 'Dodo Payments API key is not configured.', 'job-seekers-lms' ) );
		}

		$user = get_userdata( $user_id );

		$body = array(
			'product_cart' => array(
				array( 'product_id' => self::product_id(), 'quantity' => 1 ),
			),
			'customer'     => array( 'email' => $user ? $user->user_email : '' ),
			'return_url'   => home_url( '/my-learning/' ),
			'metadata'     => array(
				'plan'    => 'platform',
				'user_id' => (string) $user_id,
			),
		);

		$response = wp_remote_post(
			Settings::base_url() . '/checkouts',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . Settings::api_key(),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 || empty( $data['checkout_url'] ) ) {
			// Don't echo the provider's raw body back to the browser — it can
			// carry account details. Log it, return something generic.
			error_log( '[JSL] Dodo subscription checkout failed: ' . wp_remote_retrieve_body( $response ) );
			return new \WP_Error( 'jsl_dodo_error', __( 'Could not start checkout. Please try again.', 'job-seekers-lms' ) );
		}

		return array( 'checkout_url' => $data['checkout_url'] );
	}

	/**
	 * Grant or renew the platform subscription for a user.
	 *
	 * @param int    $user_id
	 * @param string $external_id  Provider subscription id.
	 * @param string $next_billing Provider-supplied next billing date, if any.
	 */
	public static function grant( int $user_id, string $external_id = '', string $next_billing = '' ): bool {
		$expiry = self::resolve_expiry( $next_billing );

		return Enrollment::enroll( $user_id, 0, Enrollment::PLATFORM, 'dodo', $expiry, $external_id );
	}

	public static function revoke( int $user_id ): bool {
		return Enrollment::revoke( $user_id, 0, Enrollment::PLATFORM );
	}

	/**
	 * Prefer the provider's own next-billing date; fall back to the configured
	 * period. Either way add a grace window so a webhook that lands a few
	 * hours late doesn't lock an active subscriber out.
	 *
	 * Expiries are stored in UTC — Enrollment compares them against the GMT
	 * clock, so both sides of that comparison have to be in the same zone.
	 */
	private static function resolve_expiry( string $next_billing ): string {
		$base = $next_billing ? strtotime( $next_billing ) : false;

		if ( ! $base ) {
			$base = time() + ( self::period_days() * DAY_IN_SECONDS );
		}

		return gmdate( 'Y-m-d H:i:s', $base + ( self::GRACE_DAYS * DAY_IN_SECONDS ) );
	}
}
