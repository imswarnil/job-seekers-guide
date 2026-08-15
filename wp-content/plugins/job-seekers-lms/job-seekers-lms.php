<?php
/**
 * Plugin Name: Job Seekers LMS
 * Plugin URI: https://github.com/imswarnil/job-seekers-guide
 * Description: Open-source, structured-learning-path LMS for job seekers. Courses, lessons, learning paths, a visual course builder, enrollment/progress tracking, and Dodo Payments checkout.
 * Version: 0.6.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Job Seekers Guide
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: job-seekers-lms
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_VERSION', '0.6.0' );
define( 'JSL_PLUGIN_FILE', __FILE__ );
define( 'JSL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JSL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once JSL_PLUGIN_DIR . 'includes/post-types/class-post-types.php';
require_once JSL_PLUGIN_DIR . 'includes/post-types/class-permalinks.php';
require_once JSL_PLUGIN_DIR . 'includes/post-types/class-lesson-meta.php';
require_once JSL_PLUGIN_DIR . 'includes/post-types/class-course-meta.php';
require_once JSL_PLUGIN_DIR . 'includes/quiz/class-quiz.php';
require_once JSL_PLUGIN_DIR . 'includes/media/class-placeholder.php';
require_once JSL_PLUGIN_DIR . 'includes/schema/class-json-ld.php';
require_once JSL_PLUGIN_DIR . 'includes/progress/class-progress.php';
require_once JSL_PLUGIN_DIR . 'includes/enrollment/class-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/enrollment/class-enrollment.php';
require_once JSL_PLUGIN_DIR . 'includes/access/class-access.php';
require_once JSL_PLUGIN_DIR . 'includes/api/class-course-api.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-rest.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-path-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-path-rest.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-settings.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-course-pricing.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-checkout.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-subscription.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-webhook.php';
require_once JSL_PLUGIN_DIR . 'includes/analytics/class-analytics.php';
require_once JSL_PLUGIN_DIR . 'includes/auth/class-google-auth.php';
require_once JSL_PLUGIN_DIR . 'includes/seo/class-seo.php';
require_once JSL_PLUGIN_DIR . 'includes/pwa/class-pwa.php';
require_once JSL_PLUGIN_DIR . 'includes/security/class-hardening.php';
require_once JSL_PLUGIN_DIR . 'includes/security/class-comments-off.php';
require_once JSL_PLUGIN_DIR . 'admin/class-lms-admin.php';
require_once JSL_PLUGIN_DIR . 'admin/class-console.php';
require_once JSL_PLUGIN_DIR . 'admin/class-settings-page.php';
require_once JSL_PLUGIN_DIR . 'admin/class-admin-theme.php';
require_once JSL_PLUGIN_DIR . 'includes/cli/class-seed-command.php';

/**
 * Cache-busting version for a plugin asset: the file's own modification
 * time. JSL_VERSION alone is not enough — the console's CSS and JS change
 * far more often than the version constant is bumped, and a stale cached
 * console looks exactly like a broken feature.
 *
 * @param string $relative_path Path from the plugin root, no leading slash.
 */
function jsl_plugin_asset_version( $relative_path ) {
	$file = JSL_PLUGIN_DIR . $relative_path;
	$time = file_exists( $file ) ? filemtime( $file ) : 0;

	return $time ? JSL_VERSION . '.' . $time : JSL_VERSION;
}

/**
 * Boot the plugin.
 */
function jsl_boot() {
	JSL\Post_Types::init();
	JSL\Permalinks::init();
	JSL\Lesson_Meta::init();
	JSL\Course_Meta::init();
	JSL\Quiz\Quiz::init();
	JSL\Schema\Json_Ld::init();
	JSL\Progress\Progress::init();
	JSL\Builder\Rest::init();
	JSL\Builder\Path_Rest::init();
	JSL\Payments\Settings::init();
	JSL\Payments\Course_Pricing::init();
	JSL\Payments\Checkout::init();
	JSL\Payments\Subscription::init();
	JSL\Payments\Webhook::init();
	JSL\Access\Access::init();
	JSL\Auth\Google_Auth::init();
	JSL\Seo\Seo::init();
	JSL\Pwa\Pwa::init();
	JSL\Analytics\Analytics::init();
	JSL\Security\Hardening::init();
	JSL\Security\Comments_Off::init();
	JSL\Admin\Lms_Admin::init();
	JSL\Admin\Console::init();
	JSL\Admin\Settings_Page::init();
	JSL\Admin\Admin_Theme::init();
	JSL\Cli\Seed_Command::register();
}
add_action( 'plugins_loaded', 'jsl_boot' );

/**
 * Apply schema changes on upgrade, not just on activation — an existing
 * install updated in place (git pull / plugin update) never re-fires the
 * activation hook, so new columns would otherwise never appear.
 */
function jsl_maybe_upgrade_schema() {
	if ( get_option( 'jsl_db_version' ) === JSL_VERSION ) {
		return;
	}

	JSL\Enrollment\Tables::create();
	JSL\Builder\Tables::create();
	JSL\Builder\Path_Tables::create();
	JSL\Builder\Path_Tables::migrate_legacy_course_links();

	update_option( 'jsl_db_version', JSL_VERSION, false );
}
add_action( 'admin_init', 'jsl_maybe_upgrade_schema' );

/**
 * Activation: register post types/taxonomies so rewrite rules are known,
 * create custom tables, then flush rewrites.
 */
function jsl_activate() {
	JSL\Post_Types::register();
	JSL\Permalinks::add_rewrite_rules();
	JSL\Enrollment\Tables::create();
	JSL\Builder\Tables::create();
	JSL\Builder\Path_Tables::create();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'jsl_activate' );

/**
 * Deactivation: flush rewrites only. Custom tables are left intact —
 * deactivating a plugin should never destroy user data (courses/progress).
 */
function jsl_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'jsl_deactivate' );
