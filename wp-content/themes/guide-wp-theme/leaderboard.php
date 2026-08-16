<?php
/**
 * Leaderboard.
 *
 * Rendered by Guide\Leaderboard\Leaderboard::route() at /leaderboard/, which
 * has already checked the feature is enabled. The plugin owns the data and the
 * privacy rules; this file only decides how it looks.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_rows    = \Guide\Leaderboard\Leaderboard::rows( 50 );
$guide_me      = get_current_user_id();
$guide_my_rank = $guide_me ? \Guide\Leaderboard\Leaderboard::rank_for( $guide_me ) : 0;
$guide_opted   = $guide_me ? \Guide\Leaderboard\Leaderboard::has_opted_out( $guide_me ) : false;
?>

<section class="guide-story-hero">
	<div class="guide-shell guide-story-hero__inner" style="text-align:center">
		<span class="guide-chip guide-chip--spark">
			<?php echo guide_icon( 'medal-fill' ); ?>
			<?php esc_html_e( 'Leaderboard', 'guide-wp-theme' ); ?>
		</span>

		<h1 class="guide-display mt-4"><?php esc_html_e( 'Who’s putting in the work', 'guide-wp-theme' ); ?></h1>

		<p class="mt-4" style="max-width:46ch;margin-inline:auto;opacity:.82;font-size:1.075rem">
			<?php esc_html_e( 'Ranked by lessons completed. Everyone here chose to be listed, and can leave any time.', 'guide-wp-theme' ); ?>
		</p>

		<?php if ( $guide_my_rank ) : ?>
			<p class="guide-chip guide-chip--primary mt-5">
				<?php echo guide_icon( 'trophy-fill' ); ?>
				<?php
				printf(
					/* translators: %d: the signed-in learner's rank. */
					esc_html__( 'You’re #%d', 'guide-wp-theme' ),
					(int) $guide_my_rank
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>

<div class="guide-shell guide-section">
	<div style="max-width:48rem;margin-inline:auto">

		<?php if ( empty( $guide_rows ) ) : ?>
			<div class="guide-empty">
				<span class="guide-empty__icon"><?php echo guide_icon( 'medal' ); ?></span>
				<p class="guide-empty__title"><?php esc_html_e( 'Nobody on the board yet', 'guide-wp-theme' ); ?></p>
				<p class="guide-empty__text"><?php esc_html_e( 'Complete a lesson and you’ll be the first.', 'guide-wp-theme' ); ?></p>
			</div>
		<?php else : ?>
			<ol class="guide-board">
				<?php foreach ( $guide_rows as $guide_row ) : ?>
					<?php $guide_is_me = (int) $guide_row['user_id'] === (int) $guide_me; ?>
					<li class="guide-board__row<?php echo $guide_is_me ? ' is-me' : ''; ?>">
						<span class="guide-board__rank guide-board__rank--<?php echo esc_attr( (string) min( 4, (int) $guide_row['rank'] ) ); ?>">
							<?php echo esc_html( (string) $guide_row['rank'] ); ?>
						</span>

						<?php echo guide_avatar( (int) $guide_row['user_id'], 40 ); ?>

						<span style="min-width:0;flex:1">
							<span class="guide-board__name">
								<?php echo esc_html( $guide_row['name'] ); ?>
								<?php if ( $guide_is_me ) : ?>
									<span class="guide-board__you"><?php esc_html_e( 'you', 'guide-wp-theme' ); ?></span>
								<?php endif; ?>
							</span>
							<span class="guide-board__meta">
								<?php
								printf(
									/* translators: %d: number of courses. */
									esc_html( _n( '%d course', '%d courses', (int) $guide_row['courses'], 'guide-wp-theme' ) ),
									(int) $guide_row['courses']
								);
								if ( $guide_row['minutes'] ) {
									echo ' · ';
									printf(
										/* translators: %d: minutes learned. */
										esc_html__( '%d min', 'guide-wp-theme' ),
										(int) $guide_row['minutes']
									);
								}
								?>
							</span>
						</span>

						<span class="guide-board__score">
							<span class="guide-board__lessons"><?php echo esc_html( (string) $guide_row['lessons'] ); ?></span>
							<span class="guide-board__unit"><?php esc_html_e( 'lessons', 'guide-wp-theme' ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php if ( $guide_me ) : ?>
			<?php // Anyone listed can take themselves off, immediately and without asking. ?>
			<div class="guide-card mt-6" style="padding:1.25rem;display:flex;flex-wrap:wrap;align-items:center;gap:1rem">
				<div style="min-width:0;flex:1">
					<p class="has-text-weight-semibold"><?php esc_html_e( 'Show me on the leaderboard', 'guide-wp-theme' ); ?></p>
					<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
						<?php esc_html_e( 'Only your display name and totals are shown. Turn this off and you disappear from the board immediately.', 'guide-wp-theme' ); ?>
					</p>
				</div>
				<label class="guide-switch">
					<input type="checkbox" id="guide-lb-optin" <?php checked( ! $guide_opted ); ?>>
					<span class="guide-switch__track"></span>
					<span class="is-sr-only"><?php esc_html_e( 'Show me on the leaderboard', 'guide-wp-theme' ); ?></span>
				</label>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
if ( $guide_me ) {
	wp_enqueue_script( 'guide-leaderboard', GUIDE_THEME_URI . '/assets/js/leaderboard.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/leaderboard.js' ), true );
	wp_localize_script(
		'guide-leaderboard',
		'guideLeaderboard',
		array(
			'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'i18n'    => array(
				'shown'  => __( 'You’re listed on the leaderboard', 'guide-wp-theme' ),
				'hidden' => __( 'You’ve been removed from the leaderboard', 'guide-wp-theme' ),
			),
		)
	);
}

get_footer();
