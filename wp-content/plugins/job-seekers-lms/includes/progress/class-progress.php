<?php
/**
 * Lesson progress: reads/writes against wp_jsl_progress plus the REST
 * routes the lesson player uses to mark lessons complete.
 */

namespace JSL\Progress;

use JSL\Enrollment\Tables;

defined( 'ABSPATH' ) || exit;

class Progress {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'jsl/v1',
			'/lessons/(?P<id>\d+)/complete',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'mark_complete' ),
					'permission_callback' => 'is_user_logged_in',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'mark_incomplete' ),
					'permission_callback' => 'is_user_logged_in',
				),
			)
		);
	}

	/* --- REST --- */

	public static function mark_complete( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );

		if ( 'lesson' !== get_post_type( $lesson_id ) || ! $course_id ) {
			return new \WP_REST_Response( array( 'error' => 'Not a lesson.' ), 404 );
		}

		self::complete( get_current_user_id(), $lesson_id, $course_id );

		return new \WP_REST_Response(
			array(
				'completed' => true,
				'progress'  => self::course_progress( get_current_user_id(), $course_id ),
			),
			200
		);
	}

	public static function mark_incomplete( \WP_REST_Request $request ) {
		global $wpdb;
		$lesson_id = (int) $request['id'];
		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );

		$wpdb->delete(
			Tables::progress_table_name(),
			array(
				'user_id'   => get_current_user_id(),
				'lesson_id' => $lesson_id,
			),
			array( '%d', '%d' )
		);

		return new \WP_REST_Response(
			array(
				'completed' => false,
				'progress'  => self::course_progress( get_current_user_id(), $course_id ),
			),
			200
		);
	}

	/* --- Data --- */

	public static function complete( int $user_id, int $lesson_id, int $course_id ): void {
		global $wpdb;

		if ( self::is_complete( $user_id, $lesson_id ) ) {
			return;
		}

		$wpdb->insert(
			Tables::progress_table_name(),
			array(
				'user_id'      => $user_id,
				'lesson_id'    => $lesson_id,
				'course_id'    => $course_id,
				'completed_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s' )
		);
	}

	public static function is_complete( int $user_id, int $lesson_id ): bool {
		global $wpdb;
		$table = Tables::progress_table_name();

		return (bool) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d AND lesson_id = %d", $user_id, $lesson_id )
		);
	}

	/**
	 * Lesson IDs the user has completed in a course.
	 *
	 * @return int[]
	 */
	public static function completed_lesson_ids( int $user_id, int $course_id ): array {
		global $wpdb;
		$table = Tables::progress_table_name();

		return array_map(
			'intval',
			$wpdb->get_col(
				$wpdb->prepare( "SELECT lesson_id FROM {$table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id )
			)
		);
	}

	/**
	 * A user's enrolled courses with progress, most recent enrollment first.
	 * Powers the frontend "My Learning" dashboard.
	 *
	 * @return array<int, array{course: \WP_Post, completed:int, total:int, percent:int, resume: ?\WP_Post}>
	 */
	public static function user_overview( int $user_id ): array {
		global $wpdb;
		$enroll = Tables::enrollments_table_name();

		$course_ids = array_map(
			'intval',
			$wpdb->get_col(
				$wpdb->prepare( "SELECT object_id FROM {$enroll} WHERE user_id = %d AND object_type = 'course' AND status = 'active' ORDER BY enrolled_at DESC", $user_id )
			)
		);

		$out = array();
		foreach ( $course_ids as $course_id ) {
			$course = get_post( $course_id );
			if ( ! $course || 'publish' !== $course->post_status ) {
				continue;
			}

			$completed = self::completed_lesson_ids( $user_id, $course_id );
			$flat      = \JSL\Course_Api::get_lessons_flat( $course_id );
			$resume    = null;
			foreach ( $flat as $lesson ) {
				if ( ! in_array( (int) $lesson->ID, $completed, true ) ) {
					$resume = $lesson;
					break;
				}
			}

			$total = count( $flat );
			$done  = min( count( $completed ), $total );

			$out[] = array(
				'course'    => $course,
				'completed' => $done,
				'total'     => $total,
				'percent'   => $total ? (int) round( $done / $total * 100 ) : 0,
				'resume'    => $resume,
			);
		}

		return $out;
	}

	/**
	 * Total minutes of completed lessons (uses jsl_duration_minutes meta).
	 */
	public static function minutes_completed( int $user_id ): int {
		global $wpdb;
		$table = Tables::progress_table_name();

		$lesson_ids = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT lesson_id FROM {$table} WHERE user_id = %d", $user_id ) ) );

		$minutes = 0;
		foreach ( $lesson_ids as $lesson_id ) {
			$minutes += (int) get_post_meta( $lesson_id, 'jsl_duration_minutes', true );
		}
		return $minutes;
	}

	/**
	 * Consecutive days (ending today or yesterday) with >=1 completion.
	 */
	public static function streak_days( int $user_id ): int {
		global $wpdb;
		$table = Tables::progress_table_name();

		$days = $wpdb->get_col(
			$wpdb->prepare( "SELECT DISTINCT DATE(completed_at) FROM {$table} WHERE user_id = %d ORDER BY 1 DESC LIMIT 60", $user_id )
		);
		if ( empty( $days ) ) {
			return 0;
		}

		$cursor = in_array( gmdate( 'Y-m-d' ), $days, true ) ? gmdate( 'Y-m-d' ) : gmdate( 'Y-m-d', time() - DAY_IN_SECONDS );
		$streak = 0;

		foreach ( $days as $day ) {
			if ( $day === $cursor ) {
				$streak++;
				$cursor = gmdate( 'Y-m-d', strtotime( $cursor ) - DAY_IN_SECONDS );
			} elseif ( $day < $cursor ) {
				break;
			}
		}

		return $streak;
	}

	/**
	 * @return array{completed:int, total:int, percent:int}
	 */
	public static function course_progress( int $user_id, int $course_id ): array {
		$total     = count( \JSL\Course_Api::get_lessons_flat( $course_id ) );
		$completed = count( self::completed_lesson_ids( $user_id, $course_id ) );
		$completed = min( $completed, $total );

		return array(
			'completed' => $completed,
			'total'     => $total,
			'percent'   => $total > 0 ? (int) round( ( $completed / $total ) * 100 ) : 0,
		);
	}
}
