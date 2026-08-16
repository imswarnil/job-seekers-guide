<?php
/**
 * A single receipt, rendered at /account/receipt/{id}/.
 *
 * Deliberately printable and deliberately honest about its status: this is the
 * site's record of a payment, not the legal invoice. The payment provider
 * issues that, and the reference on this page is how to find it.
 */

defined( 'ABSPATH' ) || exit;

$guide_user_id = get_current_user_id();
$guide_payment = \Guide\Billing\Billing::get_for_user( (int) get_query_var( 'guide_payment' ), $guide_user_id );

get_header();
?>

<div class="guide-shell guide-shell--narrow guide-section guide-section--tight">

	<nav class="guide-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'guide-wp-theme' ); ?>">
		<a href="<?php echo esc_url( \Guide\Account\Account::url() ); ?>"><?php esc_html_e( 'Account', 'guide-wp-theme' ); ?></a>
	</nav>

	<?php if ( ! $guide_payment ) : ?>
		<div class="guide-empty mt-4">
			<p class="guide-empty__title"><?php esc_html_e( 'Receipt not found', 'guide-wp-theme' ); ?></p>
			<p class="guide-empty__text"><?php esc_html_e( 'It may belong to another account, or it may not exist.', 'guide-wp-theme' ); ?></p>
			<a class="button is-primary" href="<?php echo esc_url( \Guide\Account\Account::url() ); ?>"><?php esc_html_e( 'Back to account', 'guide-wp-theme' ); ?></a>
		</div>
	<?php else : ?>
		<?php
		$guide_amount = \Guide\Billing\Billing::format_amount( $guide_payment['amount_minor'], $guide_payment['currency'] );
		$guide_user   = wp_get_current_user();
		?>

		<article class="guide-receipt">
			<header class="guide-receipt__head">
				<div>
					<span class="guide-brand__mark"><?php echo guide_logo_mark(); ?></span>
					<p class="guide-receipt__site mt-2"><?php bloginfo( 'name' ); ?></p>
				</div>
				<div style="text-align:right">
					<p class="guide-eyebrow"><?php esc_html_e( 'Receipt', 'guide-wp-theme' ); ?></p>
					<p class="guide-receipt__number">#<?php echo esc_html( str_pad( (string) $guide_payment['id'], 6, '0', STR_PAD_LEFT ) ); ?></p>
				</div>
			</header>

			<dl class="guide-receipt__meta">
				<div>
					<dt><?php esc_html_e( 'Date', 'guide-wp-theme' ); ?></dt>
					<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_payment['created_at'] ) ) ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Billed to', 'guide-wp-theme' ); ?></dt>
					<dd><?php echo esc_html( $guide_user->display_name ); ?><br><?php echo esc_html( $guide_user->user_email ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Status', 'guide-wp-theme' ); ?></dt>
					<dd><?php echo esc_html( ucfirst( $guide_payment['status'] ) ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Reference', 'guide-wp-theme' ); ?></dt>
					<dd><code><?php echo esc_html( $guide_payment['external_id'] ); ?></code></dd>
				</div>
			</dl>

			<table class="guide-billing-table guide-receipt__lines">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Description', 'guide-wp-theme' ); ?></th>
						<th scope="col" style="text-align:right"><?php esc_html_e( 'Amount', 'guide-wp-theme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php echo esc_html( $guide_payment['description'] ? $guide_payment['description'] : \Guide\Billing\Billing::kind_label( $guide_payment['kind'] ) ); ?>
							<?php if ( $guide_payment['period_end'] ) : ?>
								<span class="guide-billing-kind">
									<?php
									printf(
										/* translators: %s: end of the billing period. */
										esc_html__( 'Covers you until %s', 'guide-wp-theme' ),
										esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_payment['period_end'] ) ) )
									);
									?>
								</span>
							<?php endif; ?>
						</td>
						<td style="text-align:right"><?php echo $guide_amount ? esc_html( $guide_amount ) : '—'; ?></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th scope="row"><?php esc_html_e( 'Total', 'guide-wp-theme' ); ?></th>
						<td style="text-align:right"><strong><?php echo $guide_amount ? esc_html( $guide_amount ) : '—'; ?></strong></td>
					</tr>
				</tfoot>
			</table>

			<?php if ( ! $guide_amount ) : ?>
				<p class="guide-notice guide-notice--info guide-receipt__note">
					<span><?php esc_html_e( 'The payment provider did not send an amount with this event, so we cannot show one here. Your provider’s invoice has it — quote the reference above.', 'guide-wp-theme' ); ?></span>
				</p>
			<?php endif; ?>

			<p class="guide-receipt__foot">
				<?php esc_html_e( 'This is our record of the payment. Your card statement and the payment provider’s invoice are the formal documents; quote the reference above if you need one.', 'guide-wp-theme' ); ?>
			</p>
		</article>

		<div class="guide-receipt__actions">
			<button type="button" class="button" onclick="window.print()"><?php esc_html_e( 'Print', 'guide-wp-theme' ); ?></button>
			<a class="button" href="<?php echo esc_url( \Guide\Account\Account::url() ); ?>"><?php esc_html_e( 'Back to account', 'guide-wp-theme' ); ?></a>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
