<?php
/**
 * Sign in / sign up with Google (OAuth 2.0 + OpenID Connect).
 *
 * Flow: authorization code with PKCE.
 *
 *   /auth/google/start     — mints state + PKCE verifier, redirects to Google
 *   /auth/google/callback  — validates state, exchanges the code, logs in
 *
 * Security notes, because this is the one place a mistake logs the wrong
 * person in:
 *
 * - CSRF / login-fixation: `state` is random per attempt and bound to the
 *   browser two ways — an HttpOnly cookie AND a short-lived transient. Both
 *   must match, and the transient is deleted on first use, so a captured
 *   callback URL cannot be replayed.
 * - PKCE (S256) means an intercepted authorization code is useless without
 *   the verifier, which never leaves this server.
 * - The ID token is read from the token endpoint response over TLS — the
 *   back channel, authenticated by our client secret — not from the browser
 *   redirect. Per Google's own guidance that makes local signature
 *   verification unnecessary; we still validate iss, aud, exp and nonce, and
 *   we refuse to trust an email Google has not marked verified.
 * - Account linking only ever happens on a *verified* email. Without that
 *   check, anyone who could register an unverified Google address matching
 *   an existing user could take over that account.
 * - Tokens are never written to the log or to the database.
 */

namespace Guide\Auth;

defined( 'ABSPATH' ) || exit;

class Google_Auth {

	const OPTION_ENABLED       = 'jsl_google_enabled';
	const OPTION_CLIENT_ID     = 'jsl_google_client_id';
	const OPTION_CLIENT_SECRET = 'jsl_google_client_secret';
	const OPTION_ALLOW_SIGNUP  = 'jsl_google_allow_signup';

	const AUTH_ENDPOINT  = 'https://accounts.google.com/o/oauth2/v2/auth';
	const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

	const STATE_COOKIE = 'jsl_google_oauth';
	const STATE_TTL    = 600; // 10 minutes to complete a sign-in.

	/** User meta holding Google's stable subject identifier. */
	const META_SUB = 'jsl_google_sub';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		// Priority 0, ahead of core's redirect_canonical — an OAuth callback
		// must not be answered with a redirect of our own.
		add_action( 'template_redirect', array( __CLASS__, 'route' ), 0 );

		// Buttons on the wp-login screen.
		add_action( 'login_form', array( __CLASS__, 'render_login_button' ) );
		add_action( 'register_form', array( __CLASS__, 'render_login_button' ) );
	}

	/* ---------------------------------------------------------------
	 * Configuration
	 * --------------------------------------------------------------- */

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false )
			&& '' !== self::client_id()
			&& '' !== self::client_secret();
	}

	public static function client_id(): string {
		return (string) get_option( self::OPTION_CLIENT_ID, '' );
	}

	/**
	 * A constant in wp-config.php wins over the stored option, so a
	 * self-hoster can keep the secret out of the database entirely.
	 *
	 * GUIDE_GOOGLE_CLIENT_SECRET is the current name. JSL_GOOGLE_CLIENT_SECRET
	 * is still honoured because it may already be sitting in a live
	 * wp-config.php from before the rename — silently ignoring it would break
	 * sign-in on upgrade with no visible cause.
	 */
	public static function client_secret(): string {
		if ( defined( 'GUIDE_GOOGLE_CLIENT_SECRET' ) && GUIDE_GOOGLE_CLIENT_SECRET ) {
			return (string) GUIDE_GOOGLE_CLIENT_SECRET;
		}
		if ( defined( 'JSL_GOOGLE_CLIENT_SECRET' ) && JSL_GOOGLE_CLIENT_SECRET ) {
			return (string) JSL_GOOGLE_CLIENT_SECRET;
		}
		return (string) get_option( self::OPTION_CLIENT_SECRET, '' );
	}

	public static function allows_signup(): bool {
		return (bool) get_option( self::OPTION_ALLOW_SIGNUP, true );
	}

	public static function start_url( string $redirect_to = '' ): string {
		$url = home_url( '/auth/google/start' );
		return $redirect_to ? $url . '?redirect_to=' . rawurlencode( $redirect_to ) : $url;
	}

	/** The exact URI to register in the Google Cloud console. */
	public static function callback_url(): string {
		return home_url( '/auth/google/callback' );
	}

	/* ---------------------------------------------------------------
	 * Routing
	 * --------------------------------------------------------------- */

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^auth/google/(start|callback)/?$', 'index.php?jsl_google_auth=$matches[1]', 'top' );
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'jsl_google_auth';
		return $vars;
	}

	public static function route() {
		$step = get_query_var( 'jsl_google_auth' );

		if ( ! $step ) {
			return;
		}

		if ( ! self::is_enabled() ) {
			self::fail( __( 'Google sign-in is not configured.', 'guide-lms' ) );
		}

		if ( 'start' === $step ) {
			self::handle_start();
		}

		if ( 'callback' === $step ) {
			self::handle_callback();
		}
	}

	/* ---------------------------------------------------------------
	 * Step 1 — send the user to Google
	 * --------------------------------------------------------------- */

	private static function handle_start() {
		if ( is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/my-learning/' ) );
			exit;
		}

		$state    = self::random_token();
		$nonce    = self::random_token();
		$verifier = self::random_token( 64 );

		$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';

		// Server side of the state: short-lived, single use.
		set_transient(
			self::transient_key( $state ),
			array(
				'nonce'       => $nonce,
				'verifier'    => $verifier,
				'redirect_to' => $redirect_to,
			),
			self::STATE_TTL
		);

		// Browser side of the state: proves the callback belongs to the same
		// browser that started the flow.
		self::set_state_cookie( $state );

		// Built with http_build_query rather than add_query_arg: this URL has
		// to match Google's expectations byte for byte, and http_build_query
		// has one unambiguous encoding rule.
		$authorize = self::AUTH_ENDPOINT . '?' . http_build_query(
			array(
				'client_id'             => self::client_id(),
				'redirect_uri'          => self::callback_url(),
				'response_type'         => 'code',
				'scope'                 => 'openid email profile',
				'state'                 => $state,
				'nonce'                 => $nonce,
				'code_challenge'        => self::pkce_challenge( $verifier ),
				'code_challenge_method' => 'S256',
				'prompt'                => 'select_account',
			),
			'',
			'&',
			PHP_QUERY_RFC3986
		);

		// Not wp_safe_redirect: this deliberately leaves the site, and
		// the destination is a constant we control, not user input.
		wp_redirect( $authorize );
		exit;
	}

	/* ---------------------------------------------------------------
	 * Step 2 — Google sends the user back
	 * --------------------------------------------------------------- */

	private static function handle_callback() {
		if ( '' !== self::raw_query_param( 'error' ) ) {
			// User cancelled at Google's consent screen, or Google refused.
			self::clear_state_cookie();
			wp_safe_redirect( wp_login_url() );
			exit;
		}

		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';

		$cookie_state = isset( $_COOKIE[ self::STATE_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::STATE_COOKIE ] ) ) : '';

		self::clear_state_cookie();

		if ( ! $state || ! $code || ! $cookie_state || ! hash_equals( $cookie_state, $state ) ) {
			self::fail( __( 'Sign-in could not be verified. Please try again.', 'guide-lms' ) );
		}

		$stash = get_transient( self::transient_key( $state ) );
		delete_transient( self::transient_key( $state ) ); // Single use.

		if ( ! is_array( $stash ) || empty( $stash['verifier'] ) ) {
			self::fail( __( 'Your sign-in link expired. Please try again.', 'guide-lms' ) );
		}

		$claims = self::exchange_code( $code, (string) $stash['verifier'] );

		if ( is_wp_error( $claims ) ) {
			self::fail( $claims->get_error_message() );
		}

		// The nonce ties this ID token to the request we started.
		if ( ! isset( $claims['nonce'] ) || ! hash_equals( (string) $stash['nonce'], (string) $claims['nonce'] ) ) {
			self::fail( __( 'Sign-in could not be verified. Please try again.', 'guide-lms' ) );
		}

		$user_id = self::resolve_user( $claims );

		if ( is_wp_error( $user_id ) ) {
			self::fail( $user_id->get_error_message() );
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

		$redirect_to = ! empty( $stash['redirect_to'] ) ? $stash['redirect_to'] : home_url( '/my-learning/' );

		// wp_safe_redirect confines this to our own host even though the
		// value originated from a query parameter.
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Swap the authorization code for tokens and return the ID token claims.
	 *
	 * @return array|\WP_Error
	 */
	private static function exchange_code( string $code, string $verifier ) {
		$response = wp_remote_post(
			self::TOKEN_ENDPOINT,
			array(
				'timeout' => 15,
				'body'    => array(
					'code'          => $code,
					'client_id'     => self::client_id(),
					'client_secret' => self::client_secret(),
					'redirect_uri'  => self::callback_url(),
					'grant_type'    => 'authorization_code',
					'code_verifier' => $verifier,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'jsl_google_network', __( 'Could not reach Google. Please try again.', 'guide-lms' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( wp_remote_retrieve_response_code( $response ) >= 400 || empty( $body['id_token'] ) ) {
			// Deliberately vague to the user; the detail goes to the log, and
			// never includes the token payload.
			error_log( '[JSL] Google token exchange failed: ' . sanitize_text_field( (string) ( $body['error'] ?? 'unknown' ) ) );
			return new \WP_Error( 'jsl_google_token', __( 'Google sign-in failed. Please try again.', 'guide-lms' ) );
		}

		return self::validate_id_token( (string) $body['id_token'] );
	}

	/**
	 * Decode and validate the ID token's claims.
	 *
	 * The signature is not re-checked here on purpose: this token came
	 * straight from Google's token endpoint over TLS, on a channel
	 * authenticated with our client secret, which is the case Google's
	 * documentation explicitly says does not require local verification.
	 * What still matters — and is checked — is that the claims say what we
	 * expect.
	 *
	 * @return array|\WP_Error
	 */
	private static function validate_id_token( string $id_token ) {
		$parts = explode( '.', $id_token );

		if ( 3 !== count( $parts ) ) {
			return new \WP_Error( 'jsl_google_jwt', __( 'Google sign-in failed. Please try again.', 'guide-lms' ) );
		}

		$claims = json_decode( self::base64url_decode( $parts[1] ), true );

		if ( ! is_array( $claims ) ) {
			return new \WP_Error( 'jsl_google_jwt', __( 'Google sign-in failed. Please try again.', 'guide-lms' ) );
		}

		$issuer_ok = in_array( $claims['iss'] ?? '', array( 'https://accounts.google.com', 'accounts.google.com' ), true );

		// aud must be OUR client id — otherwise a token minted for a
		// different application would be accepted here.
		$audience_ok = isset( $claims['aud'] ) && hash_equals( self::client_id(), (string) $claims['aud'] );
		$unexpired   = isset( $claims['exp'] ) && (int) $claims['exp'] > time();

		if ( ! $issuer_ok || ! $audience_ok || ! $unexpired ) {
			return new \WP_Error( 'jsl_google_claims', __( 'Google sign-in could not be verified.', 'guide-lms' ) );
		}

		if ( empty( $claims['sub'] ) || empty( $claims['email'] ) ) {
			return new \WP_Error( 'jsl_google_claims', __( 'Google did not share an email address.', 'guide-lms' ) );
		}

		// An unverified address is not proof of anything.
		if ( empty( $claims['email_verified'] ) || ! filter_var( $claims['email_verified'], FILTER_VALIDATE_BOOLEAN ) ) {
			return new \WP_Error( 'jsl_google_unverified', __( 'Please verify your email with Google first.', 'guide-lms' ) );
		}

		return $claims;
	}

	/**
	 * Map Google's identity to a WordPress user: existing link, then
	 * verified-email match, then (optionally) a new account.
	 *
	 * @return int|\WP_Error User ID.
	 */
	private static function resolve_user( array $claims ) {
		$sub   = (string) $claims['sub'];
		$email = sanitize_email( (string) $claims['email'] );

		// 1. Already linked.
		$linked = get_users(
			array(
				'meta_key'    => self::META_SUB,
				'meta_value'  => $sub,
				'number'      => 1,
				'fields'      => 'ID',
				'count_total' => false,
			)
		);

		if ( ! empty( $linked ) ) {
			return (int) $linked[0];
		}

		// 2. Same verified email as an existing account — link them.
		$existing = get_user_by( 'email', $email );

		if ( $existing ) {
			update_user_meta( $existing->ID, self::META_SUB, $sub );
			return (int) $existing->ID;
		}

		// 3. New account.
		if ( ! self::allows_signup() ) {
			return new \WP_Error( 'jsl_google_no_signup', __( 'This site is not accepting new Google sign-ups.', 'guide-lms' ) );
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => self::unique_login( $email ),
				'user_email'   => $email,
				// Random, unknown to anyone: the account is Google-only until
				// the user sets a password through the normal reset flow.
				'user_pass'    => wp_generate_password( 32, true, true ),
				'display_name' => sanitize_text_field( (string) ( $claims['name'] ?? strstr( $email, '@', true ) ) ),
				'first_name'   => sanitize_text_field( (string) ( $claims['given_name'] ?? '' ) ),
				'last_name'    => sanitize_text_field( (string) ( $claims['family_name'] ?? '' ) ),
				'role'         => 'subscriber',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, self::META_SUB, $sub );
		do_action( 'jsl_google_user_registered', $user_id, $claims );

		return (int) $user_id;
	}

	/**
	 * Derive a free login name from the email's local part.
	 */
	private static function unique_login( string $email ): string {
		$base = sanitize_user( (string) strstr( $email, '@', true ), true );
		$base = $base ?: 'learner';

		$login = $base;
		$i     = 1;

		while ( username_exists( $login ) ) {
			$login = $base . ++$i;
		}

		return $login;
	}

	/* ---------------------------------------------------------------
	 * UI
	 * --------------------------------------------------------------- */

	/**
	 * "Continue with Google" on the wp-login form.
	 */
	public static function render_login_button() {
		if ( ! self::is_enabled() ) {
			return;
		}

		$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
		?>
		<div class="guide-google-signin">
			<div class="guide-google-signin__divider"><span><?php esc_html_e( 'or', 'guide-lms' ); ?></span></div>
			<a class="guide-google-signin__btn" href="<?php echo esc_url( self::start_url( $redirect_to ) ); ?>">
				<svg viewBox="0 0 48 48" width="18" height="18" aria-hidden="true">
					<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
					<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
					<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
					<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
				</svg>
				<?php esc_html_e( 'Continue with Google', 'guide-lms' ); ?>
			</a>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------
	 * Helpers
	 * --------------------------------------------------------------- */

	/**
	 * Read a parameter from the raw query string.
	 *
	 * Needed for `error` specifically: WP::parse_request() calls
	 * `unset( $error, $_GET['error'] )` while routing, so by the time any
	 * hook runs, Google's "the user cancelled" signal has been deleted from
	 * $_GET. The other parameters we read (code, state) are not WP query
	 * vars and survive normally.
	 */
	private static function raw_query_param( string $key ): string {
		$query = isset( $_SERVER['QUERY_STRING'] ) ? (string) wp_unslash( $_SERVER['QUERY_STRING'] ) : '';

		if ( '' === $query ) {
			return '';
		}

		parse_str( $query, $params );

		return isset( $params[ $key ] ) ? sanitize_text_field( (string) $params[ $key ] ) : '';
	}

	private static function random_token( int $bytes = 32 ): string {
		return rtrim( strtr( base64_encode( random_bytes( $bytes ) ), '+/', '-_' ), '=' );
	}

	private static function pkce_challenge( string $verifier ): string {
		return rtrim( strtr( base64_encode( hash( 'sha256', $verifier, true ) ), '+/', '-_' ), '=' );
	}

	private static function base64url_decode( string $value ): string {
		return (string) base64_decode( strtr( $value, '-_', '+/' ) . str_repeat( '=', ( 4 - strlen( $value ) % 4 ) % 4 ) );
	}

	private static function transient_key( string $state ): string {
		return 'jsl_goauth_' . hash( 'sha256', $state );
	}

	private static function set_state_cookie( string $state ): void {
		setcookie(
			self::STATE_COOKIE,
			$state,
			array(
				'expires'  => time() + self::STATE_TTL,
				'path'     => '/',
				'domain'   => '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax', // Must survive the top-level redirect back from Google.
			)
		);
	}

	private static function clear_state_cookie(): void {
		setcookie(
			self::STATE_COOKIE,
			'',
			array(
				'expires'  => time() - 3600,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * End the flow with a message rather than a blank page or a stack trace.
	 */
	private static function fail( string $message ) {
		wp_die(
			esc_html( $message ),
			esc_html__( 'Sign-in failed', 'guide-lms' ),
			array(
				'response'  => 400,
				'back_link' => true,
			)
		);
	}
}
