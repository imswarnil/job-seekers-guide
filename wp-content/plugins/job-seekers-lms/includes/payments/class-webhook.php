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

namespace JSL\Payments;

use JSL\Enrollment\Enrollment;

defined( 'ABSPATH' ) || exit;

class Webhook {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'jsl/v1',
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

		if ( 'payment.succeeded' === ( $payload['type'] ?? '' ) ) {
			$metadata  = $payload['data']['metadata'] ?? array();
			$course_id = isset( $metadata['course_id'] ) ? (int) $metadata['course_id'] : 0;
			$user_id   = isset( $metadata['user_id'] ) ? (int) $metadata['user_id'] : 0;

			if ( $course_id && $user_id ) {
				Enrollment::enroll( $user_id, $course_id, 'course', 'dodo' );
				do_action( 'jsl_payment_confirmed', $user_id, $course_id, $payload );
			}
		}

		return new \WP_REST_Response( array( 'received' => true ), 200 );
	}
}
