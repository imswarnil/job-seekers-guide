<?php
/**
 * Learner leaderboard.
 *
 * This publishes real people's learning activity under their display name,
 * so it is governed by two separate switches:
 *
 *   1. The site owner must turn it on (it ships OFF).
 *   2. Each learner can remove themselves (jsl_leaderboard_opt_out).
 *
 * Only a display name, an initial and aggregate counts are ever exposed —
 * never an email, a username, or which specific courses someone is taking.
 *
 * Rankings are computed from wp_jsl_progress in one grouped query and
 * cached, because this is a public page and the query grows with every
 * lesson every learner completes.
 */

namespace Guide\Leaderboard;

use Guide\Enrollment\Tables;

defined( 'ABSPATH' ) || exit;

class Leaderboard {

	const OPTION_ENABLED = 'jsl_leaderboard_enabled';
	const META_OPT_OUT   = 'jsl_leaderboard_opt_out';

	const CACHE_KEY = 'jsl_leaderboard_rows';
	const CACHE_TTL = 10 * MINUTE_IN_SECONDS;

	public static function init() {
		// Any completion changes the standings.
		add_action( 'jsl_lesson_completed', array( __CLASS__, 'flush' ) );
		add_action( 'profile_update', array( __CLASS__, 'flush' ) );

		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'route' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^leaderboard/?$', 'index.php?jsl_leaderboard=1', 'top' );
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'jsl_leaderboard';
		return $vars;
	}

	/**
	 * Render the leaderboard through a theme template, so the theme owns
	 * how it looks and the plugin only owns the data.
	 */
	public static function route() {
		if ( ! get_query_var( 'jsl_leaderboard' ) ) {
			return;
		}

		if ( ! self::is_enabled() ) {
			status_header( 404 );
			include get_query_template( '404' );
			exit;
		}

		status_header( 200 );

		$template = locate_template( 'leaderboard.php' );

		if ( $template ) {
			include $template;
			exit;
		}

		wp_die( esc_html__( 'The active theme has no leaderboard template.', 'guide-lms' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/leaderboard/opt-out',
			array(
				'methods'             => 'POST',
				'callback'            => function ( \WP_REST_Request $request ) {
					$out = (bool) $request->get_param( 'opt_out' );
					self::set_opt_out( get_current_user_id(), $out );
					return new \WP_REST_Response( array( 'opted_out' => $out ), 200 );
				},
				'permission_callback' => 'is_user_logged_in',
			)
		);
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false );
	}

	public static function url(): string {
		return home_url( '/leaderboard/' );
	}

	public static function flush() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Whether a given learner has removed themselves.
	 */
	public static function has_opted_out( int $user_id ): bool {
		return (bool) get_user_meta( $user_id, self::META_OPT_OUT, true );
	}

	public static function set_opt_out( int $user_id, bool $out ): void {
		if ( $out ) {
			update_user_meta( $user_id, self::META_OPT_OUT, 1 );
		} else {
			delete_user_meta( $user_id, self::META_OPT_OUT );
		}
		self::flush();
	}

	/**
	 * The ranked table.
	 *
	 * @param int $limit How many rows to return.
	 * @return array<int, array{user_id:int, name:string, lessons:int, minutes:int, courses:int, rank:int}>
	 */
	public static function rows( int $limit = 50 ): array {
		$cached = get_transient( self::CACHE_KEY );

		if ( is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		global $wpdb;

		$progress = Tables::progress_table_name();

		// One grouped pass over progress: lessons done and distinct courses
		// touched, per user. Minutes are summed from lesson meta in the same
		// query rather than N+1 per row.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.user_id,
				        COUNT(*) AS lessons,
				        COUNT(DISTINCT p.course_id) AS courses,
				        COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) AS minutes
				 FROM {$progress} p
				 LEFT JOIN {$wpdb->postmeta} pm
				        ON pm.post_id = p.lesson_id
				       AND pm.meta_key = %s
				 GROUP BY p.user_id
				 ORDER BY lessons DESC, minutes DESC
				 LIMIT 200",
				'jsl_duration_minutes'
			),
			ARRAY_A
		);

		$out  = array();
		$rank = 0;

		foreach ( (array) $rows as $row ) {
			$user_id = (int) $row['user_id'];
			$user    = get_userdata( $user_id );

			if ( ! $user || self::has_opted_out( $user_id ) ) {
				continue;
			}

			$out[] = array(
				'user_id' => $user_id,
				'name'    => $user->display_name,
				'lessons' => (int) $row['lessons'],
				'courses' => (int) $row['courses'],
				'minutes' => (int) $row['minutes'],
				'rank'    => ++$rank,
			);
		}

		set_transient( self::CACHE_KEY, $out, self::CACHE_TTL );

		return array_slice( $out, 0, $limit );
	}

	/**
	 * Where one learner sits, for "you are #N" on their own dashboard.
	 * Returns 0 when they are unranked or have opted out.
	 */
	public static function rank_for( int $user_id ): int {
		foreach ( self::rows( 200 ) as $row ) {
			if ( $row['user_id'] === $user_id ) {
				return $row['rank'];
			}
		}
		return 0;
	}
}
