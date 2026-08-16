<?php
/**
 * Account area, rendered at /account/ by Guide\Account\Account::route().
 *
 * Not a page template — the plugin routes to it, so the account area exists on
 * a fresh install without anyone having to create a page first.
 */

defined( 'ABSPATH' ) || exit;

$guide_user_id = get_current_user_id();
$guide_data    = \Guide\Account\Account::overview( $guide_user_id );
$guide_user    = $guide_data['user'];

get_header();
?>

<div class="guide-shell guide-section guide-section--tight">

	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Account', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php echo esc_html( $guide_user->display_name ); ?></h1>
		<p class="guide-page-head__lede"><?php echo esc_html( $guide_user->user_email ); ?></p>
	</header>

	<div class="guide-account-grid">

		<div>
			<!-- ---------------------------------------------------------- -->
			<!-- Subscription                                               -->
			<!-- ---------------------------------------------------------- -->
			<section class="guide-card" style="padding:1.5rem" aria-labelledby="guide-sub-head">
				<h2 id="guide-sub-head" class="guide-card__title"><?php esc_html_e( 'Your plan', 'guide-wp-theme' ); ?></h2>

				<?php if ( $guide_data['has_all_access'] && ! $guide_data['subscribed'] ) : ?>
					<?php // Staff: full access without a subscription. Say which it is, so nobody thinks they are being billed. ?>
					<p class="guide-notice guide-notice--info mt-3">
						<span><?php esc_html_e( 'You have full access as a member of staff. No subscription, nothing billed.', 'guide-wp-theme' ); ?></span>
					</p>

				<?php elseif ( $guide_data['subscribed'] ) : ?>
					<div class="guide-plan mt-3">
						<span class="guide-chip guide-chip--primary"><?php esc_html_e( 'Active', 'guide-wp-theme' ); ?></span>
						<p class="guide-plan__name"><?php esc_html_e( 'Full access', 'guide-wp-theme' ); ?></p>
						<?php if ( $guide_data['expires_at'] ) : ?>
							<p class="guide-plan__meta">
								<?php
								printf(
									/* translators: %s: renewal date. */
									esc_html__( 'Renews %s', 'guide-wp-theme' ),
									esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_data['expires_at'] ) ) )
								);
								?>
							</p>
						<?php endif; ?>
					</div>

					<ul class="guide-check-list mt-4">
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Every course on the platform', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'No ads, anywhere', 'guide-wp-theme' ); ?></li>
					</ul>

					<p class="mt-4 is-size-7" style="color:var(--bulma-text-weak);line-height:1.6">
						<?php esc_html_e( 'To change or cancel your subscription, use the billing portal link on any receipt below. Cancelling keeps your access until the end of the period you have already paid for — and every course you finished stays finished.', 'guide-wp-theme' ); ?>
					</p>

				<?php elseif ( $guide_data['sub_enabled'] ) : ?>
					<p class="mt-2" style="color:var(--bulma-text-weak)">
						<?php esc_html_e( 'You are on the free plan. The core path — foundations, one language, projects and the whole job-search module — stays free forever.', 'guide-wp-theme' ); ?>
					</p>

					<?php if ( $guide_data['sub_price'] ) : ?>
						<p class="guide-enroll-card__price mt-3"><?php echo esc_html( $guide_data['sub_price'] ); ?></p>
					<?php endif; ?>

					<ul class="guide-check-list mt-3">
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Every members-only course', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'No ads, anywhere', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Cancel the day you are hired', 'guide-wp-theme' ); ?></li>
					</ul>

					<button type="button" class="button is-primary is-fullwidth mt-4" id="guide-account-subscribe">
						<?php esc_html_e( 'Subscribe', 'guide-wp-theme' ); ?>
					</button>
					<p class="mt-2 is-size-7 has-text-centered" id="guide-account-subscribe-status" aria-live="polite"></p>

				<?php else : ?>
					<p class="mt-2" style="color:var(--bulma-text-weak)">
						<?php esc_html_e( 'You are on the free plan. Subscriptions are not open yet.', 'guide-wp-theme' ); ?>
					</p>
				<?php endif; ?>
			</section>

			<!-- ---------------------------------------------------------- -->
			<!-- Billing history                                            -->
			<!-- ---------------------------------------------------------- -->
			<section class="guide-card mt-5" style="padding:1.5rem" aria-labelledby="guide-billing-head">
				<h2 id="guide-billing-head" class="guide-card__title"><?php esc_html_e( 'Billing history', 'guide-wp-theme' ); ?></h2>

				<?php if ( empty( $guide_data['payments'] ) ) : ?>
					<p class="mt-2 is-size-7" style="color:var(--bulma-text-weak)">
						<?php esc_html_e( 'Nothing here yet. Anything you are charged shows up on this list with a receipt.', 'guide-wp-theme' ); ?>
					</p>
				<?php else : ?>
					<div class="guide-table-scroll mt-3">
						<table class="guide-billing-table">
							<thead>
								<tr>
									<th scope="col"><?php esc_html_e( 'Date', 'guide-wp-theme' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Description', 'guide-wp-theme' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Amount', 'guide-wp-theme' ); ?></th>
									<th scope="col"><span class="is-sr-only"><?php esc_html_e( 'Receipt', 'guide-wp-theme' ); ?></span></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $guide_data['payments'] as $guide_payment ) : ?>
									<?php $guide_amount = \Guide\Billing\Billing::format_amount( $guide_payment['amount_minor'], $guide_payment['currency'] ); ?>
									<tr>
										<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_payment['created_at'] ) ) ); ?></td>
										<td>
											<?php echo esc_html( $guide_payment['description'] ? $guide_payment['description'] : \Guide\Billing\Billing::kind_label( $guide_payment['kind'] ) ); ?>
											<span class="guide-billing-kind"><?php echo esc_html( \Guide\Billing\Billing::kind_label( $guide_payment['kind'] ) ); ?></span>
										</td>
										<td><?php echo $guide_amount ? esc_html( $guide_amount ) : '—'; ?></td>
										<td style="text-align:right">
											<a class="button is-small" href="<?php echo esc_url( \Guide\Account\Account::url( 'receipt/' . (int) $guide_payment['id'] ) ); ?>">
												<?php esc_html_e( 'Receipt', 'guide-wp-theme' ); ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</section>
		</div>

		<!-- -------------------------------------------------------------- -->
		<!-- Profile                                                        -->
		<!-- -------------------------------------------------------------- -->
		<aside>
			<section class="guide-card" style="padding:1.5rem" aria-labelledby="guide-profile-head">
				<h2 id="guide-profile-head" class="guide-card__title"><?php esc_html_e( 'Your details', 'guide-wp-theme' ); ?></h2>

				<form id="guide-profile-form" class="mt-3">
					<div class="field">
						<label class="label" for="guide-display-name"><?php esc_html_e( 'Display name', 'guide-wp-theme' ); ?></label>
						<div class="control">
							<input class="input" type="text" id="guide-display-name" name="display_name" maxlength="80" required
								value="<?php echo esc_attr( $guide_user->display_name ); ?>">
						</div>
						<p class="help"><?php esc_html_e( 'Shown on your stories and on the leaderboard.', 'guide-wp-theme' ); ?></p>
					</div>

					<div class="field">
						<label class="label" for="guide-description"><?php esc_html_e( 'About you', 'guide-wp-theme' ); ?></label>
						<div class="control">
							<textarea class="textarea" id="guide-description" name="description" rows="4" maxlength="500"><?php echo esc_textarea( $guide_user->description ); ?></textarea>
						</div>
					</div>

					<div class="field">
						<label class="label"><?php esc_html_e( 'Email', 'guide-wp-theme' ); ?></label>
						<div class="control">
							<input class="input" type="email" value="<?php echo esc_attr( $guide_user->user_email ); ?>" disabled>
						</div>
						<p class="help"><?php esc_html_e( 'Changing your email needs a confirmation step — ask us and we will sort it.', 'guide-wp-theme' ); ?></p>
					</div>

					<div class="is-flex is-align-items-center mt-4" style="gap:1rem;flex-wrap:wrap">
						<button class="button is-primary" type="submit"><?php esc_html_e( 'Save', 'guide-wp-theme' ); ?></button>
						<p class="is-size-7" style="color:var(--bulma-text-weak)" id="guide-profile-status" aria-live="polite"></p>
					</div>
				</form>
			</section>

			<section class="guide-card mt-5" style="padding:1.5rem">
				<h2 class="guide-card__title"><?php esc_html_e( 'Your learning', 'guide-wp-theme' ); ?></h2>
				<div class="guide-footer__list mt-3">
					<a href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>"><?php esc_html_e( 'Dashboard and progress', 'guide-wp-theme' ); ?></a>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'guide-wp-theme' ); ?></a>
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign out', 'guide-wp-theme' ); ?></a>
				</div>
			</section>
		</aside>
	</div>
</div>

<?php
wp_enqueue_script( 'guide-account', GUIDE_THEME_URI . '/assets/js/account.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/account.js' ), true );
wp_localize_script(
	'guide-account',
	'guideAccount',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'saving'  => __( 'Saving…', 'guide-wp-theme' ),
			'saved'   => __( 'Saved.', 'guide-wp-theme' ),
			'failed'  => __( 'Could not save — try again.', 'guide-wp-theme' ),
			'opening' => __( 'Opening checkout…', 'guide-wp-theme' ),
		),
	)
);

get_footer();
