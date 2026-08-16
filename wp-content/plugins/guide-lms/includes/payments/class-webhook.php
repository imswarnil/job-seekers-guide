<?php
/**
 * Dodo Payments webhook receiver.
 *
 * Follows the Standard Webhooks spec Dodo implements: headers webhook-id /
 * webhook-signature / webhook-timestamp, signature = HMAC-SHA256 of
 * "{id}.{timestamp}.{raw_body}" using the webhook secret, compared with
 * hash_equals (constant-time). Never trust the payload before that check
 * passes.
 */

namespace Guide\Payments;

use Guide\Enrollment\Enrollment;

defined( 'ABSPATH' ) || exit;

class Webhook {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/dodo-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle' ),
				'permission_callback' => '__return_true', // authenticity comes from signature verification, not a WP capability
			)
		);
	}

	public static function handle( \WP_REST_Request $request ) {
		$secret = Settings::webhook_secret();

		if ( ! $secret ) {
			return new \WP_REST_Response( array( 'error' => 'Webhook secret not configured.' ), 500 );
		}

		$id        = $request->get_header( 'webhook-id' );
		$timestamp = $request->get_header( 'webhook-timestamp' );
		$signature = $request->get_header( 'webhook-signature' );
		$raw_body  = $request->get_body();

		if ( ! $id || ! $timestamp || ! $signature ) {
			return new \WP_REST_Response( array( 'error' => 'Missing signature headers.' ), 400 );
		}

		// Reject stale deliveries outright — a captured request shouldn't stay
		// replayable forever just because its signature is still valid.
		if ( abs( time() - (int) $timestamp ) > 5 * MINUTE_IN_SECONDS ) {
			return new \WP_REST_Response( array( 'error' => 'Timestamp outside tolerance.' ), 400 );
		}

		$signed_content = $id . '.' . $timestamp . '.' . $raw_body;
		$expected       = base64_encode( hash_hmac( 'sha256', $signed_content, $secret, true ) );

		// webhook-signature can contain multiple space-separated "v1,<sig>" values.
		$provided_sigs = array_map(
			function ( $part ) {
				$pieces = explode( ',', $part );
				return end( $pieces );
			},
			explode( ' ', $signature )
		);

		$verified = false;
		foreach ( $provided_sigs as $provided ) {
			if ( hash_equals( $expected, $provided ) ) {
				$verified = true;
				break;
			}
		}

		if ( ! $verified ) {
			return new \WP_REST_Response( array( 'error' => 'Signature verification failed.' ), 401 );
		}

		$payload = json_decode( $raw_body, true );

		if ( ! is_array( $payload ) ) {
			return new \WP_REST_Response( array( 'error' => 'Malformed payload.' ), 400 );
		}

		// Replay protection: Standard Webhooks ids are unique per delivery, so
		// remembering the ones we've processed makes redelivery a no-op rather
		// than a second grant.
		if ( self::already_processed( $id ) ) {
			return new \WP_REST_Response( array( 'received' => true, 'duplicate' => true ), 200 );
		}

		$type     = (string) ( $payload['type'] ?? '' );
		$data     = $payload['data'] ?? array();
		$metadata = $data['metadata'] ?? array();
		$user_id  = isset( $metadata['user_id'] ) ? (int) $metadata['user_id'] : 0;
		$is_plan  = 'platform' === ( $metadata['plan'] ?? '' );

		if ( $user_id && ! get_userdata( $user_id ) ) {
			$user_id = 0;
		}

		switch ( $type ) {
			case 'payment.succeeded':
				if ( ! $user_id ) {
					break;
				}
				if ( $is_plan ) {
					Subscription::grant( $user_id, (string) ( $data['subscription_id'] ?? '' ), (string) ( $data['next_billing_date'] ?? '' ) );
					do_action( 'jsl_subscription_activated', $user_id, $payload );
					break;
				}
				$course_id = isset( $metadata['course_id'] ) ? (int) $metadata['course_id'] : 0;
				if ( $course_id && 'course' === get_post_type( $course_id ) ) {
					Enrollment::enroll( $user_id, $course_id, 'course', 'dodo', null, (string) ( $data['payment_id'] ?? '' ) );
					do_action( 'jsl_payment_confirmed', $user_id, $course_id, $payload );
				}
				break;

			case 'subscription.active':
			case 'subscription.renewed':
				if ( $user_id ) {
					Subscription::grant( $user_id, (string) ( $data['subscription_id'] ?? '' ), (string) ( $data['next_billing_date'] ?? '' ) );
					do_action( 'jsl_subscription_activated', $user_id, $payload );
				}
				break;

			case 'subscription.cancelled':
			case 'subscription.expired':
			case 'subscription.failed':
				if ( $user_id ) {
					Subscription::revoke( $user_id );
					do_action( 'jsl_subscription_ended', $user_id, $payload );
				}
				break;
		}

		self::remember( $id );

		return new \WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * Delivery ids we've already acted on, kept for a day — long enough to
	 * cover any realistic retry window without growing without bound.
	 */
	private static function already_processed( string $id ): bool {
		return (bool) get_transient( 'jsl_wh_' . md5( $id ) );
	}

	private static function remember( string $id ): void {
		set_transient( 'jsl_wh_' . md5( $id ), 1, DAY_IN_SECONDS );
	}
}
