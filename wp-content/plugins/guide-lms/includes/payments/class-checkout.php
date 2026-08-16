<?php
/**
 * Dodo Payments checkout session creation + the front-end enroll/checkout
 * REST routes (free courses enroll directly; paid courses hand back a
 * Dodo checkout_url to redirect to).
 *
 * API contract (docs.dodopayments.com, verified against current docs):
 * POST {base}/checkouts, Bearer auth, body: { product_cart, customer,
 * return_url, metadata }, response includes checkout_url.
 */

namespace Guide\Payments;

use Guide\Enrollment\Enrollment;

defined( 'ABSPATH' ) || exit;

class Checkout {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/enroll',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_enroll' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'course_id' => array( 'required' => true, 'type' => 'integer' ),
				),
			)
		);
	}

	/**
	 * Free course -> enroll immediately. Paid course -> create a Dodo
	 * checkout session and return its checkout_url for the browser to
	 * redirect to (actual enrollment happens later, from the webhook,
	 * once payment is confirmed).
	 */
	public static function handle_enroll( \WP_REST_Request $request ) {
		$course_id = (int) $request->get_param( 'course_id' );
		$user_id   = get_current_user_id();

		if ( 'course' !== get_post_type( $course_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Not a course.' ), 404 );
		}

		if ( ! Course_Pricing::is_paid( $course_id ) ) {
			Enrollment::enroll( $user_id, $course_id, 'course', 'free' );
			return new \WP_REST_Response( array( 'enrolled' => true ), 200 );
		}

		$session = self::create_session( $course_id, $user_id );

		if ( is_wp_error( $session ) ) {
			return new \WP_REST_Response( array( 'error' => $session->get_error_message() ), 502 );
		}

		return new \WP_REST_Response( array( 'checkout_url' => $session['checkout_url'] ), 200 );
	}

	/**
	 * @return array{checkout_url:string}|\WP_Error
	 */
	public static function create_session( int $course_id, int $user_id ) {
		$product_id = Course_Pricing::product_id( $course_id );

		if ( ! $product_id ) {
			return new \WP_Error( 'jsl_no_product', __( 'This course has no Dodo Product ID configured.', 'guide-lms' ) );
		}

		if ( ! Settings::api_key() ) {
			return new \WP_Error( 'jsl_no_api_key', __( 'Dodo Payments API key is not configured.', 'guide-lms' ) );
		}

		$user = get_userdata( $user_id );

		$body = array(
			'product_cart' => array(
				array( 'product_id' => $product_id, 'quantity' => 1 ),
			),
			'customer'     => array( 'email' => $user ? $user->user_email : '' ),
			'return_url'   => get_permalink( $course_id ),
			'metadata'     => array(
				'course_id' => (string) $course_id,
				'user_id'   => (string) $user_id,
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
			return new \WP_Error( 'jsl_dodo_error', 'Dodo Payments error: ' . wp_remote_retrieve_body( $response ) );
		}

		return array( 'checkout_url' => $data['checkout_url'] );
	}
}
