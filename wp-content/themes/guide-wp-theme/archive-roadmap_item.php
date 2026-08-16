<?php
/**
 * Roadmap — what is planned, what is being built, what shipped.
 *
 * Learners can upvote and suggest. Voting is one per person (enforced by a
 * UNIQUE key, not by the button being hidden), and a suggestion arrives as a
 * pending draft — the roadmap is a public statement of intent, so nothing
 * lands on it unreviewed.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_columns = array( 'suggested', 'planned', 'in_progress', 'shipped' );
$guide_user_id = get_current_user_id();
?>

<div class="guide-shell">
	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Roadmap', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'What is coming next', 'guide-wp-theme' ); ?></h1>
		<p class="guide-page-head__lede">
			<?php esc_html_e( 'Vote for what would help you most, or suggest something missing. Votes genuinely change the order things get built — this is a small project, so the loudest gap usually wins.', 'guide-wp-theme' ); ?>
		</p>
		<div class="guide-hero__actions">
			<?php if ( is_user_logged_in() ) : ?>
				<button type="button" class="button is-primary" data-suggest-open><?php esc_html_e( 'Suggest something', 'guide-wp-theme' ); ?></button>
			<?php else : ?>
				<a class="button is-primary" href="<?php echo esc_url( wp_login_url( get_post_type_archive_link( \Guide\Community\Community_Types::ROADMAP ) ) ); ?>">
					<?php esc_html_e( 'Sign in to vote', 'guide-wp-theme' ); ?>
				</a>
			<?php endif; ?>
			<a class="button" href="<?php echo esc_url( get_post_type_archive_link( \Guide\Community\Community_Types::CHANGELOG ) ); ?>">
				<?php esc_html_e( 'What already shipped', 'guide-wp-theme' ); ?>
			</a>
		</div>
	</header>

	<?php if ( is_user_logged_in() ) : ?>
		<form class="guide-suggest" id="guide-suggest-form" hidden>
			<h2 class="guide-card__title"><?php esc_html_e( 'Suggest a feature', 'guide-wp-theme' ); ?></h2>
			<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
				<?php esc_html_e( 'The most useful suggestions describe where you got stuck, not the feature you imagine fixing it.', 'guide-wp-theme' ); ?>
			</p>

			<div class="field mt-3">
				<label class="label" for="guide-suggest-title"><?php esc_html_e( 'What should we build?', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<input class="input" type="text" id="guide-suggest-title" maxlength="140" required
						placeholder="<?php esc_attr_e( 'e.g. Aptitude practice for service-company written rounds', 'guide-wp-theme' ); ?>">
				</div>
			</div>

			<div class="field">
				<label class="label" for="guide-suggest-body"><?php esc_html_e( 'Why? (optional)', 'guide-wp-theme' ); ?></label>
				<div class="control">
					<textarea class="textarea" id="guide-suggest-body" rows="4" maxlength="2000"></textarea>
				</div>
			</div>

			<div class="is-flex is-align-items-center mt-3" style="gap:1rem;flex-wrap:wrap">
				<button class="button is-primary" type="submit"><?php esc_html_e( 'Send it', 'guide-wp-theme' ); ?></button>
				<button class="button" type="button" data-suggest-cancel><?php esc_html_e( 'Cancel', 'guide-wp-theme' ); ?></button>
				<p class="is-size-7" style="color:var(--bulma-text-weak)" id="guide-suggest-status" aria-live="polite"></p>
			</div>
		</form>
	<?php endif; ?>

	<div class="guide-section guide-section--tight">
		<div class="guide-roadmap">
			<?php foreach ( $guide_columns as $guide_status ) : ?>
				<?php
				$guide_items = get_posts(
					array(
						'post_type'      => \Guide\Community\Community_Types::ROADMAP,
						'posts_per_page' => 50,
						'meta_key'       => 'jsl_roadmap_votes',
						'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
						'meta_query'     => array(
							array(
								'key'   => 'jsl_roadmap_status',
								'value' => $guide_status,
							),
						),
					)
				);
				?>
				<section class="guide-roadmap__col" aria-labelledby="guide-rm-<?php echo esc_attr( $guide_status ); ?>">
					<h2 class="guide-roadmap__head" id="guide-rm-<?php echo esc_attr( $guide_status ); ?>">
						<?php echo esc_html( \Guide\Community\Community_Types::status_label( $guide_status ) ); ?>
						<span class="guide-roadmap__count"><?php echo esc_html( (string) count( $guide_items ) ); ?></span>
					</h2>

					<?php if ( ! $guide_items ) : ?>
						<p class="guide-help" style="padding:.75rem 0"><?php esc_html_e( 'Nothing here yet.', 'guide-wp-theme' ); ?></p>
					<?php endif; ?>

					<?php foreach ( $guide_items as $guide_item ) : ?>
						<?php
						$guide_votes  = (int) get_post_meta( $guide_item->ID, 'jsl_roadmap_votes', true );
						$guide_pinned = (bool) get_post_meta( $guide_item->ID, 'jsl_roadmap_pinned', true );
						$guide_mine   = $guide_user_id
							&& class_exists( 'Guide\\Community\\Feedback' )
							&& \Guide\Community\Feedback::for_user( $guide_user_id, \Guide\Community\Community_Types::ROADMAP, (int) $guide_item->ID );
						?>
						<article class="guide-rm-card">
							<button type="button"
								class="guide-vote<?php echo $guide_mine ? ' is-voted' : ''; ?>"
								data-vote="<?php echo esc_attr( (string) $guide_item->ID ); ?>"
								<?php echo is_user_logged_in() ? '' : 'disabled'; ?>
								aria-pressed="<?php echo $guide_mine ? 'true' : 'false'; ?>"
								aria-label="<?php esc_attr_e( 'Upvote', 'guide-wp-theme' ); ?>">
								<span class="guide-vote__caret">▲</span>
								<span class="guide-vote__count"><?php echo esc_html( (string) $guide_votes ); ?></span>
							</button>

							<div style="min-width:0;flex:1">
								<h3 class="guide-rm-card__title"><?php echo esc_html( get_the_title( $guide_item ) ); ?></h3>
								<?php if ( trim( $guide_item->post_content ) ) : ?>
									<p class="guide-rm-card__body"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $guide_item->post_content ), 28 ) ); ?></p>
								<?php endif; ?>
								<?php if ( $guide_pinned ) : ?>
									<span class="guide-chip guide-chip--spark mt-2"><?php esc_html_e( 'Prioritised', 'guide-wp-theme' ); ?></span>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</div>

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

get_footer();
