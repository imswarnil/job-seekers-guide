<?php
/**
 * Ad delivery statistics — impressions and clicks, per day, per slot.
 *
 * Used by both sponsorships and AdSense placements, so the dashboard can say
 * something about ad performance either way. A sponsorship row carries its
 * campaign id; an AdSense row carries 0.
 *
 * Aggregated per day rather than logged per event. A row per impression would
 * be a table with millions of rows inside a year, answering questions nobody
 * asks — "which anonymous visitor saw ad 4 at 14:32" is not a question, and
 * storing it is a liability rather than an asset.
 *
 * Impressions are counted server-side as the slot renders. That over-counts
 * relative to a viewability-tracked network number, so the sponsor-facing copy
 * says "times shown" rather than implying anything about who looked.
 */

namespace Guide\Sponsors;

defined( 'ABSPATH' ) || exit;

class Sponsor_Stats {

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_ad_stats';
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_handle_click' ) );
	}

	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table_name();
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			day DATE NOT NULL,
			slot VARCHAR(20) NOT NULL,
			sponsorship_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
			clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY day_slot_sponsor (day, slot, sponsorship_id),
			KEY sponsorship_id (sponsorship_id),
			KEY day (day)
		) {$collate};";

		dbDelta( $sql );
	}

	/**
	 * Record one impression.
	 *
	 * A single upsert, so a busy page costs one cheap write rather than a read
	 * plus a write.
	 */
	public static function record_impression( string $slot, int $sponsorship_id = 0 ) {
		self::bump( $slot, $sponsorship_id, 'impressions' );
	}

	public static function record_click( string $slot, int $sponsorship_id = 0 ) {
		self::bump( $slot, $sponsorship_id, 'clicks' );
	}

	private static function bump( string $slot, int $sponsorship_id, string $column ) {
		global $wpdb;

		$column = 'clicks' === $column ? 'clicks' : 'impressions';
		$slot   = sanitize_key( $slot );
		$day    = current_time( 'Y-m-d' );
		$table  = self::table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $column is whitelisted above.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (day, slot, sponsorship_id, impressions, clicks)
				 VALUES (%s, %s, %d, %d, %d)
				 ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
				$day,
				$slot,
				$sponsorship_id,
				'impressions' === $column ? 1 : 0,
				'clicks' === $column ? 1 : 0
			)
		);
	}

	/**
	 * Click-through URL for a sponsored ad.
	 *
	 * Clicks go through the site so they can be counted, then redirect.
	 *
	 * There is deliberately no nonce: the link has to survive being shared and
	 * followed by anyone, which a session-bound token would break. That means
	 * the count is best-effort and can be inflated by crawlers — the sponsor
	 * copy says "clicks" rather than "visitors" for that reason. The redirect
	 * itself is safe regardless, because the destination is read from the
	 * owner-approved campaign, never from the request.
	 */
	public static function click_url( int $sponsorship_id, string $slot ): string {
		return add_query_arg(
			array(
				'guide_ad_click' => $sponsorship_id,
				'slot'           => $slot,
			),
			home_url( '/' )
		);
	}

	public static function maybe_handle_click() {
		if ( empty( $_GET['guide_ad_click'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Front-end requests only. Firing on an admin or REST request would
		// redirect someone out of a screen they were working in.
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$id   = (int) $_GET['guide_ad_click']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slot = isset( $_GET['slot'] ) ? sanitize_key( wp_unslash( $_GET['slot'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( Sponsorship::POST_TYPE !== get_post_type( $id ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$destination = (string) get_post_meta( $id, 'jsl_sponsor_url', true );

		if ( ! $destination || ! wp_http_validate_url( $destination ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		self::record_click( $slot, $id );

		// Deliberately wp_redirect, not wp_safe_redirect: the destination is an
		// external advertiser URL, which safe_redirect would refuse. It is safe
		// because it comes from an owner-approved campaign, never from the
		// request.
		wp_redirect( $destination, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	// -------------------------------------------------------------------------
	// Reading
	// -------------------------------------------------------------------------

	/**
	 * @return array{impressions:int, clicks:int, ctr:string}
	 */
	public static function totals( int $sponsorship_id = 0, int $days = 0 ): array {
		global $wpdb;

		$table = self::table_name();
		$where = array( 'sponsorship_id = %d' );
		$args  = array( $sponsorship_id );

		if ( $days > 0 ) {
			$where[] = 'day >= %s';
			$args[]  = gmdate( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT SUM(impressions) AS impressions, SUM(clicks) AS clicks
				   FROM {$table} WHERE " . implode( ' AND ', $where ),
				$args
			),
			ARRAY_A
		);

		$impressions = (int) ( $row['impressions'] ?? 0 );
		$clicks      = (int) ( $row['clicks'] ?? 0 );

		return array(
			'impressions' => $impressions,
			'clicks'      => $clicks,
			'ctr'         => $impressions > 0 ? number_format( $clicks / $impressions * 100, 2 ) : '0.00',
		);
	}

	/**
	 * Daily series for a campaign, oldest first — for the sponsor's chart.
	 *
	 * @return array<int, array{date:string, impressions:int, clicks:int}>
	 */
	public static function series( int $sponsorship_id, int $days = 30 ): array {
		global $wpdb;

		$table = self::table_name();
		$from  = gmdate( 'Y-m-d', strtotime( '-' . max( 1, $days ) . ' days' ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT day, SUM(impressions) AS impressions, SUM(clicks) AS clicks
				   FROM {$table}
				  WHERE sponsorship_id = %d AND day >= %s
				  GROUP BY day ORDER BY day ASC",
				$sponsorship_id,
				$from
			),
			ARRAY_A
		);

		$byday = array();

		foreach ( (array) $rows as $row ) {
			$byday[ $row['day'] ] = array(
				'impressions' => (int) $row['impressions'],
				'clicks'      => (int) $row['clicks'],
			);
		}

		// Fill the gaps, so a chart with quiet days does not silently compress
		// them out and imply continuous delivery.
		$series = array();

		for ( $i = $days; $i >= 0; $i-- ) {
			$date = gmdate( 'Y-m-d', strtotime( '-' . $i . ' days' ) );

			$series[] = array(
				'date'        => $date,
				'impressions' => $byday[ $date ]['impressions'] ?? 0,
				'clicks'      => $byday[ $date ]['clicks'] ?? 0,
			);
		}

		return $series;
	}

	/**
	 * Site-wide ad delivery by slot, for the owner's dashboard.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	public static function by_slot( int $days = 30 ): array {
		global $wpdb;

		$table = self::table_name();
		$from  = gmdate( 'Y-m-d', strtotime( '-' . max( 1, $days ) . ' days' ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT slot,
				        SUM(impressions) AS impressions,
				        SUM(clicks) AS clicks,
				        SUM(CASE WHEN sponsorship_id > 0 THEN impressions ELSE 0 END) AS sponsored
				   FROM {$table}
				  WHERE day >= %s
				  GROUP BY slot ORDER BY impressions DESC",
				$from
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
}
