<?php
/**
 * Security hardening for the whole site.
 *
 * - XML-RPC off (biggest brute-force / pingback-abuse surface)
 * - No WP version leakage (generator meta, ?ver= where it maps to core)
 * - Security headers on every front-end response
 * - User-enumeration blocked (?author=N scans + public wp/v2/users)
 * - Generic login errors (no "wrong password for user X")
 * - File editing in wp-admin disabled
 * - Pingbacks/trackbacks dead
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Hardening {

	public static function init() {
		// Kill XML-RPC entirely — including the endpoint itself.
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', '__return_empty_array' );
		add_action( 'init', function () {
			if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
				status_header( 403 );
				exit( 'XML-RPC is disabled on this site.' );
			}
		}, 0 );

		// Strip discovery/meta tags that leak software + endpoints.
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		add_filter( 'the_generator', '__return_empty_string' );

		add_action( 'send_headers', array( __CLASS__, 'security_headers' ) );
		add_action( 'parse_request', array( __CLASS__, 'block_author_enumeration' ) );
		add_action( 'parse_request', array( __CLASS__, 'block_core_readme' ), 1 );
		add_filter( 'rest_endpoints', array( __CLASS__, 'restrict_user_endpoints' ) );
		add_filter( 'login_errors', array( __CLASS__, 'generic_login_error' ) );
		add_filter( 'wp_headers', array( __CLASS__, 'strip_pingback_header' ) );

		// No theme/plugin file editing from wp-admin.
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		// Pingbacks/trackbacks off.
		add_filter( 'pings_open', '__return_false', 20 );
	}

	public static function security_headers() {
		if ( is_admin() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
	}

	/**
	 * /?author=N (and author archives generally) leak usernames; this is a
	 * courses site, not a blog — send them home.
	 */
	/**
	 * Refuse /readme.html and /license.txt.
	 *
	 * Both ship with WordPress and readme.html states the exact version, which
	 * is the first thing an automated scanner reads to decide which exploits to
	 * try. Ideally the web server refuses these before PHP is involved — see
	 * docs/help-centre.md — but a rule here means the leak is closed even on a
	 * host where nobody edited a vhost.
	 */
	public static function block_core_readme() {
		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
		$path = strtolower( trim( (string) $path, '/' ) );

		if ( in_array( $path, array( 'readme.html', 'license.txt', 'wp-config-sample.php' ), true ) ) {
			status_header( 404 );
			nocache_headers();
			exit;
		}
	}

	/**
	 * Block ?author=N and /author/{slug}/ scans.
	 *
	 * Hooked on `parse_request`, not `template_redirect`, and that matters:
	 * core's redirect_canonical() also runs on template_redirect and would turn
	 * /?author=1 into /author/admin/ before we ever got a look — handing the
	 * scanner the username in a Location header, which is the exact thing this
	 * is meant to prevent.
	 *
	 * The query vars are removed and the request is turned into a 404 rather
	 * than redirected. A redirect confirms something was there; a 404 says the
	 * author archive does not exist on this site, which is simply true — this
	 * is an LMS, and nothing links to one.
	 *
	 * @param \WP $wp
	 */
	public static function block_author_enumeration( $wp ) {
		if ( is_admin() ) {
			return;
		}

		$requested = isset( $_GET['author'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ! empty( $wp->query_vars['author'] )
			|| ! empty( $wp->query_vars['author_name'] );

		if ( ! $requested ) {
			return;
		}

		// Unset first: leaving them in place lets redirect_canonical rebuild
		// the pretty author URL from the id.
		unset( $wp->query_vars['author'], $wp->query_vars['author_name'] );

		$wp->query_vars['error'] = '404';
	}

	/**
	 * wp/v2/users lists every author to anonymous visitors by default.
	 * Only users who can manage users need it.
	 */
	public static function restrict_user_endpoints( $endpoints ) {
		if ( current_user_can( 'list_users' ) ) {
			return $endpoints;
		}
		foreach ( $endpoints as $route => $handlers ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}

	public static function generic_login_error() {
		return __( 'Invalid credentials. Please try again.', 'guide-lms' );
	}

	public static function strip_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
}
