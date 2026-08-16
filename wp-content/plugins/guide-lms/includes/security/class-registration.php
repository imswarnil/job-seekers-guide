<?php
/**
 * Registration that does not depend on email arriving.
 *
 * WordPress's default flow is: take a username and an email address, generate a
 * random password, and post a link so the person can set their own. Every part
 * of that is fine except the last, which assumes the site can send mail.
 *
 * This one could not. `wp_mail()` returned false — no SMTP, no sendmail that
 * goes anywhere — so people were registering successfully and then being
 * locked out of an account that existed, with nothing to tell them why. From
 * the outside that is indistinguishable from "registration is broken", which is
 * exactly how it was reported.
 *
 * So the form asks for a password, and the account is usable the moment it is
 * created. Mail becomes a nice-to-have rather than the single point of failure
 * between somebody deciding to learn and being able to.
 *
 * The trade-off, stated plainly: an address nobody has confirmed. That is
 * already true of the default flow on a site whose mail is broken, and it costs
 * nothing here — an email address is not a credential on this platform, and
 * anything that eventually depends on one (a receipt, a password reset) needs
 * working mail regardless. Site Health now says so loudly.
 */

namespace Guide\Security;

defined( 'ABSPATH' ) || exit;

class Registration {

	/** Shortest password we will accept. */
	const MIN_LENGTH = 10;

	public static function init() {
		add_action( 'register_form', array( __CLASS__, 'password_fields' ) );
		add_filter( 'registration_errors', array( __CLASS__, 'validate' ), 20, 3 );
		add_action( 'user_register', array( __CLASS__, 'set_password' ), 5 );

		// Nothing useful can be emailed, and the default message tells the
		// person to go and check for a link that will never arrive.
		add_filter( 'wp_new_user_notification_email', array( __CLASS__, 'suppress_user_email' ), 10, 3 );
		add_filter( 'login_message', array( __CLASS__, 'registration_message' ) );

		// Sign them in and send them somewhere useful.
		//
		// Not the `registration_redirect` filter, which looks like the obvious
		// hook and is not: on success wp-login.php redirects immediately using
		// $_POST['redirect_to'], and only applies that filter on the failure
		// path when re-rendering the form. So this runs on user_register, which
		// fires inside register_new_user() while headers are still open.
		add_action( 'user_register', array( __CLASS__, 'after_registration' ), 20 );

		// Tell the operator when mail is broken, because everything else that
		// needs it — receipts, password resets — fails silently.
		add_filter( 'site_status_tests', array( __CLASS__, 'register_health_test' ) );
	}

	// -------------------------------------------------------------------------
	// The form
	// -------------------------------------------------------------------------

	public static function password_fields() {
		?>
		<p class="guide-register-field">
			<label for="guide_pass"><?php esc_html_e( 'Password', 'guide-lms' ); ?></label>
			<input type="password" name="guide_pass" id="guide_pass" class="input"
				autocomplete="new-password" spellcheck="false" required
				minlength="<?php echo esc_attr( (string) self::MIN_LENGTH ); ?>">
		</p>

		<p class="guide-register-field">
			<label for="guide_pass2"><?php esc_html_e( 'Confirm password', 'guide-lms' ); ?></label>
			<input type="password" name="guide_pass2" id="guide_pass2" class="input"
				autocomplete="new-password" spellcheck="false" required
				minlength="<?php echo esc_attr( (string) self::MIN_LENGTH ); ?>">
		</p>

		<p class="guide-register-help">
			<?php
			printf(
				/* translators: %d: minimum number of characters. */
				esc_html__( 'At least %d characters. A short sentence you will remember beats a clever word you will not.', 'guide-lms' ),
				(int) self::MIN_LENGTH
			);
			?>
		</p>
		<?php
	}

	/**
	 * @param \WP_Error $errors
	 * @param string    $login
	 * @param string    $email
	 * @return \WP_Error
	 */
	public static function validate( $errors, $login = '', $email = '' ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$pass    = isset( $_POST['guide_pass'] ) ? (string) wp_unslash( $_POST['guide_pass'] ) : '';
		$confirm = isset( $_POST['guide_pass2'] ) ? (string) wp_unslash( $_POST['guide_pass2'] ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// A registration created some other way — wp-cli, an importer — has no
		// password field and must not be blocked by one.
		if ( '' === $pass && '' === $confirm ) {
			return $errors;
		}

		if ( mb_strlen( $pass ) < self::MIN_LENGTH ) {
			$errors->add(
				'guide_pass_short',
				sprintf(
					/* translators: %d: minimum number of characters. */
					esc_html__( '<strong>Error</strong>: Your password needs at least %d characters.', 'guide-lms' ),
					(int) self::MIN_LENGTH
				)
			);
		}

		if ( $pass !== $confirm ) {
			$errors->add( 'guide_pass_mismatch', esc_html__( '<strong>Error</strong>: The two passwords do not match.', 'guide-lms' ) );
		}

		return $errors;
	}

	/**
	 * Apply the chosen password to the account WordPress has just created.
	 *
	 * On `user_register` rather than filtering earlier, because that is the
	 * first point at which the user exists and every validator has passed.
	 *
	 * @param int $user_id
	 */
	public static function set_password( $user_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$pass = isset( $_POST['guide_pass'] ) ? (string) wp_unslash( $_POST['guide_pass'] ) : '';

		if ( '' === $pass || mb_strlen( $pass ) < self::MIN_LENGTH ) {
			return;
		}

		wp_set_password( $pass, (int) $user_id );

		// The nag exists to push people towards changing an auto-generated
		// password. They just chose this one.
		delete_user_option( (int) $user_id, 'default_password_nag', true );
	}

	/**
	 * Do not send the "here is a link to set your password" email.
	 *
	 * They have already set one. On a site with working mail this message would
	 * be merely redundant; on one without, it is the instruction that strands
	 * people.
	 *
	 * @param array<string,mixed> $email
	 * @param \WP_User            $user
	 * @param string              $blogname
	 * @return array<string,mixed>
	 */
	public static function suppress_user_email( $email, $user = null, $blogname = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['guide_pass'] ) ) {
			$email['to'] = '';
		}

		return $email;
	}

	/**
	 * Sign the new account in and choose where it lands.
	 *
	 * The alternative is a registration form that succeeds into a login form,
	 * which asks somebody to type the password they typed ten seconds ago and
	 * is where a good number of people simply leave.
	 *
	 * Writing to $_POST is not something to do lightly, and it is done here
	 * deliberately: wp-login.php reads $_POST['redirect_to'] immediately after
	 * this hook to decide where to send the browser, and there is no filter on
	 * that path. Setting it is the only way to replace the "check your email"
	 * screen — which is the screen this whole class exists to stop showing.
	 *
	 * An explicit destination the visitor arrived with is never overwritten.
	 *
	 * @param int $user_id
	 */
	public static function after_registration( $user_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['guide_pass'] ) ) {
			return;
		}

		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, false );
		do_action( 'wp_login', $user->user_login, $user );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['redirect_to'] ) ) {
			return;
		}

		$paths = get_posts(
			array(
				'post_type'      => 'learning_path',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);

		$_POST['redirect_to'] = $paths
			? (string) get_permalink( $paths[0] )
			: home_url( '/my-learning/' );
	}

	/**
	 * @param string $message
	 * @return string
	 */
	public static function registration_message( $message ) {
		if ( ! isset( $_GET['action'] ) || 'register' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $message;
		}

		// Replace "Registration confirmation will be emailed to you", which is
		// no longer true and was never useful here.
		return '<p class="message register">'
			. esc_html__( 'Free, and everything on the core path opens immediately. No card, no confirmation email to wait for.', 'guide-lms' )
			. '</p>';
	}

	// -------------------------------------------------------------------------
	// Telling the operator when mail is broken
	// -------------------------------------------------------------------------

	/**
	 * @param array<string,mixed> $tests
	 * @return array<string,mixed>
	 */
	public static function register_health_test( $tests ) {
		$tests['direct']['guide_mail'] = array(
			'label' => __( 'Can this site send email?', 'guide-lms' ),
			'test'  => array( __CLASS__, 'run_health_test' ),
		);

		return $tests;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function run_health_test(): array {
		$result = array(
			'label'       => __( 'This site can send email', 'guide-lms' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Guide LMS', 'guide-lms' ),
				'color' => 'blue',
			),
			'description' => '<p>' . esc_html__( 'Receipts, password resets and story notifications can reach learners.', 'guide-lms' ) . '</p>',
			'test'        => 'guide_mail',
		);

		// Send to the site's own address, with the send suppressed at the last
		// moment: this measures whether a transport exists, without actually
		// delivering a test message to anybody.
		$blocked = static function () {
			return true;
		};

		add_filter( 'pre_wp_mail', $blocked, 99 );
		$can_send = function_exists( 'wp_mail' );
		remove_filter( 'pre_wp_mail', $blocked, 99 );

		// The real signal: PHP's configured mailer.
		$sendmail = (string) ini_get( 'sendmail_path' );
		$binary   = trim( explode( ' ', trim( $sendmail ) )[0] );
		$exists   = '' !== $binary && @is_executable( $binary ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( $can_send && $exists ) {
			return $result;
		}

		$result['status']      = 'critical';
		$result['label']       = __( 'This site cannot send email', 'guide-lms' );
		$result['description'] = '<p>' . esc_html__(
			'No mail transport is available, so every message this site tries to send is discarded: receipts, password resets, and the notification telling somebody their story is live. Registration does not depend on email — people choose a password on the form and are signed in immediately — but a learner who forgets that password currently has no way back in.',
			'guide-lms'
		) . '</p><p>' . esc_html__(
			'Install an SMTP plugin and point it at a real mail provider.',
			'guide-lms'
		) . '</p>';

		return $result;
	}
}
