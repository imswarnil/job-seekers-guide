<?php
/**
 * Job Seekers Theme bootstrap. Classic (non-block) theme.
 *
 * Styling is Tailwind v4 compiled from src/app.css into assets/css/app.css
 * (committed, so production needs no Node). Design tokens live in
 * assets/css/tokens.css and are imported into the Tailwind build.
 */

defined( 'ABSPATH' ) || exit;

define( 'JSL_THEME_VERSION', '0.5.0' );
define( 'JSL_THEME_DIR', get_template_directory() );
define( 'JSL_THEME_URI', get_template_directory_uri() );

require_once JSL_THEME_DIR . '/inc/dark-mode.php';

add_action( 'after_setup_theme', 'jsl_theme_setup' );

function jsl_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	load_theme_textdomain( 'job-seekers-theme', JSL_THEME_DIR . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'jsl_theme_assets' );

// SVG favicon (assets/img/favicon.svg).
add_action( 'wp_head', 'jsl_theme_favicon', 5 );
function jsl_theme_favicon() {
	echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( JSL_THEME_URI . '/assets/img/favicon.svg' ) . '">' . "\n";
}

function jsl_theme_assets() {
	wp_enqueue_style(
		'jsl-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'jsl-app', JSL_THEME_URI . '/assets/css/app.css', array( 'jsl-fonts' ), JSL_THEME_VERSION );
	wp_enqueue_script( 'jsl-theme', JSL_THEME_URI . '/assets/js/theme.js', array(), JSL_THEME_VERSION, true );
}

/**
 * Small inline SVG icon helper. Icons are hand-drawn 24px strokes.
 *
 * @param string $name Icon key.
 * @param string $class Extra classes.
 */
function jsl_icon( $name, $class = 'w-5 h-5' ) {
	$paths = array(
		'play'      => '<path d="M8 5.5v13l11-6.5-11-6.5Z"/>',
		'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'arrow-r'   => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
		'arrow-l'   => '<path d="M20 12H4m6-6-6 6 6 6"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'layers'    => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
		'doc'       => '<path d="M7 3h7l5 5v13H7V3Z"/><path d="M14 3v5h5"/>',
		'lock'      => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'x'         => '<path d="m6 6 12 12M18 6 6 18"/>',
		'compass'   => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/>',
		'spark'     => '<path d="M12 3v4m0 10v4m9-9h-4M7 12H3m14.5-6.5-3 3m-5 5-3 3m11 0-3-3m-5-5-3-3"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}

/**
 * Escape an image src that may be a plugin-generated SVG data URI
 * (esc_url strips the data: scheme). Only base64 SVG data URIs are let
 * through verbatim; everything else goes through esc_url.
 */
function jsl_img_src( $src ) {
	if ( 0 === strpos( (string) $src, 'data:image/svg+xml;base64,' ) && preg_match( '/^[A-Za-z0-9+\/=,;:\-_]+$/', substr( $src, 5 ) ) ) {
		return esc_attr( $src );
	}
	return esc_url( $src );
}
