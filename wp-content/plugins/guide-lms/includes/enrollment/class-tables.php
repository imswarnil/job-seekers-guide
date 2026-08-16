<?php
/**
 * Custom DB tables for enrollment and lesson progress.
 *
 * Postmeta doesn't scale well for progress-tracking queries (e.g. "all
 * lessons a user has completed", "% complete for a path"), so these live
 * in dedicated tables instead.
 */

namespace Guide\Enrollment;

defined( 'ABSPATH' ) || exit;

class Tables {

	public static function enrollments_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'jsl_enrollments';
	}

	public static function progress_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'jsl_progress';
	}

	/**
	 * Create (or upgrade, via dbDelta) both tables. Safe to call on every
	 * activation — dbDelta only applies the diff.
	 */
	public static function create() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$enrollments_table = self::enrollments_table_name();
		$progress_table    = self::progress_table_name();

		$sql = "CREATE TABLE {$enrollments_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			object_type VARCHAR(20) NOT NULL DEFAULT 'course',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			source VARCHAR(20) NOT NULL DEFAULT 'free',
			external_id VARCHAR(191) NULL,
			enrolled_at DATETIME NOT NULL,
			expires_at DATETIME NULL,
			completed_at DATETIME NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_object (user_id, object_id, object_type),
			KEY object_id (object_id),
			KEY user_id (user_id),
			KEY external_id (external_id)
		) {$charset_collate};

		CREATE TABLE {$progress_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			lesson_id BIGINT UNSIGNED NOT NULL,
			course_id BIGINT UNSIGNED NOT NULL,
			completed_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_lesson (user_id, lesson_id),
			KEY user_course (user_id, course_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
