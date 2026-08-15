<?php
/**
 * REST routes for the visual learning-path builder.
 *
 * Paths, and the standalone articles/videos inside them, are created and
 * arranged entirely in the LMS console — no classic editor, no block editor.
 *
 * Every write checks the caller can edit the path (or, for step content, the
 * step's own post), and every query is prepared.
 */

namespace JSL\Builder;

defined( 'ABSPATH' ) || exit;

class Path_Rest {

	const NS = 'jsl/v1';

	/** Step kinds the builder can create inline (all are lessons). */
	const INLINE_TYPES = array( 'article', 'video', 'quiz' );

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/paths',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'list_paths' ),
					'permission_callback' => array( __CLASS__, 'can_edit_paths' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_path' ),
					'permission_callback' => array( __CLASS__, 'can_edit_paths' ),
					'args'                => array(
						'title' => array( 'required' => true, 'type' => 'string' ),
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/paths/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_path' ),
					'permission_callback' => array( __CLASS__, 'can_edit_path' ),
				),
				array(
					'methods'             => 'PATCH',
					'callback'            => array( __CLASS__, 'update_path' ),
					'permission_callback' => array( __CLASS__, 'can_edit_path' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_path' ),
					'permission_callback' => array( __CLASS__, 'can_edit_path' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/paths/(?P<id>\d+)/steps',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'add_step' ),
				'permission_callback' => array( __CLASS__, 'can_edit_path' ),
				'args'                => array(
					'step_type' => array( 'required' => true, 'type' => 'string' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/paths/(?P<id>\d+)/steps/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reorder_steps' ),
				'permission_callback' => array( __CLASS__, 'can_edit_path' ),
				'args'                => array(
					'step_ids' => array( 'required' => true, 'type' => 'array' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/path-steps/(?P<step>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'remove_step' ),
				'permission_callback' => array( __CLASS__, 'can_edit_step' ),
			)
		);

		// Courses not yet placed in this path — the "add a course" picker.
		register_rest_route(
			self::NS,
			'/paths/(?P<id>\d+)/available-courses',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'available_courses' ),
				'permission_callback' => array( __CLASS__, 'can_edit_path' ),
			)
		);
	}

	/* --- Permissions --- */

	public static function can_edit_paths(): bool {
		return current_user_can( 'edit_posts' );
	}

	public static function can_edit_path( \WP_REST_Request $request ): bool {
		$path_id = (int) $request['id'];
		return $path_id && 'learning_path' === get_post_type( $path_id ) && current_user_can( 'edit_post', $path_id );
	}

	public static function can_edit_step( \WP_REST_Request $request ): bool {
		global $wpdb;

		$table   = Path_Tables::table_name();
		$path_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT path_id FROM {$table} WHERE id = %d", (int) $request['step'] ) );

		return $path_id && current_user_can( 'edit_post', $path_id );
	}

	/* --- Paths --- */

	public static function list_paths( \WP_REST_Request $request ) {
		global $wpdb;

		$table = Path_Tables::table_name();

		$paths = get_posts(
			array(
				'post_type'      => 'learning_path',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);

		$out = array();

		foreach ( $paths as $path ) {
			$out[] = array(
				'id'        => (int) $path->ID,
				'title'     => $path->post_title,
				'excerpt'   => $path->post_excerpt,
				'status'    => $path->post_status,
				'permalink' => (string) get_permalink( $path ),
				'steps'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE path_id = %d", $path->ID ) ),
			);
		}

		return new \WP_REST_Response( array( 'paths' => $out ), 200 );
	}

	public static function create_path( \WP_REST_Request $request ) {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );

		$path_id = wp_insert_post(
			array(
				'post_type'   => 'learning_path',
				'post_title'  => $title,
				'post_status' => 'draft',
			),
			true
		);

		if ( is_wp_error( $path_id ) ) {
			return new \WP_REST_Response( array( 'error' => $path_id->get_error_message() ), 400 );
		}

		return new \WP_REST_Response( array( 'id' => (int) $path_id, 'title' => $title, 'status' => 'draft' ), 201 );
	}

	public static function update_path( \WP_REST_Request $request ) {
		$path_id = (int) $request['id'];
		$fields  = array( 'ID' => $path_id );

		if ( null !== $request->get_param( 'title' ) ) {
			$fields['post_title'] = sanitize_text_field( (string) $request->get_param( 'title' ) );
		}

		if ( null !== $request->get_param( 'excerpt' ) ) {
			$fields['post_excerpt'] = sanitize_textarea_field( (string) $request->get_param( 'excerpt' ) );
		}

		if ( null !== $request->get_param( 'status' ) ) {
			$status = (string) $request->get_param( 'status' );
			$fields['post_status'] = in_array( $status, array( 'publish', 'draft' ), true ) ? $status : 'draft';
		}

		wp_update_post( $fields );

		return new \WP_REST_Response( array( 'ok' => true, 'id' => $path_id ), 200 );
	}

	public static function delete_path( \WP_REST_Request $request ) {
		global $wpdb;

		$path_id = (int) $request['id'];

		// Remove the ordering rows; the referenced courses and lessons are
		// deliberately left alone — a path is an arrangement of content, and
		// deleting the arrangement must not delete the content.
		$wpdb->delete( Path_Tables::table_name(), array( 'path_id' => $path_id ), array( '%d' ) );
		wp_trash_post( $path_id );

		return new \WP_REST_Response( array( 'deleted' => $path_id ), 200 );
	}

	public static function get_path( \WP_REST_Request $request ) {
		$path_id = (int) $request['id'];
		$path    = get_post( $path_id );

		return new \WP_REST_Response(
			array(
				'id'        => $path_id,
				'title'     => $path->post_title,
				'excerpt'   => $path->post_excerpt,
				'status'    => $path->post_status,
				'permalink' => (string) get_permalink( $path_id ),
				'steps'     => \JSL\Course_Api::get_path_steps( $path_id, true ),
			),
			200
		);
	}

	/* --- Steps --- */

	/**
	 * Add a step. Either references an existing course
	 * (step_type=course, object_id=…) or creates a new standalone lesson
	 * inline (step_type=article|video|quiz, title=…) — which is how a path
	 * can contain a plain article or a single video without a course.
	 */
	public static function add_step( \WP_REST_Request $request ) {
		global $wpdb;

		$path_id = (int) $request['id'];
		$type    = sanitize_key( (string) $request->get_param( 'step_type' ) );

		if ( 'course' === $type ) {
			$object_id = (int) $request->get_param( 'object_id' );

			if ( 'course' !== get_post_type( $object_id ) ) {
				return new \WP_REST_Response( array( 'error' => __( 'That course does not exist.', 'job-seekers-lms' ) ), 404 );
			}

			// Adding a course to a path writes jsl_path_id onto the course, so
			// it takes edit rights on the course as well as on the path.
			if ( ! current_user_can( 'edit_post', $object_id ) ) {
				return new \WP_REST_Response( array( 'error' => __( 'You cannot add that course.', 'job-seekers-lms' ) ), 403 );
			}

			$step_type = 'course';
		} elseif ( in_array( $type, self::INLINE_TYPES, true ) ) {
			$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
			$title = $title ?: __( 'Untitled', 'job-seekers-lms' );

			$object_id = wp_insert_post(
				array(
					'post_type'   => 'lesson',
					'post_title'  => $title,
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $object_id ) ) {
				return new \WP_REST_Response( array( 'error' => $object_id->get_error_message() ), 400 );
			}

			// No jsl_course_id: this lesson belongs to the path, not a course,
			// so it lives at /library/{slug} and is open to everyone.
			update_post_meta( $object_id, 'jsl_lesson_type', $type );

			$step_type = 'lesson';
		} else {
			return new \WP_REST_Response( array( 'error' => __( 'Unknown step type.', 'job-seekers-lms' ) ), 400 );
		}

		$table     = Path_Tables::table_name();
		$max_order = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX(menu_order) FROM {$table} WHERE path_id = %d", $path_id ) );

		$wpdb->insert(
			$table,
			array(
				'path_id'    => $path_id,
				'step_type'  => $step_type,
				'object_id'  => (int) $object_id,
				'menu_order' => $max_order + 1,
			),
			array( '%d', '%s', '%d', '%d' )
		);

		// Keep the legacy meta in sync so anything still reading
		// Course_Api::get_path_courses() sees the same arrangement.
		if ( 'course' === $step_type ) {
			update_post_meta( $object_id, 'jsl_path_id', $path_id );
		}

		return new \WP_REST_Response(
			array(
				'step_id'   => (int) $wpdb->insert_id,
				'step_type' => $step_type,
				'object_id' => (int) $object_id,
				'title'     => get_the_title( $object_id ),
			),
			201
		);
	}

	public static function remove_step( \WP_REST_Request $request ) {
		global $wpdb;

		$table   = Path_Tables::table_name();
		$step_id = (int) $request['step'];

		$step = $wpdb->get_row( $wpdb->prepare( "SELECT step_type, object_id FROM {$table} WHERE id = %d", $step_id ) );

		$wpdb->delete( $table, array( 'id' => $step_id ), array( '%d' ) );

		if ( $step && 'course' === $step->step_type ) {
			delete_post_meta( (int) $step->object_id, 'jsl_path_id' );
		}

		return new \WP_REST_Response( array( 'deleted' => $step_id ), 200 );
	}

	public static function reorder_steps( \WP_REST_Request $request ) {
		global $wpdb;

		$path_id  = (int) $request['id'];
		$step_ids = array_map( 'intval', (array) $request->get_param( 'step_ids' ) );
		$table    = Path_Tables::table_name();

		foreach ( $step_ids as $order => $step_id ) {
			// path_id in the WHERE clause: a step can only be reordered
			// within the path the caller was authorized for.
			$wpdb->update(
				$table,
				array( 'menu_order' => $order ),
				array( 'id' => $step_id, 'path_id' => $path_id ),
				array( '%d' ),
				array( '%d', '%d' )
			);

			$step = $wpdb->get_row( $wpdb->prepare( "SELECT step_type, object_id FROM {$table} WHERE id = %d", $step_id ) );
			if ( $step && 'course' === $step->step_type ) {
				wp_update_post( array( 'ID' => (int) $step->object_id, 'menu_order' => $order ) );
			}
		}

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}

	public static function available_courses( \WP_REST_Request $request ) {
		global $wpdb;

		$path_id = (int) $request['id'];
		$table   = Path_Tables::table_name();

		$used = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$table} WHERE path_id = %d AND step_type = 'course'", $path_id ) );
		$used = array_map( 'intval', (array) $used );

		$courses = get_posts(
			array(
				'post_type'      => 'course',
				'posts_per_page' => 100,
				'post_status'    => array( 'publish', 'draft' ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$out = array();

		foreach ( $courses as $course ) {
			if ( in_array( (int) $course->ID, $used, true ) ) {
				continue;
			}
			$out[] = array(
				'id'     => (int) $course->ID,
				'title'  => $course->post_title,
				'status' => $course->post_status,
			);
		}

		return new \WP_REST_Response( array( 'courses' => $out ), 200 );
	}
}
