<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="jsl-container py-12 md:py-16">
	<header class="max-w-2xl">
		<span class="jsl-eyebrow"><?php esc_html_e( 'Browse', 'job-seekers-theme' ); ?></span>
		<h1 class="m-0 mt-1 text-3xl font-extrabold tracking-tight md:text-4xl"><?php the_archive_title(); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="mt-3 text-ink-muted"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				$jsl_is_course = 'course' === get_post_type();
				$jsl_is_paid   = $jsl_is_course && class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( get_the_ID() );
				$jsl_stats     = $jsl_is_course && class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_stats( get_the_ID() ) : null;
				?>
				<a class="group flex flex-col rounded-xl border border-line bg-raised p-6 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-line-strong hover:shadow-md" href="<?php the_permalink(); ?>">
					<?php if ( $jsl_is_course ) : ?>
						<span class="jsl-badge self-start <?php echo $jsl_is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>"><?php echo $jsl_is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?></span>
					<?php endif; ?>
					<h2 class="m-0 mt-3 text-lg font-bold leading-snug text-ink group-hover:text-accent"><?php the_title(); ?></h2>
					<?php if ( has_excerpt() ) : ?>
						<p class="mt-2 line-clamp-3 text-sm text-ink-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<?php if ( $jsl_stats ) : ?>
						<div class="mt-auto flex items-center gap-4 pt-5 text-xs font-medium text-ink-muted">
							<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'layers', 'w-3.5 h-3.5' ); ?><?php echo esc_html( $jsl_stats['modules'] ); ?></span>
							<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'doc', 'w-3.5 h-3.5' ); ?><?php echo esc_html( $jsl_stats['lessons'] ); ?></span>
							<?php if ( $jsl_stats['minutes'] ) : ?>
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-3.5 h-3.5' ); ?><?php echo esc_html( $jsl_stats['minutes'] ); ?>m</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</a>
				<?php
			endwhile;
			?>
		</div>
		<div class="mt-10 [&_.page-numbers]:mx-1 [&_.page-numbers]:rounded-md [&_.page-numbers]:px-3 [&_.page-numbers]:py-1.5 [&_.page-numbers.current]:bg-accent [&_.page-numbers.current]:text-on-accent">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<p class="mt-10 text-ink-muted"><?php esc_html_e( 'Nothing here yet.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
