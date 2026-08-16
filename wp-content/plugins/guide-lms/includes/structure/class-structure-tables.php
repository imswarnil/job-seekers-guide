<?php
/**
 * The loosely-coupled content structure: sections, and one outline table.
 *
 * The old model bound everything tightly. A module belonged to exactly one
 * course (`wp_jsl_modules.course_id`), and a lesson belonged to exactly one
 * course and module (`jsl_course_id`, `jsl_module_id` postmeta). That made
 * reuse impossible: to use a lesson in two places you had to duplicate it, and
 * a duplicated lesson immediately starts drifting from its twin.
 *
 * The new model separates *content* from *arrangement*:
 *
 *   Lessons and courses are content. They exist on their own.
 *   Sections are reusable groupings of lessons.
 *   Courses and paths are arrangements — ordered lists of things.
 *
 * Both relationships are expressed by ONE table, because they are the same
 * relationship: "container X holds item Y at position N".
 *
 *   wp_jsl_outline
 *     (course, 12, section, 3, 0)   course 12's first section is section 3
 *     (section, 3, lesson, 40, 0)   section 3's first lesson is lesson 40
 *     (path, 7, course, 12, 0)      path 7 starts with course 12
 *     (path, 7, section, 3, 1)      …then reuses section 3 directly
 *     (path, 7, lesson, 99, 2)      …then a single standalone lesson
 *
 * Reuse falls out of it for free: a section in two courses is two rows, a
 * lesson in two sections is two rows. Nothing is duplicated, so editing the
 * lesson updates it everywhere it appears.
 *
 * The lesson's `jsl_course_id` meta survives, but its meaning narrows to
 * "canonical course" — the one whose URL the lesson lives at, since permalinks
 * are /courses/{course}/{lesson}/ and a lesson that appears in four places
 * still needs exactly one address.
 */

namespace Guide\Structure;

defined( 'ABSPATH' ) || exit;

class Structure_Tables {

	/** Things that can contain other things. */
	const CONTAINER_COURSE  = 'course';
	const CONTAINER_PATH    = 'path';
	const CONTAINER_SECTION = 'section';

	/** Things that can be contained. */
	const ITEM_SECTION = 'section';
	const ITEM_COURSE  = 'course';
	const ITEM_LESSON  = 'lesson';

	const MIGRATED_OPTION = 'jsl_structure_migrated';

	public static function sections_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_sections';
	}

	public static function outline_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_outline';
	}

	public static function create() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$collate  = $wpdb->get_charset_collate();
		$sections = self::sections_table();
		$outline  = self::outline_table();

		// `shared` marks a section the author intends to reuse, so the builder
		// can offer a curated library rather than every section ever made.
		// It is a hint for the UI, not a constraint — any section can be
		// referenced from anywhere.
		$sql = "CREATE TABLE {$sections} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			description TEXT NULL,
			shared TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY shared (shared)
		) {$collate};

		CREATE TABLE {$outline} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			container_type VARCHAR(20) NOT NULL,
			container_id BIGINT UNSIGNED NOT NULL,
			item_type VARCHAR(20) NOT NULL,
			item_id BIGINT UNSIGNED NOT NULL,
			menu_order INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY placement (container_type, container_id, item_type, item_id),
			KEY container (container_type, container_id, menu_order),
			KEY item (item_type, item_id)
		) {$collate};";

		dbDelta( $sql );
	}

	/**
	 * Move the old tightly-coupled structure into the new tables, once.
	 *
	 * Deliberately additive: `wp_jsl_modules`, `wp_jsl_path_steps` and the
	 * lesson postmeta are all left exactly as they are. If something about the
	 * new structure turns out to be wrong, the old data is still sitting there
	 * to re-derive from — which matters a great deal more on a live site than
	 * a tidy schema does.
	 *
	 * Section IDs are preserved from module IDs, so anything that recorded a
	 * module id still resolves.
	 */
	public static function migrate_from_modules() {
		global $wpdb;

		if ( get_option( self::MIGRATED_OPTION ) ) {
			return;
		}

		$sections = self::sections_table();
		$outline  = self::outline_table();
		$modules  = $wpdb->prefix . 'jsl_modules';
		$steps    = $wpdb->prefix . 'jsl_path_steps';
		$now      = current_time( 'mysql', true );

		// Nothing to migrate on a fresh install — but still mark it done, so
		// this never runs again and cannot resurrect stale rows later.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $modules ) ) !== $modules ) {
			update_option( self::MIGRATED_OPTION, '1', false );
			return;
		}

		// 1. Modules become sections, keeping their IDs.
		$module_rows = $wpdb->get_results( "SELECT id, course_id, title, menu_order FROM {$modules} ORDER BY course_id, menu_order", ARRAY_A );

		foreach ( (array) $module_rows as $module ) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT IGNORE INTO {$sections} (id, title, description, shared, created_at, updated_at)
					 VALUES (%d, %s, NULL, 0, %s, %s)",
					(int) $module['id'],
					(string) $module['title'],
					$now,
					$now
				)
			);

			// 2. …and are placed in the course they used to belong to.
			self::place(
				self::CONTAINER_COURSE,
				(int) $module['course_id'],
				self::ITEM_SECTION,
				(int) $module['id'],
				(int) $module['menu_order']
			);
		}

		// 3. Lessons are placed in the section they used to sit in.
		$lessons = $wpdb->get_results(
			"SELECT p.ID AS lesson_id,
			        CAST(mm.meta_value AS UNSIGNED) AS module_id,
			        CAST(COALESCE(mo.meta_value, 0) AS SIGNED) AS lesson_order
			   FROM {$wpdb->posts} p
			   INNER JOIN {$wpdb->postmeta} mm ON mm.post_id = p.ID AND mm.meta_key = 'jsl_module_id'
			   LEFT JOIN {$wpdb->postmeta} mo ON mo.post_id = p.ID AND mo.meta_key = 'jsl_lesson_order'
			  WHERE p.post_type = 'lesson'",
			ARRAY_A
		);

		foreach ( (array) $lessons as $lesson ) {
			if ( ! $lesson['module_id'] ) {
				continue;
			}

			self::place(
				self::CONTAINER_SECTION,
				(int) $lesson['module_id'],
				self::ITEM_LESSON,
				(int) $lesson['lesson_id'],
				(int) $lesson['lesson_order']
			);
		}

		// 4. Path steps become path placements.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $steps ) ) === $steps ) {
			$step_rows = $wpdb->get_results( "SELECT path_id, step_type, object_id, menu_order FROM {$steps} ORDER BY path_id, menu_order", ARRAY_A );

			foreach ( (array) $step_rows as $step ) {
				$type = self::ITEM_COURSE === $step['step_type'] ? self::ITEM_COURSE : self::ITEM_LESSON;

				self::place(
					self::CONTAINER_PATH,
					(int) $step['path_id'],
					$type,
					(int) $step['object_id'],
					(int) $step['menu_order']
				);
			}
		}

		update_option( self::MIGRATED_OPTION, '1', false );
	}

	/**
	 * Put an item in a container at a position, or move it if already there.
	 *
	 * @return bool
	 */
	public static function place( string $container_type, int $container_id, string $item_type, int $item_id, int $order = 0 ): bool {
		global $wpdb;

		if ( ! $container_id || ! $item_id ) {
			return false;
		}

		// A container cannot hold itself, at any depth we support.
		if ( $container_type === $item_type && $container_id === $item_id ) {
			return false;
		}

		$table = self::outline_table();

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table}
				  WHERE container_type = %s AND container_id = %d AND item_type = %s AND item_id = %d",
				$container_type,
				$container_id,
				$item_type,
				$item_id
			)
		);

		if ( $existing ) {
			return false !== $wpdb->update( $table, array( 'menu_order' => $order ), array( 'id' => (int) $existing ) );
		}

		return false !== $wpdb->insert(
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

	/** Remove one placement. The item itself is untouched — it may be in use elsewhere. */
	public static function remove( string $container_type, int $container_id, string $item_type, int $item_id ): bool {
		global $wpdb;

		return false !== $wpdb->delete(
			self::outline_table(),
			array(
				'container_type' => $container_type,
				'container_id'   => $container_id,
				'item_type'      => $item_type,
				'item_id'        => $item_id,
			)
		);
	}

	/**
	 * Drop every placement referencing a deleted post, and every placement
	 * inside a deleted section. Without this the outline accumulates rows
	 * pointing at nothing.
	 */
	public static function forget_item( string $item_type, int $item_id ) {
		global $wpdb;

		$table = self::outline_table();

		$wpdb->delete( $table, array( 'item_type' => $item_type, 'item_id' => $item_id ) );

		if ( self::ITEM_SECTION === $item_type ) {
			$wpdb->delete( $table, array( 'container_type' => self::CONTAINER_SECTION, 'container_id' => $item_id ) );
		}
	}
}
