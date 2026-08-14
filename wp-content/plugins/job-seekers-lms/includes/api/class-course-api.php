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
