<?php
/**
 * Enrollment writes/reads against wp_jsl_enrollments.
 *
 * The same table carries two kinds of grant:
 * - object_type 'course'   — access to one course, object_id = course post ID
 * - object_type 'platform' — a subscription to everything, object_id = 0
 *
 * expires_at NULL means "never expires" (a one-off course purchase or a free
 * enrollment). Subscriptions set an expiry and are renewed by the webhook.
 */

namespace JSL\Enrollment;

defined( 'ABSPATH' ) || exit;

class Enrollment {

	/** object_type used for the platform-wide subscription grant. */
	const PLATFORM = 'platform';

	/** Per-request memo of active grants, keyed by user ID. */
	private static $grant_cache = array();

	/**
	 * Grant access. Re-granting an existing row refreshes status/expiry
	 * instead of failing on the unique key, which is what a renewal needs.
	 *
	 * @param int         $user_id
	 * @param int         $object_id   Course post ID, or 0 for a platform subscription.
	 * @param string      $object_type 'course' | 'platform'
	 * @param string      $source      'free' | 'dodo' | 'manual'
	 * @param string|null $expires_at  MySQL datetime, or null for no expiry.
	 * @param string      $external_id Provider-side id (subscription/payment), for reconciliation.
	 */
	public static function enroll(
		int $user_id,
		int $object_id,
		string $object_type = 'course',
		string $source = 'free',
		?string $expires_at = null,
		string $external_id = ''
	): bool {
		global $wpdb;

		if ( $user_id <= 0 ) {
			return false;
		}

		$table    = Tables::enrollments_table_name();
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d AND object_id = %d AND object_type = %s",
				$user_id,
				$object_id,
				$object_type
			)
		);

		$data = array(
			'status'      => 'active',
			'source'      => $source,
			'expires_at'  => $expires_at,
			'external_id' => $external_id ?: null,
		);

		if ( $existing ) {
			$updated = $wpdb->update( $table, $data, array( 'id' => (int) $existing ) );
			$ok      = false !== $updated;
		} else {
			$data['user_id']     = $user_id;
			$data['object_id']   = $object_id;
			$data['object_type'] = $object_type;
			$data['enrolled_at'] = current_time( 'mysql' );
			$ok                  = false !== $wpdb->insert( $table, $data );
		}

		if ( $ok ) {
			self::flush_cache( $user_id );
			do_action( 'jsl_access_granted', $user_id, $object_id, $object_type, $source );
		}

		return $ok;
	}

	/**
	 * Revoke a grant (subscription cancelled, refund, manual removal).
	 * Progress rows are deliberately kept — losing access shouldn't erase
	 * what someone already learned.
	 */
	public static function revoke( int $user_id, int $object_id, string $object_type = 'course' ): bool {
		global $wpdb;

		$updated = $wpdb->update(
			Tables::enrollments_table_name(),
			array( 'status' => 'cancelled' ),
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			)
		);

		self::flush_cache( $user_id );

		return false !== $updated;
	}

	/**
	 * Is this grant active and unexpired?
	 */
	public static function is_enrolled( int $user_id, int $object_id, string $object_type = 'course' ): bool {
		if ( $user_id <= 0 ) {
			return false;
		}

		foreach ( self::active_grants( $user_id ) as $grant ) {
			if ( (int) $grant['object_id'] === $object_id && $grant['object_type'] === $object_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does the user hold an active platform subscription (all courses)?
	 */
	public static function has_platform_subscription( int $user_id ): bool {
		return self::is_enrolled( $user_id, 0, self::PLATFORM );
	}

	/**
	 * Expiry of the user's platform subscription, or '' when there is none
	 * or it never expires.
	 */
	public static function subscription_expiry( int $user_id ): string {
		foreach ( self::active_grants( $user_id ) as $grant ) {
			if ( self::PLATFORM === $grant['object_type'] ) {
				return (string) $grant['expires_at'];
			}
		}
		return '';
	}

	/**
	 * All of a user's active, unexpired grants. Loaded once per request and
	 * memoized — lesson lists ask this question once per row.
	 *
	 * @return array<int, array{object_id:int, object_type:string, source:string, expires_at:?string}>
	 */
	public static function active_grants( int $user_id ): array {
		if ( isset( self::$grant_cache[ $user_id ] ) ) {
			return self::$grant_cache[ $user_id ];
		}

		global $wpdb;

		$table = Tables::enrollments_table_name();
		// GMT: expiries are written in UTC, so the comparison has to be too.
		$now = current_time( 'mysql', true );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT object_id, object_type, source, expires_at
				 FROM {$table}
				 WHERE user_id = %d
				   AND status = 'active'
				   AND ( expires_at IS NULL OR expires_at > %s )",
				$user_id,
				$now
			),
			ARRAY_A
		);

		self::$grant_cache[ $user_id ] = is_array( $rows ) ? $rows : array();

		return self::$grant_cache[ $user_id ];
	}

	/**
	 * Course IDs the user has an explicit (non-subscription) grant for.
	 *
	 * @return int[]
	 */
	public static function enrolled_course_ids( int $user_id ): array {
		$ids = array();
		foreach ( self::active_grants( $user_id ) as $grant ) {
			if ( 'course' === $grant['object_type'] ) {
				$ids[] = (int) $grant['object_id'];
			}
		}
		return $ids;
	}

	/**
	 * Drop the memoized grants for a user so a grant made earlier in this
	 * request is visible to checks made later in it.
	 */
	private static function flush_cache( int $user_id ): void {
		unset( self::$grant_cache[ $user_id ] );
	}
}
