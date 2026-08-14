<?php
/**
 * Enrollment writes/reads against wp_jsl_enrollments.
 */

namespace JSL\Enrollment;

defined( 'ABSPATH' ) || exit;

class Enrollment {

	/**
	 * @param int    $user_id
	 * @param int    $object_id   Course (or future: learning path) post ID.
	 * @param string $object_type 'course' | 'learning_path'
	 * @param string $source      'free' | 'dodo'
	 */
	public static function enroll( int $user_id, int $object_id, string $object_type = 'course', string $source = 'free' ) {
		global $wpdb;

		if ( self::is_enrolled( $user_id, $object_id, $object_type ) ) {
			return true;
		}

		return false !== $wpdb->insert(
			Tables::enrollments_table_name(),
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'status'      => 'active',
				'source'      => $source,
				'enrolled_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	public static function is_enrolled( int $user_id, int $object_id, string $object_type = 'course' ): bool {
		global $wpdb;

		$table = Tables::enrollments_table_name();

		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d AND object_id = %d AND object_type = %s AND status = 'active'",
				$user_id,
				$object_id,
				$object_type
			)
		);

		return (bool) $found;
	}
}
