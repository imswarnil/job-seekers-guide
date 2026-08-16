<?php
/**
 * "Share your story" form.
 *
 * Posts to guide/v1/stories, which always files the story as pending — the
 * copy says so, because a learner who submits and sees nothing appear would
 * reasonably assume it failed.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="guide-share" id="share" aria-labelledby="guide-share-head">
	<div class="guide-card" style="padding:2rem">
		<span class="guide-chip guide-chip--spark"><?php esc_html_e( 'Your turn', 'guide-wp-theme' ); ?></span>

		<h2 id="guide-share-head" class="title is-4 mt-4">
			<?php esc_html_e( 'Tell everyone how it went', 'guide-wp-theme' ); ?>
		</h2>
		<p class="mt-2" style="color:var(--bulma-text-weak)">
			<?php esc_html_e( 'What you applied for, what actually worked, and what you’d tell someone starting out. Include the rejections — those are the part people need to read. Stories are reviewed before they go up.', 'guide-wp-theme' ); ?>
		</p>

		<form class="mt-5" id="guide-story-form">
			<div class="field">
				<label class="label" for="guide-story-title"><?php esc_html_e( 'Headline', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<input class="input" type="text" id="guide-story-title" name="title" maxlength="120" required
						placeholder="<?php esc_attr_e( 'e.g. 33 rejections, then Accenture in three months', 'guide-wp-theme' ); ?>">
				</div>
			</div>

			<div class="guide-field-row">
				<div class="field">
					<label class="label" for="guide-story-role"><?php esc_html_e( 'Role you landed', 'guide-wp-theme' ); ?></label>
					<div class="control">
						<input class="input" type="text" id="guide-story-role" name="role" maxlength="80" required>
					</div>
				</div>

				<div class="field">
					<label class="label" for="guide-story-company"><?php esc_html_e( 'Company', 'guide-wp-theme' ); ?></label>
					<div class="control">
						<input class="input" type="text" id="guide-story-company" name="company" maxlength="80" required>
					</div>
				</div>
			</div>

			<div class="guide-field-row">
				<div class="field">
					<label class="label" for="guide-story-previous"><?php esc_html_e( 'What you did before', 'guide-wp-theme' ); ?></label>
					<div class="control">
						<input class="input" type="text" id="guide-story-previous" name="previous" maxlength="80"
							placeholder="<?php esc_attr_e( 'e.g. Mechanical graduate', 'guide-wp-theme' ); ?>">
					</div>
					<p class="help"><?php esc_html_e( 'Optional — but it is the detail people most need to see.', 'guide-wp-theme' ); ?></p>
				</div>

				<div class="field">
					<label class="label" for="guide-story-weeks"><?php esc_html_e( 'Weeks searching', 'guide-wp-theme' ); ?></label>
					<div class="control">
						<input class="input" type="number" min="0" max="260" id="guide-story-weeks" name="weeks">
					</div>
					<p class="help"><?php esc_html_e( 'Optional. Honesty here helps more than a low number.', 'guide-wp-theme' ); ?></p>
				</div>
			</div>

			<div class="field">
				<label class="label" for="guide-story-salary"><?php esc_html_e( 'Starting package', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<div class="select is-fullwidth">
						<select id="guide-story-salary" name="salary">
							<option value=""><?php esc_html_e( 'Prefer not to say', 'guide-wp-theme' ); ?></option>
							<?php foreach ( \Guide\Success\Success_Stories::SALARY_BANDS as $guide_band_key => $guide_band_label ) : ?>
								<option value="<?php echo esc_attr( $guide_band_key ); ?>"><?php echo esc_html( $guide_band_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<p class="help">
					<?php esc_html_e( 'A range, never an exact figure, and entirely optional. Low numbers are the most useful ones on this wall — a first job at ₹1.8 LPA is how a lot of careers start, including this one.', 'guide-wp-theme' ); ?>
				</p>
			</div>

			<div class="field">
				<label class="label" id="guide-story-body-label" for="guide-story-body"><?php esc_html_e( 'Your story', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<?php
					// The editor is built over this textarea by story.js and the
					// textarea is then hidden. If the script fails to load, or
					// the browser is old, what remains is a working plain-text
					// box that still submits — the form never depends on the
					// enhancement.
					?>
					<div id="guide-story-rte"></div>
					<textarea class="textarea" id="guide-story-body" name="story" rows="10" required
						placeholder="<?php esc_attr_e( 'Where you started, what you tried, what failed, what finally worked…', 'guide-wp-theme' ); ?>"></textarea>
				</div>
			</div>

			<div class="field">
				<label class="label" for="guide-story-linkedin"><?php esc_html_e( 'LinkedIn', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<input class="input" type="url" id="guide-story-linkedin" name="linkedin" placeholder="https://www.linkedin.com/in/…">
				</div>
				<p class="help"><?php esc_html_e( 'Optional. Shown publicly on your story if you add it.', 'guide-wp-theme' ); ?></p>
			</div>

			<div class="is-flex is-align-items-center mt-5" style="gap:1rem;flex-wrap:wrap">
				<button class="button is-primary" type="submit">
					<?php esc_html_e( 'Submit for review', 'guide-wp-theme' ); ?>
				</button>
				<p class="is-size-7" style="color:var(--bulma-text-weak)" id="guide-story-status" aria-live="polite"></p>
			</div>
		</form>
	</div>
</section>

<?php
wp_enqueue_script( 'guide-story', GUIDE_THEME_URI . '/assets/js/story.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/story.js' ), true );
wp_localize_script(
	'guide-story',
	'guideStory',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'guide-wp-theme' ),
			'thanks'  => __( 'Thank you — your story is in the review queue.', 'guide-wp-theme' ),
			'failed'  => __( 'Could not submit. Please try again.', 'guide-wp-theme' ),
		),
	)
);
