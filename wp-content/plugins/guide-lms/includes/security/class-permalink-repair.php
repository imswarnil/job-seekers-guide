<?php
/**
 * Get the /index.php/ out of the site's URLs — carefully.
 *
 * WordPress decides at install time whether the server can rewrite URLs. If it
 * decides no — which happens when the check runs before Apache is fully up,
 * a common accident in a container that starts alongside its database — it
 * falls back to PATHINFO permalinks and every URL on the site carries an
 * /index.php/ forever after.
 *
 * The site works. It just looks like it was built in 2006, and every canonical
 * URL, sitemap entry and piece of structured data carries the prefix, so it is
 * not purely cosmetic either.
 *
 * ---------------------------------------------------------------------------
 * The reason this is fiddly rather than a one-line option update:
 *
 * If we remove the prefix and the server genuinely cannot rewrite, every URL
 * on the site except the front page 404s. A repair that can leave the site
 * broken and walk away is worse than the problem it fixes.
 *
 * So it verifies. It changes the structure, writes .htaccess, and then asks the
 * site for a real page over HTTP. If that page does not come back, it puts
 * everything back exactly as it was. The only way this ends with clean URLs is
 * if clean URLs were observed working.
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Permalink_Repair {

	const OPTION_DONE = 'jsl_permalink_repaired';

	/**
	 * Bump to let the repair try again on sites where it previously gave up.
	 *
	 * Stored rather than a bare flag, so improving the probe can be retried
	 * without an operator having to clear anything by hand.
	 */
	const ATTEMPT = '2';

	/** Called from the upgrade routine, so it runs on deploy and not per request. */
	public static function maybe_repair() {
		if ( get_option( self::OPTION_DONE ) === self::ATTEMPT ) {
			return;
		}

		$current = (string) get_option( 'permalink_structure' );

		// Nothing to do unless the structure actually carries the prefix.
		if ( false === strpos( $current, 'index.php' ) ) {
			update_option( self::OPTION_DONE, self::ATTEMPT, false );
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Without a writable .htaccess the rules cannot be published, so the
		// clean structure would 404 everything.
		if ( ! is_writable( ABSPATH . '.htaccess' ) && ! is_writable( ABSPATH ) ) {
			update_option( self::OPTION_DONE, self::ATTEMPT, false );
			return;
		}

		// Deliberately not gated on got_mod_rewrite().
		//
		// That function asks apache_get_modules(), which only exists when PHP
		// runs as an Apache module. Under PHP-FPM or CGI — which is what the
		// official WordPress image uses — the function is absent and
		// got_mod_rewrite() reports false on a server where rewriting works
		// perfectly well. Gating on it means the repair never runs anywhere it
		// is actually needed.
		//
		// The loopback probe below tests the real thing instead of a proxy for
		// it, so it is both stricter and more accurate.

		// Try it once, and only once, whatever the outcome — a repair that
		// retries on every deploy is a repair that flaps.
		update_option( self::OPTION_DONE, self::ATTEMPT, false );

		$candidate = str_replace( '/index.php', '', $current );

		if ( '' === $candidate || '/' === $candidate ) {
			$candidate = '/%postname%/';
		}

		self::apply( $candidate );

		if ( self::pretty_urls_work() ) {
			return;
		}

		// They do not. Put it back exactly as it was.
		self::apply( $current );
	}

	private static function apply( string $structure ) {
		global $wp_rewrite;

		update_option( 'permalink_structure', $structure );

		if ( isset( $wp_rewrite ) && is_object( $wp_rewrite ) ) {
			$wp_rewrite->set_permalink_structure( $structure );
			$wp_rewrite->init();
			$wp_rewrite->flush_rules( true );
		}

		if ( function_exists( 'save_mod_rewrite_rules' ) ) {
			save_mod_rewrite_rules();
		}
	}

	/**
	 * Ask the site for a page that only resolves when rewriting works.
	 *
	 * A loopback request, which is the same mechanism core uses for its own
	 * site-health checks. The course archive is a good probe because it exists
	 * on every install of this plugin and is public.
	 */
	private static function pretty_urls_work(): bool {
		$path = '/courses/';
		$home = home_url( $path );

		// First the ordinary way.
		if ( self::fetch_ok( $home ) ) {
			return true;
		}

		// Then a true loopback: straight at the local web server, with the Host
		// header the site expects.
		//
		// A container behind a reverse proxy usually cannot resolve its own
		// public hostname — the DNS record points at the proxy, which is
		// outside. So the first attempt can fail on a server where rewriting
		// works perfectly, and taking that as a verdict means the repair never
		// succeeds anywhere it is actually needed.
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		foreach ( array( 'http://127.0.0.1', 'http://localhost' ) as $origin ) {
			if ( self::fetch_ok( $origin . $path, $host ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * One probe request.
	 *
	 * Redirects are not followed, but a redirect is not automatically a
	 * failure. Probing over plain http against a site whose home URL is https
	 * produces a canonical 301 — and that redirect is itself the proof we
	 * want, because WordPress only got far enough to issue it by resolving the
	 * clean URL. A rewrite that did not work produces a 404 instead.
	 *
	 * So: success is a 2xx, or a redirect that still points at the same path.
	 * A redirect to /index.php/courses/ is a failure, which is exactly the
	 * case being guarded against.
	 */
	private static function fetch_ok( string $url, string $host = '' ): bool {
		$args = array(
			'timeout'     => 10,
			'redirection' => 0,
			'sslverify'   => false, // Talking to ourselves; the certificate is not what is under test.
			'headers'     => array( 'Cache-Control' => 'no-cache' ),
		);

		if ( '' !== $host ) {
			$args['headers']['Host'] = $host;
		}

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( $code >= 200 && $code < 300 ) {
			return true;
		}

		if ( $code < 300 || $code >= 400 ) {
			return false;
		}

		$location = (string) wp_remote_retrieve_header( $response, 'location' );

		if ( '' === $location ) {
			return false;
		}

		$wanted = (string) wp_parse_url( $url, PHP_URL_PATH );
		$got    = (string) wp_parse_url( $location, PHP_URL_PATH );

		return untrailingslashit( $wanted ) === untrailingslashit( $got );
	}
}
