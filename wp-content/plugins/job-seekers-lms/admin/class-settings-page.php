<?php
/**
 * LMS → Settings: one screen, tabbed, for everything a site owner has to
 * configure. Replaces the scattered "Settings → Dodo Payments" page so the
 * whole product is administered from the LMS menu.
 *
 * Each tab is its own option group, so saving one tab never clears another.
 * Every field goes through the Settings API with an explicit sanitizer.
 */

namespace JSL\Admin;

use JSL\Auth\Google_Auth;
use JSL\Payments\Settings as Payment_Settings;
use JSL\Payments\Subscription;
use JSL\Seo\Seo;
use JSL\Pwa\Pwa;

defined( 'ABSPATH' ) || exit;

class Settings_Page {

	const SLUG = 'jsl-settings';

	/** tab key => [label, option group] */
	const TABS = array(
		'payments'     => array( 'Payments', 'jsl_settings_payments' ),
		'subscription' => array( 'Subscription', 'jsl_settings_subscription' ),
		'google'       => array( 'Google Sign-In', 'jsl_settings_google' ),
		'seo'          => array( 'SEO', 'jsl_settings_seo' ),
		'pwa'          => array( 'App / PWA', 'jsl_settings_pwa' ),
	);

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function register_menu() {
		add_submenu_page(
			Console::SLUG,
			__( 'LMS Settings', 'job-seekers-lms' ),
			__( 'Settings', 'job-seekers-lms' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function url( string $tab = 'payments' ): string {
		return admin_url( 'admin.php?page=' . self::SLUG . '&tab=' . $tab );
	}

	/* ---------------------------------------------------------------
	 * Registration
	 * --------------------------------------------------------------- */

	public static function register_settings() {
		$text = array( 'sanitize_callback' => 'sanitize_text_field' );
		$bool = array( 'sanitize_callback' => array( __CLASS__, 'sanitize_bool' ) );

		// Payments (Dodo).
		register_setting( 'jsl_settings_payments', Payment_Settings::OPTION_MODE, array( 'sanitize_callback' => array( Payment_Settings::class, 'sanitize_mode' ) ) );
		register_setting( 'jsl_settings_payments', Payment_Settings::OPTION_API_KEY, $text );
		register_setting( 'jsl_settings_payments', Payment_Settings::OPTION_WEBHOOK_SECRET, $text );

		// Platform subscription.
		register_setting( 'jsl_settings_subscription', Subscription::OPTION_ENABLED, $bool );
		register_setting( 'jsl_settings_subscription', Subscription::OPTION_PRODUCT_ID, $text );
		register_setting( 'jsl_settings_subscription', Subscription::OPTION_PRICE_LABEL, $text );
		register_setting( 'jsl_settings_subscription', Subscription::OPTION_PERIOD, array( 'sanitize_callback' => 'absint' ) );
		register_setting( 'jsl_settings_subscription', Subscription::OPTION_BLURB, array( 'sanitize_callback' => 'sanitize_textarea_field' ) );

		// Google sign-in.
		register_setting( 'jsl_settings_google', Google_Auth::OPTION_ENABLED, $bool );
		register_setting( 'jsl_settings_google', Google_Auth::OPTION_CLIENT_ID, $text );
		register_setting( 'jsl_settings_google', Google_Auth::OPTION_CLIENT_SECRET, $text );
		register_setting( 'jsl_settings_google', Google_Auth::OPTION_ALLOW_SIGNUP, $bool );

		// SEO.
		register_setting( 'jsl_settings_seo', Seo::OPTION_DESCRIPTION, array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
		register_setting( 'jsl_settings_seo', Seo::OPTION_SOCIAL_IMAGE, array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( 'jsl_settings_seo', Seo::OPTION_TWITTER, $text );
		register_setting( 'jsl_settings_seo', Seo::OPTION_ORG_NAME, $text );

		// PWA.
		register_setting( 'jsl_settings_pwa', Pwa::OPTION_ENABLED, $bool );
		register_setting( 'jsl_settings_pwa', Pwa::OPTION_NAME, $text );
		register_setting( 'jsl_settings_pwa', Pwa::OPTION_SHORT_NAME, $text );
		register_setting( 'jsl_settings_pwa', Pwa::OPTION_THEME_COLOR, array( 'sanitize_callback' => array( __CLASS__, 'sanitize_hex' ) ) );
	}

	public static function sanitize_bool( $value ): bool {
		return (bool) $value;
	}

	public static function sanitize_hex( $value ): string {
		$hex = sanitize_hex_color( (string) $value );
		return $hex ?: '#414BA0';
	}

	/* ---------------------------------------------------------------
	 * Rendering
	 * --------------------------------------------------------------- */

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'job-seekers-lms' ) );
		}

		$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'payments';
		$tab       = isset( self::TABS[ $requested ] ) ? $requested : 'payments';
		$group     = self::TABS[ $tab ][1];
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'LMS Settings', 'job-seekers-lms' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( self::TABS as $key => $meta ) : ?>
					<a class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( self::url( $key ) ); ?>">
						<?php echo esc_html( $meta[0] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields( $group ); ?>
				<table class="form-table" role="presentation">
					<?php call_user_func( array( __CLASS__, 'fields_' . $tab ) ); ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private static function row( string $label, string $control, string $description = '' ) {
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<?php echo $control; // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_* helpers below. ?>
				<?php if ( $description ) : ?>
					<p class="description"><?php echo wp_kses_post( $description ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	private static function text_input( string $option, string $placeholder = '', string $type = 'text' ): string {
		return sprintf(
			'<input type="%1$s" class="regular-text" name="%2$s" id="%2$s" value="%3$s" placeholder="%4$s" autocomplete="off">',
			esc_attr( $type ),
			esc_attr( $option ),
			esc_attr( (string) get_option( $option, '' ) ),
			esc_attr( $placeholder )
		);
	}

	private static function checkbox( string $option, string $label ): string {
		return sprintf(
			'<label><input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s> %3$s</label>',
			esc_attr( $option ),
			checked( (bool) get_option( $option ), true, false ),
			esc_html( $label )
		);
	}

	/* ---- Tab: Payments ---- */

	private static function fields_payments() {
		self::row(
			__( 'Mode', 'job-seekers-lms' ),
			sprintf(
				'<select name="%1$s" id="%1$s"><option value="test" %2$s>%3$s</option><option value="live" %4$s>%5$s</option></select>',
				esc_attr( Payment_Settings::OPTION_MODE ),
				selected( Payment_Settings::mode(), 'test', false ),
				esc_html__( 'Test', 'job-seekers-lms' ),
				selected( Payment_Settings::mode(), 'live', false ),
				esc_html__( 'Live', 'job-seekers-lms' )
			),
			__( 'Test mode talks to Dodo’s sandbox. Switch to Live only once you have tested a real checkout.', 'job-seekers-lms' )
		);

		self::row( __( 'API key', 'job-seekers-lms' ), self::text_input( Payment_Settings::OPTION_API_KEY, '', 'password' ) );
		self::row( __( 'Webhook secret', 'job-seekers-lms' ), self::text_input( Payment_Settings::OPTION_WEBHOOK_SECRET, '', 'password' ) );

		self::row(
			__( 'Webhook URL', 'job-seekers-lms' ),
			'<code>' . esc_html( Payment_Settings::webhook_url() ) . '</code>',
			__( 'Register this in Dodo Payments → Developer → Webhooks. Subscribe to <code>payment.succeeded</code> and, if you sell a subscription, the <code>subscription.*</code> events.', 'job-seekers-lms' )
		);
	}

	/* ---- Tab: Subscription ---- */

	private static function fields_subscription() {
		self::row(
			__( 'Platform subscription', 'job-seekers-lms' ),
			self::checkbox( Subscription::OPTION_ENABLED, __( 'Offer a subscription that unlocks every course', 'job-seekers-lms' ) ),
			__( 'Learners can still buy individual courses. A subscriber gets all of them for as long as the subscription is active.', 'job-seekers-lms' )
		);

		self::row(
			__( 'Dodo product ID', 'job-seekers-lms' ),
			self::text_input( Subscription::OPTION_PRODUCT_ID, 'pdt_…' ),
			__( 'Create a recurring product in Dodo Payments and paste its ID here.', 'job-seekers-lms' )
		);

		self::row( __( 'Price label', 'job-seekers-lms' ), self::text_input( Subscription::OPTION_PRICE_LABEL, '$19/month' ), __( 'Shown on the site. The amount actually charged is whatever the Dodo product says.', 'job-seekers-lms' ) );

		self::row(
			__( 'Billing period (days)', 'job-seekers-lms' ),
			sprintf(
				'<input type="number" min="1" class="small-text" name="%1$s" id="%1$s" value="%2$s">',
				esc_attr( Subscription::OPTION_PERIOD ),
				esc_attr( (string) Subscription::period_days() )
			),
			__( 'Fallback only — when Dodo sends a next-billing date, that wins.', 'job-seekers-lms' )
		);

		self::row(
			__( 'Pitch', 'job-seekers-lms' ),
			sprintf(
				'<textarea class="large-text" rows="3" name="%1$s" id="%1$s">%2$s</textarea>',
				esc_attr( Subscription::OPTION_BLURB ),
				esc_textarea( Subscription::blurb() )
			),
			__( 'One or two lines shown on the pricing card.', 'job-seekers-lms' )
		);
	}

	/* ---- Tab: Google Sign-In ---- */

	private static function fields_google() {
		self::row(
			__( 'Google sign-in', 'job-seekers-lms' ),
			self::checkbox( Google_Auth::OPTION_ENABLED, __( 'Let people sign in with Google', 'job-seekers-lms' ) )
		);

		self::row(
			__( 'Authorized redirect URI', 'job-seekers-lms' ),
			'<code>' . esc_html( Google_Auth::callback_url() ) . '</code>',
			__( 'Paste this into Google Cloud Console → APIs &amp; Services → Credentials → your OAuth client → Authorized redirect URIs. It must match exactly, including https and any trailing path.', 'job-seekers-lms' )
		);

		self::row( __( 'Client ID', 'job-seekers-lms' ), self::text_input( Google_Auth::OPTION_CLIENT_ID, '….apps.googleusercontent.com' ) );

		self::row(
			__( 'Client secret', 'job-seekers-lms' ),
			defined( 'JSL_GOOGLE_CLIENT_SECRET' )
				? '<em>' . esc_html__( 'Set in wp-config.php via JSL_GOOGLE_CLIENT_SECRET — this field is ignored.', 'job-seekers-lms' ) . '</em>'
				: self::text_input( Google_Auth::OPTION_CLIENT_SECRET, '', 'password' ),
			__( 'For the strongest setup, define <code>JSL_GOOGLE_CLIENT_SECRET</code> in wp-config.php instead of storing it in the database.', 'job-seekers-lms' )
		);

		self::row(
			__( 'New accounts', 'job-seekers-lms' ),
			self::checkbox( Google_Auth::OPTION_ALLOW_SIGNUP, __( 'Create an account the first time someone signs in with Google', 'job-seekers-lms' ) ),
			__( 'Turn this off to allow Google sign-in only for people who already have an account.', 'job-seekers-lms' )
		);
	}

	/* ---- Tab: SEO ---- */

	private static function fields_seo() {
		self::row(
			__( 'Default description', 'job-seekers-lms' ),
			sprintf(
				'<textarea class="large-text" rows="3" name="%1$s" id="%1$s">%2$s</textarea>',
				esc_attr( Seo::OPTION_DESCRIPTION ),
				esc_textarea( (string) get_option( Seo::OPTION_DESCRIPTION, '' ) )
			),
			__( 'Used on the homepage and anywhere a page has no excerpt of its own. Aim for 150–160 characters.', 'job-seekers-lms' )
		);

		self::row(
			__( 'Social share image', 'job-seekers-lms' ),
			self::text_input( Seo::OPTION_SOCIAL_IMAGE, 'https://…/og-image.png', 'url' ),
			__( 'Fallback Open Graph image, 1200×630. Courses and lessons use their own featured image when they have one.', 'job-seekers-lms' )
		);

		self::row( __( 'Twitter / X handle', 'job-seekers-lms' ), self::text_input( Seo::OPTION_TWITTER, '@yourhandle' ) );

		self::row(
			__( 'Organization name', 'job-seekers-lms' ),
			self::text_input( Seo::OPTION_ORG_NAME, get_bloginfo( 'name' ) ),
			__( 'Used in structured data as the course provider. Defaults to the site title.', 'job-seekers-lms' )
		);
	}

	/* ---- Tab: PWA ---- */

	private static function fields_pwa() {
		self::row(
			__( 'Installable app', 'job-seekers-lms' ),
			self::checkbox( Pwa::OPTION_ENABLED, __( 'Serve a web app manifest and service worker', 'job-seekers-lms' ) ),
			__( 'Lets learners install the site to their home screen and keeps visited lessons readable offline.', 'job-seekers-lms' )
		);

		self::row( __( 'App name', 'job-seekers-lms' ), self::text_input( Pwa::OPTION_NAME, get_bloginfo( 'name' ) ) );
		self::row( __( 'Short name', 'job-seekers-lms' ), self::text_input( Pwa::OPTION_SHORT_NAME, 'Job Seekers' ), __( 'Shown under the home-screen icon. Keep it under 12 characters.', 'job-seekers-lms' ) );

		self::row(
			__( 'Theme colour', 'job-seekers-lms' ),
			sprintf(
				'<input type="text" class="regular-text" name="%1$s" id="%1$s" value="%2$s" placeholder="#414BA0">',
				esc_attr( Pwa::OPTION_THEME_COLOR ),
				esc_attr( Pwa::theme_color() )
			),
			__( 'Colours the browser chrome when the app is launched from the home screen.', 'job-seekers-lms' )
		);

		self::row(
			__( 'Manifest', 'job-seekers-lms' ),
			'<code>' . esc_html( home_url( '/manifest.webmanifest' ) ) . '</code>',
			__( 'Generated on the fly — nothing to upload.', 'job-seekers-lms' )
		);
	}
}
