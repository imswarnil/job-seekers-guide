<?php
/**
 * Seed the courses that ship with the plugin.
 *
 * Production deploys by pulling code and restarting. Code travels; the
 * database does not. So a course written on a laptop only exists on that
 * laptop unless it is shipped as code and planted on arrival — which is what
 * this does, from the same version-gated upgrade routine that creates the
 * tables.
 *
 * ---------------------------------------------------------------------------
 * The rule that makes this safe to run on every upgrade:
 *
 *   **Seeded content is only ever replaced while it is still exactly as we
 *   seeded it.**
 *
 * Each post records a hash of the content we last wrote. On the next upgrade,
 * if the stored hash still matches what is in the database, the operator has
 * not touched it and we may update it. If it does not match, somebody has
 * edited it in the console — so we leave it completely alone, forever.
 *
 * Without that check, a deploy would silently overwrite a morning's editing,
 * which is the sort of thing that makes people stop trusting their own tools.
 *
 * Nothing here ever deletes a post. The worst case is a course nobody wanted,
 * which can be trashed by hand, rather than writing that somebody has lost.
 */

namespace Guide\Content;

defined( 'ABSPATH' ) || exit;

class Starter_Content {

	/** Bump to re-seed after editing the shipped content files. */
	const VERSION = '1';

	const OPTION_VERSION = 'jsl_starter_content_version';

	/** Hash of the content this plugin last wrote to a given post. */
	const META_HASH = '_jsl_seed_hash';

	/** Marks a post as originally seeded, for reporting. */
	const META_SEEDED = '_jsl_seeded';

	/**
	 * Plant the shipped content if it has not been planted at this version.
	 *
	 * Called from the upgrade routine, so it runs once per deploy that changes
	 * the version and never on an ordinary request.
	 */
	public static function maybe_seed() {
		if ( get_option( self::OPTION_VERSION ) === self::VERSION ) {
			return;
		}

		// Set the marker first. If seeding fails half way — a fatal in one
		// lesson, a database hiccup — the next request must not start again
		// from the top and pile up duplicate sections. A partial seed is
		// recoverable by hand; an infinite loop of them on every page load is
		// considerably worse.
		update_option( self::OPTION_VERSION, self::VERSION, false );

		foreach ( array( 'foundation', 'resume' ) as $file ) {
			$path = GUIDE_PLUGIN_DIR . 'content/' . $file . '.php';

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$course = require $path;

			if ( is_array( $course ) ) {
				self::seed_course( $course );
			}
		}
	}

	/**
	 * @param array<string, mixed> $spec
	 * @return int Course ID, or 0 on failure.
	 */
	public static function seed_course( array $spec ): int {
		$existing  = get_page_by_path( $spec['slug'], OBJECT, 'course' );
		$course_id = self::upsert(
			'course',
			$spec['slug'],
			array(
				'post_title'   => $spec['title'],
				'post_excerpt' => $spec['excerpt'],
				'post_content' => $spec['content'],
				'menu_order'   => (int) ( $spec['menu_order'] ?? 0 ),
			),
			$existing
		);

		if ( ! $course_id ) {
			return 0;
		}

		// Meta is cheap to re-apply and an operator changing a course's tier or
		// header should not be undone, so only set what is not already set —
		// except on first creation, where we own all of it.
		$fresh = ! $existing;

		self::maybe_set_meta( $course_id, 'jsl_course_code', $spec['code'], $fresh );
		self::maybe_set_meta( $course_id, 'jsl_course_level', $spec['level'], $fresh );
		self::maybe_set_meta( $course_id, 'jsl_course_header', $spec['header'], $fresh );
		self::maybe_set_meta( $course_id, 'jsl_pricing_type', $spec['tier'], $fresh );
		self::maybe_set_meta( $course_id, 'jsl_course_outcomes', $spec['outcomes'], $fresh );
		self::maybe_set_meta( $course_id, 'jsl_course_requirements', $spec['requirements'], $fresh );

		self::seed_sections( $course_id, (array) $spec['sections'] );

		return $course_id;
	}

	/**
	 * @param array<int, array<string, mixed>> $sections
	 */
	private static function seed_sections( int $course_id, array $sections ) {
		global $wpdb;

		$sections_table = $wpdb->prefix . 'jsl_sections';
		$outline_table  = $wpdb->prefix . 'jsl_outline';
		$now            = current_time( 'mysql' );

		foreach ( $sections as $order => $section ) {
			// Match an existing section by title. Titles are the only stable
			// identity a section has — the table has no slug — and a duplicate
			// section is much worse than a reused one.
			$section_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$sections_table} WHERE title = %s LIMIT 1", $section['title'] )
			);

			if ( ! $section_id ) {
				$wpdb->insert(
					$sections_table,
					array(
						'title'       => $section['title'],
						'description' => $section['description'],
						'shared'      => 0,
						'created_at'  => $now,
						'updated_at'  => $now,
					)
				);

				$section_id = (int) $wpdb->insert_id;
			}

			if ( ! $section_id ) {
				continue;
			}

			self::place( $outline_table, 'course', $course_id, 'section', $section_id, (int) $order );

			foreach ( (array) $section['lessons'] as $lesson_order => $lesson ) {
				$lesson_id = self::seed_lesson( $lesson, $course_id );

				if ( $lesson_id ) {
					self::place( $outline_table, 'section', $section_id, 'lesson', $lesson_id, (int) $lesson_order );
				}
			}
		}
	}

	/**
	 * @param array<string, mixed> $lesson
	 */
	private static function seed_lesson( array $lesson, int $course_id ): int {
		$existing  = get_page_by_path( $lesson['slug'], OBJECT, 'lesson' );
		$lesson_id = self::upsert(
			'lesson',
			$lesson['slug'],
			array(
				'post_title'   => $lesson['title'],
				'post_excerpt' => $lesson['excerpt'],
				'post_content' => $lesson['content'],
			),
			$existing
		);

		if ( ! $lesson_id ) {
			return 0;
		}

		$fresh = ! $existing;

		self::maybe_set_meta( $lesson_id, 'jsl_course_id', $course_id, $fresh );
		self::maybe_set_meta( $lesson_id, 'jsl_lesson_type', 'article', $fresh );
		self::maybe_set_meta( $lesson_id, 'jsl_duration', (int) $lesson['duration'], $fresh );

		return $lesson_id;
	}

	// -------------------------------------------------------------------------
	// The careful bits
	// -------------------------------------------------------------------------

	/**
	 * Create the post, or update it only if nobody has edited it since we wrote it.
	 *
	 * @param array<string, mixed> $fields
	 * @param \WP_Post|null        $existing
	 * @return int
	 */
	private static function upsert( string $post_type, string $slug, array $fields, $existing ): int {
		if ( $existing ) {
			$stored = (string) get_post_meta( $existing->ID, self::META_HASH, true );

			if ( '' === $stored ) {
				// No hash: either this post predates the seeder, or a human
				// wrote it. Only one of those may be replaced, so ask which.
				if ( ! self::is_demo_content( $existing ) ) {
					return (int) $existing->ID;
				}
			} elseif ( $stored !== self::hash( $existing->post_content ) ) {
				// We wrote it, and it has since been edited. Hands off.
				return (int) $existing->ID;
			}

			$fields['ID'] = $existing->ID;
			$result       = wp_update_post( $fields, true );
		} else {
			$fields['post_type']   = $post_type;
			$fields['post_status'] = 'publish';
			$fields['post_name']   = $slug;
			$result                = wp_insert_post( $fields, true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			return 0;
		}

		update_post_meta( $result, self::META_HASH, self::hash( (string) $fields['post_content'] ) );
		update_post_meta( $result, self::META_SEEDED, 1 );

		return (int) $result;
	}

	/**
	 * Is this post left over from the old demo seeder?
	 *
	 * The `wp jsl seed` command wrote placeholder courses with meta keys that
	 * the console does not use — a lesson created by a human through the
	 * builder never has `jsl_module_id`, because the builder writes to the
	 * outline table instead. That makes these keys a reliable fingerprint of
	 * generated demo content.
	 *
	 * This exists so a site already running the placeholder curriculum picks up
	 * the real writing on upgrade. Anything without the fingerprint is treated
	 * as somebody's work and left alone.
	 */
	private static function is_demo_content( \WP_Post $post ): bool {
		$fingerprints = 'lesson' === $post->post_type
			? array( 'jsl_module_id', 'jsl_lesson_order' )
			: array( 'jsl_path_id' );

		foreach ( $fingerprints as $key ) {
			if ( '' !== (string) get_post_meta( $post->ID, $key, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Content is compared after normalising whitespace.
	 *
	 * WordPress rewrites a little of what it is given — line endings, some
	 * entity encoding — so a byte-for-byte comparison would decide the operator
	 * had edited every post the moment it was saved, and the seeder would never
	 * update anything again.
	 */
	private static function hash( string $content ): string {
		return md5( preg_replace( '/\s+/', ' ', trim( $content ) ) );
	}

	/**
	 * @param mixed $value
	 */
	private static function maybe_set_meta( int $post_id, string $key, $value, bool $force ) {
		if ( $force ) {
			update_post_meta( $post_id, $key, $value );
			return;
		}

		$current = get_post_meta( $post_id, $key, true );

		// Not a string cast: outcomes and requirements are arrays, and casting
		// one emits an "Array to string conversion" warning on every upgrade.
		$empty = ( '' === $current || null === $current || array() === $current );

		if ( $empty ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/** Place an item in a container exactly once. */
	private static function place( string $table, string $container_type, int $container_id, string $item_type, int $item_id, int $order ) {
		global $wpdb;

		$row = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table}
				  WHERE container_type=%s AND container_id=%d AND item_type=%s AND item_id=%d",
				$container_type,
				$container_id,
				$item_type,
				$item_id
			)
		);

		if ( $row ) {
			$wpdb->update( $table, array( 'menu_order' => $order ), array( 'id' => $row ) );
			return;
		}

		$wpdb->insert(
			$table,
			array(
				'container_type' => $container_type,
				'container_id'   => $container_id,
				'item_type'      => $item_type,
				'item_id'        => $item_id,
				'menu_order'     => $order,
			)
		);
	}
}
