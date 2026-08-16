<?php
/**
 * Plugin Name: Guide LMS
 * Plugin URI: https://github.com/imswarnil/job-seekers-guide
 * Description: Structured-learning-path LMS. Courses, lessons, learning paths, a visual course builder, enrollment and progress tracking, quizzes, a community resource library, learner discussion, and checkout.
 * Version: 0.15.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Guide
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: guide-lms
 */

defined( 'ABSPATH' ) || exit;

define( 'GUIDE_VERSION', '0.12.0' );
define( 'GUIDE_PLUGIN_FILE', __FILE__ );
define( 'GUIDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GUIDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GUIDE_PLUGIN_DIR . 'includes/post-types/class-post-types.php';
require_once GUIDE_PLUGIN_DIR . 'includes/post-types/class-permalinks.php';
require_once GUIDE_PLUGIN_DIR . 'includes/post-types/class-lesson-meta.php';
require_once GUIDE_PLUGIN_DIR . 'includes/post-types/class-course-meta.php';
require_once GUIDE_PLUGIN_DIR . 'includes/quiz/class-quiz.php';
require_once GUIDE_PLUGIN_DIR . 'includes/media/class-placeholder.php';
require_once GUIDE_PLUGIN_DIR . 'includes/schema/class-json-ld.php';
require_once GUIDE_PLUGIN_DIR . 'includes/progress/class-progress.php';
require_once GUIDE_PLUGIN_DIR . 'includes/enrollment/class-tables.php';
require_once GUIDE_PLUGIN_DIR . 'includes/billing/class-billing.php';
require_once GUIDE_PLUGIN_DIR . 'includes/enrollment/class-enrollment.php';
require_once GUIDE_PLUGIN_DIR . 'includes/access/class-access.php';
require_once GUIDE_PLUGIN_DIR . 'includes/security/class-permalink-repair.php';
require_once GUIDE_PLUGIN_DIR . 'includes/content/class-starter-content.php';
require_once GUIDE_PLUGIN_DIR . 'includes/email/class-mailer.php';
require_once GUIDE_PLUGIN_DIR . 'includes/email/class-notifications.php';
require_once GUIDE_PLUGIN_DIR . 'includes/account/class-account.php';
require_once GUIDE_PLUGIN_DIR . 'includes/account/class-profile.php';
require_once GUIDE_PLUGIN_DIR . 'includes/sponsors/class-sponsorship.php';
require_once GUIDE_PLUGIN_DIR . 'includes/sponsors/class-sponsor-stats.php';
require_once GUIDE_PLUGIN_DIR . 'includes/sponsors/class-sponsor-portal.php';
require_once GUIDE_PLUGIN_DIR . 'includes/ads/class-ads.php';
require_once GUIDE_PLUGIN_DIR . 'includes/structure/class-structure-tables.php';
require_once GUIDE_PLUGIN_DIR . 'includes/structure/class-structure.php';
require_once GUIDE_PLUGIN_DIR . 'includes/structure/class-path-player.php';
require_once GUIDE_PLUGIN_DIR . 'includes/structure/class-structure-rest.php';
require_once GUIDE_PLUGIN_DIR . 'includes/api/class-course-api.php';
require_once GUIDE_PLUGIN_DIR . 'includes/builder/class-tables.php';
require_once GUIDE_PLUGIN_DIR . 'includes/builder/class-rest.php';
require_once GUIDE_PLUGIN_DIR . 'includes/builder/class-path-tables.php';
require_once GUIDE_PLUGIN_DIR . 'includes/builder/class-path-rest.php';
require_once GUIDE_PLUGIN_DIR . 'includes/payments/class-settings.php';
require_once GUIDE_PLUGIN_DIR . 'includes/payments/class-course-access.php';
require_once GUIDE_PLUGIN_DIR . 'includes/payments/class-checkout.php';
require_once GUIDE_PLUGIN_DIR . 'includes/payments/class-subscription.php';
require_once GUIDE_PLUGIN_DIR . 'includes/payments/class-webhook.php';
require_once GUIDE_PLUGIN_DIR . 'includes/analytics/class-analytics.php';
require_once GUIDE_PLUGIN_DIR . 'includes/auth/class-google-auth.php';
require_once GUIDE_PLUGIN_DIR . 'includes/companies/class-companies.php';
require_once GUIDE_PLUGIN_DIR . 'includes/companies/class-company-logo.php';
require_once GUIDE_PLUGIN_DIR . 'includes/community/class-community-types.php';
require_once GUIDE_PLUGIN_DIR . 'includes/community/class-feedback.php';
require_once GUIDE_PLUGIN_DIR . 'includes/community/class-discussion.php';
require_once GUIDE_PLUGIN_DIR . 'includes/success/class-success-stories.php';
require_once GUIDE_PLUGIN_DIR . 'includes/leaderboard/class-leaderboard.php';
require_once GUIDE_PLUGIN_DIR . 'includes/seo/class-seo.php';
require_once GUIDE_PLUGIN_DIR . 'includes/pwa/class-pwa.php';
require_once GUIDE_PLUGIN_DIR . 'includes/security/class-hardening.php';
require_once GUIDE_PLUGIN_DIR . 'includes/security/class-comments-off.php';
require_once GUIDE_PLUGIN_DIR . 'includes/security/class-login-guard.php';
require_once GUIDE_PLUGIN_DIR . 'includes/security/class-trim.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-lms-admin.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-console.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-settings-page.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-help-page.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-feedback-page.php';
require_once GUIDE_PLUGIN_DIR . 'admin/class-admin-theme.php';
require_once GUIDE_PLUGIN_DIR . 'includes/cli/class-seed-command.php';

/**
 * Cache-busting version for a plugin asset: the file's own modification
 * time. GUIDE_VERSION alone is not enough — the console's CSS and JS change
 * far more often than the version constant is bumped, and a stale cached
 * console looks exactly like a broken feature.
 *
 * @param string $relative_path Path from the plugin root, no leading slash.
 */
function guide_plugin_asset_version( $relative_path ) {
	$file = GUIDE_PLUGIN_DIR . $relative_path;
	$time = file_exists( $file ) ? filemtime( $file ) : 0;

	return $time ? GUIDE_VERSION . '.' . $time : GUIDE_VERSION;
}

/**
 * Boot the plugin.
 */
function guide_boot() {
	Guide\Post_Types::init();
	Guide\Permalinks::init();
	Guide\Lesson_Meta::init();
	Guide\Course_Meta::init();
	Guide\Quiz\Quiz::init();
	Guide\Schema\Json_Ld::init();
	Guide\Progress\Progress::init();
	Guide\Structure\Structure::init();
	Guide\Structure\Path_Player::init();
	Guide\Structure\Structure_Rest::init();
	Guide\Builder\Rest::init();
	Guide\Builder\Path_Rest::init();
	Guide\Payments\Settings::init();
	Guide\Payments\Course_Access::init();
	Guide\Payments\Checkout::init();
	Guide\Payments\Subscription::init();
	Guide\Payments\Webhook::init();
	Guide\Access\Access::init();
	Guide\Email\Notifications::init();
	Guide\Account\Account::init();
	Guide\Account\Profile::init();
	Guide\Sponsors\Sponsorship::init();
	Guide\Sponsors\Sponsor_Stats::init();
	Guide\Sponsors\Sponsor_Portal::init();
	Guide\Ads\Ads::init();
	Guide\Auth\Google_Auth::init();
	Guide\Companies\Companies::init();
	Guide\Companies\Company_Logo::init();
	Guide\Community\Community_Types::init();
	Guide\Community\Feedback::init();
	Guide\Community\Discussion::init();
	Guide\Success\Success_Stories::init();
	Guide\Leaderboard\Leaderboard::init();
	Guide\Seo\Seo::init();
	Guide\Pwa\Pwa::init();
	Guide\Analytics\Analytics::init();
	Guide\Security\Hardening::init();
	Guide\Security\Comments_Off::init();
	Guide\Security\Login_Guard::init();
	Guide\Security\Trim::init();
	Guide\Admin\Lms_Admin::init();
	Guide\Admin\Console::init();
	Guide\Admin\Settings_Page::init();
	Guide\Admin\Help_Page::init();
	Guide\Admin\Feedback_Page::init();
	Guide\Admin\Admin_Theme::init();
	Guide\Cli\Seed_Command::register();
}
add_action( 'plugins_loaded', 'guide_boot' );

/**
 * Apply schema changes on upgrade, not just on activation.
 *
 * An install updated in place — which is how this deploys: git pull, restart —
 * never re-fires the activation hook, so tables, roles and rewrite rules would
 * otherwise never appear.
 *
 * Hooked on `init` rather than `admin_init` so the first request from anybody
 * heals the deploy, instead of it waiting for an administrator to happen to log
 * in. When the version already matches this is a single autoloaded option read.
 */
function guide_maybe_upgrade_schema() {
	if ( get_option( 'jsl_db_version' ) === GUIDE_VERSION ) {
		return;
	}

	Guide\Enrollment\Tables::create();
	Guide\Billing\Billing::create_table();
	Guide\Community\Feedback::create_table();
	Guide\Sponsors\Sponsor_Stats::create_table();
	Guide\Builder\Tables::create();
	Guide\Builder\Path_Tables::create();
	Guide\Builder\Path_Tables::migrate_legacy_course_links();

	// Sections + outline, then move the old tightly-coupled structure across.
	// Order matters: the legacy path-step migration above must have run first,
	// or paths created before the steps table would migrate empty.
	Guide\Structure\Structure_Tables::create();
	Guide\Structure\Structure_Tables::migrate_from_modules();

	// Roles and rewrite rules are NOT part of activation on a live site:
	// production deploys by pulling code and restarting, which never fires the
	// activation hook. Anything an install needs has to happen here too.
	Guide\Sponsors\Sponsorship::sync_role();

	Guide\Post_Types::register();
	Guide\Companies\Companies::register();
	Guide\Community\Community_Types::register();
	Guide\Permalinks::add_rewrite_rules();
	Guide\Account\Account::add_rewrite_rules();
	Guide\Structure\Path_Player::add_rewrite_rules();
	Guide\Sponsors\Sponsor_Portal::add_rewrite_rules();
	flush_rewrite_rules();

	// Clean URLs, if the server turns out to support them. Runs before the
	// content seed so the permalinks reported below are the final ones.
	Guide\Security\Permalink_Repair::maybe_repair();

	// The courses that ship with the plugin. Same reasoning as the roles above:
	// a deploy moves code, not the database, so content written on a laptop has
	// to travel as code and be planted here. Never overwrites anything an
	// operator has edited — see Starter_Content for how that is enforced.
	Guide\Content\Starter_Content::maybe_seed();

	update_option( 'jsl_db_version', GUIDE_VERSION, false );
}
add_action( 'init', 'guide_maybe_upgrade_schema', 20 );

/**
 * Activation: register post types/taxonomies so rewrite rules are known,
 * create custom tables, then flush rewrites.
 */
function guide_activate() {
	Guide\Post_Types::register();
	Guide\Companies\Companies::register();
	Guide\Community\Community_Types::register();
	Guide\Permalinks::add_rewrite_rules();
	Guide\Account\Account::add_rewrite_rules();
	Guide\Structure\Path_Player::add_rewrite_rules();
	Guide\Sponsors\Sponsor_Portal::add_rewrite_rules();
	Guide\Sponsors\Sponsorship::add_role();
	Guide\Enrollment\Tables::create();
	Guide\Billing\Billing::create_table();
	Guide\Community\Feedback::create_table();
	Guide\Sponsors\Sponsor_Stats::create_table();
	Guide\Builder\Tables::create();
	Guide\Builder\Path_Tables::create();
	Guide\Structure\Structure_Tables::create();
	Guide\Structure\Structure_Tables::migrate_from_modules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'guide_activate' );

/**
 * Deactivation: flush rewrites only. Custom tables are left intact —
 * deactivating a plugin should never destroy user data (courses/progress).
 */
function guide_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'guide_deactivate' );
