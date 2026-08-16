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
 *   · The AdSense script is not loaded at all until a slot is about to come
 *     into view, so a reader who never scrolls that far never pays for it —
 *     and a subscriber never hands Google a page view.
 *
 * Every unit is responsive. One slot ID works everywhere, because a responsive
 * unit adapts to the box it is given, and requiring a separate ID per placement
 * is how a site ends up earning nothing because somebody filled in two boxes
 * out of three.
 */

namespace Guide\Ads;

use Guide\Access\Access;
use Guide\Account\Account;

defined( 'ABSPATH' ) || exit;

class Ads {

	const OPTION_ENABLED   = 'jsl_ads_enabled';
	const OPTION_CLIENT    = 'jsl_ads_client';       // ca-pub-XXXXXXXXXXXXXXXX
	const OPTION_SLOT_FEED = 'jsl_ads_slot_feed';    // archives / catalogue
	const OPTION_SLOT_PAGE = 'jsl_ads_slot_page';    // below article content
	const OPTION_SLOT_SIDE = 'jsl_ads_slot_side';    // course and lesson sidebars
	const OPTION_SLOT_ANY  = 'jsl_ads_slot_default'; // used when a named slot is blank
	const OPTION_TEST      = 'jsl_ads_test';         // render test units

	/**
	 * Shapes each placement should ask for.
	 *
	 * A sidebar rail and a full-width strip under an article want very
	 * different creatives, and "auto" in a narrow column produces a tall
	 * banner that pushes the real content off the screen.
	 */
	const FORMATS = array(
		'page' => array( 'format' => 'auto', 'responsive' => 'true' ),
		'feed' => array( 'format' => 'auto', 'responsive' => 'true' ),
		'side' => array( 'format' => 'rectangle', 'responsive' => 'true' ),
	);

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_head', array( __CLASS__, 'preconnect' ), 1 );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, true );
	}

	public static function client(): string {
		return trim( (string) get_option( self::OPTION_CLIENT, '' ) );
	}

	/**
	 * The AdSense slot ID for a placement, falling back to the default unit.
	 */
	public static function slot( string $which ): string {
		$map = array(
			'feed' => self::OPTION_SLOT_FEED,
			'page' => self::OPTION_SLOT_PAGE,
			'side' => self::OPTION_SLOT_SIDE,
		);

		$slot = trim( (string) get_option( $map[ $which ] ?? self::OPTION_SLOT_PAGE, '' ) );

		if ( '' === $slot ) {
			$slot = trim( (string) get_option( self::OPTION_SLOT_ANY, '' ) );
		}

		return $slot;
	}

	public static function is_test(): bool {
		return (bool) get_option( self::OPTION_TEST, false );
	}

	/** Configured enough to serve anything at all. */
	public static function is_ready(): bool {
		return self::is_enabled() && '' !== self::client();
	}

	// -------------------------------------------------------------------------
	// Where ads may appear
	// -------------------------------------------------------------------------

	public static function should_show(): bool {
		if ( ! self::is_ready() ) {
			return false;
		}

		// The whole point of the subscription.
		if ( class_exists( 'Guide\\Access\\Access' ) && Access::has_all_access() ) {
			return false;
		}

		return self::context_allows();
	}

	/**
	 * Is this the kind of page an ad may appear on at all?
	 *
	 * Separate from should_show() because the administrator's slot preview
	 * follows these rules but not the subscriber rule.
	 */
	public static function context_allows(): bool {
		// Never beside anything that looks like a credential or payment form.
		if ( is_admin() || is_feed() || is_embed() || is_404() ) {
			return false;
		}

		// The account area is routed by the plugin rather than backed by a
		// page, so is_page( 'account' ) is always false there — including on
		// the receipt view, which shows somebody's payment history.
		if ( class_exists( 'Guide\\Account\\Account' ) && get_query_var( Account::QUERY_VAR ) ) {
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

	// -------------------------------------------------------------------------
	// Loading
	// -------------------------------------------------------------------------

	/**
	 * Warm the connection to Google's ad host, but only where an ad can appear.
	 *
	 * A preconnect costs one DNS lookup and one TLS handshake in parallel with
	 * the page instead of after it, which is most of an ad's perceived delay.
	 * Emitting it on pages that will never show an ad would be the opposite of
	 * an optimisation.
	 */
	public static function preconnect() {
		if ( ! self::should_show() ) {
			return;
		}

		echo '<link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>' . "\n";
		echo '<link rel="dns-prefetch" href="//pagead2.googlesyndication.com">' . "\n";
	}

	/**
	 * The loader.
	 *
	 * Deliberately not an enqueued AdSense script. The tag is fetched by our
	 * own small script the first time a slot approaches the viewport, so a
	 * visitor who reads two paragraphs and leaves never downloads it, and the
	 * ad never competes with the article for bandwidth on the way in.
	 */
	public static function enqueue() {
		if ( ! self::should_show() ) {
			return;
		}

		wp_register_script( 'guide-ads', '', array(), GUIDE_VERSION, true );
		wp_enqueue_script( 'guide-ads' );

		wp_add_inline_script( 'guide-ads', self::loader_script() );
	}

	private static function loader_script(): string {
		$src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( self::client() );

		// Kept small and inline: a separate request to fetch the thing that
		// defers a request is self-defeating.
		$json = wp_json_encode( $src );

		return <<<JS
(function(){
	var src = {$json};
	var loaded = false;

	function loadOnce(){
		if (loaded) { return; }
		loaded = true;
		var s = document.createElement('script');
		s.async = true;
		s.src = src;
		s.crossOrigin = 'anonymous';
		document.head.appendChild(s);
	}

	function fill(el){
		loadOnce();
		try {
			(window.adsbygoogle = window.adsbygoogle || []).push({});
			el.setAttribute('data-guide-filled', '1');
		} catch (e) {}
	}

	function start(){
		var slots = document.querySelectorAll('ins.adsbygoogle:not([data-guide-filled])');

		if (!slots.length) { return; }

		if (!('IntersectionObserver' in window)) {
			Array.prototype.forEach.call(slots, fill);
			return;
		}

		// 400px of runway: enough that the unit is usually filled by the time
		// it is actually on screen, without loading it for a reader who never
		// gets there.
		var io = new IntersectionObserver(function(entries){
			entries.forEach(function(entry){
				if (!entry.isIntersecting) { return; }
				io.unobserve(entry.target);
				fill(entry.target);
			});
		}, { rootMargin: '400px 0px' });

		Array.prototype.forEach.call(slots, function(el){ io.observe(el); });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
JS;
	}

	// -------------------------------------------------------------------------
	// Rendering
	// -------------------------------------------------------------------------

	/**
	 * Render one ad slot.
	 *
	 * @param string $which 'page', 'feed' or 'side'.
	 * @param string $label Visible label. Ads must be labelled — it is a
	 *                      disclosure obligation and it is also just honest.
	 * @return string What was drawn: 'adsense', 'preview' or '' for nothing.
	 */
	public static function render( string $which = 'page', string $label = '' ): string {
		if ( ! self::should_show() ) {
			// Administrators see an outline of the slot instead.
			//
			// Staff see no advertising, which is correct, but it also means the
			// person running the site cannot see where ads land or whether a
			// placement is configured. The page-type rules still apply, so this
			// stays off the account and sign-in pages like everything else.
			if ( self::context_allows() && current_user_can( 'manage_options' ) ) {
				return self::render_preview( $which ) ? 'preview' : '';
			}

			return '';
		}

		$slot = self::slot( $which );

		if ( '' === $slot ) {
			return '';
		}

		$format = self::FORMATS[ $which ] ?? self::FORMATS['page'];
		$label  = $label ? $label : __( 'Advertisement', 'guide-lms' );
		?>
		<aside class="guide-ad guide-ad--<?php echo esc_attr( $which ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<span class="guide-ad__label"><?php echo esc_html( $label ); ?></span>
			<ins class="adsbygoogle"
				style="display:block"
				data-ad-client="<?php echo esc_attr( self::client() ); ?>"
				data-ad-slot="<?php echo esc_attr( $slot ); ?>"
				data-ad-format="<?php echo esc_attr( $format['format'] ); ?>"
				data-full-width-responsive="<?php echo esc_attr( $format['responsive'] ); ?>"
				<?php echo self::is_test() ? 'data-adtest="on"' : ''; ?>></ins>
		</aside>
		<?php

		return 'adsense';
	}

	/**
	 * An outline of where an ad would go, for administrators only.
	 *
	 * Never shown to a visitor, so it cannot be mistaken for content or for a
	 * broken advert.
	 */
	public static function render_preview( string $which ): bool {
		$configured = self::is_ready() && '' !== self::slot( $which );

		$note = $configured
			? __( 'An ad appears here for visitors without a subscription.', 'guide-lms' )
			: __( 'Ad slot — add a publisher ID and slot in LMS → Settings → Ads.', 'guide-lms' );
		?>
		<aside class="guide-ad guide-ad--preview guide-ad--<?php echo esc_attr( $which ); ?>"
			aria-label="<?php esc_attr_e( 'Ad slot', 'guide-lms' ); ?>">
			<span class="guide-ad__label"><?php esc_html_e( 'Ad slot', 'guide-lms' ); ?></span>
			<p class="guide-ad__note"><?php echo esc_html( $note ); ?></p>
		</aside>
		<?php

		return true;
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
