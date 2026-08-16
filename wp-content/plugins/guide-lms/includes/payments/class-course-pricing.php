<?php
/**
 * Course-level pricing fields: free/paid + Dodo Product ID, stored as
 * postmeta so they're just normal, portable WP data.
 */

namespace Guide\Payments;

defined( 'ABSPATH' ) || exit;

class Course_Pricing {

	const META_TYPE       = 'jsl_pricing_type'; // 'free' | 'paid'
	const META_PRODUCT_ID = 'jsl_dodo_product_id';
	const META_PRICE_LABEL = 'jsl_price_label'; // display-only, e.g. "$49" — the real price lives in Dodo

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'save_post_course', array( __CLASS__, 'save' ) );
	}

	/**
	 * Pricing is edited in the LMS console, so it has to be reachable over
	 * REST — the metabox below is now only a fallback for anyone who reaches
	 * a course through the classic editor.
	 */
	public static function register_meta() {
		$can_edit = function () {
			return current_user_can( 'edit_posts' );
		};

		register_post_meta(
			'course',
			self::META_TYPE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => 'free',
				'sanitize_callback' => function ( $value ) {
					return 'paid' === $value ? 'paid' : 'free';
				},
				'auth_callback'     => $can_edit,
			)
		);

		foreach ( array( self::META_PRODUCT_ID, self::META_PRICE_LABEL ) as $key ) {
			register_post_meta(
				'course',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => $can_edit,
				)
			);
		}
	}

	public static function register_meta_box() {
		add_meta_box( 'jsl_pricing', __( 'Pricing (Dodo Payments)', 'guide-lms' ), array( __CLASS__, 'render' ), 'course', 'side' );
	}

	public static function render( $post ) {
		wp_nonce_field( 'jsl_pricing_save', 'jsl_pricing_nonce' );

		$type       = get_post_meta( $post->ID, self::META_TYPE, true ) ?: 'free';
		$product_id = get_post_meta( $post->ID, self::META_PRODUCT_ID, true );
		$label      = get_post_meta( $post->ID, self::META_PRICE_LABEL, true );
		?>
		<p>
			<label>
				<input type="radio" name="jsl_pricing_type" value="free" <?php checked( $type, 'free' ); ?>>
				<?php esc_html_e( 'Free', 'guide-lms' ); ?>
			</label><br>
			<label>
				<input type="radio" name="jsl_pricing_type" value="paid" <?php checked( $type, 'paid' ); ?>>
				<?php esc_html_e( 'Paid', 'guide-lms' ); ?>
			</label>
		</p>
		<p>
			<label for="jsl_dodo_product_id"><?php esc_html_e( 'Dodo Product ID', 'guide-lms' ); ?></label>
			<input type="text" class="widefat" id="jsl_dodo_product_id" name="jsl_dodo_product_id" value="<?php echo esc_attr( $product_id ); ?>">
		</p>
		<p>
			<label for="jsl_price_label"><?php esc_html_e( 'Price label (display only)', 'guide-lms' ); ?></label>
			<input type="text" class="widefat" id="jsl_price_label" name="jsl_price_label" value="<?php echo esc_attr( $label ); ?>" placeholder="$49">
		</p>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['jsl_pricing_nonce'] ) || ! wp_verify_nonce( $_POST['jsl_pricing_nonce'], 'jsl_pricing_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$type = isset( $_POST['jsl_pricing_type'] ) && 'paid' === $_POST['jsl_pricing_type'] ? 'paid' : 'free';
		update_post_meta( $post_id, self::META_TYPE, $type );

		if ( isset( $_POST['jsl_dodo_product_id'] ) ) {
			update_post_meta( $post_id, self::META_PRODUCT_ID, sanitize_text_field( $_POST['jsl_dodo_product_id'] ) );
		}
		if ( isset( $_POST['jsl_price_label'] ) ) {
			update_post_meta( $post_id, self::META_PRICE_LABEL, sanitize_text_field( $_POST['jsl_price_label'] ) );
		}
	}

	public static function is_paid( $course_id ) {
		return 'paid' === get_post_meta( $course_id, self::META_TYPE, true );
	}

	public static function product_id( $course_id ) {
		return get_post_meta( $course_id, self::META_PRODUCT_ID, true );
	}

	public static function price_label( $course_id ) {
		return get_post_meta( $course_id, self::META_PRICE_LABEL, true );
	}
}
