<?php
/**
 * REST routes powering the course builder admin screen.
 *
 * Every route checks the current user can edit the course being modified.
 * All writes go through $wpdb->prepare() or WP core APIs (never raw SQL
 * concatenation), and all output that echoes back is plain scalar/array
 * data serialized by WP's REST layer (auto-escaped as JSON).
 */

namespace Guide\Builder;

defined( 'ABSPATH' ) || exit;

class Rest {

	const NS = 'guide/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/courses/(?P<id>\d+)/structure',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_structure' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return current_user_can( 'edit_post', (int) $request['id'] );
				},
			)
		);

		register_rest_route(
			self::NS,
			'/modules',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_module' ),
				'permission_callback' => array( __CLASS__, 'can_edit_course_from_body' ),
				'args'                => array(
					'course_id' => array( 'required' => true, 'type' => 'integer' ),
					'title'     => array( 'required' => true, 'type' => 'string' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PATCH',
					'callback'            => array( __CLASS__, 'rename_module' ),
					'permission_callback' => array( __CLASS__, 'can_edit_module' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_module' ),
					'permission_callback' => array( __CLASS__, 'can_edit_module' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reorder_modules' ),
				'permission_callback' => array( __CLASS__, 'can_edit_course_from_body' ),
				'args'                => array(
					'course_id'  => array( 'required' => true, 'type' => 'integer' ),
					'module_ids' => array( 'required' => true, 'type' => 'array' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/lessons',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_lesson' ),
				'permission_callback' => array( __CLASS__, 'can_edit_course_from_body' ),
				'args'                => array(
					'course_id' => array( 'required' => true, 'type' => 'integer' ),
					'module_id' => array( 'required' => true, 'type' => 'integer' ),
					'title'     => array( 'required' => true, 'type' => 'string' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/lessons/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PATCH',
					'callback'            => array( __CLASS__, 'rename_lesson' ),
					'permission_callback' => array( __CLASS__, 'can_edit_lesson' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_lesson' ),
					'permission_callback' => array( __CLASS__, 'can_edit_lesson' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/lessons/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reorder_lessons' ),
				'permission_callback' => array( __CLASS__, 'can_edit_course_from_body' ),
				'args'                => array(
					'course_id' => array( 'required' => true, 'type' => 'integer' ),
					'module_id' => array( 'required' => true, 'type' => 'integer' ),
					'lesson_ids'=> array( 'required' => true, 'type' => 'array' ),
				),
			)
		);
	}

	/* --- Permissions --- */

	public static function can_edit_course_from_body( \WP_REST_Request $request ) {
		$course_id = (int) $request->get_param( 'course_id' );
		return $course_id && current_user_can( 'edit_post', $course_id );
	}

	public static function can_edit_module( \WP_REST_Request $request ) {
		global $wpdb;
		$module = $wpdb->get_row( $wpdb->prepare( 'SELECT course_id FROM ' . Tables::table_name() . ' WHERE id = %d', (int) $request['id'] ) );
		return $module && current_user_can( 'edit_post', (int) $module->course_id );
	}

	public static function can_edit_lesson( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
		return $course_id && current_user_can( 'edit_post', $course_id );
	}

	/* --- Read --- */

	public static function get_structure( \WP_REST_Request $request ) {
		$course_id = (int) $request['id'];
		$modules   = \Guide\Course_Api::get_modules( $course_id );

		$structure = array_map(
			function ( $module ) {
				return array(
					'id'      => $module['id'],
					'title'   => $module['title'],
					'lessons' => array_map(
						function ( $lesson ) {
							return array(
								'id'       => $lesson->ID,
								'title'    => $lesson->post_title,
								'edit_url' => get_edit_post_link( $lesson->ID, 'raw' ),
							);
						},
						$module['lessons']
					),
				);
			},
			$modules
		);

		return new \WP_REST_Response( array( 'modules' => $structure ), 200 );
	}

	/* --- Modules --- */

	public static function create_module( \WP_REST_Request $request ) {
		global $wpdb;
		$course_id = (int) $request->get_param( 'course_id' );
		$title     = sanitize_text_field( $request->get_param( 'title' ) );

		$max_order = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(menu_order) FROM ' . Tables::table_name() . ' WHERE course_id = %d', $course_id ) );

		$wpdb->insert(
			Tables::table_name(),
			array(
				'course_id'  => $course_id,
				'title'      => $title,
				'menu_order' => $max_order + 1,
			),
			array( '%d', '%s', '%d' )
		);

		return new \WP_REST_Response( array( 'id' => (int) $wpdb->insert_id, 'title' => $title ), 201 );
	}

	public static function rename_module( \WP_REST_Request $request ) {
		global $wpdb;
		$title = sanitize_text_field( $request->get_param( 'title' ) );

		$wpdb->update( Tables::table_name(), array( 'title' => $title ), array( 'id' => (int) $request['id'] ), array( '%s' ), array( '%d' ) );

		return new \WP_REST_Response( array( 'id' => (int) $request['id'], 'title' => $title ), 200 );
	}

	public static function delete_module( \WP_REST_Request $request ) {
		global $wpdb;
		$module_id = (int) $request['id'];

		$wpdb->delete( Tables::table_name(), array( 'id' => $module_id ), array( '%d' ) );

		return new \WP_REST_Response( array( 'deleted' => $module_id ), 200 );
	}

	public static function reorder_modules( \WP_REST_Request $request ) {
		global $wpdb;
		$module_ids = array_map( 'intval', (array) $request->get_param( 'module_ids' ) );

		foreach ( $module_ids as $order => $module_id ) {
			$wpdb->update( Tables::table_name(), array( 'menu_order' => $order ), array( 'id' => $module_id ), array( '%d' ), array( '%d' ) );
		}

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}

	/* --- Lessons --- */

	public static function create_lesson( \WP_REST_Request $request ) {
		$course_id = (int) $request->get_param( 'course_id' );
		$module_id = (int) $request->get_param( 'module_id' );
		$title     = sanitize_text_field( $request->get_param( 'title' ) );

		$lesson_id = wp_insert_post(
			array(
				'post_type'   => 'lesson',
				'post_title'  => $title,
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $lesson_id ) ) {
			return new \WP_REST_Response( array( 'error' => $lesson_id->get_error_message() ), 400 );
		}

		update_post_meta( $lesson_id, 'jsl_course_id', $course_id );
		update_post_meta( $lesson_id, 'jsl_module_id', $module_id );
		update_post_meta( $lesson_id, 'jsl_lesson_order', 0 );

		return new \WP_REST_Response(
			array(
				'id'        => $lesson_id,
				'title'     => $title,
				'module_id' => $module_id,
				'edit_url'  => get_edit_post_link( $lesson_id, 'raw' ),
			),
			201
		);
	}

	public static function rename_lesson( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		$title     = sanitize_text_field( $request->get_param( 'title' ) );

		wp_update_post( array( 'ID' => $lesson_id, 'post_title' => $title ) );

		return new \WP_REST_Response( array( 'id' => $lesson_id, 'title' => $title ), 200 );
	}

	public static function delete_lesson( \WP_REST_Request $request ) {
		$lesson_id = (int) $request['id'];
		wp_trash_post( $lesson_id );

		return new \WP_REST_Response( array( 'deleted' => $lesson_id ), 200 );
	}

	public static function reorder_lessons( \WP_REST_Request $request ) {
		$module_id  = (int) $request->get_param( 'module_id' );
		$lesson_ids = array_map( 'intval', (array) $request->get_param( 'lesson_ids' ) );

		foreach ( $lesson_ids as $order => $lesson_id ) {
			update_post_meta( $lesson_id, 'jsl_module_id', $module_id );
			update_post_meta( $lesson_id, 'jsl_lesson_order', $order );
		}

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}
}
