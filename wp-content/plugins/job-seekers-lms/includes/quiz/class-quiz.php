<?php
/**
 * Quiz engine for quiz-type lessons.
 *
 * Quiz data lives in the jsl_quiz post meta as JSON and is deliberately
 * NOT registered in REST — correct answers must never reach the browser.
 *
 *   { "pass": 70, "questions": [
 *       { "q": "…", "options": ["a","b","c","d"], "correct": 1, "explain": "…" }
 *   ] }
 *
 * Routes:
 *  GET  jsl/v1/lessons/{id}/quiz        public questions (options only, no answers)
 *  POST jsl/v1/lessons/{id}/quiz/grade  logged-in; grades server-side, marks the
 *                                       lesson complete on a passing score
 *  GET  jsl/v1/lessons/{id}/quiz-admin  editors: full quiz for the builder
 *  POST jsl/v1/lessons/{id}/quiz-admin  editors: save full quiz
 */

namespace JSL\Quiz;

defined( 'ABSPATH' ) || exit;

class Quiz {

	const META_KEY = 'jsl_quiz';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'jsl/v1',
			'/lessons/(?P<id>\d+)/quiz',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_public' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'jsl/v1',
			'/lessons/(?P<id>\d+)/quiz/grade',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'grade' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'answers' => array( 'required' => true, 'type' => 'array' ),
				),
			)
		);

		register_rest_route(
			'jsl/v1',
			'/lessons/(?P<id>\d+)/quiz-admin',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_admin' ),
					'permission_callback' => array( __CLASS__, 'can_edit' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_admin' ),
					'permission_callback' => array( __CLASS__, 'can_edit' ),
				),
			)
		);
	}

	public static function can_edit( \WP_REST_Request $request ) {
		return current_user_can( 'edit_post', (int) $request['id'] );
	}

	/* --- Data --- */

	public static function get_quiz( int $lesson_id ): array {
		$raw  = get_post_meta( $lesson_id, self::META_KEY, true );
		$data = $raw ? json_decode( $raw, true ) : null;

		if ( ! is_array( $data ) || empty( $data['questions'] ) || ! is_array( $data['questions'] ) ) {
			return array( 'pass' => 70, 'questions' => array() );
		}

		$data['pass'] = isset( $data['pass'] ) ? max( 1, min( 100, (int) $data['pass'] ) ) : 70;
		return $data;
	}

	/**
	 * Deep-sanitize an incoming quiz payload before storing.
	 */
	private static function sanitize_quiz( $input ): array {
		$out = array(
			'pass'      => isset( $input['pass'] ) ? max( 1, min( 100, (int) $input['pass'] ) ) : 70,
			'questions' => array(),
		);

		foreach ( (array) ( $input['questions'] ?? array() ) as $question ) {
			$options = array_slice( array_map( 'sanitize_text_field', (array) ( $question['options'] ?? array() ) ), 0, 6 );
			$options = array_values( array_filter( $options, 'strlen' ) );

			if ( count( $options ) < 2 || empty( $question['q'] ) ) {
				continue;
			}

			$out['questions'][] = array(
				'q'       => sanitize_text_field( $question['q'] ),
				'options' => $options,
				'correct' => min( max( 0, (int) ( $question['correct'] ?? 0 ) ), count( $options ) - 1 ),
				'explain' => sanitize_text_field( $question['explain'] ?? '' ),
			);
		}

		return $out;
	}

	/* --- REST --- */

	public static function get_public( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		if ( 'lesson' !== get_post_type( $lesson_id ) || 'publish' !== get_post_status( $lesson_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Not found.' ), 404 );
		}

		$quiz = self::get_quiz( $lesson_id );

		return rest_ensure_response(
			array(
				'pass'      => $quiz['pass'],
				'questions' => array_map(
					function ( $question ) {
						return array(
							'q'       => $question['q'],
							'options' => $question['options'],
						);
					},
					$quiz['questions']
				),
			)
		);
	}

	public static function grade( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		if ( 'lesson' !== get_post_type( $lesson_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Not found.' ), 404 );
		}

		$quiz = self::get_quiz( $lesson_id );
		if ( empty( $quiz['questions'] ) ) {
			return new \WP_REST_Response( array( 'error' => 'No quiz on this lesson.' ), 400 );
		}

		$answers = array_map( 'intval', (array) $request->get_param( 'answers' ) );
		$total   = count( $quiz['questions'] );
		$correct = 0;
		$review  = array();

		foreach ( $quiz['questions'] as $i => $question ) {
			$given = $answers[ $i ] ?? -1;
			$is_ok = $given === $question['correct'];
			if ( $is_ok ) {
				$correct++;
			}
			$review[] = array(
				'correct_index' => $question['correct'],
				'is_correct'    => $is_ok,
				'explain'       => $question['explain'],
			);
		}

		$score  = (int) round( $correct / $total * 100 );
		$passed = $score >= $quiz['pass'];

		$progress = null;
		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
		if ( $passed && $course_id && class_exists( 'JSL\\Progress\\Progress' ) ) {
			\JSL\Progress\Progress::complete( get_current_user_id(), $lesson_id, $course_id );
			$progress = \JSL\Progress\Progress::course_progress( get_current_user_id(), $course_id );
		}

		return rest_ensure_response(
			array(
				'score'    => $score,
				'passed'   => $passed,
				'pass'     => $quiz['pass'],
				'correct'  => $correct,
				'total'    => $total,
				'review'   => $review,
				'progress' => $progress,
			)
		);
	}

	public static function get_admin( \WP_REST_Request $request ) {
		return rest_ensure_response( self::get_quiz( (int) $request['id'] ) );
	}

	public static function save_admin( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		$quiz      = self::sanitize_quiz( $request->get_json_params() );

		update_post_meta( $lesson_id, self::META_KEY, wp_slash( wp_json_encode( $quiz, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );

		return rest_ensure_response( $quiz );
	}
}
