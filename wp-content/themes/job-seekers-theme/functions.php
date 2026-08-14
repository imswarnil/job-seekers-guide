<?php
/**
 * Job Seekers Theme bootstrap. Classic (non-block) theme.
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_THEME_VERSION', '0.2.0' );
define( 'JSL_THEME_DIR', get_template_directory() );
define( 'JSL_THEME_URI', get_template_directory_uri() );

require_once JSL_THEME_DIR . '/inc/dark-mode.php';

add_action( 'after_setup_theme', 'jsl_theme_setup' );

function jsl_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'job-seekers-theme' ),
		)
	);
	load_theme_textdomain( 'job-seekers-theme', JSL_THEME_DIR . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'jsl_theme_assets' );

function jsl_theme_assets() {
	wp_enqueue_style( 'jsl-tokens', JSL_THEME_URI . '/assets/css/tokens.css', array(), JSL_THEME_VERSION );
	wp_enqueue_style( 'jsl-base', JSL_THEME_URI . '/assets/css/base.css', array( 'jsl-tokens' ), JSL_THEME_VERSION );
	wp_enqueue_style( 'jsl-typography', JSL_THEME_URI . '/assets/css/typography.css', array( 'jsl-tokens' ), JSL_THEME_VERSION );
	wp_enqueue_style( 'jsl-components', JSL_THEME_URI . '/assets/css/components.css', array( 'jsl-tokens' ), JSL_THEME_VERSION );

	wp_enqueue_script( 'jsl-theme-toggle', JSL_THEME_URI . '/assets/js/theme.js', array(), JSL_THEME_VERSION, true );
}
