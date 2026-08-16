<?php
/**
 * Brute-force and bot protection for the login and registration forms.
 *
 * WordPress ships with none of this. An unprotected wp-login.php on a public
 * IP will be attacked continuously — not because anyone targeted this site,
 * but because every WordPress on the internet is scanned constantly.
 *
 * Three layers, cheapest first:
 *
 *   1. A honeypot field bots fill in and humans never see. Free, catches most
 *      automated form submissions, and costs a real user nothing.
 *   2. A minimum form-render age. A form submitted 400ms after it loaded was
 *      not filled in by a person reading it.
 *   3. Per-IP attempt throttling with a lockout that grows. This is the layer
 *      that actually stops a determined credential-stuffing run.
 *
 * Deliberately no third-party CAPTCHA by default. A CAPTCHA sends every
 * visitor's IP to another company before they have even signed in, is a real
 * accessibility barrier, and is beaten cheaply by solving services — while the
 * throttle below stops the same attack without any of that. There is a hook to
 * add one if it is ever genuinely needed.
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Login_Guard {

	const OPTION_ENABLED = 'jsl_login_guard';

	/** Attempts from one IP before it is locked out. */
	const MAX_ATTEMPTS = 5;

	/** How long the window is, and how long the first lockout lasts. */
	const WINDOW      = 900;  // 15 minutes
	const BASE_LOCKOUT = 900; // 15 minutes, doubling each repeat

	/** A form filled in faster than this was not read. */
	const MIN_FORM_AGE = 2;

	const HONEYPOT = 'guide_website_url';
	const TIMESTAMP = 'guide_form_ts';

	/** The refusal raised this request, re-asserted after core has run. */
	private static $verdict = null;

	public static function init() {
		if ( ! self::is_enabled() ) {
			return;
		}

		// Refuse before WordPress even checks the password.
		add_action( 'login_form', array( __CLASS__, 'render_traps' ) );
		add_action( 'register_form', array( __CLASS__, 'render_traps' ) );
		add_filter( 'authenticate', array( __CLASS__, 'check_before_auth' ), 5, 3 );

		// This second filter is not belt-and-braces — without it the first one
		// does nothing at all.
		//
		// Core's wp_authenticate_username_password() runs at priority 20 and
		// begins with `if ( $user instanceof WP_User ) return $user;`. A
		// WP_Error is not a WP_User, so core carries straight on, checks the
		// password itself, and returns the authenticated user — silently
		// discarding whatever refusal was raised at priority 5. A trap that
		// only returns a WP_Error early is therefore decorative: a bot that
		// tripped the honeypot with a valid password would still be signed in.
		//
		// Re-asserting the verdict after core has had its turn is what actually
		// refuses the request.
		add_filter( 'authenticate', array( __CLASS__, 'enforce_verdict' ), 30, 3 );

		// And for a lockout, refuse at the front door instead, so no password
		// is ever hashed for an IP that is already locked out.
		add_action( 'login_init', array( __CLASS__, 'refuse_locked_request' ) );

		add_action( 'wp_login_failed', array( __CLASS__, 'record_failure' ) );
		add_action( 'wp_login', array( __CLASS__, 'clear_on_success' ), 10, 2 );

		add_filter( 'registration_errors', array( __CLASS__, 'check_registration' ), 10, 3 );

		// The REST and XML-RPC login paths bypass the form entirely, so the
		// throttle has to sit on the authentication filter as well — which it
		// does, above. This just closes application passwords, which are a
		// credential most sites never intentionally use.
		add_filter( 'wp_is_application_passwords_available', '__return_false' );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, true );
	}

	// -------------------------------------------------------------------------
	// Traps
	// -------------------------------------------------------------------------

	/**
	 * A honeypot and a timestamp.
	 *
	 * The honeypot is hidden with inline CSS rather than a stylesheet class, so
	 * it still works if the stylesheet fails to load — in which case a real
	 * user would see the field and (crucially) it is labelled, telling them to
	 * leave it alone.
	 */
	public static function render_traps() {
		?>
		<div style="position:absolute!important;left:-9999px!important;top:-9999px!important" aria-hidden="true">
			<label for="<?php echo esc_attr( self::HONEYPOT ); ?>">
				<?php esc_html_e( 'Leave this field empty', 'guide-lms' ); ?>
			</label>
			<input type="text" name="<?php echo esc_attr( self::HONEYPOT ); ?>"
				id="<?php echo esc_attr( self::HONEYPOT ); ?>" value="" tabindex="-1" autocomplete="off">
		</div>
		<input type="hidden" name="<?php echo esc_attr( self::TIMESTAMP ); ?>"
			value="<?php echo esc_attr( (string) time() ); ?>">
		<?php
	}

	/**
	 * Runs before WordPress validates credentials.
	 *
	 * @param null|\WP_User|\WP_Error $user
	 * @param string                  $username
	 * @param string                  $password
	 * @return null|\WP_User|\WP_Error
	 */
	public static function check_before_auth( $user, $username, $password ) {
		self::$verdict = null;

		// An empty submission is just someone hitting enter; leave it to core.
		if ( '' === $username && '' === $password ) {
			return $user;
		}

		$ip = self::ip();

		if ( self::is_locked_out( $ip ) ) {
			return self::refuse(
				'guide_locked_out',
				sprintf(
					/* translators: %d: minutes remaining. */
					esc_html__( 'Too many attempts. Try again in %d minutes.', 'guide-lms' ),
					(int) ceil( self::lockout_remaining( $ip ) / 60 )
				)
			);
		}

		// Only inspect the traps when the request actually came from a form —
		// programmatic sign-ins (wp_signon in a test, an SSO handoff) have no
		// timestamp and must not be blocked.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST[ self::TIMESTAMP ] ) ) {
			if ( ! empty( $_POST[ self::HONEYPOT ] ) ) {
				self::record_failure( $username );
				return self::refuse( 'guide_bot', esc_html__( 'Invalid credentials. Please try again.', 'guide-lms' ) );
			}

			$age = time() - (int) $_POST[ self::TIMESTAMP ];

			if ( $age < self::MIN_FORM_AGE ) {
				self::record_failure( $username );
				return self::refuse( 'guide_too_fast', esc_html__( 'Invalid credentials. Please try again.', 'guide-lms' ) );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		/**
		 * Add an extra check — a CAPTCHA, an allow-list, anything.
		 *
		 * @param null|\WP_User|\WP_Error $user
		 * @param string                  $username
		 */
		return apply_filters( 'guide_login_precheck', $user, $username );
	}

	/**
	 * Refuse a locked-out login request outright.
	 *
	 * Cheapest possible rejection: no user lookup, no password hashing, no
	 * database write. Password hashing is deliberately expensive, which makes
	 * an unthrottled login form a denial-of-service amplifier as much as a
	 * credential-guessing one.
	 */
	public static function refuse_locked_request() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['log'] ) && empty( $_POST['pwd'] ) ) {
			return;
		}

		$ip = self::ip();

		if ( ! self::is_locked_out( $ip ) ) {
			return;
		}

		status_header( 429 );
		nocache_headers();

		wp_die(
			esc_html(
				sprintf(
					/* translators: %d: minutes remaining. */
					__( 'Too many sign-in attempts from this connection. Try again in %d minutes.', 'guide-lms' ),
					(int) ceil( self::lockout_remaining( $ip ) / 60 )
				)
			),
			esc_html__( 'Too many attempts', 'guide-lms' ),
			array( 'response' => 429 )
		);
	}

	/**
	 * Have the last word.
	 *
	 * Runs after core's authenticator, so whatever was refused at priority 5
	 * stays refused. Also re-checks the lockout, because a run of failures
	 * inside this same request could have crossed the threshold.
	 *
	 * @param null|\WP_User|\WP_Error $user
	 * @param string                  $username
	 * @param string                  $password
	 * @return null|\WP_User|\WP_Error
	 */
	public static function enforce_verdict( $user, $username = '', $password = '' ) {
		if ( '' === $username && '' === $password ) {
			return $user;
		}

		if ( self::$verdict instanceof \WP_Error ) {
			return self::$verdict;
		}

		if ( self::is_locked_out( self::ip() ) ) {
			return new \WP_Error(
				'guide_locked_out',
				sprintf(
					/* translators: %d: minutes remaining. */
					esc_html__( 'Too many attempts. Try again in %d minutes.', 'guide-lms' ),
					(int) ceil( self::lockout_remaining( self::ip() ) / 60 )
				)
			);
		}

		return $user;
	}

	/** Record a refusal so enforce_verdict() can re-assert it, and return it. */
	private static function refuse( string $code, string $message ): \WP_Error {
		self::$verdict = new \WP_Error( $code, $message );

		return self::$verdict;
	}

	public static function check_registration( $errors, $login, $email ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST[ self::HONEYPOT ] ) ) {
			$errors->add( 'guide_bot', esc_html__( 'Registration could not be completed.', 'guide-lms' ) );
		}

		if ( isset( $_POST[ self::TIMESTAMP ] ) && ( time() - (int) $_POST[ self::TIMESTAMP ] ) < self::MIN_FORM_AGE ) {
			$errors->add( 'guide_too_fast', esc_html__( 'Registration could not be completed.', 'guide-lms' ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( self::is_locked_out( self::ip() ) ) {
			$errors->add( 'guide_locked_out', esc_html__( 'Too many attempts. Try again shortly.', 'guide-lms' ) );
		}

		return $errors;
	}

	// -------------------------------------------------------------------------
	// Throttle
	// -------------------------------------------------------------------------

	private static function key( string $ip ): string {
		return 'guide_lg_' . md5( $ip );
	}

	/**
	 * The client IP.
	 *
	 * Proxy headers are trusted only when the site says it is behind a proxy,
	 * because otherwise anyone can send X-Forwarded-For and rotate their way
	 * around the throttle for free.
	 */
	public static function ip(): string {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		if ( defined( 'GUIDE_BEHIND_PROXY' ) && GUIDE_BEHIND_PROXY && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$first     = trim( explode( ',', $forwarded )[0] );

			if ( filter_var( $first, FILTER_VALIDATE_IP ) ) {
				return $first;
			}
		}

		return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';
	}

	/**
	 * @return array{count:int, locked_until:int, strikes:int}
	 */
	private static function state( string $ip ): array {
		$state = get_transient( self::key( $ip ) );

		if ( ! is_array( $state ) ) {
			return array(
				'count'        => 0,
				'locked_until' => 0,
				'strikes'      => 0,
			);
		}

		return wp_parse_args(
			$state,
			array(
				'count'        => 0,
				'locked_until' => 0,
				'strikes'      => 0,
			)
		);
	}

	public static function is_locked_out( string $ip ): bool {
		return self::state( $ip )['locked_until'] > time();
	}

	public static function lockout_remaining( string $ip ): int {
		return max( 0, self::state( $ip )['locked_until'] - time() );
	}

	/**
	 * Record a failed attempt, locking out once the threshold is passed.
	 *
	 * The lockout doubles with each repeat, so a persistent attacker backs off
	 * geometrically while somebody who mistyped their password twice is barely
	 * inconvenienced.
	 *
	 * @param string $username
	 */
	public static function record_failure( $username = '' ) {
		$ip    = self::ip();
		$state = self::state( $ip );

		++$state['count'];

		if ( $state['count'] >= self::MAX_ATTEMPTS ) {
			++$state['strikes'];
			$state['locked_until'] = time() + ( self::BASE_LOCKOUT * ( 2 ** min( 5, $state['strikes'] - 1 ) ) );
			$state['count']        = 0;

			/**
			 * Fires when an IP is locked out — somewhere to hang alerting.
			 *
			 * @param string $ip
			 * @param int    $strikes
			 */
			do_action( 'guide_login_lockout', $ip, $state['strikes'] );
		}

		// Keep the strike history well beyond the lockout, so releasing and
		// immediately retrying escalates rather than starting over.
		set_transient( self::key( $ip ), $state, max( self::WINDOW, self::BASE_LOCKOUT * 8 ) );
	}

	/**
	 * @param string   $login
	 * @param \WP_User $user
	 */
	public static function clear_on_success( $login = '', $user = null ) {
		delete_transient( self::key( self::ip() ) );
	}
}
