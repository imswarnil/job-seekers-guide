<?php
/**
 * Sponsorship — companies paying to appear on the site.
 *
 * The model, deliberately:
 *
 *   1. A sponsor submits a creative (logo, headline, body, link) for a slot and
 *      a number of months. It is a DRAFT, visible to nobody.
 *   2. The site owner reviews it. Approving locks the creative — the sponsor
 *      cannot edit it afterwards. That is the point: what was approved is what
 *      runs, and an editable-after-approval ad is an ad you have not actually
 *      reviewed.
 *   3. Payment is taken. Only once paid does the campaign go live, on a date
 *      range set from the paid month count.
 *   4. The sponsor sees impressions and clicks for their own campaigns, and
 *      nobody else's.
 *
 * Sponsors get their own role rather than being editors with extra rules. A
 * role you can reason about ("can this person edit posts? no, never") is worth
 * a great deal more than a capability check scattered across twelve files.
 *
 * Sponsored slots take priority over AdSense: a paying sponsor should not be
 * competing with a network filler for the same space. AdSense fills whatever
 * is unsold.
 */

namespace Guide\Sponsors;

defined( 'ABSPATH' ) || exit;

class Sponsorship {

	const POST_TYPE = 'sponsorship';
	const ROLE      = 'guide_sponsor';

	/** Where an ad can go, and what shape it is. */
	const SLOTS = array(
		'leaderboard' => array(
			'label' => 'Leaderboard',
			'note'  => 'Wide banner below the course catalogue and under lesson content.',
			'ratio' => '728 × 90',
		),
		'square'      => array(
			'label' => 'Square',
			'note'  => 'In the sidebar of course and company pages.',
			'ratio' => '300 × 250',
		),
		'badge'       => array(
			'label' => 'Course navigation badge',
			'note'  => 'A small badge at the foot of the lesson player sidebar. Seen by learners mid-course.',
			'ratio' => 'Logo + one line',
		),
	);

	/** Campaign lifecycle. Stored as post_status-adjacent meta, not post_status,
	 *  because WordPress statuses carry editing semantics we do not want. */
	const STATUSES = array(
		'submitted' => 'Awaiting review',
		'approved'  => 'Approved — awaiting payment',
		'live'      => 'Live',
		'ended'     => 'Ended',
		'rejected'  => 'Not accepted',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'upload_mimes', array( __CLASS__, 'restrict_sponsor_uploads' ) );
		add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'own_media_only' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 27 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );

		// Expire campaigns whose window has closed.
		add_action( 'guide_sponsorship_sweep', array( __CLASS__, 'expire_finished' ) );
		add_action( 'init', array( __CLASS__, 'schedule_sweep' ) );
	}

	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Sponsorships', 'guide-lms' ),
					'singular_name' => __( 'Sponsorship', 'guide-lms' ),
					'edit_item'     => __( 'Review sponsorship', 'guide-lms' ),
				),
				// Never public: a campaign is a record, not a page.
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'supports'     => array( 'title', 'author' ),
				'capabilities' => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap' => true,
			)
		);
	}

	public static function register_meta() {
		$owner_only = function () {
			return current_user_can( 'manage_options' );
		};

		$fields = array(
			'jsl_sponsor_slot'      => 'string',
			'jsl_sponsor_status'    => 'string',
			'jsl_sponsor_headline'  => 'string',
			'jsl_sponsor_body'      => 'string',
			'jsl_sponsor_url'       => 'string',
			'jsl_sponsor_logo'      => 'integer',
			'jsl_sponsor_months'    => 'integer',
			'jsl_sponsor_starts'    => 'string',
			'jsl_sponsor_ends'      => 'string',
			'jsl_sponsor_payment'   => 'string',
			'jsl_sponsor_company'   => 'string',
			'jsl_sponsor_locked'    => 'boolean',
		);

		foreach ( $fields as $key => $type ) {
			register_post_meta(
				self::POST_TYPE,
				$key,
				array(
					'type'          => $type,
					'single'        => true,
					'show_in_rest'  => false,
					'auth_callback' => $owner_only,
				)
			);
		}
	}

	// -------------------------------------------------------------------------
	// Role
	// -------------------------------------------------------------------------

	/**
	 * A sponsor can read, and manage their own campaigns through the front-end
	 * flow. Deliberately no `edit_posts` — they must never reach the editor,
	 * the media library at large, or anyone else's content.
	 */
	public static function add_role() {
		self::sync_role();
	}

	/**
	 * Create the role, or bring an existing one in line with the capabilities
	 * declared here.
	 *
	 * add_role() is a no-op when the role already exists, so on its own it can
	 * never correct a capability set — a site that installed an older version
	 * would keep the old permissions forever. This is called from the upgrade
	 * routine as well as activation, because production deploys by pulling code
	 * and restarting, which never fires the activation hook.
	 */
	public static function sync_role() {
		$caps = array(
			'read'         => true,
			'upload_files' => true, // Needed to submit a logo, and nothing else.
		);

		$role = get_role( self::ROLE );

		if ( ! $role ) {
			add_role( self::ROLE, __( 'Sponsor', 'guide-lms' ), $caps );
			return;
		}

		foreach ( $caps as $cap => $grant ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}

		// Strip anything not declared above, so a capability added by an older
		// version or by hand cannot linger unnoticed.
		foreach ( array_keys( (array) $role->capabilities ) as $cap ) {
			if ( ! isset( $caps[ $cap ] ) ) {
				$role->remove_cap( $cap );
			}
		}
	}

	public static function remove_role() {
		remove_role( self::ROLE );
	}

	/**
	 * A sponsor needs `upload_files` to submit a logo, and that alone would let
	 * them put any allowed file type into the shared media library. Narrow it
	 * to images — the only thing the flow actually needs.
	 *
	 * @param array<string,string> $mimes
	 * @return array<string,string>
	 */
	public static function restrict_sponsor_uploads( $mimes ) {
		if ( ! self::is_sponsor() ) {
			return $mimes;
		}

		// SVG is deliberately absent. An SVG is a document that can carry
		// <script>, and it is served from our own origin — WordPress core
		// blocks it by default for exactly that reason, and a sponsor is an
		// outside party. PNG or WebP is enough for a logo.
		return array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'webp'         => 'image/webp',
		);
	}

	/**
	 * And they should only ever see their own uploads, not the whole library.
	 *
	 * @param array<string,mixed> $args
	 * @return array<string,mixed>
	 */
	public static function own_media_only( $args ) {
		if ( self::is_sponsor() ) {
			$args['author'] = get_current_user_id();
		}

		return $args;
	}

	public static function is_sponsor( int $user_id = 0 ): bool {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		return $user && in_array( self::ROLE, (array) $user->roles, true );
	}

	// -------------------------------------------------------------------------
	// Lifecycle
	// -------------------------------------------------------------------------

	public static function status( int $id ): string {
		$status = (string) get_post_meta( $id, 'jsl_sponsor_status', true );
		return isset( self::STATUSES[ $status ] ) ? $status : 'submitted';
	}

	public static function is_locked( int $id ): bool {
		return (bool) get_post_meta( $id, 'jsl_sponsor_locked', true );
	}

	/**
	 * Approve a campaign. This is the point of no return for the creative.
	 */
	public static function approve( int $id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		update_post_meta( $id, 'jsl_sponsor_status', 'approved' );
		update_post_meta( $id, 'jsl_sponsor_locked', 1 );

		do_action( 'guide_sponsorship_approved', $id );

		return true;
	}

	public static function reject( int $id, string $reason = '' ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		update_post_meta( $id, 'jsl_sponsor_status', 'rejected' );

		if ( $reason ) {
			update_post_meta( $id, 'jsl_sponsor_reason', sanitize_textarea_field( $reason ) );
		}

		do_action( 'guide_sponsorship_rejected', $id, $reason );

		return true;
	}

	/**
	 * Payment confirmed — start the clock.
	 *
	 * The window starts now, not at submission, because a sponsor should not
	 * lose days to however long review took.
	 */
	public static function activate( int $id, string $payment_ref = '' ): bool {
		$months = max( 1, (int) get_post_meta( $id, 'jsl_sponsor_months', true ) );

		$starts = current_time( 'mysql', true );
		$ends   = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $months . ' months', strtotime( $starts ) ) );

		update_post_meta( $id, 'jsl_sponsor_status', 'live' );
		update_post_meta( $id, 'jsl_sponsor_starts', $starts );
		update_post_meta( $id, 'jsl_sponsor_ends', $ends );

		if ( $payment_ref ) {
			update_post_meta( $id, 'jsl_sponsor_payment', sanitize_text_field( $payment_ref ) );
		}

		do_action( 'guide_sponsorship_live', $id );

		return true;
	}

	public static function schedule_sweep() {
		if ( ! wp_next_scheduled( 'guide_sponsorship_sweep' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', 'guide_sponsorship_sweep' );
		}
	}

	/** Move campaigns past their end date out of rotation. */
	public static function expire_finished() {
		$now = current_time( 'mysql', true );

		$live = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'posts_per_page' => 200,
				'post_status'    => 'any',
				'meta_query'     => array(
					array(
						'key'   => 'jsl_sponsor_status',
						'value' => 'live',
					),
					array(
						'key'     => 'jsl_sponsor_ends',
						'value'   => $now,
						'compare' => '<',
						'type'    => 'DATETIME',
					),
				),
				'fields'         => 'ids',
			)
		);

		foreach ( $live as $id ) {
			update_post_meta( $id, 'jsl_sponsor_status', 'ended' );
			do_action( 'guide_sponsorship_ended', $id );
		}
	}

	// -------------------------------------------------------------------------
	// Serving
	// -------------------------------------------------------------------------

	/**
	 * The campaign to show in a slot right now.
	 *
	 * Where several are live for one slot, the least-served wins — so a
	 * sponsor who joined later still gets delivery rather than being starved by
	 * whoever happens to sort first.
	 *
	 * @return \WP_Post|null
	 */
	public static function for_slot( string $slot ) {
		if ( ! isset( self::SLOTS[ $slot ] ) ) {
			return null;
		}

		$now = current_time( 'mysql', true );

		$candidates = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'posts_per_page' => 20,
				'post_status'    => 'any',
				'meta_query'     => array(
					array( 'key' => 'jsl_sponsor_status', 'value' => 'live' ),
					array( 'key' => 'jsl_sponsor_slot', 'value' => $slot ),
					array( 'key' => 'jsl_sponsor_ends', 'value' => $now, 'compare' => '>=', 'type' => 'DATETIME' ),
				),
			)
		);

		// Demonstration campaigns are visible to administrators only.
		//
		// They ship with the plugin so the review and payment flow can be
		// walked with realistic data, and so an operator can see what a filled
		// slot looks like. But a live campaign is a public statement that a
		// named company is paying to be here, and that statement must never be
		// fabricated in front of a learner — not by accident, and not because
		// somebody clicked Approve to have a look.
		$public_only = ! current_user_can( 'manage_options' );

		if ( $public_only ) {
			$candidates = array_values(
				array_filter(
					$candidates,
					static function ( $campaign ) {
						return ! get_post_meta( $campaign->ID, 'jsl_sponsor_demo', true );
					}
				)
			);
		}

		if ( ! $candidates ) {
			return null;
		}

		if ( 1 === count( $candidates ) ) {
			return $candidates[0];
		}

		$least = null;
		$fewest = PHP_INT_MAX;

		foreach ( $candidates as $candidate ) {
			$served = Sponsor_Stats::totals( (int) $candidate->ID )['impressions'];

			if ( $served < $fewest ) {
				$fewest = $served;
				$least  = $candidate;
			}
		}

		return $least;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function creative( int $id ): array {
		return array(
			'id'       => $id,
			'company'  => (string) get_post_meta( $id, 'jsl_sponsor_company', true ),
			'headline' => (string) get_post_meta( $id, 'jsl_sponsor_headline', true ),
			'body'     => (string) get_post_meta( $id, 'jsl_sponsor_body', true ),
			'url'      => (string) get_post_meta( $id, 'jsl_sponsor_url', true ),
			'logo'     => (int) get_post_meta( $id, 'jsl_sponsor_logo', true ),
			'slot'     => (string) get_post_meta( $id, 'jsl_sponsor_slot', true ),
		);
	}

	// -------------------------------------------------------------------------
	// Admin
	// -------------------------------------------------------------------------

	public static function register_menu() {
		$pending = count(
			get_posts(
				array(
					'post_type'      => self::POST_TYPE,
					'posts_per_page' => 50,
					'post_status'    => 'any',
					'fields'         => 'ids',
					'meta_query'     => array(
						array( 'key' => 'jsl_sponsor_status', 'value' => 'submitted' ),
					),
				)
			)
		);

		$label = __( 'Sponsorships', 'guide-lms' );

		if ( $pending ) {
			$label .= ' <span class="update-plugins count-' . (int) $pending . '"><span class="update-count">'
				. number_format_i18n( $pending ) . '</span></span>';
		}

		add_submenu_page(
			'guide-lms',
			__( 'Sponsorships', 'guide-lms' ),
			$label,
			'manage_options',
			'edit.php?post_type=' . self::POST_TYPE
		);
	}

	public static function meta_boxes() {
		add_meta_box( 'guide-sponsor-review', __( 'Campaign', 'guide-lms' ), array( __CLASS__, 'box_review' ), self::POST_TYPE, 'normal', 'high' );
	}

	public static function box_review( \WP_Post $post ) {
		wp_nonce_field( 'guide_sponsor_meta', 'guide_sponsor_nonce' );

		$creative = self::creative( $post->ID );
		$status   = self::status( $post->ID );
		$stats    = Sponsor_Stats::totals( $post->ID );
		$months   = (int) get_post_meta( $post->ID, 'jsl_sponsor_months', true );
		$starts   = (string) get_post_meta( $post->ID, 'jsl_sponsor_starts', true );
		$ends     = (string) get_post_meta( $post->ID, 'jsl_sponsor_ends', true );
		?>
		<style>
			.guide-sp{display:grid;grid-template-columns:1fr 16rem;gap:20px}
			.guide-sp dt{font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#5a5d71;margin-top:10px}
			.guide-sp dd{margin:2px 0 0}
			.guide-sp__preview{border:1px solid #e2e3ee;border-radius:8px;padding:14px;background:#f6f7fb}
		</style>

		<div class="guide-sp">
			<div>
				<dl>
					<dt><?php esc_html_e( 'Status', 'guide-lms' ); ?></dt>
					<dd><strong><?php echo esc_html( self::STATUSES[ $status ] ); ?></strong></dd>

					<dt><?php esc_html_e( 'Company', 'guide-lms' ); ?></dt>
					<dd><?php echo esc_html( $creative['company'] ); ?></dd>

					<dt><?php esc_html_e( 'Slot', 'guide-lms' ); ?></dt>
					<dd><?php echo esc_html( self::SLOTS[ $creative['slot'] ]['label'] ?? $creative['slot'] ); ?></dd>

					<dt><?php esc_html_e( 'Headline', 'guide-lms' ); ?></dt>
					<dd><?php echo esc_html( $creative['headline'] ); ?></dd>

					<dt><?php esc_html_e( 'Body', 'guide-lms' ); ?></dt>
					<dd><?php echo esc_html( $creative['body'] ); ?></dd>

					<dt><?php esc_html_e( 'Destination', 'guide-lms' ); ?></dt>
					<dd><a href="<?php echo esc_url( $creative['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $creative['url'] ); ?></a></dd>

					<dt><?php esc_html_e( 'Months requested', 'guide-lms' ); ?></dt>
					<dd><?php echo esc_html( (string) $months ); ?></dd>

					<?php if ( $starts ) : ?>
						<dt><?php esc_html_e( 'Runs', 'guide-lms' ); ?></dt>
						<dd>
							<?php
							echo esc_html(
								date_i18n( get_option( 'date_format' ), strtotime( $starts ) )
								. ' — '
								. date_i18n( get_option( 'date_format' ), strtotime( $ends ) )
							);
							?>
						</dd>
					<?php endif; ?>

					<dt><?php esc_html_e( 'Delivery', 'guide-lms' ); ?></dt>
					<dd>
						<?php
						printf(
							/* translators: 1: impressions, 2: clicks, 3: click-through rate. */
							esc_html__( '%1$s impressions, %2$s clicks (%3$s)', 'guide-lms' ),
							esc_html( number_format_i18n( $stats['impressions'] ) ),
							esc_html( number_format_i18n( $stats['clicks'] ) ),
							esc_html( $stats['ctr'] . '%' )
						);
						?>
					</dd>
				</dl>

				<hr>

				<?php if ( 'submitted' === $status ) : ?>
					<p class="description">
						<?php esc_html_e( 'Approving locks the creative — the sponsor cannot change it afterwards. Check the destination URL before you do.', 'guide-lms' ); ?>
					</p>
					<p>
						<button class="button button-primary" name="guide_sponsor_action" value="approve" type="submit"><?php esc_html_e( 'Approve', 'guide-lms' ); ?></button>
						<button class="button" name="guide_sponsor_action" value="reject" type="submit"><?php esc_html_e( 'Reject', 'guide-lms' ); ?></button>
					</p>
				<?php elseif ( 'approved' === $status ) : ?>
					<p class="description"><?php esc_html_e( 'Locked and awaiting payment. It goes live automatically once payment clears.', 'guide-lms' ); ?></p>
					<p>
						<button class="button" name="guide_sponsor_action" value="activate" type="submit"><?php esc_html_e( 'Mark paid and start now', 'guide-lms' ); ?></button>
					</p>
				<?php elseif ( 'live' === $status ) : ?>
					<p>
						<button class="button" name="guide_sponsor_action" value="end" type="submit"><?php esc_html_e( 'End this campaign', 'guide-lms' ); ?></button>
					</p>
				<?php endif; ?>
			</div>

			<div class="guide-sp__preview">
				<p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#5a5d71">
					<?php esc_html_e( 'Preview', 'guide-lms' ); ?>
				</p>
				<?php if ( $creative['logo'] ) : ?>
					<?php echo wp_get_attachment_image( $creative['logo'], 'medium', false, array( 'style' => 'max-width:100%;height:auto;margin:8px 0' ) ); ?>
				<?php endif; ?>
				<p style="font-weight:700"><?php echo esc_html( $creative['headline'] ); ?></p>
				<p style="font-size:13px;color:#5a5d71"><?php echo esc_html( $creative['body'] ); ?></p>
			</div>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['guide_sponsor_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['guide_sponsor_nonce'] ) ), 'guide_sponsor_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_POST['guide_sponsor_action'] ) ? sanitize_key( wp_unslash( $_POST['guide_sponsor_action'] ) ) : '';

		switch ( $action ) {
			case 'approve':
				self::approve( $post_id );
				break;
			case 'reject':
				self::reject( $post_id );
				break;
			case 'activate':
				self::activate( $post_id );
				break;
			case 'end':
				update_post_meta( $post_id, 'jsl_sponsor_status', 'ended' );
				break;
		}
	}
}
