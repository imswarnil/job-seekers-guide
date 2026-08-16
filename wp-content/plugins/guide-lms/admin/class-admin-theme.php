<?php
/**
 * wp-admin reskin: applies the Guide design tokens across the admin — menu,
 * admin bar, buttons, forms, notices — plus a matching login screen.
 *
 * Colour and type only, no resets and no layout changes, so core screens and
 * other plugins' pages keep working. The console's own screens get real Bulma
 * from console.min.css, which is scoped under `.guide-admin` precisely so it
 * cannot reach the rest of wp-admin.
 */

namespace Guide\Admin;

defined( 'ABSPATH' ) || exit;

class Admin_Theme {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_login' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'login_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'login_text' ) );

		// Wrap the login form in a two-column shell: what this place is on the
		// left, the form on the right. A bare form on a coloured background
		// tells a first-time visitor nothing, and the sign-up page is the one
		// screen where somebody is actively deciding whether to bother.
		add_action( 'login_header', array( __CLASS__, 'open_shell' ) );
		add_action( 'login_footer', array( __CLASS__, 'close_shell' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
	}

	const FONTS_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap';

	public static function enqueue() {
		wp_enqueue_style( 'guide-admin-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-admin-skin',
			GUIDE_PLUGIN_URL . 'admin/assets/css/admin-skin.min.css',
			array( 'guide-admin-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/admin-skin.min.css' )
		);
	}

	/**
	 * The scoped Bulma build. Only loaded on the console's own screens —
	 * 392 KB of framework has no business on every admin page, and nothing in
	 * it applies outside `.guide-admin` anyway.
	 */
	public static function enqueue_console() {
		wp_enqueue_style( 'guide-admin-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-console',
			GUIDE_PLUGIN_URL . 'admin/assets/css/console.min.css',
			array( 'guide-admin-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/console.min.css' )
		);
	}

	/**
	 * The panel beside the form.
	 *
	 * Different copy for signing up and signing in: somebody registering is
	 * deciding, somebody logging in has already decided and wants the form.
	 */
	public static function open_shell() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'login';

		$points = 'register' === $action
			? array(
				__( 'Every foundation course, free — not a trial', 'guide-lms' ),
				__( 'A path in order, so you always know what is next', 'guide-lms' ),
				__( 'The job search taught as its own subject', 'guide-lms' ),
			)
			: array(
				__( 'Your progress is exactly where you left it', 'guide-lms' ),
				__( 'Pick up from the lesson you stopped on', 'guide-lms' ),
			);

		$heading = 'register' === $action
			? __( 'You were never bad at this. Nobody gave you the order.', 'guide-lms' )
			: __( 'Welcome back.', 'guide-lms' );
		?>
		<div class="guide-login-shell">
			<aside class="guide-login-brand">
				<a class="guide-login-brand__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
				</a>

				<h2 class="guide-login-brand__heading"><?php echo esc_html( $heading ); ?></h2>

				<ul class="guide-login-brand__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>

				<p class="guide-login-brand__foot">
					<?php esc_html_e( 'Built by somebody who was rejected 33 times, for everybody who is being rejected now.', 'guide-lms' ); ?>
				</p>
			</aside>

			<div class="guide-login-main">
		<?php
	}

	public static function close_shell() {
		echo '</div></div>';
	}

	public static function enqueue_login() {
		wp_enqueue_style( 'guide-login-fonts', self::FONTS_URL, array(), null );
		wp_enqueue_style(
			'guide-login',
			GUIDE_PLUGIN_URL . 'admin/assets/css/login.min.css',
			array( 'guide-login-fonts' ),
			guide_plugin_asset_version( 'admin/assets/css/login.min.css' )
		);
	}

	public static function login_url() {
		return home_url( '/' );
	}

	public static function login_text() {
		return get_bloginfo( 'name' );
	}

	public static function footer_text() {
		return esc_html( get_bloginfo( 'name' ) ) . ' — powered by the Job Seekers LMS.';
	}
}
