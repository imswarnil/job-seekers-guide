<?php
/**
 * LMS analytics: aggregate queries over wp_jsl_enrollments / wp_jsl_progress
 * plus the admin-only REST routes the LMS console dashboard consumes.
 */

namespace Guide\Analytics;

use Guide\Enrollment\Tables;

defined( 'ABSPATH' ) || exit;

class Analytics {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		$routes = array(
			'/analytics/overview'            => 'overview',
			'/analytics/courses'             => 'courses',
			'/analytics/learners'            => 'learners',
			'/analytics/learners/(?P<id>\d+)' => 'learner',
		);

		foreach ( $routes as $route => $method ) {
			register_rest_route(
				'guide/v1',
				$route,
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'rest_' . $method ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/* --- REST callbacks --- */

	public static function rest_overview() {
		return rest_ensure_response( self::overview() );
	}

	public static function rest_courses() {
		return rest_ensure_response( array( 'courses' => self::course_stats() ) );
	}

	public static function rest_learners( \WP_REST_Request $request ) {
		return rest_ensure_response( array( 'learners' => self::learner_list( (int) ( $request['per_page'] ?: 50 ) ) ) );
	}

	public static function rest_learner( \WP_REST_Request $request ) {
		$data = self::learner_detail( (int) $request['id'] );
		if ( ! $data ) {
			return new \WP_REST_Response( array( 'error' => 'Learner not found.' ), 404 );
		}
		return rest_ensure_response( $data );
	}

	/* --- Aggregates --- */

	public static function overview(): array {
		global $wpdb;
		$enroll   = Tables::enrollments_table_name();
		$progress = Tables::progress_table_name();

		return array(
			'learners'        => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$enroll}" ),
			'enrollments'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$enroll} WHERE status = 'active'" ),
			'completions'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$progress}" ),
			'active_7d'       => (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$progress} WHERE completed_at >= %s", gmdate( 'Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS ) )
			),
			'completions_14d' => self::completions_per_day( 14 ),
			'activity'        => self::recent_activity( 12 ),

			// Everything below answers "is the platform working?" rather than
			// "how many rows are in a table". A dashboard of raw counts tells
			// you nothing you can act on.
			'subscribers'     => self::subscriber_count(),
			'content'         => self::content_counts(),
			'feedback'        => self::feedback_summary(),
			'ads'             => self::ad_summary(),
			'funnel'          => self::funnel(),
		);
	}

	/** Learners holding an active platform subscription. */
	public static function subscriber_count(): int {
		global $wpdb;

		$enroll = Tables::enrollments_table_name();
		$now    = current_time( 'mysql', true );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM {$enroll}
				  WHERE object_type = 'platform' AND status = 'active'
				    AND ( expires_at IS NULL OR expires_at > %s )",
				$now
			)
		);
	}

	/** How much there is to learn — the supply side. */
	public static function content_counts(): array {
		global $wpdb;

		$sections = $wpdb->prefix . 'jsl_sections';

		return array(
			'courses'   => (int) wp_count_posts( 'course' )->publish,
			'lessons'   => (int) wp_count_posts( 'lesson' )->publish,
			'paths'     => (int) wp_count_posts( 'learning_path' )->publish,
			'companies' => post_type_exists( 'company' ) ? (int) wp_count_posts( 'company' )->publish : 0,
			'help'      => post_type_exists( 'help_article' ) ? (int) wp_count_posts( 'help_article' )->publish : 0,
			'sections'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$sections}" ),
		);
	}

	/**
	 * Reaction totals plus the worst-rated piece of content.
	 *
	 * The single worst item is more useful on a dashboard than a ratio: it is
	 * the thing to go and fix this week.
	 */
	public static function feedback_summary(): array {
		global $wpdb;

		if ( ! class_exists( 'Guide\\Community\\Feedback' ) ) {
			return array( 'up' => 0, 'down' => 0, 'unread' => 0, 'worst' => null );
		}

		$table = \Guide\Community\Feedback::table_name();

		$row = $wpdb->get_row(
			"SELECT SUM(sentiment='up') AS ups, SUM(sentiment='down') AS downs
			   FROM {$table} WHERE object_type <> 'roadmap_item'",
			ARRAY_A
		);

		$worst = $wpdb->get_row(
			"SELECT object_id, SUM(sentiment='down') AS downs
			   FROM {$table}
			  WHERE object_type <> 'roadmap_item'
			  GROUP BY object_id
			 HAVING downs > 0
			  ORDER BY downs DESC LIMIT 1",
			ARRAY_A
		);

		return array(
			'up'     => (int) ( $row['ups'] ?? 0 ),
			'down'   => (int) ( $row['downs'] ?? 0 ),
			'unread' => \Guide\Community\Feedback::unread_count(),
			'worst'  => $worst && get_post( (int) $worst['object_id'] )
				? array(
					'title' => get_the_title( (int) $worst['object_id'] ),
					'link'  => (string) get_permalink( (int) $worst['object_id'] ),
					'downs' => (int) $worst['downs'],
				)
				: null,
		);
	}

	/** Ad and sponsorship delivery over the last 30 days. */
	public static function ad_summary(): array {
		if ( ! class_exists( 'Guide\\Sponsors\\Sponsor_Stats' ) ) {
			return array( 'slots' => array(), 'live' => 0, 'pending' => 0 );
		}

		$live = get_posts(
			array(
				'post_type'      => \Guide\Sponsors\Sponsorship::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'meta_query'     => array( array( 'key' => 'jsl_sponsor_status', 'value' => 'live' ) ),
			)
		);

		$pending = get_posts(
			array(
				'post_type'      => \Guide\Sponsors\Sponsorship::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'meta_query'     => array( array( 'key' => 'jsl_sponsor_status', 'value' => 'submitted' ) ),
			)
		);

		return array(
			'slots'   => \Guide\Sponsors\Sponsor_Stats::by_slot( 30 ),
			'live'    => count( $live ),
			'pending' => count( $pending ),
		);
	}

	/**
	 * The drop-off that actually matters: signed up → started → finished
	 * something. Enrolments without completions is the number that says the
	 * content is not landing.
	 */
	public static function funnel(): array {
		global $wpdb;

		$enroll   = Tables::enrollments_table_name();
		$progress = Tables::progress_table_name();

		$registered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$enrolled   = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$enroll}" );
		$started    = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$progress}" );

		$finished = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM (
				SELECT user_id, COUNT(*) AS done FROM {$progress} GROUP BY user_id HAVING done >= 5
			 ) AS t"
		);

		return array(
			'registered' => $registered,
			'enrolled'   => $enrolled,
			'started'    => $started,
			'engaged'    => $finished,
		);
	}

	/**
	 * Completions per day for the last N days (oldest first).
	 *
	 * @return array<int, array{date:string, count:int}>
	 */
	public static function completions_per_day( int $days, int $user_id = 0 ): array {
		global $wpdb;
		$progress = Tables::progress_table_name();
		$since    = gmdate( 'Y-m-d', time() - ( $days - 1 ) * DAY_IN_SECONDS );

		$where  = $wpdb->prepare( 'DATE(completed_at) >= %s', $since );
		$where .= $user_id ? $wpdb->prepare( ' AND user_id = %d', $user_id ) : '';

		$rows = $wpdb->get_results(
			"SELECT DATE(completed_at) AS day, COUNT(*) AS n FROM {$progress} WHERE {$where} GROUP BY day",
			OBJECT_K
		);

		$out = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$day   = gmdate( 'Y-m-d', time() - $i * DAY_IN_SECONDS );
			$out[] = array(
				'date'  => $day,
				'count' => isset( $rows[ $day ] ) ? (int) $rows[ $day ]->n : 0,
			);
		}
		return $out;
	}

	/**
	 * @return array<int, array{user:string, lesson:string, course:string, when:string}>
	 */
	public static function recent_activity( int $limit ): array {
		global $wpdb;
		$progress = Tables::progress_table_name();

		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT user_id, lesson_id, course_id, completed_at FROM {$progress} ORDER BY completed_at DESC LIMIT %d", $limit )
		);

		return array_map(
			function ( $row ) {
				$user = get_userdata( (int) $row->user_id );
				return array(
					'user_id' => (int) $row->user_id,
					'user'    => $user ? $user->display_name : __( 'Deleted user', 'guide-lms' ),
					'lesson'  => wp_specialchars_decode( get_the_title( (int) $row->lesson_id ), ENT_QUOTES ),
					'course'  => wp_specialchars_decode( get_the_title( (int) $row->course_id ), ENT_QUOTES ),
					'when'    => human_time_diff( strtotime( $row->completed_at ), current_time( 'timestamp' ) ),
				);
			},
			$rows
		);
	}

	/**
	 * Per-course stats: enrolled learners, avg progress %, completion count.
	 */
	public static function course_stats(): array {
		global $wpdb;
		$enroll   = Tables::enrollments_table_name();
		$progress = Tables::progress_table_name();

		$courses = get_posts( array( 'post_type' => 'course', 'posts_per_page' => -1, 'post_status' => array( 'publish', 'draft' ) ) );

		$enrolled_by_course = $wpdb->get_results(
			"SELECT object_id, COUNT(*) AS n FROM {$enroll} WHERE object_type = 'course' AND status = 'active' GROUP BY object_id",
			OBJECT_K
		);
		$completed_by_course = $wpdb->get_results(
			"SELECT course_id, COUNT(*) AS n FROM {$progress} GROUP BY course_id",
			OBJECT_K
		);

		return array_map(
			function ( $course ) use ( $enrolled_by_course, $completed_by_course ) {
				$stats     = class_exists( 'Guide\\Course_Api' ) ? \Guide\Course_Api::get_stats( $course->ID ) : array( 'modules' => 0, 'lessons' => 0, 'minutes' => 0 );
				$enrolled  = isset( $enrolled_by_course[ $course->ID ] ) ? (int) $enrolled_by_course[ $course->ID ]->n : 0;
				$completed = isset( $completed_by_course[ $course->ID ] ) ? (int) $completed_by_course[ $course->ID ]->n : 0;
				$possible  = $enrolled * max( 1, $stats['lessons'] );

				return array(
					'id'           => $course->ID,
					'title'        => wp_specialchars_decode( get_the_title( $course ), ENT_QUOTES ),
					'status'       => $course->post_status,
					'modules'      => $stats['modules'],
					'lessons'      => $stats['lessons'],
					'enrolled'     => $enrolled,
					'completions'  => $completed,
					'avg_progress' => $enrolled && $stats['lessons'] ? (int) round( min( 1, $completed / $possible ) * 100 ) : 0,
				);
			},
			$courses
		);
	}

	/**
	 * Learners with enrollment/progress rollups, most recently active first.
	 */
	public static function learner_list( int $limit = 50 ): array {
		global $wpdb;
		$enroll   = Tables::enrollments_table_name();
		$progress = Tables::progress_table_name();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.user_id,
					COUNT(DISTINCT e.object_id) AS enrollments,
					COALESCE(p.completed, 0) AS completed,
					p.last_active
				FROM {$enroll} e
				LEFT JOIN (
					SELECT user_id, COUNT(*) AS completed, MAX(completed_at) AS last_active
					FROM {$progress} GROUP BY user_id
				) p ON p.user_id = e.user_id
				WHERE e.status = 'active'
				GROUP BY e.user_id
				ORDER BY p.last_active DESC
				LIMIT %d",
				$limit
			)
		);

		return array_values( array_filter( array_map(
			function ( $row ) {
				$user = get_userdata( (int) $row->user_id );
				if ( ! $user ) {
					return null;
				}
				return array(
					'id'          => (int) $row->user_id,
					'name'        => $user->display_name,
					'email'       => $user->user_email,
					'avatar'      => get_avatar_url( $user->ID, array( 'size' => 64 ) ),
					'enrollments' => (int) $row->enrollments,
					'completed'   => (int) $row->completed,
					'last_active' => $row->last_active ? human_time_diff( strtotime( $row->last_active ), current_time( 'timestamp' ) ) : null,
					'registered'  => gmdate( 'M j, Y', strtotime( $user->user_registered ) ),
				);
			},
			$rows
		) ) );
	}

	/**
	 * One learner's full profile: identity + per-course progress + activity.
	 */
	public static function learner_detail( int $user_id ): ?array {
		global $wpdb;
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}

		$enroll  = Tables::enrollments_table_name();
		$courses = $wpdb->get_results(
			$wpdb->prepare( "SELECT object_id, enrolled_at, source FROM {$enroll} WHERE user_id = %d AND object_type = 'course' AND status = 'active' ORDER BY enrolled_at DESC", $user_id )
		);

		$course_progress = array_map(
			function ( $row ) use ( $user_id ) {
				$course_id = (int) $row->object_id;
				$p         = class_exists( 'Guide\\Progress\\Progress' )
					? \Guide\Progress\Progress::course_progress( $user_id, $course_id )
					: array( 'completed' => 0, 'total' => 0, 'percent' => 0 );

				return array(
					'id'          => $course_id,
					'title'       => wp_specialchars_decode( get_the_title( $course_id ), ENT_QUOTES ),
					'source'      => $row->source,
					'enrolled_at' => gmdate( 'M j, Y', strtotime( $row->enrolled_at ) ),
					'completed'   => $p['completed'],
					'total'       => $p['total'],
					'percent'     => $p['percent'],
				);
			},
			$courses
		);

		$progress_table = Tables::progress_table_name();
		$total_done     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$progress_table} WHERE user_id = %d", $user_id ) );
		$last_active    = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(completed_at) FROM {$progress_table} WHERE user_id = %d", $user_id ) );

		$recent = $wpdb->get_results(
			$wpdb->prepare( "SELECT lesson_id, course_id, completed_at FROM {$progress_table} WHERE user_id = %d ORDER BY completed_at DESC LIMIT 10", $user_id )
		);

		return array(
			'id'          => $user_id,
			'name'        => $user->display_name,
			'email'       => $user->user_email,
			'avatar'      => get_avatar_url( $user_id, array( 'size' => 128 ) ),
			'registered'  => gmdate( 'M j, Y', strtotime( $user->user_registered ) ),
			'last_active' => $last_active ? human_time_diff( strtotime( $last_active ), current_time( 'timestamp' ) ) : null,
			'total_done'  => $total_done,
			'courses'     => $course_progress,
			'days_14'     => self::completions_per_day( 14, $user_id ),
			'activity'    => array_map(
				function ( $row ) {
					return array(
						'lesson' => wp_specialchars_decode( get_the_title( (int) $row->lesson_id ), ENT_QUOTES ),
						'course' => wp_specialchars_decode( get_the_title( (int) $row->course_id ), ENT_QUOTES ),
						'when'   => human_time_diff( strtotime( $row->completed_at ), current_time( 'timestamp' ) ),
					);
				},
				$recent
			),
		);
	}
}
