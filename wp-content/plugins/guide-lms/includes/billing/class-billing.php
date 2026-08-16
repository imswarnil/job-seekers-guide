<?php
/**
 * Billing history.
 *
 * The enrollments table records *what access someone has*; it says nothing
 * about what they were charged. A learner asking "what did I pay and when?"
 * needs a separate, append-only record, so this keeps one.
 *
 * Deliberately append-only and deliberately not the source of truth for money:
 * the payment provider is. This table exists so the learner can see their own
 * history without leaving the site, and so support questions can be answered
 * without a provider login. The provider's own invoice stays the legal
 * document, which is why every row carries its external reference.
 */

namespace Guide\Billing;

defined( 'ABSPATH' ) || exit;

class Billing {

	const KIND_SUBSCRIPTION = 'subscription';
	const KIND_RENEWAL      = 'renewal';
	const KIND_COURSE       = 'course';
	const KIND_REFUND       = 'refund';

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_payments';
	}

	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table_name();
		$collate = $wpdb->get_charset_collate();

		// external_id is UNIQUE so a webhook delivered twice cannot produce two
		// receipts for one payment.
		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			kind VARCHAR(20) NOT NULL DEFAULT 'subscription',
			status VARCHAR(20) NOT NULL DEFAULT 'paid',
			external_id VARCHAR(191) NOT NULL,
			amount_minor BIGINT NULL,
			currency VARCHAR(8) NULL,
			description VARCHAR(191) NULL,
			period_start DATETIME NULL,
			period_end DATETIME NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY external_id (external_id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$collate};";

		dbDelta( $sql );
	}

	/**
	 * Record a payment. Idempotent on external_id.
	 *
	 * @param array{user_id:int, external_id:string, kind?:string, status?:string,
	 *              amount_minor?:int|null, currency?:string, description?:string,
	 *              period_start?:string, period_end?:string} $data
	 */
	public static function record( array $data ): bool {
		global $wpdb;

		$user_id     = (int) ( $data['user_id'] ?? 0 );
		$external_id = trim( (string) ( $data['external_id'] ?? '' ) );

		if ( ! $user_id || '' === $external_id ) {
			return false;
		}

		$row = array(
			'user_id'      => $user_id,
			'kind'         => sanitize_key( (string) ( $data['kind'] ?? self::KIND_SUBSCRIPTION ) ),
			'status'       => sanitize_key( (string) ( $data['status'] ?? 'paid' ) ),
			'external_id'  => substr( $external_id, 0, 191 ),
			'amount_minor' => isset( $data['amount_minor'] ) && null !== $data['amount_minor']
				? (int) $data['amount_minor']
				: null,
			'currency'     => $data['currency'] ? strtoupper( substr( sanitize_text_field( (string) $data['currency'] ), 0, 8 ) ) : null,
			'description'  => isset( $data['description'] ) ? substr( sanitize_text_field( (string) $data['description'] ), 0, 191 ) : null,
			'period_start' => ! empty( $data['period_start'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( (string) $data['period_start'] ) ) : null,
			'period_end'   => ! empty( $data['period_end'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( (string) $data['period_end'] ) ) : null,
			'created_at'   => current_time( 'mysql', true ),
		);

		// INSERT IGNORE via a pre-check keeps this readable and works on every
		// MySQL/MariaDB version dbDelta supports.
		$exists = $wpdb->get_var(
			$wpdb->prepare( 'SELECT id FROM ' . self::table_name() . ' WHERE external_id = %s', $row['external_id'] )
		);

		if ( $exists ) {
			return true;
		}

		return (bool) $wpdb->insert( self::table_name(), $row );
	}

	/**
	 * A user's payments, newest first.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function for_user( int $user_id, int $limit = 50 ): array {
		global $wpdb;

		if ( ! $user_id ) {
			return array();
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table_name() . ' WHERE user_id = %d ORDER BY created_at DESC LIMIT %d',
				$user_id,
				max( 1, min( 200, $limit ) )
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/** One payment, scoped to its owner so a guessed id cannot leak a receipt. */
	public static function get_for_user( int $payment_id, int $user_id ) {
		global $wpdb;

		if ( ! $payment_id || ! $user_id ) {
			return null;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table_name() . ' WHERE id = %d AND user_id = %d',
				$payment_id,
				$user_id
			),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Format a minor-unit amount for display.
	 *
	 * Returns an empty string when the provider did not send an amount, so the
	 * receipt shows "—" rather than an invented "₹0.00".
	 */
	public static function format_amount( $amount_minor, $currency ): string {
		if ( null === $amount_minor || '' === $amount_minor ) {
			return '';
		}

		$symbols = array(
			'INR' => '₹',
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
		);

		$code   = strtoupper( (string) $currency );
		$symbol = $symbols[ $code ] ?? ( $code ? $code . ' ' : '' );

		return $symbol . number_format_i18n( ( (int) $amount_minor ) / 100, 2 );
	}

	/** Human label for a row's kind. */
	public static function kind_label( string $kind ): string {
		switch ( $kind ) {
			case self::KIND_RENEWAL:
				return __( 'Subscription renewal', 'guide-lms' );
			case self::KIND_COURSE:
				return __( 'Course purchase', 'guide-lms' );
			case self::KIND_REFUND:
				return __( 'Refund', 'guide-lms' );
			default:
				return __( 'Subscription', 'guide-lms' );
		}
	}
}
