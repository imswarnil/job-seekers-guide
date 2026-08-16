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
use Guide\Account\Account;

defined( 'ABSPATH' ) || exit;

class Ads {

	const OPTION_ENABLED   = 'jsl_ads_enabled';
	const OPTION_CLIENT    = 'jsl_ads_client';       // ca-pub-XXXXXXXXXXXXXXXX
	const OPTION_SLOT_FEED = 'jsl_ads_slot_feed';    // archives / catalogue
	const OPTION_SLOT_PAGE = 'jsl_ads_slot_page';    // below article content
	const OPTION_TEST      = 'jsl_ads_test';         // render test units
	const OPTION_SLOT_ANY  = 'jsl_ads_slot_default'; // used when a named slot is blank
	const OPTION_HOUSE     = 'jsl_ads_house';        // show the house ad in empty slots

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, false ) && '' !== self::client();
	}

	/** Cheap check for any live campaign, memoised per request. */
	public static function has_live_sponsorship(): bool {
		static $has = null;

		if ( null !== $has ) {
			return $has;
		}

		$has = false;

		if ( class_exists( 'Guide\\Sponsors\\Sponsorship' ) ) {
			foreach ( array_keys( \Guide\Sponsors\Sponsorship::SLOTS ) as $slot ) {
				if ( \Guide\Sponsors\Sponsorship::for_slot( $slot ) ) {
					$has = true;
					break;
				}
			}
		}

		return $has;
	}

	public static function client(): string {
		return (string) get_option( self::OPTION_CLIENT, '' );
	}

	/**
	 * The AdSense slot ID for a placement.
	 *
	 * Falls back to a single default unit when the specific one is blank.
	 * AdSense responsive units adapt to the space they are given, so one unit
	 * genuinely does work everywhere — and requiring three slot IDs before a
	 * single ad appears is the sort of setup step that leaves a site earning
	 * nothing for a month because somebody filled in two boxes out of three.
	 */
	public static function slot( string $which ): string {
		$key = 'feed' === $which ? self::OPTION_SLOT_FEED : self::OPTION_SLOT_PAGE;

		$slot = trim( (string) get_option( $key, '' ) );

		if ( '' === $slot ) {
			$slot = trim( (string) get_option( self::OPTION_SLOT_ANY, '' ) );
		}

		return $slot;
	}

	/** Should empty slots show the house "sponsor this space" card? */
	public static function house_enabled(): bool {
		return (bool) get_option( self::OPTION_HOUSE, true );
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
		// A live sponsorship is worth rendering even when AdSense is not set
		// up at all — the two are independent revenue streams.
		if ( ! self::is_enabled() && ! self::has_live_sponsorship() ) {
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
	 * Separate from should_show() because the house card follows these rules
	 * but not the subscriber rule — see render() for why.
	 */
	public static function context_allows(): bool {
		// Never beside anything that looks like a credential or payment form.
		if ( is_admin() || is_feed() || is_embed() || is_404() ) {
			return false;
		}

		// The account area is routed by the plugin, not backed by a WordPress
		// page, so is_page( 'account' ) is *always* false there and checking it
		// silently protected nothing — including the receipt view, which shows
		// somebody's payment history.
		if ( class_exists( 'Guide\\Account\\Account' ) && get_query_var( Account::QUERY_VAR ) ) {
			return false;
		}

		if ( function_exists( 'is_page' ) && is_page( array( 'account', 'my-learning', 'checkout' ) ) ) {
			return false;
		}

		// The sponsor console is where somebody is buying a placement. An ad
		// next to that is absurd, and it is another routed view.
		if ( class_exists( 'Guide\\Sponsors\\Sponsorship' ) && get_query_var( 'guide_sponsor' ) ) {
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
		// Only load Google's script when there is actually an AdSense unit to
		// fill — a page served entirely by sponsors should hand Google nothing.
		if ( ! self::should_show() || ! self::is_enabled() ) {
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
	/**
	 * @return string What was drawn: 'sponsor', 'adsense', 'house' or '' for nothing.
	 */
	public static function render( string $which = 'page', string $label = '' ): string {
		if ( ! self::should_show() ) {
			// One exception, for administrators only.
			//
			// Staff and subscribers see no advertising, which is correct — but
			// it also means the person running the site never sees which slots
			// are sitting unsold, on the pages where they are unsold. The house
			// card is not an advertisement; it is the site's own note that this
			// space is for sale, and the operator is precisely who needs to see
			// it. Page-type rules still apply, so it stays off the account and
			// sign-in pages like everything else.
			if ( self::context_allows()
				&& current_user_can( 'manage_options' )
				&& ! self::has_sponsor_for( $which )
			) {
				return self::render_house( $which ) ? 'house' : '';
			}

			return '';
		}

		// The chain, in order of what the slot is worth:
		//
		//   1. A paying sponsor. It would be indefensible to sell a placement
		//      and then let an ad network compete with it for the same space.
		//   2. AdSense, if it is switched on and configured.
		//   3. The house ad — "this space is for sale".
		//
		// The third exists because the first two can both be absent for months
		// on a new site, and a slot that renders nothing at all is a slot
		// nobody remembers they have. It also happens to be the only
		// advertisement the sponsorship product gets.
		if ( self::render_sponsor( $which, $label ) ) {
			return 'sponsor';
		}

		// should_show() deliberately returns true for a sponsor-only request
		// (the two revenue streams are independent), so without this check a
		// site running sponsors with AdSense disabled would still emit an empty
		// <ins> and an adsbygoogle.push() against a script that was never
		// loaded: a grey gap on the page and an error in the console.
		$slot = ( self::is_enabled() && '' !== self::client() ) ? self::slot( $which ) : '';

		if ( '' === $slot ) {
			return self::render_house( $which ) ? 'house' : '';
		}

		\Guide\Sponsors\Sponsor_Stats::record_impression( $which, 0 );

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

		return 'adsense';
	}


	/** Is a campaign live for the placement this slot maps to? */
	private static function has_sponsor_for( string $which ): bool {
		if ( ! class_exists( 'Guide\\Sponsors\\Sponsorship' ) ) {
			return false;
		}

		$slot_map = array(
			'page'  => 'leaderboard',
			'feed'  => 'leaderboard',
			'side'  => 'square',
			'badge' => 'badge',
		);

		return (bool) \Guide\Sponsors\Sponsorship::for_slot( $slot_map[ $which ] ?? $which );
	}

	/**
	 * Draw a sponsored creative if one is live for this slot.
	 *
	 * @return bool True when a sponsor filled the slot.
	 */
	public static function render_sponsor( string $which, string $label = '' ): bool {
		if ( ! class_exists( 'Guide\\Sponsors\\Sponsorship' ) ) {
			return false;
		}

		// The front end asks for 'page' or 'feed'; sponsorship slots are named
		// by shape. Both leaderboard placements map to the wide creative.
		$slot_map = array(
			'page' => 'leaderboard',
			'feed' => 'leaderboard',
			'side' => 'square',
			'badge' => 'badge',
		);

		$slot = $slot_map[ $which ] ?? $which;

		$campaign = \Guide\Sponsors\Sponsorship::for_slot( $slot );

		if ( ! $campaign ) {
			return false;
		}

		$creative = \Guide\Sponsors\Sponsorship::creative( (int) $campaign->ID );
		$label    = $label ? $label : __( 'Sponsored', 'guide-lms' );

		\Guide\Sponsors\Sponsor_Stats::record_impression( $slot, (int) $campaign->ID );

		$href = \Guide\Sponsors\Sponsor_Stats::click_url( (int) $campaign->ID, $slot );
		?>
		<aside class="guide-ad guide-ad--sponsor guide-ad--<?php echo esc_attr( $slot ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<span class="guide-ad__label"><?php echo esc_html( $label ); ?></span>

			<?php // rel="sponsored nofollow" is required disclosure, not decoration. ?>
			<a class="guide-sponsor" href="<?php echo esc_url( $href ); ?>" rel="sponsored nofollow noopener" target="_blank">
				<?php if ( $creative['logo'] ) : ?>
					<span class="guide-sponsor__logo">
						<?php echo wp_get_attachment_image( $creative['logo'], 'medium', false, array( 'alt' => esc_attr( $creative['company'] ) ) ); ?>
					</span>
				<?php endif; ?>

				<span class="guide-sponsor__text">
					<span class="guide-sponsor__headline"><?php echo esc_html( $creative['headline'] ); ?></span>
					<?php if ( $creative['body'] ) : ?>
						<span class="guide-sponsor__body"><?php echo esc_html( $creative['body'] ); ?></span>
					<?php endif; ?>
					<span class="guide-sponsor__by">
						<?php
						printf(
							/* translators: %s: sponsoring company name. */
							esc_html__( 'Sponsored by %s', 'guide-lms' ),
							esc_html( $creative['company'] )
						);
						?>
					</span>
				</span>
			</a>
		</aside>
		<?php

		return true;
	}


	/**
	 * The house ad: an empty slot offering itself.
	 *
	 * Deliberately quiet. It sits in the same box as a real ad, carries the
	 * same "Sponsored" framing so nobody is misled about what the space is for,
	 * and links to the sponsorship page. On a site whose whole argument is that
	 * it is not a content farm, a flashing "ADVERTISE HERE" banner would do
	 * more damage than the slot is worth.
	 *
	 * Never shown to somebody who is mid-purchase of a sponsorship, and never
	 * when an operator has switched it off.
	 */
	public static function render_house( string $which ): bool {
		if ( ! self::house_enabled() ) {
			return false;
		}

		$url = home_url( '/sponsor/' );

		$copy = array(
			'badge' => __( 'Reach people learning to code, right here in the course sidebar.', 'guide-lms' ),
			'feed'  => __( 'Put your company in front of people about to start job hunting.', 'guide-lms' ),
		);

		$body = $copy[ $which ] ?? __( 'Put your company in front of people learning to get their first software job.', 'guide-lms' );
		?>
		<aside class="guide-ad guide-ad--house guide-ad--<?php echo esc_attr( $which ); ?>"
			aria-label="<?php esc_attr_e( 'Sponsorship', 'guide-lms' ); ?>">
			<span class="guide-ad__label"><?php esc_html_e( 'Sponsorship', 'guide-lms' ); ?></span>

			<a class="guide-house" href="<?php echo esc_url( $url ); ?>">
				<span class="guide-house__headline"><?php esc_html_e( 'This space is available', 'guide-lms' ); ?></span>
				<span class="guide-house__body"><?php echo esc_html( $body ); ?></span>
				<span class="guide-house__cta"><?php esc_html_e( 'Sponsor the platform', 'guide-lms' ); ?></span>
			</a>
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
