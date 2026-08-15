<?php
/**
 * Minimal read API the theme uses to render course data.
 *
 * Deliberately small for now — full enrollment/progress-aware methods land
 * with the Dodo Payments + enrollment logic phase (see TODO.md). This is
 * enough to prove the theme <-> plugin data boundary works.
 */

namespace JSL;

defined( 'ABSPATH' ) || exit;

class Course_Api {

	/**
	 * Lessons belonging to a course, in author-defined order.
	 *
	 * @param int $course_id Course post ID.
	 * @return \WP_Post[]
	 */
	public static function get_lessons( int $course_id ): array {
		$query = new \WP_Query(
			array(
				'post_type'      => 'lesson',
				'posts_per_page' => -1,
				'meta_key'       => 'jsl_course_id',
				'meta_value'     => $course_id,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		return $query->posts;
	}

	/**
	 * A course's modules, each with its ordered lessons. Same shape the
	 * course-builder REST endpoint returns — this is what the theme
	 * templates consume.
	 *
	 * @param int $course_id Course post ID.
	 * @return array<int, array{id:int, title:string, lessons: \WP_Post[]}>
	 */
	public static function get_modules( int $course_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'jsl_modules';

		$modules = $wpdb->get_results(
			$wpdb->prepare( "SELECT id, title, menu_order FROM {$table} WHERE course_id = %d ORDER BY menu_order ASC", $course_id )
		);

		$result = array();

		foreach ( $modules as $module ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'lesson',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array( 'key' => 'jsl_module_id', 'value' => (int) $module->id ),
					),
					'meta_key'       => 'jsl_lesson_order',
					'orderby'        => 'meta_value_num',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				)
			);

			$result[] = array(
				'id'      => (int) $module->id,
				'title'   => $module->title,
				'lessons' => $query->posts,
			);
		}

		return $result;
	}

	/**
	 * All lessons of a course flattened in learner order: modules in
	 * builder order, lessons in order within each module.
	 *
	 * @return \WP_Post[]
	 */
	public static function get_lessons_flat( int $course_id ): array {
		$flat = array();
		foreach ( self::get_modules( $course_id ) as $module ) {
			foreach ( $module['lessons'] as $lesson ) {
				$flat[] = $lesson;
			}
		}
		return $flat;
	}

	/**
	 * Previous/next lesson around the given lesson, in learner order.
	 *
	 * @return array{prev: ?\WP_Post, next: ?\WP_Post}
	 */
	public static function adjacent_lessons( int $course_id, int $lesson_id ): array {
		$flat = self::get_lessons_flat( $course_id );

		foreach ( $flat as $i => $lesson ) {
			if ( (int) $lesson->ID === $lesson_id ) {
				return array(
					'prev' => $flat[ $i - 1 ] ?? null,
					'next' => $flat[ $i + 1 ] ?? null,
				);
			}
		}

		return array( 'prev' => null, 'next' => null );
	}

	/**
	 * Aggregate stats for a course card / hero.
	 *
	 * @return array{modules:int, lessons:int, minutes:int}
	 */
	public static function get_stats( int $course_id ): array {
		$modules = self::get_modules( $course_id );
		$lessons = 0;
		$minutes = 0;

		foreach ( $modules as $module ) {
			foreach ( $module['lessons'] as $lesson ) {
				$lessons++;
				$minutes += (int) get_post_meta( $lesson->ID, 'jsl_duration_minutes', true );
			}
		}

		return array(
			'modules' => count( $modules ),
			'lessons' => $lessons,
			'minutes' => $minutes,
		);
	}

	/**
	 * Courses belonging to a learning path, in author-defined order.
	 *
	 * @param int $path_id Learning path post ID.
	 * @return \WP_Post[]
	 */
	public static function get_path_courses( int $path_id ): array {
		$query = new \WP_Query(
			array(
				'post_type'      => 'course',
				'posts_per_page' => -1,
				'meta_key'       => 'jsl_path_id',
				'meta_value'     => $path_id,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		return $query->posts;
	}
}
