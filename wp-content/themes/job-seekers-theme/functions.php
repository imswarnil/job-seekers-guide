<?php
/**
 * Job Seekers Theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_THEME_VERSION', '0.1.0' );
define( 'JSL_THEME_DIR', get_template_directory() );
define( 'JSL_THEME_URI', get_template_directory_uri() );

require_once JSL_THEME_DIR . '/inc/dark-mode.php';

add_action( 'after_setup_theme', 'jsl_theme_setup' );

function jsl_theme_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	load_theme_textdomain( 'job-seekers-theme', JSL_THEME_DIR . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'jsl_theme_assets' );

function jsl_theme_assets() {
	wp_enqueue_style(
		'jsl-base',
		JSL_THEME_URI . '/assets/css/base.css',
		array(),
		JSL_THEME_VERSION
	);

	wp_enqueue_script(
		'jsl-dark-mode',
		JSL_THEME_URI . '/assets/js/dark-mode.js',
		array(),
		JSL_THEME_VERSION,
		true
	);
}
