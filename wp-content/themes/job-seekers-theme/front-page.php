<?php
/**
 * Front page: hero + learning paths + course grid.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paths = get_posts( array( 'post_type' => 'learning_path', 'posts_per_page' => -1 ) );
?>

<div class="jsl-container">
	<div class="jsl-hero jsl-container--narrow" style="padding-inline:0">
		<span class="jsl-eyebrow"><?php esc_html_e( 'Open source', 'job-seekers-theme' ); ?></span>
		<h1><?php esc_html_e( 'Structured learning paths for job seekers', 'job-seekers-theme' ); ?></h1>
		<p class="jsl-lede"><?php esc_html_e( 'Follow a guided path, practice real skills, and track your progress toward an offer.', 'job-seekers-theme' ); ?></p>
	</div>

	<?php foreach ( $paths as $path ) : ?>
		<section style="margin-top:var(--jsl-space-7)">
			<h2><a href="<?php echo esc_url( get_permalink( $path ) ); ?>"><?php echo get_the_title( $path ); ?></a></h2>
			<?php if ( $path->post_excerpt ) : ?>
				<p class="jsl-lede"><?php echo esc_html( $path->post_excerpt ); ?></p>
			<?php endif; ?>

			<?php
			$courses = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_path_courses( $path->ID ) : array();
			if ( ! empty( $courses ) ) :
				?>
				<div class="jsl-card-grid">
					<?php foreach ( $courses as $course ) : ?>
						<a class="jsl-card" href="<?php echo esc_url( get_permalink( $course ) ); ?>">
							<?php
							$is_paid = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $course->ID );
							?>
							<span class="jsl-badge <?php echo $is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>">
								<?php echo $is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
							</span>
							<h3 class="jsl-card__title"><?php echo get_the_title( $course ); ?></h3>
							<p class="jsl-card__meta"><?php echo esc_html( $course->post_excerpt ); ?></p>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<?php if ( empty( $paths ) ) : ?>
		<p class="jsl-card__meta" style="margin-top:var(--jsl-space-6)"><?php esc_html_e( 'No learning paths yet.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
