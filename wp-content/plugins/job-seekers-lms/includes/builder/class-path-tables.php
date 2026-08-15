<?php
/**
 * Storage for learning-path steps.
 *
 * A path is an ordered list of heterogeneous steps — a whole course, or a
 * single standalone piece (article, video or quiz, all of which are just
 * lessons with no parent course). Modelling that as a table of ordered
 * references keeps a path free to mix them, which a "courses belong to a
 * path" meta field could not express.
 */

namespace JSL\Builder;

defined( 'ABSPATH' ) || exit;

class Path_Tables {

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_path_steps';
	}

	public static function create() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			path_id BIGINT UNSIGNED NOT NULL,
			step_type VARCHAR(20) NOT NULL DEFAULT 'course',
			object_id BIGINT UNSIGNED NOT NULL,
			menu_order INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY path_order (path_id, menu_order),
			KEY object_id (object_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Bring pre-existing paths — which linked courses through the course's
	 * own jsl_path_id meta — into the steps table, once.
	 *
	 * Without this, upgrading would silently empty every existing path.
	 */
	public static function migrate_legacy_course_links() {
		global $wpdb;

		if ( get_option( 'jsl_path_steps_migrated' ) ) {
			return;
		}

		$table = self::table_name();

		$paths = get_posts(
			array(
				'post_type'      => 'learning_path',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);

		foreach ( $paths as $path_id ) {
			$existing = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE path_id = %d", $path_id ) );

			if ( $existing ) {
				continue; // Already has steps — leave it alone.
			}

			$courses = get_posts(
				array(
					'post_type'      => 'course',
					'posts_per_page' => -1,
					'post_status'    => 'any',
					'meta_key'       => 'jsl_path_id',
					'meta_value'     => $path_id,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);

			foreach ( $courses as $order => $course_id ) {
				$wpdb->insert(
					$table,
					array(
						'path_id'    => $path_id,
						'step_type'  => 'course',
						'object_id'  => $course_id,
						'menu_order' => $order,
					),
					array( '%d', '%s', '%d', '%d' )
				);
			}
		}

		update_option( 'jsl_path_steps_migrated', 1, false );
	}
}
