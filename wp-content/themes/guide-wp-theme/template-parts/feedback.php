<?php
/**
 * "Was this useful?" — up/down plus an optional note.
 *
 * Placed at the end of lessons, courses and help articles. Deliberately two
 * buttons and one box: anything more elaborate gets used less, and the useful
 * signal here is volume plus the occasional sentence explaining a thumbs-down.
 *
 * @var array $args { object_type: 'course'|'lesson'|'help_article' }
 */

defined( 'ABSPATH' ) || exit;

$guide_object_type = isset( $args['object_type'] ) ? (string) $args['object_type'] : 'lesson';
$guide_object_id   = isset( $args['object_id'] ) ? (int) $args['object_id'] : get_the_ID();

if ( ! $guide_object_id ) {
	return;
}

$guide_mine = is_user_logged_in() && class_exists( 'Guide\\Community\\Feedback' )
	? \Guide\Community\Feedback::for_user( get_current_user_id(), $guide_object_type, $guide_object_id )
	: null;
?>

<section class="guide-feedback" aria-labelledby="guide-fb-head"
	data-feedback
	data-object-type="<?php echo esc_attr( $guide_object_type ); ?>"
	data-object-id="<?php echo esc_attr( (string) $guide_object_id ); ?>">

	<p class="guide-feedback__head" id="guide-fb-head"><?php esc_html_e( 'Was this useful?', 'guide-wp-theme' ); ?></p>

	<?php if ( ! is_user_logged_in() ) : ?>
		<p class="guide-feedback__note">
			<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?></a>
			<?php esc_html_e( 'to tell us — it is how this gets better.', 'guide-wp-theme' ); ?>
		</p>
	<?php else : ?>
		<div class="guide-feedback__buttons">
			<button type="button" class="guide-react<?php echo ( $guide_mine && 'up' === $guide_mine['sentiment'] ) ? ' is-on' : ''; ?>"
				data-react="up" aria-pressed="<?php echo ( $guide_mine && 'up' === $guide_mine['sentiment'] ) ? 'true' : 'false'; ?>">
				<?php echo guide_icon( 'check-circle' ); ?>
				<span><?php esc_html_e( 'Yes', 'guide-wp-theme' ); ?></span>
			</button>

			<button type="button" class="guide-react<?php echo ( $guide_mine && 'down' === $guide_mine['sentiment'] ) ? ' is-on is-down' : ''; ?>"
				data-react="down" aria-pressed="<?php echo ( $guide_mine && 'down' === $guide_mine['sentiment'] ) ? 'true' : 'false'; ?>">
				<?php echo guide_icon( 'x' ); ?>
				<span><?php esc_html_e( 'Not really', 'guide-wp-theme' ); ?></span>
			</button>

			<span class="guide-feedback__status" data-feedback-status aria-live="polite"></span>
		</div>

		<div class="guide-feedback__note-box" data-feedback-note <?php echo $guide_mine ? '' : 'hidden'; ?>>
			<label class="is-sr-only" for="guide-fb-message"><?php esc_html_e( 'Anything to add?', 'guide-wp-theme' ); ?></label>
			<textarea class="textarea" id="guide-fb-message" rows="3" maxlength="1000"
				placeholder="<?php esc_attr_e( 'Anything to add? What was missing, unclear, or wrong…', 'guide-wp-theme' ); ?>"><?php echo esc_textarea( $guide_mine['message'] ?? '' ); ?></textarea>
			<button type="button" class="button is-small is-primary mt-2" data-feedback-send>
				<?php esc_html_e( 'Send', 'guide-wp-theme' ); ?>
			</button>
		</div>
	<?php endif; ?>
</section>

<?php
wp_enqueue_script( 'guide-community', GUIDE_THEME_URI . '/assets/js/community.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/community.js' ), true );
wp_localize_script(
	'guide-community',
	'guideCommunity',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'guide-wp-theme' ),
			'thanks'  => __( 'Thank you — it is in the review queue.', 'guide-wp-theme' ),
			'failed'  => __( 'Could not save — try again.', 'guide-wp-theme' ),
			'noted'   => __( 'Noted — thank you.', 'guide-wp-theme' ),
		),
	)
);
