<?php
/**
 * Wall of Success — every published story, plus the form for a learner to add
 * theirs.
 *
 * This page does more emotional work than any other on the site: it is what a
 * person reads at rejection number twelve. So the stories lead, and the
 * numbers stay small.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_stories_on = class_exists( 'Guide\\Success\\Success_Stories' );
$guide_count      = $guide_stories_on ? \Guide\Success\Success_Stories::published_count() : 0;
$guide_my_status  = $guide_stories_on ? \Guide\Success\Success_Stories::user_story_status( get_current_user_id() ) : '';
?>

<section class="guide-story-hero">
	<div class="guide-shell guide-story-hero__inner" style="text-align:center">
		<span class="guide-chip guide-chip--spark">
			<?php echo guide_icon( 'trophy-fill' ); ?>
			<?php esc_html_e( 'Wall of Success', 'guide-wp-theme' ); ?>
		</span>

		<h1 class="guide-display mt-4"><?php esc_html_e( 'People who got the job', 'guide-wp-theme' ); ?></h1>

		<p class="mt-4" style="max-width:52ch;margin-inline:auto;opacity:.82;font-size:1.075rem">
			<?php
			echo $guide_count > 0
				? esc_html(
					sprintf(
						/* translators: %d: number of published stories. */
						_n(
							'%d learner has written down how it actually went — the rejections included. Yours could be next.',
							'%d learners have written down how it actually went — the rejections included. Yours could be next.',
							$guide_count,
							'guide-wp-theme'
						),
						$guide_count
					)
				)
				: esc_html__( 'No stories yet — be the first to tell everyone how it went.', 'guide-wp-theme' );
			?>
		</p>

		<?php if ( is_user_logged_in() && ! $guide_my_status ) : ?>
			<div class="guide-hero__actions" style="justify-content:center">
				<a class="button is-primary is-medium" href="#share"><?php esc_html_e( 'Share your story', 'guide-wp-theme' ); ?></a>
			</div>
		<?php elseif ( 'pending' === $guide_my_status ) : ?>
			<p class="guide-chip guide-chip--primary mt-5">
				<?php echo guide_icon( 'clock' ); ?>
				<?php esc_html_e( 'Your story is waiting to be reviewed — thank you.', 'guide-wp-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<div class="guide-shell guide-section">
	<?php if ( have_posts() ) : ?>
		<div class="guide-grid guide-grid--wide">
			<?php
			while ( have_posts() ) :
				the_post();
				$guide_d      = \Guide\Success\Success_Stories::details( get_the_ID() );
				$guide_author = get_the_author();
				?>
				<article class="guide-card guide-card--link">
					<div class="guide-card__body" style="padding:1.5rem">
						<div class="is-flex is-align-items-center" style="gap:.75rem">
							<?php echo guide_avatar( (int) get_the_author_meta( 'ID' ), 44 ); ?>
							<div style="min-width:0">
								<p class="guide-story-card__name"><?php echo esc_html( $guide_author ); ?></p>
								<?php if ( $guide_d['role'] || $guide_d['company'] ) : ?>
									<p class="guide-story-card__role">
										<?php echo esc_html( trim( $guide_d['role'] . ( $guide_d['company'] ? ' · ' . $guide_d['company'] : '' ), ' ·' ) ); ?>
									</p>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( $guide_d['previous'] && $guide_d['role'] ) : ?>
							<p class="guide-transition mt-3">
								<?php echo esc_html( $guide_d['previous'] ); ?>
								<?php echo guide_icon( 'arrow-right' ); ?>
								<?php echo esc_html( $guide_d['role'] ); ?>
							</p>
						<?php endif; ?>

						<h2 class="guide-card__title mt-3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

						<p class="guide-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 34 ) ); ?></p>

						<div class="guide-card__meta">
							<?php if ( $guide_d['weeks'] ) : ?>
								<span class="guide-chip guide-chip--spark">
									<?php
									printf(
										/* translators: %d: number of weeks spent searching. */
										esc_html( _n( '%d week search', '%d week search', (int) $guide_d['weeks'], 'guide-wp-theme' ) ),
										(int) $guide_d['weeks']
									);
									?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<div class="mt-6">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'class'     => 'pagination is-centered',
					'prev_text' => esc_html__( 'Previous', 'guide-wp-theme' ),
					'next_text' => esc_html__( 'Next', 'guide-wp-theme' ),
				)
			);
			?>
		</div>
	<?php else : ?>
		<div class="guide-empty" style="max-width:34rem;margin-inline:auto">
			<span class="guide-empty__icon"><?php echo guide_icon( 'trophy-fill' ); ?></span>
			<p class="guide-empty__title"><?php esc_html_e( 'No stories yet', 'guide-wp-theme' ); ?></p>
			<p class="guide-empty__text"><?php esc_html_e( 'When learners land a role, their stories show up here.', 'guide-wp-theme' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( is_user_logged_in() && ! $guide_my_status ) : ?>
		<?php get_template_part( 'template-parts/story-form' ); ?>
	<?php elseif ( ! is_user_logged_in() ) : ?>
		<div class="guide-card mt-6" style="max-width:36rem;margin-inline:auto;padding:2rem;text-align:center">
			<h2 class="guide-card__title"><?php esc_html_e( 'Got the job?', 'guide-wp-theme' ); ?></h2>
			<p class="guide-card__excerpt mt-2"><?php esc_html_e( 'Sign in to add your story to the wall.', 'guide-wp-theme' ); ?></p>
			<a class="button is-primary mt-4" href="<?php echo esc_url( wp_login_url( get_post_type_archive_link( 'success_story' ) ) ); ?>">
				<?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
