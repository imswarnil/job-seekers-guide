<?php
/**
 * Discussion.
 *
 * Signed-in only, plain text, one level of replies. See
 * includes/community/class-discussion.php in the plugin for why.
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() || ! comments_open() ) {
	return;
}

$guide_count = (int) get_comments_number();
?>

<section class="guide-comments" id="comments" aria-labelledby="guide-comments-head">
	<h2 class="guide-comments__title" id="guide-comments-head">
		<?php
		if ( $guide_count ) {
			printf(
				/* translators: %s: number of comments. */
				esc_html( _n( '%s question', '%s questions', $guide_count, 'guide-wp-theme' ) ),
				esc_html( number_format_i18n( $guide_count ) )
			);
		} else {
			esc_html_e( 'Questions', 'guide-wp-theme' );
		}
		?>
	</h2>

	<?php if ( have_comments() ) : ?>
		<ol class="guide-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'max_depth'   => 2,
					'short_ping'  => true,
					'avatar_size' => 0,
					'callback'    => 'guide_render_comment',
					'end-callback' => 'guide_render_comment_end',
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'class'     => 'pagination is-centered',
				'prev_text' => esc_html__( 'Older', 'guide-wp-theme' ),
				'next_text' => esc_html__( 'Newer', 'guide-wp-theme' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( is_user_logged_in() ) : ?>
		<?php
		comment_form(
			array(
				'class_form'           => 'guide-comment-form',
				'title_reply'          => esc_html__( 'Ask a question', 'guide-wp-theme' ),
				'title_reply_to'       => esc_html__( 'Reply to %s', 'guide-wp-theme' ),
				'label_submit'         => esc_html__( 'Post', 'guide-wp-theme' ),
				'class_submit'         => 'button is-primary',
				'comment_notes_before' => '',
				'comment_notes_after'  => '<p class="guide-comment-form__note">'
					. esc_html__( 'Plain text only. Say what you tried and what happened — that is the question people can actually answer. Your first post is reviewed before it appears.', 'guide-wp-theme' )
					. '</p>',
				'comment_field'        => '<div class="field"><div class="control">'
					. '<textarea class="textarea" id="comment" name="comment" rows="4" required'
					. ' placeholder="' . esc_attr__( 'What are you stuck on?', 'guide-wp-theme' ) . '"></textarea>'
					. '</div></div>',
			)
		);
		?>
	<?php else : ?>
		<div class="guide-comment-signin">
			<p>
				<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?></a>
				<?php esc_html_e( 'to ask a question or help somebody else out.', 'guide-wp-theme' ); ?>
			</p>
			<p class="guide-comment-form__note">
				<?php esc_html_e( 'Discussion is signed-in only. It keeps the spam out, which is what keeps the answers worth reading.', 'guide-wp-theme' ); ?>
			</p>
		</div>
	<?php endif; ?>
</section>
