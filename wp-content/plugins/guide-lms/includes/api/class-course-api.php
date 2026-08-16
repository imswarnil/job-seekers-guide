<?php
/**
 * Minimal read API the theme uses to render course data.
 *
 * Deliberately small for now — full enrollment/progress-aware methods land
 * with the Dodo Payments + enrollment logic phase (see TODO.md). This is
 * enough to prove the theme <-> plugin data boundary works.
 */

namespace Guide;

use Guide\Structure\Structure;
use Guide\Structure\Structure_Tables;

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
		$result = array();

		// Sections come from the outline table now, so a section can appear in
		// more than one course and a lesson in more than one section. The
		// shape returned is unchanged, so every existing caller still works.
		foreach ( Structure::outline( Structure_Tables::CONTAINER_COURSE, $course_id ) as $entry ) {
			if ( 'section' !== $entry['type'] ) {
				continue;
			}

			$result[] = array(
				'id'      => $entry['id'],
				'title'   => $entry['title'],
				'lessons' => $entry['lessons'],
				'shared'  => $entry['shared'],
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
		return Structure::flatten_lessons( Structure_Tables::CONTAINER_COURSE, $course_id );
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
	 * The ordered steps of a learning path.
	 *
	 * A step is a whole course, a section (reused from a course or created for
	 * this path), or a single lesson — which is what lets a path mix "take
	 * this course", "these six lessons from that one", and "read this".
	 *
	 * @param int  $path_id     Learning path post ID.
	 * @param bool $include_ids Retained for call-site compatibility. Steps are
	 *                          now identified by type + id, both of which are
	 *                          always returned, so this no longer changes the
	 *                          shape of the result.
	 * @return array<int, array{type:string, id:int, title:string, permalink:string, status:string, lesson_type:string, post:?\WP_Post}>
	 */
	public static function get_path_steps( int $path_id, bool $include_ids = false ): array {
		$steps = array();

		foreach ( Structure::contents( Structure_Tables::CONTAINER_PATH, $path_id ) as $entry ) {

			// A section placed directly in a path — the thing that lets a path
			// curate its own grouping of lessons rather than only pointing at
			// whole courses.
			if ( Structure_Tables::ITEM_SECTION === $entry['item_type'] ) {
				$section = Structure::get_section( $entry['item_id'] );

				if ( ! $section ) {
					continue;
				}

				$step = array(
					'type'        => 'section',
					'id'          => (int) $section['id'],
					'title'       => $section['title'],
					'permalink'   => '',
					'status'      => 'publish',
					'lesson_type' => '',
					'lessons'     => Structure::section_lessons( (int) $section['id'] ),
					'post'        => null,
				);

				$steps[] = $step;
				continue;
			}

			$post = get_post( $entry['item_id'] );

			// A step whose content was deleted or trashed is skipped rather
			// than rendered as a broken row.
			if ( ! $post || 'trash' === $post->post_status ) {
				continue;
			}

			$step = array(
				'type'        => (string) $entry['item_type'],
				'id'          => (int) $post->ID,
				'title'       => $post->post_title,
				'permalink'   => (string) get_permalink( $post ),
				'status'      => $post->post_status,
				'lesson_type' => Structure_Tables::ITEM_LESSON === $entry['item_type']
					? ( (string) get_post_meta( $post->ID, 'jsl_lesson_type', true ) ?: 'article' )
					: '',
				'post'        => $post,
			);

			$steps[] = $step;
		}

		return $steps;
	}

	/**
	 * Courses belonging to a learning path, in author-defined order.
	 *
	 * Reads the path-steps table (the source of truth since the visual path
	 * builder landed) and falls back to the older jsl_path_id meta for any
	 * path that predates it and has no steps yet.
	 *
	 * @param int $path_id Learning path post ID.
	 * @return \WP_Post[]
	 */
	public static function get_path_courses( int $path_id ): array {
		$courses = array();

		foreach ( self::get_path_steps( $path_id ) as $step ) {
			if ( 'course' === $step['type'] ) {
				$courses[] = $step['post'];
			}
		}

		if ( ! empty( $courses ) ) {
			return $courses;
		}

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
