<?php
/**
 * The sponsor's side: submit a campaign, pay for it, watch it deliver.
 *
 * Routed at /sponsor/ so a sponsor never sees wp-admin. They are not staff and
 * should not be handed an interface built for staff — the role has no
 * `edit_posts` for exactly that reason.
 *
 * The flow is: submit → owner reviews → approved and locked → pay → live.
 * Editing stops at approval, deliberately. An ad that can be changed after
 * review is an ad that was never really reviewed.
 */

namespace Guide\Sponsors;

use Guide\Payments\Settings as Payment_Settings;

defined( 'ABSPATH' ) || exit;

class Sponsor_Portal {

	const SLUG            = 'sponsor';
	const QUERY_VAR       = 'guide_sponsor_view';
	const REWRITE_OPTION  = 'jsl_sponsor_rewrite';
	const REWRITE_VERSION = '1';

	const OPTION_PRICES  = 'jsl_sponsor_prices';   // slot => monthly price label
	const OPTION_PRODUCT = 'jsl_sponsor_product';  // Dodo product id
	const OPTION_OPEN    = 'jsl_sponsor_open';     // accepting submissions?

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'route' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_flush' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function url( string $sub = '' ): string {
		return home_url( '/' . self::SLUG . '/' . ( $sub ? trim( $sub, '/' ) . '/' : '' ) );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^' . self::SLUG . '/?$', 'index.php?' . self::QUERY_VAR . '=overview', 'top' );
		add_rewrite_rule( '^' . self::SLUG . '/apply/?$', 'index.php?' . self::QUERY_VAR . '=apply', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function maybe_flush() {
		if ( get_option( self::REWRITE_OPTION ) === self::REWRITE_VERSION ) {
			return;
		}

		self::add_rewrite_rules();
		flush_rewrite_rules();
		update_option( self::REWRITE_OPTION, self::REWRITE_VERSION, false );
	}

	public static function is_open(): bool {
		return (bool) get_option( self::OPTION_OPEN, true );
	}

	/** Monthly price label per slot, for display. */
	public static function price( string $slot ): string {
		$prices = (array) get_option( self::OPTION_PRICES, array() );
		return (string) ( $prices[ $slot ] ?? '' );
	}

	public static function route() {
		$view = get_query_var( self::QUERY_VAR );

		if ( ! $view ) {
			return;
		}

		// The apply page is public — a company evaluating whether to sponsor
		// should not have to make an account to read the rates.
		if ( 'overview' === $view && ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( self::url() ) );
			exit;
		}

		nocache_headers();
		add_filter( 'wp_robots', 'wp_robots_no_robots' );
		status_header( 200 );

		$template = locate_template( array( 'guide-sponsor.php' ) );

		if ( $template ) {
			include $template;
			exit;
		}

		wp_die( esc_html__( 'The active theme does not provide a sponsor template.', 'guide-lms' ) );
	}

	/**
	 * A sponsor's own campaigns.
	 *
	 * @return \WP_Post[]
	 */
	public static function campaigns_for( int $user_id ): array {
		if ( ! $user_id ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => Sponsorship::POST_TYPE,
				'post_status'    => 'any',
				'author'         => $user_id,
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	// -------------------------------------------------------------------------
	// REST
	// -------------------------------------------------------------------------

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/sponsorships',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'submit' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'company'  => array( 'required' => true, 'type' => 'string' ),
					'slot'     => array( 'required' => true, 'type' => 'string' ),
					'headline' => array( 'required' => true, 'type' => 'string' ),
					'url'      => array( 'required' => true, 'type' => 'string' ),
					'months'   => array( 'required' => true, 'type' => 'integer' ),
				),
			)
		);

		register_rest_route(
			'guide/v1',
			'/sponsorships/(?P<id>\d+)/pay',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'pay' ),
				'permission_callback' => 'is_user_logged_in',
			)
		);
	}

	/**
	 * Submit a campaign for review.
	 *
	 * Everything arrives as a draft with status `submitted`. Nothing a sponsor
	 * types is rendered anywhere until the owner has read it.
	 */
	public static function submit( \WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! self::is_open() ) {
			return new \WP_REST_Response( array( 'error' => __( 'Sponsorship applications are closed at the moment.', 'guide-lms' ) ), 403 );
		}

		$slot = sanitize_key( (string) $request->get_param( 'slot' ) );

		if ( ! isset( Sponsorship::SLOTS[ $slot ] ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Unknown slot.', 'guide-lms' ) ), 400 );
		}

		$url = esc_url_raw( (string) $request->get_param( 'url' ) );

		if ( ! $url || ! wp_http_validate_url( $url ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'That destination URL does not look valid.', 'guide-lms' ) ), 400 );
		}

		$company  = sanitize_text_field( (string) $request->get_param( 'company' ) );
		$headline = sanitize_text_field( (string) $request->get_param( 'headline' ) );
		$body     = sanitize_textarea_field( (string) $request->get_param( 'body' ) );
		$months   = max( 1, min( 12, (int) $request->get_param( 'months' ) ) );
		$logo     = (int) $request->get_param( 'logo' );

		if ( mb_strlen( $company ) < 2 || mb_strlen( $headline ) < 4 ) {
			return new \WP_REST_Response( array( 'error' => __( 'Company and headline are required.', 'guide-lms' ) ), 400 );
		}

		// A logo must be an image this user actually uploaded — otherwise the
		// field is a way to display any attachment on the site.
		if ( $logo ) {
			$attachment = get_post( $logo );

			if ( ! $attachment
				|| 'attachment' !== $attachment->post_type
				|| (int) $attachment->post_author !== $user_id
				|| 0 !== strpos( (string) get_post_mime_type( $logo ), 'image/' ) ) {
				$logo = 0;
			}
		}

		// One pending application at a time, so review does not become a queue
		// of one company's retries.
		$pending = get_posts(
			array(
				'post_type'      => Sponsorship::POST_TYPE,
				'post_status'    => 'any',
				'author'         => $user_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => 'jsl_sponsor_status', 'value' => 'submitted' ),
				),
			)
		);

		if ( $pending ) {
			return new \WP_REST_Response( array( 'error' => __( 'You already have an application waiting to be reviewed.', 'guide-lms' ) ), 429 );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => Sponsorship::POST_TYPE,
				'post_status' => 'draft',
				'post_title'  => $company . ' — ' . ( Sponsorship::SLOTS[ $slot ]['label'] ?? $slot ),
				'post_author' => $user_id,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'error' => $post_id->get_error_message() ), 400 );
		}

		update_post_meta( $post_id, 'jsl_sponsor_company', $company );
		update_post_meta( $post_id, 'jsl_sponsor_slot', $slot );
		update_post_meta( $post_id, 'jsl_sponsor_headline', $headline );
		update_post_meta( $post_id, 'jsl_sponsor_body', $body );
		update_post_meta( $post_id, 'jsl_sponsor_url', $url );
		update_post_meta( $post_id, 'jsl_sponsor_logo', $logo );
		update_post_meta( $post_id, 'jsl_sponsor_months', $months );
		update_post_meta( $post_id, 'jsl_sponsor_status', 'submitted' );
		update_post_meta( $post_id, 'jsl_sponsor_locked', 0 );

		do_action( 'guide_sponsorship_submitted', $post_id );

		return new \WP_REST_Response( array( 'submitted' => true, 'id' => (int) $post_id ), 201 );
	}

	/**
	 * Start a Dodo checkout for an approved campaign.
	 */
	public static function pay( \WP_REST_Request $request ) {
		$id      = (int) $request['id'];
		$user_id = get_current_user_id();
		$post    = get_post( $id );

		if ( ! $post || Sponsorship::POST_TYPE !== $post->post_type || (int) $post->post_author !== $user_id ) {
			return new \WP_REST_Response( array( 'error' => __( 'Not your campaign.', 'guide-lms' ) ), 403 );
		}

		if ( 'approved' !== Sponsorship::status( $id ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'This campaign is not awaiting payment.', 'guide-lms' ) ), 409 );
		}

		$product = (string) get_option( self::OPTION_PRODUCT, '' );

		if ( ! $product || ! Payment_Settings::api_key() ) {
			return new \WP_REST_Response( array( 'error' => __( 'Sponsorship payments are not configured yet — we will invoice you directly.', 'guide-lms' ) ), 503 );
		}

		$months = max( 1, (int) get_post_meta( $id, 'jsl_sponsor_months', true ) );
		$user   = get_userdata( $user_id );

		$response = wp_remote_post(
			Payment_Settings::base_url() . '/checkouts',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . Payment_Settings::api_key(),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'product_cart' => array(
							array( 'product_id' => $product, 'quantity' => $months ),
						),
						'customer'     => array( 'email' => $user ? $user->user_email : '' ),
						'return_url'   => self::url(),
						'metadata'     => array(
							'sponsorship_id' => (string) $id,
							'user_id'        => (string) $user_id,
							'kind'           => 'sponsorship',
						),
					)
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( array( 'error' => $response->get_error_message() ), 502 );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data['checkout_url'] ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Could not start checkout.', 'guide-lms' ) ), 502 );
		}

		return new \WP_REST_Response( array( 'checkout_url' => $data['checkout_url'] ), 200 );
	}
}
