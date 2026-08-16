<?php
/**
 * LMS → Settings: one screen, tabbed, for everything a site owner has to
 * configure. Replaces the scattered "Settings → Dodo Payments" page so the
 * whole product is administered from the LMS menu.
 *
 * Each tab is its own option group, so saving one tab never clears another.
 * Every field goes through the Settings API with an explicit sanitizer.
 */

namespace Guide\Admin;

use Guide\Auth\Google_Auth;
use Guide\Payments\Settings as Payment_Settings;
use Guide\Ads\Ads;
use Guide\Payments\Subscription;
use Guide\Seo\Seo;
use Guide\Pwa\Pwa;
use Guide\Success\Success_Stories;
use Guide\Leaderboard\Leaderboard;

defined( 'ABSPATH' ) || exit;

class Settings_Page {

	const SLUG = 'guide-settings';

	/** tab key => [label, option group] */
	const TABS = array(
		'payments'     => array( 'Payments', 'jsl_settings_payments' ),
		'subscription' => array( 'Subscription', 'jsl_settings_subscription' ),
		'google'       => array( 'Google Sign-In', 'jsl_settings_google' ),
		'community'    => array( 'Community', 'jsl_settings_community' ),
		'ads'          => array( 'Ads', 'jsl_settings_ads' ),
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
			__( 'LMS Settings', 'guide-lms' ),
			__( 'Settings', 'guide-lms' ),
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

		// Community (stories + leaderboard).
		register_setting( 'jsl_settings_community', Success_Stories::OPTION_ENABLED, $bool );
		register_setting( 'jsl_settings_community', Leaderboard::OPTION_ENABLED, $bool );

		// Ads.
		register_setting( 'jsl_settings_ads', Ads::OPTION_ENABLED, $bool );
		register_setting( 'jsl_settings_ads', Ads::OPTION_CLIENT, $text );
		register_setting( 'jsl_settings_ads', Ads::OPTION_SLOT_FEED, $text );
		register_setting( 'jsl_settings_ads', Ads::OPTION_SLOT_PAGE, $text );
		register_setting( 'jsl_settings_ads', Ads::OPTION_TEST, $bool );

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
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'guide-lms' ) );
		}

		$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'payments';
		$tab       = isset( self::TABS[ $requested ] ) ? $requested : 'payments';
		$group     = self::TABS[ $tab ][1];
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'LMS Settings', 'guide-lms' ); ?></h1>

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
			__( 'Mode', 'guide-lms' ),
			sprintf(
				'<select name="%1$s" id="%1$s"><option value="test" %2$s>%3$s</option><option value="live" %4$s>%5$s</option></select>',
				esc_attr( Payment_Settings::OPTION_MODE ),
				selected( Payment_Settings::mode(), 'test', false ),
				esc_html__( 'Test', 'guide-lms' ),
				selected( Payment_Settings::mode(), 'live', false ),
				esc_html__( 'Live', 'guide-lms' )
			),
			__( 'Test mode talks to Dodo’s sandbox. Switch to Live only once you have tested a real checkout.', 'guide-lms' )
		);

		self::row( __( 'API key', 'guide-lms' ), self::text_input( Payment_Settings::OPTION_API_KEY, '', 'password' ) );
		self::row( __( 'Webhook secret', 'guide-lms' ), self::text_input( Payment_Settings::OPTION_WEBHOOK_SECRET, '', 'password' ) );

		self::row(
			__( 'Webhook URL', 'guide-lms' ),
			'<code>' . esc_html( Payment_Settings::webhook_url() ) . '</code>',
			__( 'Register this in Dodo Payments → Developer → Webhooks. Subscribe to <code>payment.succeeded</code> and, if you sell a subscription, the <code>subscription.*</code> events.', 'guide-lms' )
		);
	}

	/* ---- Tab: Subscription ---- */

	private static function fields_subscription() {
		self::row(
			__( 'Platform subscription', 'guide-lms' ),
			self::checkbox( Subscription::OPTION_ENABLED, __( 'Offer a subscription that unlocks every course', 'guide-lms' ) ),
			__( 'The subscription is the only thing sold — courses have no individual price. A subscriber gets every members-only course, and sees no ads, for as long as it is active.', 'guide-lms' )
		);

		self::row(
			__( 'Dodo product ID', 'guide-lms' ),
			self::text_input( Subscription::OPTION_PRODUCT_ID, 'pdt_…' ),
			__( 'Create a recurring product in Dodo Payments and paste its ID here.', 'guide-lms' )
		);

		self::row( __( 'Price label', 'guide-lms' ), self::text_input( Subscription::OPTION_PRICE_LABEL, '$19/month' ), __( 'Shown on the site. The amount actually charged is whatever the Dodo product says.', 'guide-lms' ) );

		self::row(
			__( 'Billing period (days)', 'guide-lms' ),
			sprintf(
				'<input type="number" min="1" class="small-text" name="%1$s" id="%1$s" value="%2$s">',
				esc_attr( Subscription::OPTION_PERIOD ),
				esc_attr( (string) Subscription::period_days() )
			),
			__( 'Fallback only — when Dodo sends a next-billing date, that wins.', 'guide-lms' )
		);

		self::row(
			__( 'Pitch', 'guide-lms' ),
			sprintf(
				'<textarea class="large-text" rows="3" name="%1$s" id="%1$s">%2$s</textarea>',
				esc_attr( Subscription::OPTION_BLURB ),
				esc_textarea( Subscription::blurb() )
			),
			__( 'One or two lines shown on the pricing card.', 'guide-lms' )
		);
	}

	/* ---- Tab: Google Sign-In ---- */

	private static function fields_google() {
		self::row(
			__( 'Google sign-in', 'guide-lms' ),
			self::checkbox( Google_Auth::OPTION_ENABLED, __( 'Let people sign in with Google', 'guide-lms' ) )
		);

		self::row(
			__( 'Authorized redirect URI', 'guide-lms' ),
			'<code>' . esc_html( Google_Auth::callback_url() ) . '</code>',
			__( 'Paste this into Google Cloud Console → APIs &amp; Services → Credentials → your OAuth client → Authorized redirect URIs. It must match exactly, including https and any trailing path.', 'guide-lms' )
		);

		self::row( __( 'Client ID', 'guide-lms' ), self::text_input( Google_Auth::OPTION_CLIENT_ID, '….apps.googleusercontent.com' ) );

		$secret_constant = defined( 'GUIDE_GOOGLE_CLIENT_SECRET' ) ? 'GUIDE_GOOGLE_CLIENT_SECRET' : ( defined( 'JSL_GOOGLE_CLIENT_SECRET' ) ? 'JSL_GOOGLE_CLIENT_SECRET' : '' );

		self::row(
			__( 'Client secret', 'guide-lms' ),
			$secret_constant
				? '<em>' . sprintf(
					/* translators: %s: the wp-config.php constant name currently in use. */
					esc_html__( 'Set in wp-config.php via %s — this field is ignored.', 'guide-lms' ),
					'<code>' . esc_html( $secret_constant ) . '</code>'
				) . '</em>'
				: self::text_input( Google_Auth::OPTION_CLIENT_SECRET, '', 'password' ),
			__( 'For the strongest setup, define <code>GUIDE_GOOGLE_CLIENT_SECRET</code> in wp-config.php instead of storing it in the database.', 'guide-lms' )
		);

		self::row(
			__( 'New accounts', 'guide-lms' ),
			self::checkbox( Google_Auth::OPTION_ALLOW_SIGNUP, __( 'Create an account the first time someone signs in with Google', 'guide-lms' ) ),
			__( 'Turn this off to allow Google sign-in only for people who already have an account.', 'guide-lms' )
		);
	}

	/* ---- Tab: Community ---- */

	private static function fields_community() {
		self::row(
			__( 'Success stories', 'guide-lms' ),
			self::checkbox( Success_Stories::OPTION_ENABLED, __( 'Let learners submit success stories', 'guide-lms' ) ),
			sprintf(
				/* translators: %s: the Wall of Success URL. */
				__( 'Stories are always submitted as <strong>pending</strong> and never appear until you approve them in LMS → Stories. Public wall: %s', 'guide-lms' ),
				'<code>' . esc_html( Success_Stories::archive_url() ) . '</code>'
			)
		);

		self::row(
			__( 'Public leaderboard', 'guide-lms' ),
			self::checkbox( Leaderboard::OPTION_ENABLED, __( 'Publish a leaderboard of learners', 'guide-lms' ) ),
			sprintf(
				/* translators: %s: the leaderboard URL. */
				__( 'This publishes learners’ display names and lesson counts at %s. Only aggregate totals are shown — never an email or which courses someone is taking — and every learner can remove themselves from the board. Off by default.', 'guide-lms' ),
				'<code>' . esc_html( Leaderboard::url() ) . '</code>'
			)
		);
	}

	/* ---- Tab: Ads ---- */

	private static function fields_ads() {
		self::row(
			__( 'Show ads', 'guide-lms' ),
			self::checkbox( Ads::OPTION_ENABLED, __( 'Show AdSense units to visitors without a subscription', 'guide-lms' ) ),
			__( 'Subscribers and staff never see an ad, and the AdSense script is not even loaded for them. Ads never appear inside lesson content, or on the account and sign-in pages.', 'guide-lms' )
		);

		self::row(
			__( 'Publisher ID', 'guide-lms' ),
			self::text_input( Ads::OPTION_CLIENT, 'ca-pub-0000000000000000' ),
			__( 'From AdSense → Account → Settings. Ads stay off until this is filled in.', 'guide-lms' )
		);

		self::row(
			__( 'Slot: below content', 'guide-lms' ),
			self::text_input( Ads::OPTION_SLOT_PAGE, '1234567890' ),
			__( 'Shown under a lesson and under a course curriculum.', 'guide-lms' )
		);

		self::row(
			__( 'Slot: course listings', 'guide-lms' ),
			self::text_input( Ads::OPTION_SLOT_FEED, '1234567890' ),
			__( 'Shown once beneath the catalogue grid.', 'guide-lms' )
		);

		self::row(
			__( 'Test mode', 'guide-lms' ),
			self::checkbox( Ads::OPTION_TEST, __( 'Render test units', 'guide-lms' ) ),
			__( 'Adds <code>data-adtest="on"</code>. Use this while checking placement — clicking your own live ads will get the account banned.', 'guide-lms' )
		);
	}

	/* ---- Tab: SEO ---- */

	private static function fields_seo() {
		self::row(
			__( 'Default description', 'guide-lms' ),
			sprintf(
				'<textarea class="large-text" rows="3" name="%1$s" id="%1$s">%2$s</textarea>',
				esc_attr( Seo::OPTION_DESCRIPTION ),
				esc_textarea( (string) get_option( Seo::OPTION_DESCRIPTION, '' ) )
			),
			__( 'Used on the homepage and anywhere a page has no excerpt of its own. Aim for 150–160 characters.', 'guide-lms' )
		);

		self::row(
			__( 'Social share image', 'guide-lms' ),
			self::text_input( Seo::OPTION_SOCIAL_IMAGE, 'https://…/og-image.png', 'url' ),
			__( 'Fallback Open Graph image, 1200×630. Courses and lessons use their own featured image when they have one.', 'guide-lms' )
		);

		self::row( __( 'Twitter / X handle', 'guide-lms' ), self::text_input( Seo::OPTION_TWITTER, '@yourhandle' ) );

		self::row(
			__( 'Organization name', 'guide-lms' ),
			self::text_input( Seo::OPTION_ORG_NAME, get_bloginfo( 'name' ) ),
			__( 'Used in structured data as the course provider. Defaults to the site title.', 'guide-lms' )
		);
	}

	/* ---- Tab: PWA ---- */

	private static function fields_pwa() {
		self::row(
			__( 'Installable app', 'guide-lms' ),
			self::checkbox( Pwa::OPTION_ENABLED, __( 'Serve a web app manifest and service worker', 'guide-lms' ) ),
			__( 'Lets learners install the site to their home screen and keeps visited lessons readable offline.', 'guide-lms' )
		);

		self::row( __( 'App name', 'guide-lms' ), self::text_input( Pwa::OPTION_NAME, get_bloginfo( 'name' ) ) );
		self::row( __( 'Short name', 'guide-lms' ), self::text_input( Pwa::OPTION_SHORT_NAME, 'Job Seekers' ), __( 'Shown under the home-screen icon. Keep it under 12 characters.', 'guide-lms' ) );

		self::row(
			__( 'Theme colour', 'guide-lms' ),
			sprintf(
				'<input type="text" class="regular-text" name="%1$s" id="%1$s" value="%2$s" placeholder="#414BA0">',
				esc_attr( Pwa::OPTION_THEME_COLOR ),
				esc_attr( Pwa::theme_color() )
			),
			__( 'Colours the browser chrome when the app is launched from the home screen.', 'guide-lms' )
		);

		self::row(
			__( 'Manifest', 'guide-lms' ),
			'<code>' . esc_html( home_url( '/manifest.webmanifest' ) ) . '</code>',
			__( 'Generated on the fly — nothing to upload.', 'guide-lms' )
		);
	}
}
