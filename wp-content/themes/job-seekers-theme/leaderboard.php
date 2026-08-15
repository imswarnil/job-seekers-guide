<?php
/**
 * Leaderboard.
 *
 * Rendered by JSL\Leaderboard\Leaderboard::route() at /leaderboard/, which
 * has already checked the feature is enabled. The plugin owns the data and
 * the privacy rules; this file only decides how it looks.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$rows     = \JSL\Leaderboard\Leaderboard::rows( 50 );
$me       = get_current_user_id();
$my_rank  = $me ? \JSL\Leaderboard\Leaderboard::rank_for( $me ) : 0;
$opted    = $me ? \JSL\Leaderboard\Leaderboard::has_opted_out( $me ) : false;

/** Podium colours for the top three. */
$medal = array(
	1 => 'bg-[#FFD34D] text-[#3d2f00]',
	2 => 'bg-[#D7DCE5] text-[#2b2f38]',
	3 => 'bg-[#E4A672] text-[#3a2412]',
);
?>

<section class="relative overflow-hidden bg-hero text-on-hero">
	<div class="pointer-events-none absolute inset-0" aria-hidden="true"
		style="background: radial-gradient(50rem 30rem at 50% -20%, color-mix(in srgb, var(--md-primary-40) 60%, transparent), transparent 70%);"></div>

	<div class="jsl-container relative py-14 text-center md:py-18">
		<span class="md-chip !h-8 mx-auto border-white/25 !text-white/85">
			<?php echo jsl_icon( 'medal-fill', 'w-4 h-4' ); ?>
			<?php esc_html_e( 'Leaderboard', 'job-seekers-theme' ); ?>
		</span>

		<h1 class="mx-auto mt-6 max-w-[16ch] text-balance font-display text-[clamp(2.25rem,1.5rem+2.4vw,3.15rem)] font-extrabold leading-[1.08] tracking-[-0.03em]">
			<?php esc_html_e( 'Who’s putting in the work', 'job-seekers-theme' ); ?>
		</h1>

		<p class="mx-auto mt-5 max-w-lg text-lg text-hero-muted">
			<?php esc_html_e( 'Ranked by lessons completed. Everyone here chose to be listed.', 'job-seekers-theme' ); ?>
		</p>

		<?php if ( $my_rank ) : ?>
			<p class="mx-auto mt-7 inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-3 text-sm font-semibold">
				<?php echo jsl_icon( 'trophy-fill', 'w-4 h-4' ); ?>
				<?php printf( esc_html__( 'You’re #%d', 'job-seekers-theme' ), (int) $my_rank ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<div class="jsl-container py-12">
	<div class="mx-auto max-w-3xl">

		<?php if ( empty( $rows ) ) : ?>
			<div class="md-card md-card--filled p-10 text-center">
				<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-surface-highest text-on-surface-variant">
					<?php echo jsl_icon( 'medal', 'w-6 h-6' ); ?>
				</span>
				<p class="mt-4 font-display font-bold"><?php esc_html_e( 'Nobody on the board yet', 'job-seekers-theme' ); ?></p>
				<p class="mt-1 text-sm text-on-surface-variant"><?php esc_html_e( 'Complete a lesson and you’ll be the first.', 'job-seekers-theme' ); ?></p>
			</div>
		<?php else : ?>
			<ol class="md-card md-list !p-0">
				<?php foreach ( $rows as $row ) : ?>
					<?php $is_me = $row['user_id'] === $me; ?>
					<li class="border-b border-outline-variant last:border-b-0">
						<div class="md-list-item md-list-item--two-line <?php echo $is_me ? '!bg-secondary-container !text-on-secondary-container' : ''; ?>">
							<span class="md-list-item__leading grid h-9 w-9 place-items-center rounded-full font-mono text-sm font-bold <?php echo $medal[ $row['rank'] ] ?? 'bg-surface-high text-on-surface-variant'; ?>">
								<?php echo esc_html( $row['rank'] ); ?>
							</span>

							<?php echo jsl_avatar( $row['user_id'], 40 ); ?>

							<span class="md-list-item__content">
								<span class="md-list-item__headline">
									<?php echo esc_html( $row['name'] ); ?>
									<?php if ( $is_me ) : ?>
										<span class="ml-1.5 text-xs font-bold uppercase tracking-wide opacity-70"><?php esc_html_e( 'you', 'job-seekers-theme' ); ?></span>
									<?php endif; ?>
								</span>
								<span class="md-list-item__supporting">
									<?php
									printf(
										esc_html( _n( '%d course', '%d courses', $row['courses'], 'job-seekers-theme' ) ),
										(int) $row['courses']
									);
									if ( $row['minutes'] ) {
										echo ' · ';
										printf( esc_html__( '%d min', 'job-seekers-theme' ), (int) $row['minutes'] );
									}
									?>
								</span>
							</span>

							<span class="md-list-item__trailing text-right">
								<span class="block font-display text-lg font-extrabold text-on-surface"><?php echo esc_html( $row['lessons'] ); ?></span>
								<span class="block text-[0.65rem] uppercase tracking-wide"><?php esc_html_e( 'lessons', 'job-seekers-theme' ); ?></span>
							</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php if ( $me ) : ?>
			<!-- Anyone listed can take themselves off. -->
			<div class="md-card mt-8 flex flex-wrap items-center gap-4 p-6">
				<div class="min-w-0 flex-1">
					<p class="m-0 font-semibold text-on-surface"><?php esc_html_e( 'Show me on the leaderboard', 'job-seekers-theme' ); ?></p>
					<p class="m-0 mt-1 text-sm text-on-surface-variant"><?php esc_html_e( 'Only your display name and totals are shown. Turn this off and you disappear from the board immediately.', 'job-seekers-theme' ); ?></p>
				</div>
				<label class="md-switch">
					<input type="checkbox" id="jsl-lb-optin" <?php checked( ! $opted ); ?>>
					<span class="md-switch__track"></span>
				</label>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
if ( $me ) {
	wp_enqueue_script( 'jsl-leaderboard', JSL_THEME_URI . '/assets/js/leaderboard.js', array( 'jsl-md3' ), jsl_asset_version( '/assets/js/leaderboard.js' ), true );
	wp_localize_script(
		'jsl-leaderboard',
		'jslLeaderboard',
		array(
			'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'i18n'    => array(
				'shown'  => __( 'You’re listed on the leaderboard', 'job-seekers-theme' ),
				'hidden' => __( 'You’ve been removed from the leaderboard', 'job-seekers-theme' ),
			),
		)
	);
}

get_footer();
