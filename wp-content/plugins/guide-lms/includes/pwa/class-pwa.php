<?php
/**
 * Progressive web app: manifest, service worker, offline fallback, install
 * affordance.
 *
 * Everything is generated and served from PHP — nothing to upload, and the
 * manifest always reflects the current settings and site URL.
 *
 * Caching strategy, deliberately conservative for a site with paid content:
 *
 * - HTML  — network first, cache only as a fallback for when the network is
 *           gone. A cache-first HTML strategy on an LMS would happily serve
 *           a logged-out or pre-purchase version of a page to someone who
 *           has since signed in or paid.
 * - Assets — stale-while-revalidate (CSS/JS/fonts/images), which is safe
 *           because they are versioned and contain nothing personal.
 * - Never cached — anything under /wp-admin, /wp-login.php, the REST API,
 *           or any response to a non-GET request, and any response that
 *           carries a Set-Cookie or Vary: Cookie.
 */

namespace Guide\Pwa;

defined( 'ABSPATH' ) || exit;

class Pwa {

	const OPTION_ENABLED     = 'jsl_pwa_enabled';
	const OPTION_NAME        = 'jsl_pwa_name';
	const OPTION_SHORT_NAME  = 'jsl_pwa_short_name';
	const OPTION_THEME_COLOR = 'jsl_pwa_theme_color';

	/**
	 * Bumped whenever the service worker's logic changes, so browsers
	 * discard the old caches instead of serving them forever.
	 */
	const SW_VERSION = '1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		// Priority 0: these are extensionful, un-slashed URLs, so they have to
		// be served before core's redirect_canonical bounces them to a
		// trailing-slash variant.
		add_action( 'template_redirect', array( __CLASS__, 'serve' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'render_head' ), 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_registration' ) );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false );
	}

	public static function app_name(): string {
		return (string) ( get_option( self::OPTION_NAME, '' ) ?: get_bloginfo( 'name' ) );
	}

	public static function short_name(): string {
		return (string) ( get_option( self::OPTION_SHORT_NAME, '' ) ?: mb_substr( self::app_name(), 0, 12 ) );
	}

	/**
	 * Always re-sanitized on the way out, not just on save: this value is
	 * printed into a <style> block on the offline page, where escaping
	 * helpers meant for HTML would not stop a CSS injection.
	 */
	public static function theme_color(): string {
		$stored = sanitize_hex_color( (string) get_option( self::OPTION_THEME_COLOR, '' ) );
		return $stored ?: '#414BA0';
	}

	/* ---------------------------------------------------------------
	 * Routes
	 * --------------------------------------------------------------- */

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^manifest\.webmanifest$', 'index.php?jsl_pwa=manifest', 'top' );
		// Served from the root so the worker's scope covers the whole site.
		add_rewrite_rule( '^sw\.js$', 'index.php?jsl_pwa=sw', 'top' );
		add_rewrite_rule( '^offline/?$', 'index.php?jsl_pwa=offline', 'top' );
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'jsl_pwa';
		return $vars;
	}

	public static function serve() {
		$what = get_query_var( 'jsl_pwa' );

		if ( ! $what ) {
			return;
		}

		switch ( $what ) {
			case 'manifest':
				self::serve_manifest();
			case 'sw':
				self::serve_service_worker();
			case 'offline':
				self::serve_offline();
		}
	}

	private static function serve_manifest() {
		nocache_headers();
		header( 'Content-Type: application/manifest+json; charset=utf-8' );

		$icon = self::icon_url();

		$manifest = array(
			'name'             => self::app_name(),
			'short_name'       => self::short_name(),
			'description'      => (string) get_bloginfo( 'description' ),
			'start_url'        => home_url( '/?source=pwa' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'orientation'      => 'portrait-primary',
			'background_color' => '#FCF8FF',
			'theme_color'      => self::theme_color(),
			'lang'             => get_bloginfo( 'language' ),
			'dir'              => is_rtl() ? 'rtl' : 'ltr',
			'categories'       => array( 'education', 'productivity' ),
			'icons'            => array(
				array(
					'src'     => $icon,
					'sizes'   => 'any',
					'type'    => 'image/svg+xml',
					'purpose' => 'any',
				),
				array(
					'src'     => $icon,
					'sizes'   => 'any',
					'type'    => 'image/svg+xml',
					'purpose' => 'maskable',
				),
			),
			'shortcuts'        => array(
				array(
					'name' => __( 'My Learning', 'guide-lms' ),
					'url'  => home_url( '/my-learning/' ),
				),
				array(
					'name' => __( 'Browse courses', 'guide-lms' ),
					'url'  => (string) get_post_type_archive_link( 'course' ),
				),
			),
		);

		echo wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * The app icon. The theme's favicon.svg is a filled tile, which is
	 * exactly what a maskable icon needs.
	 */
	public static function icon_url(): string {
		$theme_icon = get_template_directory() . '/assets/img/favicon.svg';

		if ( file_exists( $theme_icon ) ) {
			return get_template_directory_uri() . '/assets/img/favicon.svg';
		}

		return (string) get_site_icon_url( 512 );
	}

	private static function serve_service_worker() {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		// The worker itself must never be cached long, or a fix can't ship.
		header( 'Cache-Control: no-cache, max-age=0' );
		// Belt and braces: a worker served from / already has root scope.
		header( 'Service-Worker-Allowed: /' );

		$config = wp_json_encode(
			array(
				'version'     => self::SW_VERSION . '.' . GUIDE_VERSION,
				'offlineUrl'  => home_url( '/offline/' ),
				'precache'    => array_values( array_filter( array( home_url( '/offline/' ), self::icon_url() ) ) ),
			)
		);

		echo "const JSL = {$config};\n";
		readfile( GUIDE_PLUGIN_DIR . 'includes/pwa/service-worker.js' );
		exit;
	}

	private static function serve_offline() {
		status_header( 200 );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );

		// Deliberately self-contained: this page has to render when the
		// network — and therefore the stylesheet — may be unavailable.
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'You’re offline', 'guide-lms' ); ?> — <?php bloginfo( 'name' ); ?></title>
	<style>
		:root { color-scheme: light dark; }
		body {
			margin: 0; min-height: 100vh; display: grid; place-items: center;
			font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			background: #FCF8FF; color: #1B1B21; padding: 2rem; text-align: center;
		}
		@media (prefers-color-scheme: dark) { body { background: #121218; color: #E4E1EC; } }
		.mark { width: 64px; height: 64px; border-radius: 16px; background: <?php echo esc_html( self::theme_color() ); ?>; display: grid; place-items: center; margin: 0 auto 1.5rem; }
		h1 { font-size: 1.5rem; margin: 0 0 .5rem; letter-spacing: -0.02em; }
		p { margin: 0 auto 1.75rem; max-width: 32ch; line-height: 1.6; opacity: .75; }
		a { display: inline-block; padding: .75rem 1.5rem; border-radius: 999px; background: <?php echo esc_html( self::theme_color() ); ?>; color: #fff; text-decoration: none; font-weight: 600; }
	</style>
</head>
<body>
	<main>
		<div class="mark">
			<svg viewBox="0 0 256 256" width="34" height="34" fill="none" aria-hidden="true">
				<circle cx="106" cy="106" r="72" stroke="#fff" stroke-width="18"/>
				<path d="M158 158L214 214" stroke="#fff" stroke-width="24" stroke-linecap="round"/>
				<rect x="58" y="90" width="96" height="58" rx="12" fill="#fff"/>
			</svg>
		</div>
		<h1><?php esc_html_e( 'You’re offline', 'guide-lms' ); ?></h1>
		<p><?php esc_html_e( 'Lessons you have already opened are still available. Reconnect to load anything new.', 'guide-lms' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>"><?php esc_html_e( 'Go to My Learning', 'guide-lms' ); ?></a>
	</main>
</body>
</html>
		<?php
		exit;
	}

	/* ---------------------------------------------------------------
	 * Front-end wiring
	 * --------------------------------------------------------------- */

	public static function render_head() {
		if ( ! self::is_enabled() ) {
			return;
		}

		printf( "<link rel=\"manifest\" href=\"%s\">\n", esc_url( home_url( '/manifest.webmanifest' ) ) );
		printf( "<meta name=\"theme-color\" content=\"%s\">\n", esc_attr( self::theme_color() ) );
		printf( "<meta name=\"apple-mobile-web-app-capable\" content=\"yes\">\n" );
		printf( "<meta name=\"apple-mobile-web-app-status-bar-style\" content=\"default\">\n" );
		printf( "<meta name=\"apple-mobile-web-app-title\" content=\"%s\">\n", esc_attr( self::short_name() ) );
		printf( "<link rel=\"apple-touch-icon\" href=\"%s\">\n", esc_url( self::icon_url() ) );
	}

	public static function enqueue_registration() {
		if ( ! self::is_enabled() || is_admin() ) {
			return;
		}

		wp_register_script( 'guide-pwa', '', array(), GUIDE_VERSION, true );
		wp_enqueue_script( 'guide-pwa' );

		$sw_url = home_url( '/sw.js' );

		wp_add_inline_script(
			'guide-pwa',
			"if ('serviceWorker' in navigator) {
				window.addEventListener('load', function () {
					navigator.serviceWorker.register(" . wp_json_encode( $sw_url ) . ", { scope: '/' }).catch(function () {});
				});
			}"
		);
	}
}
