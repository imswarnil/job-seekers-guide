<?php
/**
 * Learning path page: intro + the path's steps as an ordered timeline.
 *
 * A path is a mix of whole courses and standalone pieces (article / video /
 * quiz), so it renders steps rather than just courses.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$guide_path_id = get_the_ID();
	$guide_steps   = class_exists( 'Guide\\Course_Api' )
		? \Guide\Course_Api::get_path_steps( $guide_path_id )
		: array();
	?>

	<section class="guide-course-hero">
		<div class="guide-shell guide-shell--narrow">
			<span class="guide-eyebrow"><?php esc_html_e( 'Learning path', 'guide-wp-theme' ); ?></span>
			<h1 class="guide-display mt-2"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="guide-course-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<?php if ( $guide_steps ) : ?>
				<?php
				$guide_path_lessons = class_exists( 'Guide\\Structure\\Structure' )
					? \Guide\Structure\Structure::flatten_lessons( 'path', $guide_path_id )
					: array();
				$guide_path_percent = ( $guide_path_lessons && class_exists( 'Guide\\Structure\\Path_Player' ) )
					? \Guide\Structure\Path_Player::percent( get_current_user_id(), $guide_path_lessons )
					: 0;
				?>
				<div class="guide-course-hero__meta">
					<span class="guide-chip guide-chip--spark">
						<?php
						printf(
							/* translators: %d: number of steps in the path. */
							esc_html( _n( '%d step', '%d steps', count( $guide_steps ), 'guide-wp-theme' ) ),
							(int) count( $guide_steps )
						);
						?>
					</span>
					<?php if ( $guide_path_lessons ) : ?>
						<span class="guide-chip guide-chip--outline">
							<?php echo guide_icon( 'article' ); ?>
							<?php
							printf(
								/* translators: %d: number of lessons across the whole path. */
								esc_html( _n( '%d lesson', '%d lessons', count( $guide_path_lessons ), 'guide-wp-theme' ) ),
								(int) count( $guide_path_lessons )
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<?php if ( $guide_path_lessons && class_exists( 'Guide\\Structure\\Path_Player' ) ) : ?>
					<?php // The path has its own player, so following it end to end never drops you into a single course's navigation. ?>
					<div class="guide-hero__actions">
						<a class="button is-primary is-medium" href="<?php echo esc_url( \Guide\Structure\Path_Player::start_url( $guide_path_id ) ); ?>">
							<?php echo $guide_path_percent > 0 ? esc_html__( 'Continue this path', 'guide-wp-theme' ) : esc_html__( 'Start this path', 'guide-wp-theme' ); ?>
						</a>
					</div>

					<?php if ( $guide_path_percent > 0 ) : ?>
						<div class="mt-4" style="max-width:22rem">
							<div class="guide-progress-label">
								<span><?php echo esc_html( sprintf( '%d%%', $guide_path_percent ) ); ?></span>
							</div>
							<span class="guide-progress">
								<span class="guide-progress__bar" style="width:<?php echo esc_attr( (string) $guide_path_percent ); ?>%"></span>
							</span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>

	<div class="guide-shell guide-shell--narrow guide-section">
		<?php if ( trim( (string) get_the_content() ) ) : ?>
			<div class="guide-prose" style="margin-bottom:3rem"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( empty( $guide_steps ) ) : ?>
			<div class="guide-empty">
				<p class="guide-empty__title"><?php esc_html_e( 'This path is being put together', 'guide-wp-theme' ); ?></p>
				<p class="guide-empty__text"><?php esc_html_e( 'Check back soon — or browse the full catalogue in the meantime.', 'guide-wp-theme' ); ?></p>
				<a class="button is-primary" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'guide-wp-theme' ); ?></a>
			</div>
		<?php else : ?>
			<ol class="guide-timeline">
				<?php
				foreach ( $guide_steps as $guide_index => $guide_step ) :
					$guide_is_course = 'course' === $guide_step['type'];

					if ( $guide_is_course ) {
						$guide_is_paid = class_exists( 'Guide\\Payments\\Course_Access' )
							&& \Guide\Payments\Course_Access::is_premium( $guide_step['id'] );
						$guide_stats = \Guide\Course_Api::get_stats( $guide_step['id'] );
						$guide_kind  = __( 'Course', 'guide-wp-theme' );
					} else {
						$guide_is_paid = false;
						$guide_stats   = null;
						$guide_kind    = 'video' === $guide_step['lesson_type']
							? __( 'Watch', 'guide-wp-theme' )
							: ( 'quiz' === $guide_step['lesson_type']
								? __( 'Check yourself', 'guide-wp-theme' )
								: __( 'Read', 'guide-wp-theme' ) );
					}

					$guide_minutes = $guide_is_course
						? 0
						: (int) get_post_meta( $guide_step['id'], 'jsl_duration_minutes', true );
					?>
					<li class="guide-timeline__item">
						<span class="guide-timeline__marker"><?php echo esc_html( (string) ( $guide_index + 1 ) ); ?></span>

						<a class="guide-card guide-card--link" style="padding:1.25rem" href="<?php echo esc_url( $guide_step['permalink'] ); ?>">
							<div class="is-flex is-align-items-center" style="gap:.6rem;justify-content:space-between">
								<span class="guide-filter-group__label"><?php echo esc_html( $guide_kind ); ?></span>
								<?php if ( $guide_is_course ) : ?>
									<span class="guide-price-tag <?php echo $guide_is_paid ? 'guide-price-tag--paid' : 'guide-price-tag--free'; ?>">
										<?php echo $guide_is_paid ? esc_html__( 'Members', 'guide-wp-theme' ) : esc_html__( 'Free', 'guide-wp-theme' ); ?>
									</span>
								<?php endif; ?>
							</div>

							<h2 class="guide-timeline__title mt-2"><?php echo esc_html( $guide_step['title'] ); ?></h2>

							<?php if ( $guide_step['post']->post_excerpt ) : ?>
								<p class="guide-timeline__text"><?php echo esc_html( $guide_step['post']->post_excerpt ); ?></p>
							<?php endif; ?>

							<div class="guide-card__meta">
								<?php if ( $guide_stats ) : ?>
									<span class="guide-card__meta-item">
										<?php echo guide_icon( 'stack' ); ?>
										<?php
										printf(
											/* translators: %d: number of modules. */
											esc_html( _n( '%d module', '%d modules', (int) $guide_stats['modules'], 'guide-wp-theme' ) ),
											(int) $guide_stats['modules']
										);
										?>
									</span>
									<span class="guide-card__meta-item">
										<?php echo guide_icon( 'article' ); ?>
										<?php
										printf(
											/* translators: %d: number of lessons. */
											esc_html( _n( '%d lesson', '%d lessons', (int) $guide_stats['lessons'], 'guide-wp-theme' ) ),
											(int) $guide_stats['lessons']
										);
										?>
									</span>
								<?php endif; ?>
								<?php if ( $guide_minutes ) : ?>
									<span class="guide-card__meta-item">
										<?php echo guide_icon( 'clock' ); ?>
										<?php
										printf(
											/* translators: %d: duration in minutes. */
											esc_html__( '%d min', 'guide-wp-theme' ),
											$guide_minutes
										);
										?>
									</span>
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


comments_template();

get_footer();
