<?php
/**
 * One course/lesson/path card. Used by the catalogue, the homepage, search,
 * and anywhere else a post needs to be listed.
 */

defined( 'ABSPATH' ) || exit;

$guide_id      = get_the_ID();
$guide_is_course = 'course' === get_post_type();
$guide_is_paid = $guide_is_course
	&& class_exists( 'Guide\\Payments\\Course_Pricing' )
	&& \Guide\Payments\Course_Pricing::is_paid( $guide_id );
$guide_stats = $guide_is_course && class_exists( 'Guide\\Course_Api' )
	? \Guide\Course_Api::get_stats( $guide_id )
	: null;
$guide_code = $guide_is_course ? (string) get_post_meta( $guide_id, 'jsl_course_code', true ) : '';
$guide_img  = get_the_post_thumbnail_url( $guide_id, 'medium_large' );

if ( ! $guide_img && $guide_is_course && class_exists( 'Guide\\Media\\Placeholder' ) ) {
	$guide_img = \Guide\Media\Placeholder::course( $guide_id );
}
?>
<article class="guide-card guide-card--link">
	<?php if ( $guide_img ) : ?>
		<div class="guide-card__media">
			<img src="<?php echo guide_img_src( $guide_img ); ?>" alt="" loading="lazy" decoding="async">
		</div>
	<?php endif; ?>

	<div class="guide-card__body">
		<div class="is-flex is-align-items-center" style="gap:.5rem;flex-wrap:wrap">
			<?php if ( $guide_is_course ) : ?>
				<span class="guide-price-tag <?php echo $guide_is_paid ? 'guide-price-tag--paid' : 'guide-price-tag--free'; ?>">
					<?php echo $guide_is_paid ? esc_html__( 'Paid', 'guide-wp-theme' ) : esc_html__( 'Free', 'guide-wp-theme' ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $guide_code ) : ?>
				<span class="guide-course-code"><?php echo esc_html( $guide_code ); ?></span>
			<?php endif; ?>
		</div>

		<h3 class="guide-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="guide-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<?php endif; ?>

		<?php if ( $guide_stats ) : ?>
			<div class="guide-card__meta">
				<span class="guide-card__meta-item">
					<?php echo guide_icon( 'stack' ); ?>
					<?php
					printf(
						/* translators: %s: number of modules. */
						esc_html( _n( '%s module', '%s modules', (int) $guide_stats['modules'], 'guide-wp-theme' ) ),
						esc_html( number_format_i18n( (int) $guide_stats['modules'] ) )
					);
					?>
				</span>
				<span class="guide-card__meta-item">
					<?php echo guide_icon( 'article' ); ?>
					<?php
					printf(
						/* translators: %s: number of lessons. */
						esc_html( _n( '%s lesson', '%s lessons', (int) $guide_stats['lessons'], 'guide-wp-theme' ) ),
						esc_html( number_format_i18n( (int) $guide_stats['lessons'] ) )
					);
					?>
				</span>
				<?php if ( ! empty( $guide_stats['minutes'] ) ) : ?>
					<span class="guide-card__meta-item">
						<?php echo guide_icon( 'clock' ); ?>
						<?php
						printf(
							/* translators: %s: total minutes. */
							esc_html__( '%s min', 'guide-wp-theme' ),
							esc_html( number_format_i18n( (int) $guide_stats['minutes'] ) )
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</article>
