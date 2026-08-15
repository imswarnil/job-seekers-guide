<?php
/**
 * Archive: a filter-chip row over a grid of M3 cards.
 *
 * The chips are links, not JavaScript — filtering by topic is a real
 * navigation with its own URL, which keeps it shareable and crawlable.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jsl_is_course_archive = is_post_type_archive( 'course' );

// Topic chips, only where they mean something and only for topics in use.
$jsl_topics = $jsl_is_course_archive
	? get_terms(
		array(
			'taxonomy'   => 'course_category',
			'hide_empty' => true,
		)
	)
	: array();

$jsl_current_topic = is_tax( 'course_category' ) ? (int) get_queried_object_id() : 0;
?>

<div class="jsl-container py-12 md:py-16">
	<header class="max-w-2xl">
		<span class="jsl-eyebrow"><?php esc_html_e( 'Browse', 'job-seekers-theme' ); ?></span>
		<h1 class="m-0 mt-1 font-display text-3xl font-extrabold tracking-tight md:text-4xl">
<?php the_archive_title(); ?>
		</h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="mt-3 text-on-surface-variant"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( ! empty( $jsl_topics ) && ! is_wp_error( $jsl_topics ) ) : ?>
		<div class="mt-7 flex flex-wrap gap-2" role="group" aria-label="<?php esc_attr_e( 'Filter by topic', 'job-seekers-theme' ); ?>">
			<a class="md-chip <?php echo $jsl_current_topic ? '' : 'md-chip--selected'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>">
				<?php if ( ! $jsl_current_topic ) : ?>
					<?php echo jsl_icon( 'check', 'w-[18px] h-[18px]' ); ?>
				<?php endif; ?>
				<?php esc_html_e( 'All', 'job-seekers-theme' ); ?>
			</a>
			<?php foreach ( $jsl_topics as $jsl_topic ) : ?>
				<?php $jsl_on = $jsl_current_topic === (int) $jsl_topic->term_id; ?>
				<a class="md-chip <?php echo $jsl_on ? 'md-chip--selected' : ''; ?>" href="<?php echo esc_url( get_term_link( $jsl_topic ) ); ?>">
					<?php if ( $jsl_on ) : ?>
						<?php echo jsl_icon( 'check', 'w-[18px] h-[18px]' ); ?>
					<?php endif; ?>
					<?php echo esc_html( $jsl_topic->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				$jsl_is_course = 'course' === get_post_type();
				$jsl_is_paid   = $jsl_is_course && class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( get_the_ID() );
				$jsl_stats     = $jsl_is_course && class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_stats( get_the_ID() ) : null;
				$jsl_img       = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' )
					?: ( $jsl_is_course && class_exists( 'JSL\\Media\\Placeholder' ) ? \JSL\Media\Placeholder::course( get_the_ID() ) : '' );
				?>
				<a class="md-card md-card--elevated group" href="<?php the_permalink(); ?>">
					<?php if ( $jsl_img ) : ?>
						<img class="md-card__media" src="<?php echo jsl_img_src( $jsl_img ); ?>" alt="" loading="lazy">
					<?php endif; ?>

					<div class="md-card__body flex flex-1 flex-col !p-6">
						<?php if ( $jsl_is_course ) : ?>
							<span class="md-chip md-chip--static self-start <?php echo $jsl_is_paid ? 'md-chip--tertiary' : 'md-chip--selected'; ?> !h-7 !px-3 !text-xs">
								<?php echo $jsl_is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
							</span>
						<?php endif; ?>

						<h2 class="m-0 mt-3 font-display text-lg font-bold leading-snug text-on-surface group-hover:text-primary"><?php the_title(); ?></h2>

						<?php if ( has_excerpt() ) : ?>
							<p class="mt-2 line-clamp-3 text-sm text-on-surface-variant"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<?php if ( $jsl_stats ) : ?>
							<div class="mt-auto flex items-center gap-4 pt-5 text-xs font-medium text-on-surface-variant">
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'stack', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['modules'] ); ?></span>
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'article', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['lessons'] ); ?></span>
								<?php if ( $jsl_stats['minutes'] ) : ?>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['minutes'] ); ?>m</span>
								<?php endif; ?>
								<span class="ml-auto text-primary opacity-0 transition-opacity group-hover:opacity-100"><?php echo jsl_icon( 'arrow-right', 'w-4 h-4' ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</a>
				<?php
			endwhile;
			?>
		</div>

		<div class="mt-10 [&_.page-numbers]:mx-1 [&_.page-numbers]:inline-grid [&_.page-numbers]:h-10 [&_.page-numbers]:min-w-10 [&_.page-numbers]:place-items-center [&_.page-numbers]:rounded-full [&_.page-numbers]:px-3 [&_.page-numbers]:text-sm [&_.page-numbers]:font-semibold [&_.page-numbers]:no-underline [&_.page-numbers.current]:bg-secondary-container [&_.page-numbers.current]:text-on-secondary-container">
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		</div>
	<?php else : ?>
		<div class="md-card md-card--filled mt-10 p-10 text-center">
			<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-surface-highest text-on-surface-variant">
				<?php echo jsl_icon( 'magnifying-glass', 'w-6 h-6' ); ?>
			</span>
			<p class="mt-4 font-display font-bold"><?php esc_html_e( 'Nothing here yet', 'job-seekers-theme' ); ?></p>
			<p class="mt-1 text-sm text-on-surface-variant"><?php esc_html_e( 'Try another topic, or browse everything.', 'job-seekers-theme' ); ?></p>
			<a class="jsl-btn jsl-btn--tonal mt-6" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'All courses', 'job-seekers-theme' ); ?></a>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
