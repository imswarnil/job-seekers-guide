<?php
/**
 * Plugin Name: Job Seekers LMS
 * Plugin URI: https://github.com/imswarnil/job-seekers-guide
 * Description: Open-source, structured-learning-path LMS for job seekers. Courses, lessons, learning paths, a visual course builder, enrollment/progress tracking, and Dodo Payments checkout.
 * Version: 0.5.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Job Seekers Guide
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: job-seekers-lms
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_VERSION', '0.5.0' );
define( 'JSL_PLUGIN_FILE', __FILE__ );
define( 'JSL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JSL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once JSL_PLUGIN_DIR . 'includes/post-types/class-post-types.php';
require_once JSL_PLUGIN_DIR . 'includes/post-types/class-lesson-meta.php';
require_once JSL_PLUGIN_DIR . 'includes/post-types/class-course-meta.php';
require_once JSL_PLUGIN_DIR . 'includes/quiz/class-quiz.php';
require_once JSL_PLUGIN_DIR . 'includes/media/class-placeholder.php';
require_once JSL_PLUGIN_DIR . 'includes/schema/class-json-ld.php';
require_once JSL_PLUGIN_DIR . 'includes/progress/class-progress.php';
require_once JSL_PLUGIN_DIR . 'includes/enrollment/class-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/enrollment/class-enrollment.php';
require_once JSL_PLUGIN_DIR . 'includes/api/class-course-api.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/builder/class-rest.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-settings.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-course-pricing.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-checkout.php';
require_once JSL_PLUGIN_DIR . 'includes/payments/class-webhook.php';
require_once JSL_PLUGIN_DIR . 'includes/analytics/class-analytics.php';
require_once JSL_PLUGIN_DIR . 'includes/security/class-hardening.php';
require_once JSL_PLUGIN_DIR . 'includes/security/class-comments-off.php';
require_once JSL_PLUGIN_DIR . 'admin/class-lms-admin.php';
require_once JSL_PLUGIN_DIR . 'admin/class-console.php';
require_once JSL_PLUGIN_DIR . 'admin/class-admin-theme.php';
require_once JSL_PLUGIN_DIR . 'includes/cli/class-seed-command.php';

/**
 * Boot the plugin.
 */
function jsl_boot() {
	JSL\Post_Types::init();
	JSL\Lesson_Meta::init();
	JSL\Course_Meta::init();
	JSL\Quiz\Quiz::init();
	JSL\Schema\Json_Ld::init();
	JSL\Progress\Progress::init();
	JSL\Builder\Rest::init();
	JSL\Payments\Settings::init();
	JSL\Payments\Course_Pricing::init();
	JSL\Payments\Checkout::init();
	JSL\Payments\Webhook::init();
	JSL\Analytics\Analytics::init();
	JSL\Security\Hardening::init();
	JSL\Security\Comments_Off::init();
	JSL\Admin\Lms_Admin::init();
	JSL\Admin\Console::init();
	JSL\Admin\Admin_Theme::init();
	JSL\Cli\Seed_Command::register();
}
add_action( 'plugins_loaded', 'jsl_boot' );

/**
 * Activation: register post types/taxonomies so rewrite rules are known,
 * create custom tables, then flush rewrites.
 */
function jsl_activate() {
	JSL\Post_Types::init();
	JSL\Enrollment\Tables::create();
	JSL\Builder\Tables::create();
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
