<?php
/**
 * Wall of Success — every published story, plus the form for a learner to
 * add theirs.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jsl_stories_on = class_exists( 'JSL\\Success\\Success_Stories' );
$jsl_count      = $jsl_stories_on ? \JSL\Success\Success_Stories::published_count() : 0;
$jsl_my_status  = $jsl_stories_on ? \JSL\Success\Success_Stories::user_story_status( get_current_user_id() ) : '';
?>

<section class="relative overflow-hidden bg-hero text-on-hero">
	<div class="pointer-events-none absolute inset-0" aria-hidden="true"
		style="background: radial-gradient(50rem 30rem at 50% -20%, color-mix(in srgb, var(--md-tertiary-40) 55%, transparent), transparent 70%);"></div>

	<div class="jsl-container relative py-16 text-center md:py-20">
		<span class="md-chip !h-8 mx-auto border-white/25 !text-white/85">
			<?php echo jsl_icon( 'trophy-fill', 'w-4 h-4' ); ?>
			<?php esc_html_e( 'Wall of Success', 'job-seekers-theme' ); ?>
		</span>

		<h1 class="mx-auto mt-6 max-w-[18ch] text-balance font-display text-[clamp(2.25rem,1.5rem+2.4vw,3.15rem)] font-extrabold leading-[1.08] tracking-[-0.03em]">
			<?php esc_html_e( 'People who got the job', 'job-seekers-theme' ); ?>
		</h1>

		<p class="mx-auto mt-5 max-w-xl text-lg text-hero-muted">
			<?php
			echo $jsl_count > 0
				? esc_html( sprintf( _n( '%d learner has shared how it went. Yours could be next.', '%d learners have shared how it went. Yours could be next.', $jsl_count, 'job-seekers-theme' ), $jsl_count ) )
				: esc_html__( 'No stories yet — be the first to tell everyone how it went.', 'job-seekers-theme' );
			?>
		</p>

		<?php if ( is_user_logged_in() && ! $jsl_my_status ) : ?>
			<a class="jsl-btn jsl-btn--primary jsl-btn--lg mt-8" href="#share">
				<?php echo jsl_icon( 'sparkle-fill', 'w-5 h-5' ); ?>
				<?php esc_html_e( 'Share your story', 'job-seekers-theme' ); ?>
			</a>
		<?php elseif ( 'pending' === $jsl_my_status ) : ?>
			<p class="mx-auto mt-8 inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-3 text-sm font-semibold">
				<?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?>
				<?php esc_html_e( 'Your story is waiting to be reviewed — thank you!', 'job-seekers-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<div class="jsl-container py-14">
	<?php if ( have_posts() ) : ?>
		<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				$jsl_d      = \JSL\Success\Success_Stories::details( get_the_ID() );
				$jsl_author = get_the_author();
				?>
				<a class="md-card md-card--elevated group" href="<?php the_permalink(); ?>">
					<div class="md-card__body flex flex-1 flex-col !p-7">
						<div class="flex items-center gap-3">
							<?php echo jsl_avatar( get_the_author_meta( 'ID' ), 44 ); ?>
							<div class="min-w-0">
								<p class="m-0 truncate text-sm font-bold text-on-surface"><?php echo esc_html( $jsl_author ); ?></p>
								<?php if ( $jsl_d['role'] || $jsl_d['company'] ) : ?>
									<p class="m-0 truncate text-xs text-on-surface-variant">
										<?php echo esc_html( trim( $jsl_d['role'] . ( $jsl_d['company'] ? ' · ' . $jsl_d['company'] : '' ), ' ·' ) ); ?>
									</p>
								<?php endif; ?>
							</div>
						</div>

						<h2 class="m-0 mt-5 font-display text-lg font-bold leading-snug text-on-surface group-hover:text-primary">
							<?php the_title(); ?>
						</h2>

						<p class="mt-3 line-clamp-4 text-sm leading-relaxed text-on-surface-variant">
							<?php
							// get_the_excerpt(), not a hand-rolled trim: it inserts
							// the line breaks between blocks that stop the last word
							// of one paragraph fusing with the first of the next.
							echo esc_html( get_the_excerpt() );
							?>
						</p>

						<div class="mt-auto flex items-center gap-3 pt-6 text-xs font-medium text-on-surface-variant">
							<?php if ( $jsl_d['weeks'] ) : ?>
								<span class="md-chip md-chip--static md-chip--tertiary !h-7 !px-3 !text-xs">
									<?php printf( esc_html( _n( '%d week', '%d weeks', $jsl_d['weeks'], 'job-seekers-theme' ) ), (int) $jsl_d['weeks'] ); ?>
								</span>
							<?php endif; ?>
							<span class="ml-auto inline-flex items-center gap-1.5 font-bold text-primary">
								<?php esc_html_e( 'Read story', 'job-seekers-theme' ); ?>
								<?php echo jsl_icon( 'arrow-right', 'w-4 h-4' ); ?>
							</span>
						</div>
					</div>
				</a>
			<?php endwhile; ?>
		</div>

		<div class="mt-12 [&_.page-numbers]:mx-1 [&_.page-numbers]:inline-grid [&_.page-numbers]:h-10 [&_.page-numbers]:min-w-10 [&_.page-numbers]:place-items-center [&_.page-numbers]:rounded-full [&_.page-numbers]:px-3 [&_.page-numbers]:text-sm [&_.page-numbers]:font-semibold [&_.page-numbers]:no-underline [&_.page-numbers.current]:bg-secondary-container [&_.page-numbers.current]:text-on-secondary-container">
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		</div>
	<?php else : ?>
		<div class="md-card md-card--filled mx-auto max-w-lg p-10 text-center">
			<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-tertiary-container text-on-tertiary-container">
				<?php echo jsl_icon( 'trophy-fill', 'w-6 h-6' ); ?>
			</span>
			<p class="mt-4 font-display font-bold"><?php esc_html_e( 'No stories yet', 'job-seekers-theme' ); ?></p>
			<p class="mt-1 text-sm text-on-surface-variant"><?php esc_html_e( 'When learners land a role, their stories show up here.', 'job-seekers-theme' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( is_user_logged_in() && ! $jsl_my_status ) : ?>
		<?php get_template_part( 'template-parts/story-form' ); ?>
	<?php elseif ( ! is_user_logged_in() ) : ?>
		<div class="md-card md-card--filled mx-auto mt-16 max-w-2xl p-8 text-center">
			<h2 class="m-0 font-display text-xl font-bold"><?php esc_html_e( 'Got the job?', 'job-seekers-theme' ); ?></h2>
			<p class="mt-2 text-on-surface-variant"><?php esc_html_e( 'Sign in to add your story to the wall.', 'job-seekers-theme' ); ?></p>
			<a class="jsl-btn jsl-btn--primary mt-6" href="<?php echo esc_url( wp_login_url( get_post_type_archive_link( 'success_story' ) ) ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
