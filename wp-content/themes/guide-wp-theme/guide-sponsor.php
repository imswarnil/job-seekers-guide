<?php
/**
 * Sponsor portal — /sponsor/ and /sponsor/apply/.
 *
 * Sponsors never see wp-admin: they are not staff, and their role has no
 * editing capability at all. This is the whole of their interface.
 */

defined( 'ABSPATH' ) || exit;

use Guide\Sponsors\Sponsorship;
use Guide\Sponsors\Sponsor_Portal;
use Guide\Sponsors\Sponsor_Stats;

$guide_view = get_query_var( Sponsor_Portal::QUERY_VAR );

get_header();
?>

<div class="guide-shell guide-section guide-section--tight">

<?php if ( 'apply' === $guide_view ) : ?>

	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Sponsorship', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'Reach people who are about to get hired', 'guide-wp-theme' ); ?></h1>
		<p class="guide-page-head__lede">
			<?php esc_html_e( 'Everyone here is actively preparing for a job in software — learning, building, and applying. Sponsorships are clearly labelled, reviewed before they run, and never dressed up as content.', 'guide-wp-theme' ); ?>
		</p>
	</header>

	<div class="guide-slot-grid">
		<?php foreach ( Sponsorship::SLOTS as $guide_key => $guide_slot ) : ?>
			<div class="guide-card" style="padding:1.5rem">
				<h2 class="guide-card__title"><?php echo esc_html( $guide_slot['label'] ); ?></h2>
				<p class="guide-card__excerpt mt-2"><?php echo esc_html( $guide_slot['note'] ); ?></p>
				<p class="guide-course-code mt-2"><?php echo esc_html( $guide_slot['ratio'] ); ?></p>
				<?php $guide_price = Sponsor_Portal::price( $guide_key ); ?>
				<?php if ( $guide_price ) : ?>
					<p class="guide-enroll-card__price mt-3"><?php echo esc_html( $guide_price ); ?><span class="guide-per"><?php esc_html_e( '/month', 'guide-wp-theme' ); ?></span></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="guide-notice guide-notice--info mt-5" style="max-width:44rem">
		<span>
			<?php esc_html_e( 'How it works: submit a creative, we review it, and once approved it is locked and you pay for the months you want. It goes live when payment clears, and you can see impressions and clicks for your own campaigns at any time.', 'guide-wp-theme' ); ?>
		</span>
	</div>

	<?php if ( ! Sponsor_Portal::is_open() ) : ?>
		<div class="guide-empty mt-5">
			<p class="guide-empty__title"><?php esc_html_e( 'Applications are closed right now', 'guide-wp-theme' ); ?></p>
			<p class="guide-empty__text"><?php esc_html_e( 'Check back shortly, or get in touch.', 'guide-wp-theme' ); ?></p>
		</div>
	<?php elseif ( ! is_user_logged_in() ) : ?>
		<div class="guide-card mt-5" style="padding:1.5rem;max-width:36rem">
			<h2 class="guide-card__title"><?php esc_html_e( 'Ready to apply?', 'guide-wp-theme' ); ?></h2>
			<p class="guide-card__excerpt mt-2"><?php esc_html_e( 'Create an account or sign in, and the application form is on the next screen.', 'guide-wp-theme' ); ?></p>
			<a class="button is-primary mt-4" href="<?php echo esc_url( wp_login_url( Sponsor_Portal::url( 'apply' ) ) ); ?>">
				<?php esc_html_e( 'Sign in to apply', 'guide-wp-theme' ); ?>
			</a>
		</div>
	<?php else : ?>
		<form class="guide-suggest mt-5" id="guide-sponsor-form" style="max-width:44rem">
			<h2 class="guide-card__title"><?php esc_html_e( 'Apply for a slot', 'guide-wp-theme' ); ?></h2>

			<div class="guide-field-row mt-3">
				<div class="field">
					<label class="label" for="guide-sp-company"><?php esc_html_e( 'Company', 'guide-wp-theme' ); ?></label>
					<div class="control"><input class="input" type="text" id="guide-sp-company" maxlength="80" required></div>
				</div>
				<div class="field">
					<label class="label" for="guide-sp-slot"><?php esc_html_e( 'Slot', 'guide-wp-theme' ); ?></label>
					<div class="control">
						<div class="select is-fullwidth">
							<select id="guide-sp-slot">
								<?php foreach ( Sponsorship::SLOTS as $guide_key => $guide_slot ) : ?>
									<option value="<?php echo esc_attr( $guide_key ); ?>"><?php echo esc_html( $guide_slot['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="field">
				<label class="label" for="guide-sp-headline"><?php esc_html_e( 'Headline', 'guide-wp-theme' ); ?></label>
				<div class="control"><input class="input" type="text" id="guide-sp-headline" maxlength="80" required></div>
				<p class="help"><?php esc_html_e( 'One line. What you want a job seeker to know.', 'guide-wp-theme' ); ?></p>
			</div>

			<div class="field">
				<label class="label" for="guide-sp-body"><?php esc_html_e( 'Supporting line', 'guide-wp-theme' ); ?></label>
				<div class="control"><textarea class="textarea" id="guide-sp-body" rows="2" maxlength="180"></textarea></div>
			</div>

			<div class="guide-field-row">
				<div class="field">
					<label class="label" for="guide-sp-url"><?php esc_html_e( 'Destination URL', 'guide-wp-theme' ); ?></label>
					<div class="control"><input class="input" type="url" id="guide-sp-url" required placeholder="https://"></div>
				</div>
				<div class="field">
					<label class="label" for="guide-sp-months"><?php esc_html_e( 'Months', 'guide-wp-theme' ); ?></label>
					<div class="control"><input class="input" type="number" id="guide-sp-months" min="1" max="12" value="1" required></div>
				</div>
			</div>

			<div class="field">
				<label class="label" for="guide-sp-logo"><?php esc_html_e( 'Logo', 'guide-wp-theme' ); ?></label>
				<div class="control"><input class="input" type="file" id="guide-sp-logo" accept="image/*"></div>
				<p class="help"><?php esc_html_e( 'PNG or WebP with a transparent background works best. Around 400px wide is plenty.', 'guide-wp-theme' ); ?></p>
			</div>

			<div class="is-flex is-align-items-center mt-4" style="gap:1rem;flex-wrap:wrap">
				<button class="button is-primary" type="submit"><?php esc_html_e( 'Submit for review', 'guide-wp-theme' ); ?></button>
				<p class="is-size-7" style="color:var(--bulma-text-weak)" id="guide-sponsor-status" aria-live="polite"></p>
			</div>

			<p class="help mt-3">
				<?php esc_html_e( 'Nothing is charged now. Once we approve it the creative is locked, and you pay for the months you chose.', 'guide-wp-theme' ); ?>
			</p>
		</form>
	<?php endif; ?>

<?php else : ?>

	<?php $guide_campaigns = Sponsor_Portal::campaigns_for( get_current_user_id() ); ?>

	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Sponsorship', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'Your campaigns', 'guide-wp-theme' ); ?></h1>
		<div class="guide-hero__actions">
			<a class="button is-primary" href="<?php echo esc_url( Sponsor_Portal::url( 'apply' ) ); ?>"><?php esc_html_e( 'Apply for a slot', 'guide-wp-theme' ); ?></a>
		</div>
	</header>

	<?php if ( ! $guide_campaigns ) : ?>
		<div class="guide-empty">
			<p class="guide-empty__title"><?php esc_html_e( 'No campaigns yet', 'guide-wp-theme' ); ?></p>
			<p class="guide-empty__text"><?php esc_html_e( 'Apply for a slot and it will appear here.', 'guide-wp-theme' ); ?></p>
		</div>
	<?php else : ?>
		<div class="mt-4" style="display:flex;flex-direction:column;gap:1rem">
			<?php foreach ( $guide_campaigns as $guide_campaign ) : ?>
				<?php
				$guide_id     = (int) $guide_campaign->ID;
				$guide_status = Sponsorship::status( $guide_id );
				$guide_stats  = Sponsor_Stats::totals( $guide_id );
				$guide_slot   = (string) get_post_meta( $guide_id, 'jsl_sponsor_slot', true );
				$guide_ends   = (string) get_post_meta( $guide_id, 'jsl_sponsor_ends', true );
				?>
				<article class="guide-card" style="padding:1.5rem">
					<div class="is-flex is-align-items-center" style="gap:.75rem;flex-wrap:wrap">
						<h2 class="guide-card__title" style="flex:1;min-width:12rem">
							<?php echo esc_html( get_the_title( $guide_campaign ) ); ?>
						</h2>
						<span class="guide-badge-status guide-badge-status--<?php echo esc_attr( $guide_status ); ?>">
							<?php echo esc_html( Sponsorship::STATUSES[ $guide_status ] ); ?>
						</span>
					</div>

					<p class="guide-card__excerpt mt-1">
						<?php echo esc_html( Sponsorship::SLOTS[ $guide_slot ]['label'] ?? $guide_slot ); ?>
						<?php if ( $guide_ends && 'live' === $guide_status ) : ?>
							·
							<?php
							printf(
								/* translators: %s: campaign end date. */
								esc_html__( 'runs until %s', 'guide-wp-theme' ),
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_ends ) ) )
							);
							?>
						<?php endif; ?>
					</p>

					<?php if ( in_array( $guide_status, array( 'live', 'ended' ), true ) ) : ?>
						<div class="guide-stats mt-4">
							<div class="guide-stat">
								<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( $guide_stats['impressions'] ) ); ?></p>
								<p class="guide-stat__label"><?php esc_html_e( 'Times shown', 'guide-wp-theme' ); ?></p>
							</div>
							<div class="guide-stat">
								<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( $guide_stats['clicks'] ) ); ?></p>
								<p class="guide-stat__label"><?php esc_html_e( 'Clicks', 'guide-wp-theme' ); ?></p>
							</div>
							<div class="guide-stat">
								<p class="guide-stat__value"><?php echo esc_html( $guide_stats['ctr'] ); ?>%</p>
								<p class="guide-stat__label"><?php esc_html_e( 'Click rate', 'guide-wp-theme' ); ?></p>
							</div>
						</div>

						<?php $guide_series = Sponsor_Stats::series( $guide_id, 30 ); ?>
						<?php
						$guide_peak = 1;
						foreach ( $guide_series as $guide_point ) {
							$guide_peak = max( $guide_peak, (int) $guide_point['impressions'] );
						}
						?>
						<div class="guide-chart mt-4">
							<?php foreach ( $guide_series as $guide_point ) : ?>
								<span class="guide-chart__bar"
									style="height:<?php echo esc_attr( (string) max( 2, (int) round( $guide_point['impressions'] / $guide_peak * 100 ) ) ); ?>%"
									title="<?php echo esc_attr( $guide_point['date'] . ': ' . $guide_point['impressions'] ); ?>"></span>
							<?php endforeach; ?>
						</div>
						<div class="guide-chart__axis">
							<span><?php esc_html_e( '30 days ago', 'guide-wp-theme' ); ?></span>
							<span><?php esc_html_e( 'today', 'guide-wp-theme' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( 'approved' === $guide_status ) : ?>
						<div class="guide-notice guide-notice--info mt-4">
							<span><?php esc_html_e( 'Approved and locked. Pay for your months and it goes live.', 'guide-wp-theme' ); ?></span>
						</div>
						<button class="button is-primary mt-3" data-sponsor-pay="<?php echo esc_attr( (string) $guide_id ); ?>">
							<?php esc_html_e( 'Pay and go live', 'guide-wp-theme' ); ?>
						</button>
					<?php elseif ( 'submitted' === $guide_status ) : ?>
						<p class="mt-3 is-size-7" style="color:var(--bulma-text-weak)">
							<?php esc_html_e( 'We are reviewing this. You can still change it until it is approved — after that the creative is locked.', 'guide-wp-theme' ); ?>
						</p>
					<?php elseif ( 'rejected' === $guide_status ) : ?>
						<?php $guide_reason = (string) get_post_meta( $guide_id, 'jsl_sponsor_reason', true ); ?>
						<div class="guide-notice guide-notice--warning mt-4">
							<span><?php echo $guide_reason ? esc_html( $guide_reason ) : esc_html__( 'We could not accept this one. Get in touch and we will explain.', 'guide-wp-theme' ); ?></span>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

<?php endif; ?>
</div>

<?php
wp_enqueue_script( 'guide-sponsor', GUIDE_THEME_URI . '/assets/js/sponsor.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/sponsor.js' ), true );
wp_localize_script(
	'guide-sponsor',
	'guideSponsor',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'root'    => esc_url_raw( rest_url() ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'portal'  => esc_url_raw( Sponsor_Portal::url() ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'guide-wp-theme' ),
			'thanks'  => __( 'Submitted — we will review it shortly.', 'guide-wp-theme' ),
			'failed'  => __( 'Could not submit. Please try again.', 'guide-wp-theme' ),
			'opening' => __( 'Opening checkout…', 'guide-wp-theme' ),
		),
	)
);

get_footer();
