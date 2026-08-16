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

namespace Guide\Builder;

use Guide\Structure\Structure;
use Guide\Structure\Structure_Tables;

defined( 'ABSPATH' ) || exit;

class Path_Rest {

	const NS = 'guide/v1';

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
					'items' => array( 'required' => true, 'type' => 'array' ),
				),
			)
		);

		// Scoped to the path, because a step is now identified by type + id
		// and only means anything inside the path it belongs to.
		register_rest_route(
			self::NS,
			'/paths/(?P<id>\d+)/steps/remove',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'remove_step' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return current_user_can( 'edit_post', (int) $request['id'] );
				},
				'args'                => array(
					'item_type' => array( 'required' => true, 'type' => 'string' ),
					'item_id'   => array( 'required' => true, 'type' => 'integer' ),
				),
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
		Structure::clear_container( Structure_Tables::CONTAINER_PATH, $path_id );
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
				'steps'     => \Guide\Course_Api::get_path_steps( $path_id, true ),
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
				return new \WP_REST_Response( array( 'error' => __( 'That course does not exist.', 'guide-lms' ) ), 404 );
			}

			// Adding a course to a path writes jsl_path_id onto the course, so
			// it takes edit rights on the course as well as on the path.
			if ( ! current_user_can( 'edit_post', $object_id ) ) {
				return new \WP_REST_Response( array( 'error' => __( 'You cannot add that course.', 'guide-lms' ) ), 403 );
			}

			$step_type = 'course';
		} elseif ( in_array( $type, self::INLINE_TYPES, true ) ) {
			$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
			$title = $title ?: __( 'Untitled', 'guide-lms' );

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
			return new \WP_REST_Response( array( 'error' => __( 'Unknown step type.', 'guide-lms' ) ), 400 );
		}

		// The outline table is the source of truth now — see
		// includes/structure/class-structure-tables.php. Writing here and
		// reading from the outline is what makes reuse possible.
		$order = Structure::count_items( Structure_Tables::CONTAINER_PATH, $path_id );

		Structure_Tables::place(
			Structure_Tables::CONTAINER_PATH,
			$path_id,
			$step_type,
			(int) $object_id,
			$order
		);

		// Keep the legacy meta in sync so anything still reading
		// Course_Api::get_path_courses() sees the same arrangement.
		if ( 'course' === $step_type ) {
			update_post_meta( $object_id, 'jsl_path_id', $path_id );
		}

		return new \WP_REST_Response(
			array(
				'step_type' => $step_type,
				'object_id' => (int) $object_id,
				'title'     => get_the_title( $object_id ),
			),
			201
		);
	}

	/**
	 * Remove a step from a path.
	 *
	 * Identified by type + id rather than a row id: a path can now hold
	 * sections as well as posts, and section ids and post ids are separate
	 * sequences that can collide. A bare number is ambiguous.
	 *
	 * The item itself is never deleted — it is very likely used elsewhere.
	 */
	public static function remove_step( \WP_REST_Request $request ) {
		$path_id   = (int) $request['id'];
		$item_type = sanitize_key( (string) $request->get_param( 'item_type' ) );
		$item_id   = (int) $request->get_param( 'item_id' );

		if ( ! in_array( $item_type, array( 'course', 'lesson', 'section' ), true ) || ! $item_id ) {
			return new \WP_REST_Response( array( 'error' => __( 'Unknown step.', 'guide-lms' ) ), 400 );
		}

		Structure_Tables::remove( Structure_Tables::CONTAINER_PATH, $path_id, $item_type, $item_id );

		if ( 'course' === $item_type ) {
			delete_post_meta( $item_id, 'jsl_path_id' );
		}

		return new \WP_REST_Response( array( 'deleted' => true ), 200 );
	}

	/**
	 * Replace a path's ordering wholesale.
	 *
	 * The console sends the entire new order after a drag: it is far less
	 * error-prone than a diff, and these lists are short.
	 */
	public static function reorder_steps( \WP_REST_Request $request ) {
		$path_id = (int) $request['id'];
		$items   = (array) $request->get_param( 'items' );
		$clean   = array();

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$clean[] = array(
				'item_type' => sanitize_key( (string) ( $item['item_type'] ?? '' ) ),
				'item_id'   => (int) ( $item['item_id'] ?? 0 ),
			);
		}

		Structure::set_contents( Structure_Tables::CONTAINER_PATH, $path_id, $clean );

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}

	public static function available_courses( \WP_REST_Request $request ) {
		global $wpdb;

		$path_id = (int) $request['id'];

		$used = array();
		foreach ( Structure::contents( Structure_Tables::CONTAINER_PATH, $path_id ) as $entry ) {
			if ( Structure_Tables::ITEM_COURSE === $entry['item_type'] ) {
				$used[] = (int) $entry['item_id'];
			}
		}

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
