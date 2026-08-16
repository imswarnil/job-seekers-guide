<?php
/**
 * Custom table for course modules (the grouping unit the course builder
 * drags lessons between). Lesson->module assignment + ordering lives on
 * the lesson as postmeta (jsl_module_id, jsl_lesson_order) since lessons
 * are still normal posts.
 */

namespace Guide\Builder;

defined( 'ABSPATH' ) || exit;

class Tables {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'jsl_modules';
	}

	public static function create() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$table           = self::table_name();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			course_id BIGINT UNSIGNED NOT NULL,
			title VARCHAR(255) NOT NULL,
			menu_order INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY course_id (course_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
