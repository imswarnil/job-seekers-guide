<?php
/**
 * Comment surfaces that stay removed even now that discussion exists.
 *
 * Discussion is open on lessons, help articles, company guides and stories —
 * see includes/community/class-discussion.php, which owns that policy. This
 * class no longer closes comments or empties comment queries; it only trims
 * the parts of wp-admin that assume a blog, and keeps pingbacks dead.
 *
 * The Comments menu is deliberately restored: a moderation queue you cannot
 * reach is a moderation queue that does not exist.
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Comments_Off {

	public static function init() {
		// Pingbacks and trackbacks stay off: they are a spam vector with no
		// upside for a site nobody is linking to from a blogroll.
		add_filter( 'pings_open', '__return_false', 20 );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_dashboard_widget' ) );
	}






	public static function remove_dashboard_widget() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
}
