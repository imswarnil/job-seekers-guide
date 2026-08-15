<?php
/**
 * Learning path page: intro + the path's courses as ordered milestone steps.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$path_id = get_the_ID();
	// A path is an ordered mix of whole courses and standalone pieces
	// (article / video / quiz), so render steps rather than just courses.
	$steps = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_path_steps( $path_id ) : array();
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

		<?php if ( empty( $steps ) ) : ?>
			<p class="text-on-surface-variant"><?php esc_html_e( 'This path is being put together — check back soon.', 'job-seekers-theme' ); ?></p>
		<?php else : ?>
			<ol class="jsl-path-line m-0 flex list-none flex-col gap-5 p-0 pl-6">
				<?php
				foreach ( $steps as $index => $step ) :
					$is_course = 'course' === $step['type'];

					if ( $is_course ) {
						$is_paid = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $step['id'] );
						$stats   = \JSL\Course_Api::get_stats( $step['id'] );
						$icon    = 'stack';
						$kind    = __( 'Course', 'job-seekers-theme' );
					} else {
						$is_paid = false;
						$stats   = null;
						$icon    = 'video' === $step['lesson_type'] ? 'film-strip' : ( 'quiz' === $step['lesson_type'] ? 'question' : 'article' );
						$kind    = 'video' === $step['lesson_type']
							? __( 'Watch', 'job-seekers-theme' )
							: ( 'quiz' === $step['lesson_type'] ? __( 'Check yourself', 'job-seekers-theme' ) : __( 'Read', 'job-seekers-theme' ) );
					}

					$minutes = $is_course ? 0 : (int) get_post_meta( $step['id'], 'jsl_duration_minutes', true );
					?>
					<li class="relative">
						<span class="absolute -left-[2.1rem] top-6 grid h-9 w-9 place-items-center rounded-full <?php echo $is_course ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container'; ?> font-mono text-sm font-bold"><?php echo esc_html( $index + 1 ); ?></span>
						<a class="group block rounded-xl border border-outline-variant bg-surface-lowest p-6 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-outline hover:shadow-md" href="<?php echo esc_url( $step['permalink'] ); ?>">
							<div class="flex items-center justify-between gap-3">
								<span class="inline-flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-wider text-on-surface-variant">
									<?php echo jsl_icon( $icon, 'w-3.5 h-3.5' ); ?>
									<?php echo esc_html( $kind ); ?>
								</span>
								<?php if ( $is_course ) : ?>
									<span class="jsl-badge <?php echo $is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>"><?php echo $is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?></span>
								<?php endif; ?>
							</div>

							<h2 class="m-0 mt-2 text-lg font-bold text-ink group-hover:text-primary"><?php echo esc_html( $step['title'] ); ?></h2>

							<?php if ( $step['post']->post_excerpt ) : ?>
								<p class="mt-2 text-sm text-on-surface-variant"><?php echo esc_html( $step['post']->post_excerpt ); ?></p>
							<?php endif; ?>

							<div class="mt-4 flex items-center gap-4 text-xs font-medium text-on-surface-variant">
								<?php if ( $stats ) : ?>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'stack', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d module', '%d modules', $stats['modules'], 'job-seekers-theme' ) ), (int) $stats['modules'] ); ?></span>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'article', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d lesson', '%d lessons', $stats['lessons'], 'job-seekers-theme' ) ), (int) $stats['lessons'] ); ?></span>
								<?php endif; ?>
								<?php if ( $minutes ) : ?>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-3.5 h-3.5' ); ?><?php printf( esc_html__( '%d min', 'job-seekers-theme' ), $minutes ); ?></span>
								<?php endif; ?>
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
