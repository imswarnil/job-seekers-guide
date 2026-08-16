<?php
/**
 * The learner's account area at /account/.
 *
 * Everything a signed-in person might reasonably want to do with their own
 * record, in one place: edit their details, see the state of their
 * subscription, read their billing history, open a receipt, and leave.
 *
 * Routed rather than requiring a WordPress page to exist, so a fresh install
 * has a working account area without anyone remembering to create a page.
 */

namespace Guide\Account;

use Guide\Access\Access;
use Guide\Billing\Billing;
use Guide\Enrollment\Enrollment;
use Guide\Payments\Subscription;

defined( 'ABSPATH' ) || exit;

class Account {

	const SLUG          = 'account';
	const QUERY_VAR     = 'guide_account';
	const REWRITE_OPTION = 'jsl_account_rewrite_version';
	const REWRITE_VERSION = '1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
		add_action( 'template_redirect', array( __CLASS__, 'route' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_flush_rewrites' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function url( string $sub = '' ): string {
		return home_url( '/' . self::SLUG . '/' . ( $sub ? trim( $sub, '/' ) . '/' : '' ) );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^' . self::SLUG . '/?$', 'index.php?' . self::QUERY_VAR . '=overview', 'top' );
		add_rewrite_rule( '^' . self::SLUG . '/receipt/([0-9]+)/?$', 'index.php?' . self::QUERY_VAR . '=receipt&guide_payment=$matches[1]', 'top' );
	}

	public static function register_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		$vars[] = 'guide_payment';
		return $vars;
	}

	public static function maybe_flush_rewrites() {
		if ( get_option( self::REWRITE_OPTION ) === self::REWRITE_VERSION ) {
			return;
		}
		self::add_rewrite_rules();
		flush_rewrite_rules();
		update_option( self::REWRITE_OPTION, self::REWRITE_VERSION, false );
	}

	/**
	 * Render the account area through the theme, so it inherits the site's
	 * header, footer and styling instead of being a bare page.
	 */
	public static function route() {
		$view = get_query_var( self::QUERY_VAR );

		if ( ! $view ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( self::url() ) );
			exit;
		}

		// An account page must never be cached or indexed — it is one person's
		// private record.
		nocache_headers();
		add_filter( 'wp_robots', 'wp_robots_no_robots' );

		status_header( 200 );

		$template = 'receipt' === $view ? 'guide-receipt.php' : 'guide-account.php';
		$located  = locate_template( array( $template ) );

		if ( $located ) {
			include $located;
			exit;
		}

		// No theme template: rather than a white screen, say so plainly.
		wp_die(
			esc_html__( 'The active theme does not provide an account template.', 'guide-lms' ),
			esc_html__( 'Account', 'guide-lms' ),
			array( 'response' => 200 )
		);
	}

	// -------------------------------------------------------------------------
	// Data for the templates
	// -------------------------------------------------------------------------

	/**
	 * Everything the overview needs, resolved once.
	 *
	 * @return array<string, mixed>
	 */
	public static function overview( int $user_id ): array {
		$expiry = Enrollment::subscription_expiry( $user_id );

		return array(
			'user'           => get_userdata( $user_id ),
			'has_all_access' => Access::has_all_access( $user_id ),
			'subscribed'     => Enrollment::has_platform_subscription( $user_id ),
			'expires_at'     => $expiry,
			'sub_enabled'    => class_exists( 'Guide\\Payments\\Subscription' ) && Subscription::is_enabled(),
			'sub_price'      => class_exists( 'Guide\\Payments\\Subscription' ) ? Subscription::price_label() : '',
			'payments'       => Billing::for_user( $user_id ),
		);
	}

	// -------------------------------------------------------------------------
	// REST: profile updates
	// -------------------------------------------------------------------------

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/account/profile',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_profile' ),
				'permission_callback' => 'is_user_logged_in',
			)
		);
	}

	/**
	 * Update the signed-in user's own display name and description.
	 *
	 * Deliberately narrow: no email, no role, no capability fields. Email
	 * changes need a confirmation flow to avoid becoming an account-takeover
	 * primitive, and that belongs in its own change rather than bolted on here.
	 */
	public static function update_profile( \WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new \WP_REST_Response( array( 'error' => __( 'Not signed in.', 'guide-lms' ) ), 401 );
		}

		$name = sanitize_text_field( (string) $request->get_param( 'display_name' ) );
		$bio  = sanitize_textarea_field( (string) $request->get_param( 'description' ) );

		$name = trim( mb_substr( $name, 0, 80 ) );
		$bio  = trim( mb_substr( $bio, 0, 500 ) );

		if ( '' === $name ) {
			return new \WP_REST_Response( array( 'error' => __( 'A display name is required.', 'guide-lms' ) ), 400 );
		}

		$result = wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => $name,
				'description'  => $bio,
			)
		);

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}

		return new \WP_REST_Response(
			array(
				'saved'        => true,
				'display_name' => $name,
			),
			200
		);
	}
}
