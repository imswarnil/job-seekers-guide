<?php
/**
 * Course enrollment.
 *
 * There is no per-course checkout any more — the platform sells one thing, a
 * subscription (see class-subscription.php). So this route only ever records
 * an enrollment; when a course is premium and the user has no subscription it
 * says so and points at the subscribe flow rather than creating a payment.
 */

namespace Guide\Payments;

use Guide\Access\Access;
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
					'course_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);
	}

	/**
	 * Enroll the current user in a course they are allowed to take.
	 *
	 * Access is decided by Access::course_denial_reason(), not re-derived here,
	 * so this route can never disagree with what the lesson player enforces.
	 */
	public static function handle_enroll( \WP_REST_Request $request ) {
		$course_id = (int) $request->get_param( 'course_id' );
		$user_id   = get_current_user_id();

		if ( 'course' !== get_post_type( $course_id ) || 'publish' !== get_post_status( $course_id ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Not a course.', 'guide-lms' ) ), 404 );
		}

		$reason = Access::course_denial_reason( $course_id, $user_id );

		if ( Access::REASON_SUBSCRIBE === $reason ) {
			return new \WP_REST_Response(
				array(
					'error'            => __( 'This course is part of the subscription.', 'guide-lms' ),
					'needsSubscription' => true,
				),
				402
			);
		}

		if ( Access::REASON_OK !== $reason ) {
			return new \WP_REST_Response( array( 'error' => __( 'You cannot enroll in this course.', 'guide-lms' ) ), 403 );
		}

		Enrollment::enroll( $user_id, $course_id, 'course', 'free' );

		return new \WP_REST_Response( array( 'enrolled' => true ), 200 );
	}
}
