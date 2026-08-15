<?php
/**
 * Learning path page: intro + the path's courses as ordered milestone steps.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$path_id = get_the_ID();
	$courses = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_path_courses( $path_id ) : array();
	?>

	<section class="bg-hero text-on-hero">
		<div class="jsl-container max-w-3xl py-14 md:py-20">
			<span class="jsl-eyebrow text-signal-300"><?php esc_html_e( 'Learning path', 'job-seekers-theme' ); ?></span>
			<h1 class="m-0 mt-2 text-3xl font-extrabold tracking-tight md:text-4xl"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="mt-4 text-lg text-hero-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<div class="jsl-container max-w-3xl py-12">
		<?php if ( get_the_content() ) : ?>
			<div class="jsl-prose mb-12"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( empty( $courses ) ) : ?>
			<p class="text-ink-muted"><?php esc_html_e( 'Courses for this path are coming soon.', 'job-seekers-theme' ); ?></p>
		<?php else : ?>
			<ol class="jsl-path-line m-0 flex list-none flex-col gap-5 p-0 pl-6">
				<?php
				foreach ( $courses as $index => $course ) :
					$is_paid = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $course->ID );
					$stats   = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_stats( $course->ID ) : array( 'modules' => 0, 'lessons' => 0, 'minutes' => 0 );
					?>
					<li class="relative">
						<span class="absolute -left-[2.1rem] top-6 grid h-9 w-9 place-items-center rounded-full bg-accent font-mono text-sm font-bold text-on-accent shadow-accent"><?php echo esc_html( $index + 1 ); ?></span>
						<a class="group block rounded-xl border border-line bg-raised p-6 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-line-strong hover:shadow-md" href="<?php echo esc_url( get_permalink( $course ) ); ?>">
							<div class="flex items-center justify-between gap-3">
								<h2 class="m-0 text-lg font-bold text-ink group-hover:text-accent"><?php echo esc_html( get_the_title( $course ) ); ?></h2>
								<span class="jsl-badge <?php echo $is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>"><?php echo $is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?></span>
							</div>
							<?php if ( $course->post_excerpt ) : ?>
								<p class="mt-2 text-sm text-ink-muted"><?php echo esc_html( $course->post_excerpt ); ?></p>
							<?php endif; ?>
							<div class="mt-4 flex items-center gap-4 text-xs font-medium text-ink-muted">
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'layers', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d module', '%d modules', $stats['modules'], 'job-seekers-theme' ) ), (int) $stats['modules'] ); ?></span>
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'doc', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d lesson', '%d lessons', $stats['lessons'], 'job-seekers-theme' ) ), (int) $stats['lessons'] ); ?></span>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
