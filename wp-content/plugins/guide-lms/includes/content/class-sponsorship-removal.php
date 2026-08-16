<?php
/**
 * Remove every trace of the sponsorship module.
 *
 * The code is gone, but code is only half of a feature. The other half is in
 * the database, and a deploy moves code without touching it — so a site that
 * upgrades past this point would keep a custom table, a post type's worth of
 * rows, a role that grants capabilities nothing checks any more, a scheduled
 * task pointing at a hook nobody listens to, and a pile of orphaned meta.
 *
 * None of that is harmful on its own. All of it is the sort of residue that
 * makes a database impossible to reason about two years later, and an orphaned
 * role in particular is a real thing: a user who still has it keeps
 * capabilities that were granted for a feature that no longer exists.
 *
 * So this runs once, tidies up, and records that it did.
 *
 * It is deliberately thorough about posts. `sponsorship` is no longer a
 * registered post type, which means WordPress will not show those rows in any
 * admin screen and `wp_delete_post()` on them behaves oddly — they would simply
 * sit there invisible forever. They are removed directly, meta included.
 */

namespace Guide\Content;

defined( 'ABSPATH' ) || exit;

class Sponsorship_Removal {

	const OPTION_DONE = 'jsl_sponsorship_removed';

	/** The role the module created for sponsors. */
	const ROLE = 'guide_sponsor';

	/** Options the module owned. */
	const OPTIONS = array(
		'jsl_sponsor_open',
		'jsl_sponsor_prices',
		'jsl_sponsor_product',
		'jsl_sponsor_rewrite',
		'jsl_ads_house',
	);

	public static function run() {
		if ( get_option( self::OPTION_DONE ) ) {
			return;
		}

		global $wpdb;

		update_option( self::OPTION_DONE, 1, false );

		// 1. Posts and their meta. Done in SQL because the post type is no
		// longer registered, so the usual helpers cannot see these rows.
		$ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", 'sponsorship' )
		);

		foreach ( $ids as $id ) {
			$wpdb->delete( $wpdb->postmeta, array( 'post_id' => (int) $id ), array( '%d' ) );
			$wpdb->delete( $wpdb->posts, array( 'ID' => (int) $id ), array( '%d' ) );
		}

		// 2. Meta left on anything else — a course that once carried a
		// sponsorship reference, for instance.
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'jsl_sponsor%'" );

		// 3. The impressions and clicks table.
		$table = $wpdb->prefix . 'jsl_ad_stats';
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// 4. Options.
		foreach ( self::OPTIONS as $option ) {
			delete_option( $option );
		}

		// 5. The scheduled sweep, which now points at a hook nothing listens to.
		$timestamp = wp_next_scheduled( 'guide_sponsorship_sweep' );

		while ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'guide_sponsorship_sweep' );
			$timestamp = wp_next_scheduled( 'guide_sponsorship_sweep' );
		}

		// 6. The role. Anybody still holding it becomes a subscriber, which is
		// what they would have been anyway — never left with no role at all,
		// because a user with no role cannot sign in.
		self::retire_role();

		// 7. Rewrite rules for /sponsor/ are regenerated on the next flush,
		// which the upgrade routine does immediately after this runs.
	}

	private static function retire_role() {
		if ( ! get_role( self::ROLE ) ) {
			return;
		}

		$holders = get_users( array( 'role' => self::ROLE, 'fields' => 'ID' ) );

		foreach ( $holders as $user_id ) {
			$user = get_userdata( (int) $user_id );

			if ( ! $user ) {
				continue;
			}

			$user->remove_role( self::ROLE );

			if ( empty( $user->roles ) ) {
				$user->add_role( 'subscriber' );
			}
		}

		remove_role( self::ROLE );
	}
}
