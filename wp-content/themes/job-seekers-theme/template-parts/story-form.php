<?php
/**
 * "Share your story" form.
 *
 * Posts to jsl/v1/stories, which always files the story as pending — the
 * copy says so, because a learner who submits and sees nothing appear
 * would reasonably assume it failed.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="mx-auto mt-16 max-w-2xl scroll-mt-24" id="share" aria-labelledby="jsl-share-head">
	<div class="md-card p-8 md:p-10">
		<span class="md-chip md-chip--static md-chip--tertiary !h-7 !text-xs"><?php esc_html_e( 'Your turn', 'job-seekers-theme' ); ?></span>

		<h2 id="jsl-share-head" class="m-0 mt-4 font-display text-2xl font-extrabold tracking-tight">
			<?php esc_html_e( 'Tell everyone how it went', 'job-seekers-theme' ); ?>
		</h2>
		<p class="mt-2 text-on-surface-variant">
			<?php esc_html_e( 'What you applied for, what actually worked, and what you’d tell someone starting out. Stories are reviewed before they go up.', 'job-seekers-theme' ); ?>
		</p>

		<form class="mt-8 flex flex-col gap-5" id="jsl-story-form">
			<label class="md-field">
				<input class="md-field__input" type="text" id="jsl-story-title" name="title" placeholder=" " maxlength="120" required>
				<span class="md-field__label"><?php esc_html_e( 'Headline', 'job-seekers-theme' ); ?></span>
			</label>

			<div class="grid gap-5 sm:grid-cols-2">
				<label class="md-field">
					<input class="md-field__input" type="text" id="jsl-story-role" name="role" placeholder=" " maxlength="80" required>
					<span class="md-field__label"><?php esc_html_e( 'Role you landed', 'job-seekers-theme' ); ?></span>
				</label>

				<label class="md-field">
					<input class="md-field__input" type="text" id="jsl-story-company" name="company" placeholder=" " maxlength="80" required>
					<span class="md-field__label"><?php esc_html_e( 'Company', 'job-seekers-theme' ); ?></span>
				</label>
			</div>

			<div class="grid gap-5 sm:grid-cols-2">
				<label class="md-field">
					<input class="md-field__input" type="text" id="jsl-story-previous" name="previous" placeholder=" " maxlength="80">
					<span class="md-field__label"><?php esc_html_e( 'What you did before (optional)', 'job-seekers-theme' ); ?></span>
				</label>

				<label class="md-field">
					<input class="md-field__input" type="number" min="0" max="260" id="jsl-story-weeks" name="weeks" placeholder=" ">
					<span class="md-field__label"><?php esc_html_e( 'Weeks searching (optional)', 'job-seekers-theme' ); ?></span>
				</label>
			</div>

			<label class="md-field">
				<textarea class="md-field__input" id="jsl-story-body" name="story" rows="8" placeholder=" " required></textarea>
				<span class="md-field__label"><?php esc_html_e( 'Your story', 'job-seekers-theme' ); ?></span>
			</label>

			<label class="md-field">
				<input class="md-field__input" type="url" id="jsl-story-linkedin" name="linkedin" placeholder=" ">
				<span class="md-field__label"><?php esc_html_e( 'LinkedIn (optional)', 'job-seekers-theme' ); ?></span>
			</label>

			<div class="flex flex-wrap items-center gap-4">
				<button class="jsl-btn jsl-btn--primary jsl-btn--lg" type="submit">
					<?php echo jsl_icon( 'paper-plane-tilt', 'w-5 h-5' ); ?>
					<?php esc_html_e( 'Submit for review', 'job-seekers-theme' ); ?>
				</button>
				<p class="m-0 text-sm text-on-surface-variant" id="jsl-story-status" aria-live="polite"></p>
			</div>
		</form>
	</div>
</section>

<?php
wp_enqueue_script( 'jsl-story', JSL_THEME_URI . '/assets/js/story.js', array( 'jsl-md3' ), jsl_asset_version( '/assets/js/story.js' ), true );
wp_localize_script(
	'jsl-story',
	'jslStory',
	array(
		'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'job-seekers-theme' ),
			'thanks'  => __( 'Thank you — your story is in the review queue.', 'job-seekers-theme' ),
			'failed'  => __( 'Could not submit. Please try again.', 'job-seekers-theme' ),
		),
	)
);
