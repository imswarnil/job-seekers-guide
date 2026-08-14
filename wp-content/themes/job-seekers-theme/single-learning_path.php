<?php
/**
 * Learning path page: intro + ordered course grid.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$path_id = get_the_ID();
	$courses = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_path_courses( $path_id ) : array();
	?>

	<div class="jsl-container">
		<div class="jsl-hero jsl-container--narrow" style="padding-inline:0">
			<span class="jsl-eyebrow"><?php esc_html_e( 'Learning Path', 'job-seekers-theme' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<div class="jsl-lede"><?php the_content(); ?></div>
		</div>

		<?php if ( ! empty( $courses ) ) : ?>
			<div class="jsl-card-grid">
				<?php foreach ( $courses as $index => $course ) : ?>
					<a class="jsl-card" href="<?php echo esc_url( get_permalink( $course ) ); ?>">
						<span class="jsl-eyebrow"><?php echo esc_html( sprintf( __( 'Step %d', 'job-seekers-theme' ), $index + 1 ) ); ?></span>
						<h3 class="jsl-card__title"><?php echo get_the_title( $course ); ?></h3>
						<p class="jsl-card__meta"><?php echo esc_html( $course->post_excerpt ); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
