<?php
/**
 * Google AdSense placements.
 *
 * Ads are how the free tier stays free, so they are deliberately limited and
 * deliberately honest about it:
 *
 *   · Subscribers and staff never see one. That is half of what a subscription
 *     buys, so it has to be absolute — not "fewer ads", none.
 *   · Never inside lesson content. A learner in the middle of an explanation is
 *     the worst possible moment to interrupt, and an ad wedged between two
 *     paragraphs of a tutorial is the thing that makes a site feel cheap.
 *   · Never on sign-in, checkout, or account pages, where a stray ad next to a
 *     payment form reads as a phishing risk.
 *   · The AdSense script is only loaded on pages that will actually render a
 *     slot, so signed-in subscribers never hand Google a page view at all.
 */

namespace Guide\Ads;

use Guide\Access\Access;

defined( 'ABSPATH' ) || exit;

class Ads {

	const OPTION_ENABLED   = 'jsl_ads_enabled';
	const OPTION_CLIENT    = 'jsl_ads_client';       // ca-pub-XXXXXXXXXXXXXXXX
	const OPTION_SLOT_FEED = 'jsl_ads_slot_feed';    // archives / catalogue
	const OPTION_SLOT_PAGE = 'jsl_ads_slot_page';    // below article content
	const OPTION_TEST      = 'jsl_ads_test';         // render test units

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false ) && '' !== self::client();
	}

	public static function client(): string {
		return (string) get_option( self::OPTION_CLIENT, '' );
	}

	public static function slot( string $which ): string {
		$key = 'feed' === $which ? self::OPTION_SLOT_FEED : self::OPTION_SLOT_PAGE;
		return (string) get_option( $key, '' );
	}

	public static function is_test(): bool {
		return (bool) get_option( self::OPTION_TEST, false );
	}

	/**
	 * Should this request see ads at all?
	 *
	 * One function, asked by both the script loader and every placement, so a
	 * slot can never render on a page whose script was suppressed (or worse,
	 * the other way round).
	 */
	public static function should_show(): bool {
		if ( ! self::is_enabled() ) {
			return false;
		}

		// The whole point of the subscription.
		if ( class_exists( 'Guide\\Access\\Access' ) && Access::has_all_access() ) {
			return false;
		}

		// Never beside anything that looks like a credential or payment form.
		if ( is_admin() || is_feed() || is_embed() || is_404() ) {
			return false;
		}

		if ( function_exists( 'is_page' ) && is_page( array( 'account', 'my-learning', 'checkout' ) ) ) {
			return false;
		}

		/**
		 * Suppress ads for a particular request.
		 *
		 * @param bool $show
		 */
		return (bool) apply_filters( 'guide_show_ads', true );
	}

	public static function enqueue() {
		if ( ! self::should_show() ) {
			return;
		}

		wp_enqueue_script(
			'guide-adsense',
			'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( self::client() ),
			array(),
			null,
			array(
				'strategy' => 'async',
				'in_footer' => false,
			)
		);

		// AdSense requires the crossorigin attribute on its loader.
		add_filter(
			'script_loader_tag',
			static function ( $tag, $handle ) {
				if ( 'guide-adsense' !== $handle ) {
					return $tag;
				}
				return str_replace( ' src=', ' crossorigin="anonymous" src=', $tag );
			},
			10,
			2
		);
	}

	/**
	 * Render one ad slot.
	 *
	 * @param string $which 'feed' (in a list of cards) or 'page' (below content).
	 * @param string $label Visible label. Ads must be labelled — it is a
	 *                      disclosure obligation and it is also just honest.
	 */
	public static function render( string $which = 'page', string $label = '' ) {
		if ( ! self::should_show() ) {
			return;
		}

		$slot = self::slot( $which );

		if ( '' === $slot ) {
			return;
		}

		$label = $label ? $label : __( 'Advertisement', 'guide-lms' );
		?>
		<aside class="guide-ad guide-ad--<?php echo esc_attr( $which ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<span class="guide-ad__label"><?php echo esc_html( $label ); ?></span>
			<ins class="adsbygoogle"
				style="display:block"
				data-ad-client="<?php echo esc_attr( self::client() ); ?>"
				data-ad-slot="<?php echo esc_attr( $slot ); ?>"
				data-ad-format="auto"
				data-full-width-responsive="true"
				<?php echo self::is_test() ? 'data-adtest="on"' : ''; ?>></ins>
			<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		</aside>
		<?php
	}

	/**
	 * The "turn these off" prompt shown under a slot to signed-in learners.
	 * Non-nagging: one line, one link, only where an ad already appeared.
	 */
	public static function render_upsell() {
		if ( ! self::should_show() || ! is_user_logged_in() ) {
			return;
		}

		if ( ! class_exists( 'Guide\\Payments\\Subscription' ) || ! \Guide\Payments\Subscription::is_enabled() ) {
			return;
		}
		?>
		<p class="guide-ad__upsell">
			<?php esc_html_e( 'Ads keep the free courses free.', 'guide-lms' ); ?>
			<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( 'Subscribe to remove them', 'guide-lms' ); ?></a>
		</p>
		<?php
	}
}
