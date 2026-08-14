<?php
/**
 * Plugin Name: Job Seekers LMS
 * Plugin URI: https://github.com/imswarnil/job-seekers-guide
 * Description: Open-source, structured-learning-path LMS for job seekers. Courses, lessons, learning paths, enrollment/progress tracking, Dodo Payments checkout, and in-browser code practice.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Job Seekers Guide
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: job-seekers-lms
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_VERSION', '0.1.0' );
define( 'JSL_PLUGIN_FILE', __FILE__ );
define( 'JSL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JSL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once JSL_PLUGIN_DIR . 'includes/post-types/class-post-types.php';
require_once JSL_PLUGIN_DIR . 'includes/enrollment/class-tables.php';
require_once JSL_PLUGIN_DIR . 'includes/api/class-course-api.php';

/**
 * Boot the plugin.
 */
function jsl_boot() {
	JSL\Post_Types::init();
}
add_action( 'plugins_loaded', 'jsl_boot' );

/**
 * Activation: register post types/taxonomies so rewrite rules are known,
 * create custom tables, then flush rewrites.
 */
function jsl_activate() {
	JSL\Post_Types::init();
	JSL\Enrollment\Tables::create();
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
