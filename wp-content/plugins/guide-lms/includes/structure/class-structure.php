<?php
/**
 * Reading and writing the loosely-coupled outline.
 *
 * Everything that renders a course, a path, or a section asks this class. It
 * is the only place that knows how the outline table is shaped, so the storage
 * can change again without touching a template.
 *
 * See class-structure-tables.php for why the model looks like this.
 */

namespace Guide\Structure;

defined( 'ABSPATH' ) || exit;

class Structure {

	/** Guards against a section that somehow contains itself. */
	const MAX_DEPTH = 4;

	public static function init() {
		// Deleting a lesson or course must not leave placements pointing at it.
		add_action( 'deleted_post', array( __CLASS__, 'on_post_deleted' ), 10, 2 );
	}

	public static function on_post_deleted( $post_id, $post = null ) {
		$type = $post instanceof \WP_Post ? $post->post_type : get_post_type( $post_id );

		if ( 'lesson' === $type ) {
			Structure_Tables::forget_item( Structure_Tables::ITEM_LESSON, (int) $post_id );
		} elseif ( 'course' === $type ) {
			Structure_Tables::forget_item( Structure_Tables::ITEM_COURSE, (int) $post_id );
			// A course's own placements go too; its sections survive, because
			// they may well be used somewhere else.
			self::clear_container( Structure_Tables::CONTAINER_COURSE, (int) $post_id );
		} elseif ( 'learning_path' === $type ) {
			self::clear_container( Structure_Tables::CONTAINER_PATH, (int) $post_id );
		}
	}

	// -------------------------------------------------------------------------
	// Sections
	// -------------------------------------------------------------------------

	/**
	 * @return array<string,mixed>|null
	 */
	public static function get_section( int $section_id ) {
		global $wpdb;

		if ( ! $section_id ) {
			return null;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . Structure_Tables::sections_table() . ' WHERE id = %d', $section_id ),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	public static function create_section( string $title, bool $shared = false, string $description = '' ): int {
		global $wpdb;

		$title = trim( wp_strip_all_tags( $title ) );

		if ( '' === $title ) {
			return 0;
		}

		$now = current_time( 'mysql', true );

		$ok = $wpdb->insert(
			Structure_Tables::sections_table(),
			array(
				'title'       => mb_substr( $title, 0, 255 ),
				'description' => $description ? wp_strip_all_tags( $description ) : null,
				'shared'      => $shared ? 1 : 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);

		return $ok ? (int) $wpdb->insert_id : 0;
	}

	public static function update_section( int $section_id, array $fields ): bool {
		global $wpdb;

		$data = array();

		if ( isset( $fields['title'] ) ) {
			$title = trim( wp_strip_all_tags( (string) $fields['title'] ) );
			if ( '' === $title ) {
				return false;
			}
			$data['title'] = mb_substr( $title, 0, 255 );
		}

		if ( array_key_exists( 'description', $fields ) ) {
			$data['description'] = $fields['description'] ? wp_strip_all_tags( (string) $fields['description'] ) : null;
		}

		if ( isset( $fields['shared'] ) ) {
			$data['shared'] = $fields['shared'] ? 1 : 0;
		}

		if ( ! $data ) {
			return false;
		}

		$data['updated_at'] = current_time( 'mysql', true );

		return false !== $wpdb->update( Structure_Tables::sections_table(), $data, array( 'id' => $section_id ) );
	}

	/**
	 * Delete a section and every placement that mentions it.
	 *
	 * The lessons inside it are NOT deleted — they are content in their own
	 * right and are very likely used elsewhere. That is the whole point of the
	 * model, and it is also the behaviour least likely to destroy work by
	 * accident.
	 */
	public static function delete_section( int $section_id ): bool {
		global $wpdb;

		Structure_Tables::forget_item( Structure_Tables::ITEM_SECTION, $section_id );

		return false !== $wpdb->delete( Structure_Tables::sections_table(), array( 'id' => $section_id ) );
	}

	/**
	 * Sections available to reuse, newest first.
	 *
	 * @param bool $shared_only Only sections explicitly marked reusable.
	 * @return array<int, array<string,mixed>>
	 */
	public static function section_library( bool $shared_only = false, string $search = '', int $limit = 100 ): array {
		global $wpdb;

		$table = Structure_Tables::sections_table();
		$where = array( '1=1' );
		$args  = array();

		if ( $shared_only ) {
			$where[] = 'shared = 1';
		}

		if ( '' !== $search ) {
			$where[] = 'title LIKE %s';
			$args[]  = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' AND ', $where ) . ' ORDER BY updated_at DESC LIMIT %d';
		$args[] = max( 1, min( 500, $limit ) );

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( ! is_array( $rows ) ) {
			return array();
		}

		// The counts are what make the library usable — "Operating Systems
		// (6 lessons, used in 2 courses)" is a decision; a bare title is not.
		foreach ( $rows as $i => $row ) {
			$rows[ $i ]['lesson_count'] = self::count_items( Structure_Tables::CONTAINER_SECTION, (int) $row['id'], Structure_Tables::ITEM_LESSON );
			$rows[ $i ]['used_in']      = self::count_usages( Structure_Tables::ITEM_SECTION, (int) $row['id'] );
		}

		return $rows;
	}

	// -------------------------------------------------------------------------
	// Placements
	// -------------------------------------------------------------------------

	/**
	 * The raw contents of a container, in order.
	 *
	 * @return array<int, array{item_type:string, item_id:int, menu_order:int}>
	 */
	public static function contents( string $container_type, int $container_id ): array {
		global $wpdb;

		if ( ! $container_id ) {
			return array();
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT item_type, item_id, menu_order FROM ' . Structure_Tables::outline_table() . '
				  WHERE container_type = %s AND container_id = %d
				  ORDER BY menu_order ASC, id ASC',
				$container_type,
				$container_id
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		return array_map(
			static function ( $row ) {
				return array(
					'item_type'  => (string) $row['item_type'],
					'item_id'    => (int) $row['item_id'],
					'menu_order' => (int) $row['menu_order'],
				);
			},
			$rows
		);
	}

	public static function count_items( string $container_type, int $container_id, string $item_type = '' ): int {
		global $wpdb;

		$sql  = 'SELECT COUNT(*) FROM ' . Structure_Tables::outline_table() . ' WHERE container_type = %s AND container_id = %d';
		$args = array( $container_type, $container_id );

		if ( $item_type ) {
			$sql   .= ' AND item_type = %s';
			$args[] = $item_type;
		}

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/** How many containers reference this item — i.e. how reused it is. */
	public static function count_usages( string $item_type, int $item_id ): int {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . Structure_Tables::outline_table() . ' WHERE item_type = %s AND item_id = %d',
				$item_type,
				$item_id
			)
		);
	}

	/**
	 * Which containers hold this item. Used to warn before an edit that will
	 * change something in more than one place.
	 *
	 * @return array<int, array{container_type:string, container_id:int}>
	 */
	public static function usages( string $item_type, int $item_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT container_type, container_id FROM ' . Structure_Tables::outline_table() . '
				  WHERE item_type = %s AND item_id = %d',
				$item_type,
				$item_id
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	public static function clear_container( string $container_type, int $container_id ): bool {
		global $wpdb;

		return false !== $wpdb->delete(
			Structure_Tables::outline_table(),
			array(
				'container_type' => $container_type,
				'container_id'   => $container_id,
			)
		);
	}

	/**
	 * Replace a container's contents wholesale, in the order given.
	 *
	 * Used by the builder when a drag finishes: sending the whole new order is
	 * far less error-prone than sending a diff, and the table is small.
	 *
	 * @param array<int, array{item_type:string, item_id:int}> $items
	 */
	public static function set_contents( string $container_type, int $container_id, array $items ): bool {
		if ( ! $container_id ) {
			return false;
		}

		self::clear_container( $container_type, $container_id );

		$order = 0;
		foreach ( $items as $item ) {
			$type = isset( $item['item_type'] ) ? (string) $item['item_type'] : '';
			$id   = isset( $item['item_id'] ) ? (int) $item['item_id'] : 0;

			if ( ! in_array( $type, array( Structure_Tables::ITEM_SECTION, Structure_Tables::ITEM_COURSE, Structure_Tables::ITEM_LESSON ), true ) ) {
				continue;
			}

			Structure_Tables::place( $container_type, $container_id, $type, $id, $order );
			++$order;
		}

		return true;
	}

	// -------------------------------------------------------------------------
	// Resolved views
	// -------------------------------------------------------------------------

	/**
	 * A container resolved into renderable sections and their lessons.
	 *
	 * Works for a course or a path. A path may also hold whole courses and
	 * loose lessons, which are returned as their own entries so the caller can
	 * decide how to draw them.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	public static function outline( string $container_type, int $container_id, int $depth = 0 ): array {
		if ( $depth >= self::MAX_DEPTH ) {
			return array();
		}

		$out = array();

		foreach ( self::contents( $container_type, $container_id ) as $entry ) {
			switch ( $entry['item_type'] ) {

				case Structure_Tables::ITEM_SECTION:
					$section = self::get_section( $entry['item_id'] );

					if ( ! $section ) {
						break;
					}

					$out[] = array(
						'type'        => 'section',
						'id'          => (int) $section['id'],
						'title'       => $section['title'],
						'description' => $section['description'],
						'shared'      => (bool) $section['shared'],
						'lessons'     => self::section_lessons( (int) $section['id'] ),
					);
					break;

				case Structure_Tables::ITEM_COURSE:
					$course = get_post( $entry['item_id'] );

					if ( ! $course || 'course' !== $course->post_type ) {
						break;
					}

					$out[] = array(
						'type'     => 'course',
						'id'       => (int) $course->ID,
						'title'    => get_the_title( $course ),
						'post'     => $course,
						// One level down: the course's own sections, so a path
						// player can walk straight through them.
						'sections' => self::outline( Structure_Tables::CONTAINER_COURSE, (int) $course->ID, $depth + 1 ),
					);
					break;

				case Structure_Tables::ITEM_LESSON:
					$lesson = get_post( $entry['item_id'] );

					if ( ! $lesson || 'lesson' !== $lesson->post_type ) {
						break;
					}

					$out[] = array(
						'type'  => 'lesson',
						'id'    => (int) $lesson->ID,
						'title' => get_the_title( $lesson ),
						'post'  => $lesson,
					);
					break;
			}
		}

		return $out;
	}

	/**
	 * The published lessons in a section, in order.
	 *
	 * @return \WP_Post[]
	 */
	public static function section_lessons( int $section_id ): array {
		$ids = array();

		foreach ( self::contents( Structure_Tables::CONTAINER_SECTION, $section_id ) as $entry ) {
			if ( Structure_Tables::ITEM_LESSON === $entry['item_type'] ) {
				$ids[] = $entry['item_id'];
			}
		}

		if ( ! $ids ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'        => 'lesson',
				'post__in'         => $ids,
				'orderby'          => 'post__in',
				'posts_per_page'   => -1,
				'post_status'      => 'publish',
				'suppress_filters' => false,
			)
		);

		// get_posts() with post__in respects our order, but a lesson that has
		// been unpublished simply drops out — which is what we want.
		return $posts;
	}

	/**
	 * Every lesson in a container, flattened into the order a learner meets
	 * them. This is what drives "lesson 7 of 14", prev/next, and progress.
	 *
	 * @return \WP_Post[]
	 */
	public static function flatten_lessons( string $container_type, int $container_id, int $depth = 0 ): array {
		if ( $depth >= self::MAX_DEPTH ) {
			return array();
		}

		$lessons = array();

		foreach ( self::contents( $container_type, $container_id ) as $entry ) {
			switch ( $entry['item_type'] ) {
				case Structure_Tables::ITEM_LESSON:
					$post = get_post( $entry['item_id'] );
					if ( $post && 'lesson' === $post->post_type && 'publish' === $post->post_status ) {
						$lessons[] = $post;
					}
					break;

				case Structure_Tables::ITEM_SECTION:
					foreach ( self::section_lessons( $entry['item_id'] ) as $post ) {
						$lessons[] = $post;
					}
					break;

				case Structure_Tables::ITEM_COURSE:
					foreach ( self::flatten_lessons( Structure_Tables::CONTAINER_COURSE, $entry['item_id'], $depth + 1 ) as $post ) {
						$lessons[] = $post;
					}
					break;
			}
		}

		// A lesson reused twice in one path would otherwise be counted twice
		// and break "lesson 7 of 14". First occurrence wins.
		$seen   = array();
		$unique = array();

		foreach ( $lessons as $lesson ) {
			if ( isset( $seen[ $lesson->ID ] ) ) {
				continue;
			}
			$seen[ $lesson->ID ] = true;
			$unique[]            = $lesson;
		}

		return $unique;
	}
}
