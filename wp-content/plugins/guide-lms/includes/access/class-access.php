<?php
/**
 * The single gatekeeper for "may this user see this lesson?".
 *
 * Every surface — lesson template, REST progress writes, quiz questions,
 * search results, JSON-LD — asks this class rather than re-deriving the
 * rules, so there is exactly one place access can be wrong.
 *
 * Rules, in order:
 *   1. Anyone who can edit posts (authors/admins) sees everything.
 *   2. A lesson flagged as a free preview is open to everyone.
 *   3. A free course is open to everyone.
 *   4. A premium course opens for anyone with an active platform subscription.
 *      There is no per-course purchase — the site sells one subscription.
 *   5. Legacy per-course grants from the old pricing model are still honoured,
 *      so nobody loses access to something they paid for before the change.
 */

namespace Guide\Access;

use Guide\Enrollment\Enrollment;
use Guide\Payments\Course_Access;

defined( 'ABSPATH' ) || exit;

class Access {

	/** Why access was denied — drives which upsell the template renders. */
	const REASON_OK           = 'ok';
	const REASON_LOGIN        = 'login_required';
	const REASON_SUBSCRIBE    = 'subscription_required';
	const REASON_NO_SUCH_ITEM = 'not_found';

	/**
	 * Kept so any stored value or third-party check written against the old
	 * per-course purchase model still resolves to "needs to pay us something".
	 *
	 * @deprecated Use REASON_SUBSCRIBE — there are no per-course purchases.
	 */
	const REASON_PURCHASE = 'subscription_required';

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'guard_lesson_request' ) );
		add_filter( 'rest_prepare_lesson', array( __CLASS__, 'filter_rest_lesson_content' ), 10, 2 );
	}

	/**
	 * Can the user open this lesson?
	 *
	 * @param int $lesson_id Lesson post ID.
	 * @param int $user_id   Defaults to the current user.
	 */
	public static function can_view_lesson( int $lesson_id, int $user_id = 0 ): bool {
		return self::REASON_OK === self::lesson_denial_reason( $lesson_id, $user_id );
	}

	/**
	 * Same check as can_view_lesson() but returns why, for the UI.
	 *
	 * @return string One of the REASON_* constants.
	 */
	public static function lesson_denial_reason( int $lesson_id, int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();

		if ( self::user_is_staff( $user_id ) ) {
			return self::REASON_OK;
		}

		if ( 'lesson' !== get_post_type( $lesson_id ) ) {
			return self::REASON_NO_SUCH_ITEM;
		}

		// Free preview lessons are the sample chapter — always open.
		if ( get_post_meta( $lesson_id, 'jsl_is_preview', true ) ) {
			return self::REASON_OK;
		}

		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );

		// A standalone lesson (learning-path article/video) has no course to
		// buy, so it follows the site's default: open.
		if ( ! $course_id ) {
			return self::REASON_OK;
		}

		return self::course_denial_reason( $course_id, $user_id );
	}

	/**
	 * Can the user consume this course's content?
	 */
	public static function can_view_course_content( int $course_id, int $user_id = 0 ): bool {
		return self::REASON_OK === self::course_denial_reason( $course_id, $user_id ?: get_current_user_id() );
	}

	public static function course_denial_reason( int $course_id, int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();

		if ( self::user_is_staff( $user_id ) ) {
			return self::REASON_OK;
		}

		if ( ! Course_Access::is_premium( $course_id ) ) {
			return self::REASON_OK;
		}

		if ( ! $user_id ) {
			return self::REASON_LOGIN;
		}

		// Premium is unlocked by the platform subscription, and only by that.
		if ( Enrollment::has_platform_subscription( $user_id ) ) {
			return self::REASON_OK;
		}

		// Course-scoped grants no longer exist, but a site that ran the old
		// per-course checkout may still hold them. Honouring them means nobody
		// loses access to something they actually paid for.
		if ( Enrollment::is_enrolled( $user_id, $course_id, 'course' ) ) {
			return self::REASON_OK;
		}

		return self::REASON_SUBSCRIBE;
	}

	/**
	 * True when the user holds all-access (subscription or staff). Used by
	 * the UI to swap "Buy this course" for "Included in your plan".
	 */
	public static function has_all_access( int $user_id = 0 ): bool {
		$user_id = $user_id ?: get_current_user_id();

		return self::user_is_staff( $user_id ) || Enrollment::has_platform_subscription( $user_id );
	}

	/**
	 * Whether the lesson should render as a locked teaser rather than a 302.
	 * Locked lessons stay crawlable (title + excerpt) but never leak the body.
	 */
	public static function is_locked( int $lesson_id, int $user_id = 0 ): bool {
		$reason = self::lesson_denial_reason( $lesson_id, $user_id );
		return self::REASON_OK !== $reason && self::REASON_NO_SUCH_ITEM !== $reason;
	}

	private static function user_is_staff( int $user_id ): bool {
		if ( ! $user_id ) {
			return false;
		}
		return user_can( $user_id, 'edit_posts' );
	}

	/**
	 * Server-side guard. Even though the template renders a locked state,
	 * strip the content at the source so nothing (feeds, REST, embeds,
	 * a template bug) can serve a paid lesson body to someone without access.
	 */
	public static function guard_lesson_request() {
		if ( ! is_singular( 'lesson' ) ) {
			return;
		}

		$lesson_id = (int) get_queried_object_id();

		if ( ! self::is_locked( $lesson_id ) ) {
			return;
		}

		add_filter( 'the_content', array( __CLASS__, 'strip_locked_content' ), 1 );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function strip_locked_content( $content ) {
		if ( is_singular( 'lesson' ) && in_the_loop() && is_main_query() ) {
			return '';
		}
		return $content;
	}

	/**
	 * Belt-and-braces for the REST layer: lesson content served through
	 * wp/v2/lessons is blanked for users without access.
	 */
	public static function filter_rest_lesson_content( $response, $post ) {
		if ( ! $response instanceof \WP_REST_Response || ! $post instanceof \WP_Post ) {
			return $response;
		}

		if ( ! self::is_locked( (int) $post->ID ) ) {
			return $response;
		}

		$data = $response->get_data();

		if ( isset( $data['content'] ) ) {
			$data['content'] = array(
				'rendered'  => '',
				'raw'       => '',
				'protected' => true,
			);
		}

		$response->set_data( $data );

		return $response;
	}
}
