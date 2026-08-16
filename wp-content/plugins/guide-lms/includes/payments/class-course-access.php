<?php
/**
 * A course's access tier: free, or included in the platform subscription.
 *
 * There is no per-course price any more. The platform sells exactly one thing —
 * a subscription — and that grants everything. A course is therefore not
 * "₹499"; it is either open to everyone or part of the subscription.
 *
 * Why the model changed: per-course pricing pushed a learner into a purchasing
 * decision at every step of a path, which is precisely the paralysis this
 * platform exists to remove. One subscription, or free, is a decision someone
 * makes once.
 *
 * Storage note: the meta key is still `jsl_pricing_type`, unchanged from when
 * this held free/paid, so no data migration is needed. The legacy value 'paid'
 * reads as 'premium'.
 */

namespace Guide\Payments;

defined( 'ABSPATH' ) || exit;

class Course_Access {

	/** Open to everyone, signed in or not. */
	const TIER_FREE = 'free';

	/** Included in the platform subscription. */
	const TIER_PREMIUM = 'premium';

	const META_TIER = 'jsl_pricing_type';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'save_post_course', array( __CLASS__, 'save' ) );
	}

	/**
	 * The tier is edited in the LMS console, so it has to be reachable over
	 * REST. The metabox below is a fallback for anyone who reaches a course
	 * through the classic editor.
	 */
	public static function register_meta() {
		register_post_meta(
			'course',
			self::META_TIER,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => self::TIER_FREE,
				'sanitize_callback' => array( __CLASS__, 'sanitize_tier' ),
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * @param mixed $value
	 * @return string One of the TIER_* constants.
	 */
	public static function sanitize_tier( $value ): string {
		$value = is_string( $value ) ? strtolower( trim( $value ) ) : '';

		// 'paid' is the pre-subscription value still sitting in existing rows.
		if ( self::TIER_PREMIUM === $value || 'paid' === $value ) {
			return self::TIER_PREMIUM;
		}

		return self::TIER_FREE;
	}

	public static function tier( int $course_id ): string {
		return self::sanitize_tier( get_post_meta( $course_id, self::META_TIER, true ) );
	}

	/** True when the course is part of the subscription rather than open. */
	public static function is_premium( int $course_id ): bool {
		return self::TIER_PREMIUM === self::tier( $course_id );
	}

	public static function is_free( int $course_id ): bool {
		return ! self::is_premium( $course_id );
	}

	// -------------------------------------------------------------------------
	// Classic-editor fallback metabox
	// -------------------------------------------------------------------------

	public static function register_meta_box() {
		add_meta_box(
			'guide-course-access',
			__( 'Access', 'guide-lms' ),
			array( __CLASS__, 'render_meta_box' ),
			'course',
			'side',
			'default'
		);
	}

	public static function render_meta_box( \WP_Post $post ) {
		wp_nonce_field( 'guide_course_access', 'guide_course_access_nonce' );
		$tier = self::tier( $post->ID );
		?>
		<p>
			<label>
				<input type="radio" name="guide_course_tier" value="<?php echo esc_attr( self::TIER_FREE ); ?>" <?php checked( $tier, self::TIER_FREE ); ?>>
				<?php esc_html_e( 'Free — open to everyone', 'guide-lms' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="radio" name="guide_course_tier" value="<?php echo esc_attr( self::TIER_PREMIUM ); ?>" <?php checked( $tier, self::TIER_PREMIUM ); ?>>
				<?php esc_html_e( 'Premium — included in the subscription', 'guide-lms' ); ?>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( 'There is no per-course price. Subscribers get every premium course.', 'guide-lms' ); ?>
		</p>
		<?php
	}

	public static function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['guide_course_access_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['guide_course_access_nonce'] ) ), 'guide_course_access' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$tier = isset( $_POST['guide_course_tier'] )
			? self::sanitize_tier( sanitize_key( wp_unslash( $_POST['guide_course_tier'] ) ) )
			: self::TIER_FREE;

		update_post_meta( $post_id, self::META_TIER, $tier );
	}
}
