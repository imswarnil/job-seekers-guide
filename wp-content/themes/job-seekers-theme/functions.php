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

require_once JSL_THEME_DIR . '/inc/icons.php';
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
	// Plus Jakarta Sans (display) + Inter (body/UI) + JetBrains Mono (code).
	wp_enqueue_style(
		'jsl-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@400;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'jsl-app', JSL_THEME_URI . '/assets/css/app.css', array( 'jsl-fonts' ), jsl_asset_version( '/assets/css/app.css' ) );
	wp_enqueue_script( 'jsl-theme', JSL_THEME_URI . '/assets/js/theme.js', array(), jsl_asset_version( '/assets/js/theme.js' ), true );
}

/**
 * Cache-busting version for a theme asset: the file's own modification time.
 *
 * The theme version alone is not enough — CSS and JS change far more often
 * than the version constant gets bumped, and a stale cached stylesheet after
 * a deploy looks exactly like a broken design.
 *
 * @param string $relative_path Path from the theme root, with a leading slash.
 */
function jsl_asset_version( $relative_path ) {
	$file = JSL_THEME_DIR . $relative_path;
	$time = file_exists( $file ) ? filemtime( $file ) : 0;

	return $time ? JSL_THEME_VERSION . '.' . $time : JSL_THEME_VERSION;
}

/**
 * Inline Phosphor icon.
 *
 * Icons are baked into inc/icons.php by `npm run icons`, so rendering one
 * costs an array lookup — no icon font, no network request, and it works
 * offline in the PWA. Append "-fill" to a name for the solid variant
 * (e.g. jsl_icon( 'check-circle-fill' )).
 *
 * @param string $name  Phosphor icon name, or a legacy alias.
 * @param string $class Classes for the <svg> element.
 * @param string $title Accessible name. Empty (default) renders the icon as
 *                      decorative, which is right when adjacent text already
 *                      names the action.
 */
function jsl_icon( $name, $class = 'w-5 h-5', $title = '' ) {
	static $paths = null;

	if ( null === $paths ) {
		$paths = jsl_icon_paths();
	}

	// Names used by templates written before the Phosphor switch.
	$aliases = array(
		'play'    => 'play-fill',
		'arrow-r' => 'arrow-right',
		'arrow-l' => 'arrow-left',
		'layers'  => 'stack',
		'doc'     => 'article',
		'menu'    => 'list',
		'spark'   => 'sparkle',
	);

	$key = $aliases[ $name ] ?? $name;

	if ( ! isset( $paths[ $key ] ) ) {
		return '';
	}

	$a11y = $title
		? 'role="img" aria-label="' . esc_attr( $title ) . '"'
		: 'aria-hidden="true" focusable="false"';

	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 256 256" fill="currentColor" ' . $a11y . '>' . $paths[ $key ] . '</svg>';
}

/**
 * The brand mark, inline. A magnifying glass with a briefcase in the lens —
 * the job search, and the thing being searched for.
 *
 * Kept in sync with assets/img/logo.svg (that file is the standalone asset
 * for the manifest, OG images and anywhere an <img> is needed; this is the
 * inline version so it can take its colour from its container).
 *
 * @param string $class Classes for the <svg>.
 */
function jsl_logo_mark( $class = 'w-5 h-5' ) {
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 256 256" fill="none" aria-hidden="true" focusable="false">'
		. '<circle cx="106" cy="106" r="72" stroke="currentColor" stroke-width="18"/>'
		. '<path d="M158 158L214 214" stroke="currentColor" stroke-width="24" stroke-linecap="round"/>'
		. '<path d="M90 90V82a12 12 0 0 1 12-12h8a12 12 0 0 1 12 12v8" stroke="currentColor" stroke-width="13" stroke-linecap="round"/>'
		. '<path d="M70 90h72a12 12 0 0 1 12 12v34a12 12 0 0 1-12 12H70a12 12 0 0 1-12-12v-34a12 12 0 0 1 12-12Zm28 22h16a4 4 0 0 1 0 8H98a4 4 0 0 1 0-8Z" fill="currentColor" fill-rule="evenodd"/>'
		. '</svg>';
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
