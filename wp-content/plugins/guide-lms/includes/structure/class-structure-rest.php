<?php
/**
 * REST routes for the section library and the outline.
 *
 * These are what make the loose coupling usable from the console: list the
 * sections that exist, create one, and place existing sections/courses/lessons
 * into a course or a path.
 *
 * Everything here requires `edit_posts` at minimum, and anything scoped to a
 * specific course or path checks `edit_post` on that object — a contributor
 * must not be able to restructure someone else's course by posting an id.
 */

namespace Guide\Structure;

defined( 'ABSPATH' ) || exit;

class Structure_Rest {

	const NS = 'guide/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_edit_content(): bool {
		return current_user_can( 'edit_posts' );
	}

	public static function register_routes() {

		// ---- Section library -------------------------------------------------

		register_rest_route(
			self::NS,
			'/sections',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'list_sections' ),
					'permission_callback' => array( __CLASS__, 'can_edit_content' ),
					'args'                => array(
						'search' => array( 'type' => 'string' ),
						'shared' => array( 'type' => 'boolean' ),
					),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_section' ),
					'permission_callback' => array( __CLASS__, 'can_edit_content' ),
					'args'                => array(
						'title'  => array( 'required' => true, 'type' => 'string' ),
						'shared' => array( 'type' => 'boolean' ),
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/sections/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_section' ),
					'permission_callback' => array( __CLASS__, 'can_edit_content' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_section' ),
					'permission_callback' => array( __CLASS__, 'can_edit_content' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_section' ),
					'permission_callback' => array( __CLASS__, 'can_edit_content' ),
				),
			)
		);

		// ---- Outline ---------------------------------------------------------

		register_rest_route(
			self::NS,
			'/outline/(?P<type>course|path|section)/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_outline' ),
					'permission_callback' => array( __CLASS__, 'can_edit_container' ),
				),
				array(
					// Replace the whole ordering. Sending the full new order is
					// far less error-prone than a diff, and these lists are small.
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'set_outline' ),
					'permission_callback' => array( __CLASS__, 'can_edit_container' ),
					'args'                => array(
						'items' => array( 'required' => true, 'type' => 'array' ),
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/outline/(?P<type>course|path|section)/(?P<id>\d+)/place',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'place_item' ),
				'permission_callback' => array( __CLASS__, 'can_edit_container' ),
				'args'                => array(
					'item_type' => array( 'required' => true, 'type' => 'string' ),
					'item_id'   => array( 'required' => true, 'type' => 'integer' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/outline/(?P<type>course|path|section)/(?P<id>\d+)/remove',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'remove_item' ),
				'permission_callback' => array( __CLASS__, 'can_edit_container' ),
				'args'                => array(
					'item_type' => array( 'required' => true, 'type' => 'string' ),
					'item_id'   => array( 'required' => true, 'type' => 'integer' ),
				),
			)
		);

		// ---- Lesson search, for pulling a lesson in from any course ----------

		register_rest_route(
			self::NS,
			'/lesson-library',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'lesson_library' ),
				'permission_callback' => array( __CLASS__, 'can_edit_content' ),
				'args'                => array(
					'search' => array( 'type' => 'string' ),
					'course' => array( 'type' => 'integer' ),
				),
			)
		);
	}

	/**
	 * A section is site-wide content, so editing one needs `edit_posts`; a
	 * course or path is a specific object, so it needs rights on that object.
	 */
	public static function can_edit_container( \WP_REST_Request $request ): bool {
		$type = (string) $request['type'];
		$id   = (int) $request['id'];

		if ( 'section' === $type ) {
			return current_user_can( 'edit_posts' );
		}

		return current_user_can( 'edit_post', $id );
	}

	// -------------------------------------------------------------------------
	// Sections
	// -------------------------------------------------------------------------

	public static function list_sections( \WP_REST_Request $request ) {
		$sections = Structure::section_library(
			(bool) $request->get_param( 'shared' ),
			(string) $request->get_param( 'search' )
		);

		return new \WP_REST_Response( array_map( array( __CLASS__, 'shape_section' ), $sections ), 200 );
	}

	public static function get_section( \WP_REST_Request $request ) {
		$section = Structure::get_section( (int) $request['id'] );

		if ( ! $section ) {
			return new \WP_REST_Response( array( 'error' => __( 'Section not found.', 'guide-lms' ) ), 404 );
		}

		$shaped            = self::shape_section( $section );
		$shaped['lessons'] = array_map(
			static function ( $lesson ) {
				return array(
					'id'    => (int) $lesson->ID,
					'title' => get_the_title( $lesson ),
					'type'  => (string) get_post_meta( $lesson->ID, 'jsl_lesson_type', true ) ?: 'article',
				);
			},
			Structure::section_lessons( (int) $section['id'] )
		);

		return new \WP_REST_Response( $shaped, 200 );
	}

	public static function create_section( \WP_REST_Request $request ) {
		$id = Structure::create_section(
			(string) $request->get_param( 'title' ),
			(bool) $request->get_param( 'shared' ),
			(string) $request->get_param( 'description' )
		);

		if ( ! $id ) {
			return new \WP_REST_Response( array( 'error' => __( 'A title is required.', 'guide-lms' ) ), 400 );
		}

		// Optionally place it straight into a container, which is what the
		// builder's "add section" button wants in one round trip.
		$container_type = (string) $request->get_param( 'container_type' );
		$container_id   = (int) $request->get_param( 'container_id' );

		if ( $container_type && $container_id && self::may_edit( $container_type, $container_id ) ) {
			$order = Structure::count_items( $container_type, $container_id );
			Structure_Tables::place( $container_type, $container_id, Structure_Tables::ITEM_SECTION, $id, $order );
		}

		return new \WP_REST_Response( self::shape_section( Structure::get_section( $id ) ), 201 );
	}

	public static function update_section( \WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$fields = array();

		foreach ( array( 'title', 'description', 'shared' ) as $key ) {
			if ( null !== $request->get_param( $key ) ) {
				$fields[ $key ] = $request->get_param( $key );
			}
		}

		if ( ! Structure::update_section( $id, $fields ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Nothing to update.', 'guide-lms' ) ), 400 );
		}

		return new \WP_REST_Response( self::shape_section( Structure::get_section( $id ) ), 200 );
	}

	/**
	 * Delete a section. The lessons inside it survive — they are content in
	 * their own right and very likely used elsewhere.
	 */
	public static function delete_section( \WP_REST_Request $request ) {
		$id = (int) $request['id'];

		$usages = Structure::count_usages( Structure_Tables::ITEM_SECTION, $id );

		// Deleting a section used in several places is almost always a
		// mistake, so it has to be asked for explicitly.
		if ( $usages > 1 && ! $request->get_param( 'force' ) ) {
			return new \WP_REST_Response(
				array(
					'error'   => sprintf(
						/* translators: %d: number of courses and paths using this section. */
						__( 'This section is used in %d places. Removing it here is usually what you want; pass force to delete it everywhere.', 'guide-lms' ),
						$usages
					),
					'usages'  => $usages,
					'confirm' => true,
				),
				409
			);
		}

		Structure::delete_section( $id );

		return new \WP_REST_Response( array( 'deleted' => true ), 200 );
	}

	// -------------------------------------------------------------------------
	// Outline
	// -------------------------------------------------------------------------

	public static function get_outline( \WP_REST_Request $request ) {
		$type = (string) $request['type'];
		$id   = (int) $request['id'];

		$out = array();

		foreach ( Structure::outline( $type, $id ) as $entry ) {
			$row = array(
				'type'  => $entry['type'],
				'id'    => $entry['id'],
				'title' => $entry['title'],
			);

			if ( 'section' === $entry['type'] ) {
				$row['shared']  = $entry['shared'];
				$row['used_in'] = Structure::count_usages( Structure_Tables::ITEM_SECTION, (int) $entry['id'] );
				$row['lessons'] = array_map(
					static function ( $lesson ) {
						return array(
							'id'    => (int) $lesson->ID,
							'title' => get_the_title( $lesson ),
							'type'  => (string) get_post_meta( $lesson->ID, 'jsl_lesson_type', true ) ?: 'article',
						);
					},
					$entry['lessons']
				);
			}

			$out[] = $row;
		}

		return new \WP_REST_Response( $out, 200 );
	}

	public static function set_outline( \WP_REST_Request $request ) {
		$items = (array) $request->get_param( 'items' );
		$clean = array();

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$clean[] = array(
				'item_type' => isset( $item['item_type'] ) ? sanitize_key( (string) $item['item_type'] ) : '',
				'item_id'   => isset( $item['item_id'] ) ? (int) $item['item_id'] : 0,
			);
		}

		Structure::set_contents( (string) $request['type'], (int) $request['id'], $clean );

		return new \WP_REST_Response( array( 'saved' => true ), 200 );
	}

	public static function place_item( \WP_REST_Request $request ) {
		$container_type = (string) $request['type'];
		$container_id   = (int) $request['id'];
		$item_type      = sanitize_key( (string) $request->get_param( 'item_type' ) );
		$item_id        = (int) $request->get_param( 'item_id' );

		if ( ! self::item_exists( $item_type, $item_id ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'That item does not exist.', 'guide-lms' ) ), 404 );
		}

		$order = null !== $request->get_param( 'menu_order' )
			? (int) $request->get_param( 'menu_order' )
			: Structure::count_items( $container_type, $container_id );

		Structure_Tables::place( $container_type, $container_id, $item_type, $item_id, $order );

		return new \WP_REST_Response( array( 'placed' => true ), 200 );
	}

	public static function remove_item( \WP_REST_Request $request ) {
		Structure_Tables::remove(
			(string) $request['type'],
			(int) $request['id'],
			sanitize_key( (string) $request->get_param( 'item_type' ) ),
			(int) $request->get_param( 'item_id' )
		);

		return new \WP_REST_Response( array( 'removed' => true ), 200 );
	}

	// -------------------------------------------------------------------------
	// Lesson library
	// -------------------------------------------------------------------------

	/**
	 * Every lesson on the site, so one can be pulled into any section
	 * regardless of which course it was originally written for.
	 */
	public static function lesson_library( \WP_REST_Request $request ) {
		$args = array(
			'post_type'      => 'lesson',
			'posts_per_page' => 100,
			'post_status'    => array( 'publish', 'draft' ),
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);

		$search = (string) $request->get_param( 'search' );

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		$course = (int) $request->get_param( 'course' );

		if ( $course ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'jsl_course_id',
					'value' => $course,
				),
			);
		}

		$out = array();

		foreach ( get_posts( $args ) as $lesson ) {
			$home = (int) get_post_meta( $lesson->ID, 'jsl_course_id', true );

			$out[] = array(
				'id'      => (int) $lesson->ID,
				'title'   => get_the_title( $lesson ),
				'type'    => (string) get_post_meta( $lesson->ID, 'jsl_lesson_type', true ) ?: 'article',
				'status'  => $lesson->post_status,
				'course'  => $home ? get_the_title( $home ) : '',
				'used_in' => Structure::count_usages( Structure_Tables::ITEM_LESSON, (int) $lesson->ID ),
			);
		}

		return new \WP_REST_Response( $out, 200 );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private static function may_edit( string $container_type, int $container_id ): bool {
		if ( Structure_Tables::CONTAINER_SECTION === $container_type ) {
			return current_user_can( 'edit_posts' );
		}

		return current_user_can( 'edit_post', $container_id );
	}

	private static function item_exists( string $item_type, int $item_id ): bool {
		if ( Structure_Tables::ITEM_SECTION === $item_type ) {
			return (bool) Structure::get_section( $item_id );
		}

		$post = get_post( $item_id );

		if ( ! $post ) {
			return false;
		}

		$expected = Structure_Tables::ITEM_COURSE === $item_type ? 'course' : 'lesson';

		return $expected === $post->post_type;
	}

	/**
	 * @param array<string,mixed>|null $section
	 * @return array<string,mixed>
	 */
	private static function shape_section( $section ): array {
		if ( ! $section ) {
			return array();
		}

		$id = (int) $section['id'];

		return array(
			'id'           => $id,
			'title'        => (string) $section['title'],
			'description'  => (string) ( $section['description'] ?? '' ),
			'shared'       => (bool) $section['shared'],
			'lesson_count' => isset( $section['lesson_count'] )
				? (int) $section['lesson_count']
				: Structure::count_items( Structure_Tables::CONTAINER_SECTION, $id, Structure_Tables::ITEM_LESSON ),
			'used_in'      => isset( $section['used_in'] )
				? (int) $section['used_in']
				: Structure::count_usages( Structure_Tables::ITEM_SECTION, $id ),
		);
	}
}
