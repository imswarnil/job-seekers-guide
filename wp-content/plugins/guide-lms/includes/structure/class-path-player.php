<?php
/**
 * The learning-path player.
 *
 * A path is a curated sequence that may cut across several courses, so walking
 * it needs its own navigation. Without this, a learner who reached lesson 4 of
 * a path would hit the *course's* "next lesson" at the end of a borrowed
 * section and be quietly dropped out of the path they were following.
 *
 * The player therefore keeps the path in the URL:
 *
 *   /learning-paths/{path}/learn/{lesson}/
 *
 * The lesson content is identical to its canonical course URL — the same post,
 * the same progress record. Only the surrounding navigation differs. That is
 * the point of the loose coupling: one lesson, many arrangements.
 */

namespace Guide\Structure;

use Guide\Access\Access;
use Guide\Progress\Progress;

defined( 'ABSPATH' ) || exit;

class Path_Player {

	const QUERY_PATH   = 'guide_path_learn';
	const QUERY_LESSON = 'guide_path_lesson';

	const REWRITE_OPTION  = 'jsl_path_player_rewrite';
	const REWRITE_VERSION = '1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_action( 'template_include', array( __CLASS__, 'use_template' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_flush_rewrites' ) );
	}

	public static function add_rewrite_rules() {
		$base = self::path_base();

		add_rewrite_rule(
			'^' . $base . '/([^/]+)/learn/([^/]+)/?$',
			'index.php?' . self::QUERY_PATH . '=$matches[1]&' . self::QUERY_LESSON . '=$matches[2]',
			'top'
		);

		// No lesson given: send them to the first one they have not finished.
		add_rewrite_rule(
			'^' . $base . '/([^/]+)/learn/?$',
			'index.php?' . self::QUERY_PATH . '=$matches[1]',
			'top'
		);
	}

	/**
	 * The path archive's URL base, so the player never drifts from wherever
	 * the CPT is actually registered.
	 */
	private static function path_base(): string {
		$object = get_post_type_object( 'learning_path' );
		$slug   = $object && ! empty( $object->rewrite['slug'] ) ? $object->rewrite['slug'] : 'learning-paths';

		return trim( (string) $slug, '/' );
	}

	public static function register_query_vars( $vars ) {
		$vars[] = self::QUERY_PATH;
		$vars[] = self::QUERY_LESSON;
		return $vars;
	}

	public static function maybe_flush_rewrites() {
		if ( get_option( self::REWRITE_OPTION ) === self::REWRITE_VERSION ) {
			return;
		}

		self::add_rewrite_rules();
		flush_rewrite_rules();
		update_option( self::REWRITE_OPTION, self::REWRITE_VERSION, false );
	}

	/** The player URL for a lesson within a path. */
	public static function lesson_url( int $path_id, int $lesson_id ): string {
		$path   = get_post( $path_id );
		$lesson = get_post( $lesson_id );

		if ( ! $path || ! $lesson ) {
			return '';
		}

		return home_url( '/' . self::path_base() . '/' . $path->post_name . '/learn/' . $lesson->post_name . '/' );
	}

	public static function start_url( int $path_id ): string {
		$path = get_post( $path_id );

		return $path ? home_url( '/' . self::path_base() . '/' . $path->post_name . '/learn/' ) : '';
	}

	/**
	 * Resolve the request into the path, its lessons, and the current one.
	 *
	 * @return array{path:\WP_Post, lesson:\WP_Post, lessons:\WP_Post[], index:int}|null
	 */
	public static function current() {
		static $resolved = null;

		if ( null !== $resolved ) {
			return $resolved ?: null;
		}

		$resolved = false;

		$path_slug = get_query_var( self::QUERY_PATH );

		if ( ! $path_slug ) {
			return null;
		}

		$path = get_page_by_path( $path_slug, OBJECT, 'learning_path' );

		if ( ! $path || 'publish' !== $path->post_status ) {
			return null;
		}

		$lessons = Structure::flatten_lessons( Structure_Tables::CONTAINER_PATH, (int) $path->ID );

		if ( ! $lessons ) {
			return null;
		}

		$lesson_slug = get_query_var( self::QUERY_LESSON );
		$lesson      = null;
		$index       = 0;

		if ( $lesson_slug ) {
			foreach ( $lessons as $i => $candidate ) {
				if ( $candidate->post_name === $lesson_slug ) {
					$lesson = $candidate;
					$index  = $i;
					break;
				}
			}

			// A lesson that is not part of this path is not an error worth a
			// 404 — it is usually a stale bookmark from before the path was
			// re-curated. Start them at the beginning instead.
			if ( ! $lesson ) {
				$lesson = $lessons[0];
				$index  = 0;
			}
		} else {
			$index  = self::resume_index( (int) $path->ID, $lessons );
			$lesson = $lessons[ $index ];
		}

		$resolved = array(
			'path'    => $path,
			'lesson'  => $lesson,
			'lessons' => $lessons,
			'index'   => $index,
		);

		return $resolved;
	}

	/**
	 * The first lesson the learner has not completed, or the first one.
	 *
	 * @param \WP_Post[] $lessons
	 */
	private static function resume_index( int $path_id, array $lessons ): int {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! class_exists( 'Guide\\Progress\\Progress' ) ) {
			return 0;
		}

		$done = self::completed_ids( $user_id, $lessons );

		foreach ( $lessons as $i => $lesson ) {
			if ( ! isset( $done[ (int) $lesson->ID ] ) ) {
				return $i;
			}
		}

		return 0;
	}

	/**
	 * Which of these lessons the user has completed.
	 *
	 * Progress is recorded per lesson, not per path, so a lesson finished
	 * inside its own course already counts here. Following a path never asks
	 * anyone to redo work they have done.
	 *
	 * @param \WP_Post[] $lessons
	 * @return array<int,bool> Keyed by lesson ID.
	 */
	public static function completed_ids( int $user_id, array $lessons ): array {
		if ( ! $user_id || ! $lessons || ! class_exists( 'Guide\\Progress\\Progress' ) ) {
			return array();
		}

		$done    = array();
		$courses = array();

		// Group by canonical course so we make one progress query per course
		// rather than one per lesson.
		foreach ( $lessons as $lesson ) {
			$course_id = (int) get_post_meta( $lesson->ID, 'jsl_course_id', true );
			if ( $course_id ) {
				$courses[ $course_id ] = true;
			}
		}

		foreach ( array_keys( $courses ) as $course_id ) {
			foreach ( Progress::completed_lesson_ids( $user_id, $course_id ) as $lesson_id ) {
				$done[ (int) $lesson_id ] = true;
			}
		}

		return $done;
	}

	/**
	 * Progress through the path, as a percentage.
	 *
	 * @param \WP_Post[] $lessons
	 */
	public static function percent( int $user_id, array $lessons ): int {
		if ( ! $lessons ) {
			return 0;
		}

		$done  = self::completed_ids( $user_id, $lessons );
		$count = 0;

		foreach ( $lessons as $lesson ) {
			if ( isset( $done[ (int) $lesson->ID ] ) ) {
				++$count;
			}
		}

		return (int) round( $count / count( $lessons ) * 100 );
	}

	/**
	 * The path outline annotated for the player sidebar: every section and
	 * course heading with its lessons, each flagged current/complete/locked.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	public static function sidebar( int $path_id, int $current_lesson_id, int $user_id ): array {
		$lessons = Structure::flatten_lessons( Structure_Tables::CONTAINER_PATH, $path_id );
		$done    = self::completed_ids( $user_id, $lessons );
		$groups  = array();

		foreach ( Structure::outline( Structure_Tables::CONTAINER_PATH, $path_id ) as $entry ) {

			if ( 'lesson' === $entry['type'] ) {
				$groups[] = array(
					'title'   => '',
					'kind'    => 'loose',
					'lessons' => self::annotate( array( $entry['post'] ), $current_lesson_id, $done, $path_id ),
				);
				continue;
			}

			if ( 'section' === $entry['type'] ) {
				$groups[] = array(
					'title'   => $entry['title'],
					'kind'    => 'section',
					'lessons' => self::annotate( $entry['lessons'], $current_lesson_id, $done, $path_id ),
				);
				continue;
			}

			// A whole course contributes its own sections as groups, labelled
			// with the course so the learner knows where they have wandered.
			foreach ( $entry['sections'] as $section ) {
				if ( 'section' !== $section['type'] ) {
					continue;
				}

				$groups[] = array(
					'title'   => $section['title'],
					'kind'    => 'course',
					'course'  => $entry['title'],
					'lessons' => self::annotate( $section['lessons'], $current_lesson_id, $done, $path_id ),
				);
			}
		}

		return $groups;
	}

	/**
	 * @param \WP_Post[]      $lessons
	 * @param array<int,bool> $done
	 */
	private static function annotate( array $lessons, int $current_id, array $done, int $path_id ): array {
		$out = array();

		foreach ( $lessons as $lesson ) {
			if ( ! $lesson instanceof \WP_Post ) {
				continue;
			}

			$out[] = array(
				'id'       => (int) $lesson->ID,
				'title'    => get_the_title( $lesson ),
				'url'      => self::lesson_url( $path_id, (int) $lesson->ID ),
				'minutes'  => (int) get_post_meta( $lesson->ID, 'jsl_duration_minutes', true ),
				'type'     => (string) get_post_meta( $lesson->ID, 'jsl_lesson_type', true ) ?: 'article',
				'current'  => (int) $lesson->ID === $current_id,
				'complete' => isset( $done[ (int) $lesson->ID ] ),
				'locked'   => class_exists( 'Guide\\Access\\Access' ) && Access::is_locked( (int) $lesson->ID ),
			);
		}

		return $out;
	}

	/**
	 * Hand the request to the path-player template.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function use_template( $template ) {
		if ( ! self::current() ) {
			return $template;
		}

		$located = locate_template( array( 'guide-path-player.php' ) );

		return $located ? $located : $template;
	}
}
