<?php
/**
 * Lesson player.
 *
 * A course sidebar holding the curriculum — a slide-over drawer on narrow
 * windows, permanently visible from 960px up — beside the lesson itself.
 *
 * Access is decided by the plugin, never here. This template only chooses
 * which state to draw; a locked lesson's body has already been stripped
 * server-side by Guide\Access\Access.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'player' );

while ( have_posts() ) :
	the_post();
	$guide_lesson_id = get_the_ID();
	$guide_course_id = (int) get_post_meta( $guide_lesson_id, 'jsl_course_id', true );
	$guide_has_api   = class_exists( 'Guide\\Course_Api' );
	$guide_modules   = $guide_course_id && $guide_has_api ? \Guide\Course_Api::get_modules( $guide_course_id ) : array();
	$guide_adjacent  = $guide_course_id && $guide_has_api
		? \Guide\Course_Api::adjacent_lessons( $guide_course_id, $guide_lesson_id )
		: array(
			'prev' => null,
			'next' => null,
		);

	$guide_user_id   = get_current_user_id();
	$guide_completed = $guide_user_id && $guide_course_id && class_exists( 'Guide\\Progress\\Progress' )
		? \Guide\Progress\Progress::completed_lesson_ids( $guide_user_id, $guide_course_id )
		: array();

	$guide_total = 0;
	foreach ( $guide_modules as $guide_module ) {
		$guide_total += count( $guide_module['lessons'] );
	}

	$guide_percent = $guide_total > 0 ? (int) round( count( $guide_completed ) / $guide_total * 100 ) : 0;
	$guide_is_done = in_array( $guide_lesson_id, $guide_completed, true );

	$guide_lock_reason = class_exists( 'Guide\\Access\\Access' )
		? \Guide\Access\Access::lesson_denial_reason( $guide_lesson_id )
		: 'ok';
	$guide_locked = ! in_array( $guide_lock_reason, array( 'ok', 'not_found' ), true );

	$guide_video_start = (int) get_post_meta( $guide_lesson_id, 'jsl_video_start', true );
	$guide_video_end   = (int) get_post_meta( $guide_lesson_id, 'jsl_video_end', true );
	$guide_video       = class_exists( 'Guide\\Lesson_Meta' )
		? \Guide\Lesson_Meta::embed_info(
			(string) get_post_meta( $guide_lesson_id, 'jsl_video_url', true ),
			$guide_video_start,
			$guide_video_end
		)
		: null;
	$guide_duration    = (int) get_post_meta( $guide_lesson_id, 'jsl_duration_minutes', true );
	$guide_lesson_type = get_post_meta( $guide_lesson_id, 'jsl_lesson_type', true );
	$guide_lesson_type = $guide_lesson_type ? $guide_lesson_type : ( $guide_video ? 'video' : 'article' );

	$guide_poster = get_the_post_thumbnail_url( $guide_lesson_id, 'large' );
	if ( ! $guide_poster && $guide_video && ! empty( $guide_video['poster'] ) ) {
		$guide_poster = $guide_video['poster'];
	}
	?>

	<div class="guide-player">

		<div class="guide-player__scrim" data-drawer-scrim></div>

		<aside class="guide-player__sidebar" id="guide-course-drawer" data-drawer
			aria-label="<?php esc_attr_e( 'Course content', 'guide-wp-theme' ); ?>">

			<?php if ( $guide_course_id ) : ?>
				<div class="guide-player__sidebar-head">
					<p class="guide-filter-group__label"><?php esc_html_e( 'Course', 'guide-wp-theme' ); ?></p>
					<a class="guide-player__course-title mt-1" href="<?php echo esc_url( get_permalink( $guide_course_id ) ); ?>">
						<?php echo esc_html( get_the_title( $guide_course_id ) ); ?>
					</a>

					<?php if ( $guide_user_id && $guide_total ) : ?>
						<div class="mt-3">
							<div class="guide-progress-label">
								<span data-progress-label>
									<?php
									printf(
										/* translators: 1: completed lessons, 2: total lessons. */
										esc_html__( '%1$d / %2$d complete', 'guide-wp-theme' ),
										(int) count( $guide_completed ),
										(int) $guide_total
									);
									?>
								</span>
								<span data-progress-percent><?php echo esc_html( (string) $guide_percent ); ?>%</span>
							</div>
							<span class="guide-progress">
								<span class="guide-progress__bar" data-progress-bar style="width:<?php echo esc_attr( (string) $guide_percent ); ?>%"></span>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<nav class="guide-player__modules" aria-label="<?php esc_attr_e( 'Course lessons', 'guide-wp-theme' ); ?>">
				<?php foreach ( $guide_modules as $guide_module_index => $guide_module ) : ?>
					<p class="guide-player__module-label">
						<?php echo esc_html( sprintf( '%02d · %s', $guide_module_index + 1, $guide_module['title'] ) ); ?>
					</p>

					<?php
					foreach ( $guide_module['lessons'] as $guide_mod_lesson ) :
						$guide_row_done    = in_array( (int) $guide_mod_lesson->ID, $guide_completed, true );
						$guide_row_current = (int) $guide_mod_lesson->ID === $guide_lesson_id;
						$guide_row_min     = (int) get_post_meta( $guide_mod_lesson->ID, 'jsl_duration_minutes', true );
						$guide_row_locked  = class_exists( 'Guide\\Access\\Access' )
							&& \Guide\Access\Access::is_locked( (int) $guide_mod_lesson->ID );
						?>
						<a class="guide-player__lesson<?php echo $guide_row_done ? ' is-complete' : ''; ?><?php echo $guide_row_current ? ' is-current' : ''; ?>"
							href="<?php echo esc_url( get_permalink( $guide_mod_lesson ) ); ?>"
							<?php echo $guide_row_current ? 'aria-current="page"' : ''; ?>
							data-lesson-row="<?php echo esc_attr( (string) $guide_mod_lesson->ID ); ?>">

							<span class="guide-player__lesson-icon" data-lesson-dot>
								<?php
								if ( $guide_row_done ) {
									echo guide_icon( 'check-circle-fill' );
								} elseif ( $guide_row_locked ) {
									echo guide_icon( 'lock-simple' );
								} else {
									echo guide_icon( 'circle' );
								}
								?>
							</span>

							<span style="min-width:0;flex:1"><?php echo esc_html( get_the_title( $guide_mod_lesson ) ); ?></span>

							<?php if ( $guide_row_min ) : ?>
								<span class="guide-player__lesson-duration"><?php echo esc_html( (string) $guide_row_min ); ?>m</span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</nav>

			<?php // Sponsor badge: seen by learners mid-course, which is the whole value of the slot. ?>
			<?php guide_ad( 'badge', false ); ?>
		</aside>

		<article class="guide-player__main">
			<div class="guide-player__body">

				<?php if ( $guide_video && 'quiz' !== $guide_lesson_type && ! $guide_locked ) : ?>
					<div class="guide-video"
						data-embed-type="<?php echo esc_attr( $guide_video['type'] ); ?>"
						data-embed-src="<?php echo esc_url( $guide_video['src'] ); ?>"
						data-start="<?php echo esc_attr( (string) $guide_video['start'] ); ?>"
						data-end="<?php echo esc_attr( (string) $guide_video['end'] ); ?>"
						data-title="<?php the_title_attribute(); ?>">
						<?php if ( $guide_poster ) : ?>
							<img class="guide-video__poster" src="<?php echo guide_img_src( $guide_poster ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<button type="button" class="guide-video__play" aria-label="<?php esc_attr_e( 'Play video', 'guide-wp-theme' ); ?>">
							<?php echo guide_icon( 'play-fill' ); ?>
						</button>
					</div>
				<?php endif; ?>

				<header>
					<h1 class="guide-lesson-title"><?php the_title(); ?></h1>

					<div class="guide-lesson-meta">
						<span class="guide-card__meta-item">
							<?php
							echo guide_icon(
								'quiz' === $guide_lesson_type
									? 'list-checks'
									: ( 'video' === $guide_lesson_type ? 'film-strip' : 'article' )
							);
							?>
							<?php
							echo 'quiz' === $guide_lesson_type
								? esc_html__( 'Quiz', 'guide-wp-theme' )
								: ( 'video' === $guide_lesson_type ? esc_html__( 'Video', 'guide-wp-theme' ) : esc_html__( 'Article', 'guide-wp-theme' ) );
							?>
						</span>

						<?php if ( $guide_duration ) : ?>
							<span class="guide-card__meta-item">
								<?php echo guide_icon( 'clock' ); ?>
								<?php
								printf(
									/* translators: %d: duration in minutes. */
									esc_html__( '%d min', 'guide-wp-theme' ),
									(int) $guide_duration
								);
								?>
							</span>
						<?php endif; ?>

						<span style="margin-left:auto">
							<?php if ( $guide_user_id && $guide_course_id && ! $guide_locked ) : ?>
								<button type="button"
									class="button is-small <?php echo $guide_is_done ? 'is-light' : 'is-primary'; ?>"
									id="guide-complete-btn"
									data-lesson-id="<?php echo esc_attr( (string) $guide_lesson_id ); ?>"
									data-completed="<?php echo $guide_is_done ? '1' : '0'; ?>"
									data-label-done="<?php esc_attr_e( 'Completed', 'guide-wp-theme' ); ?>"
									data-label-todo="<?php esc_attr_e( 'Mark complete', 'guide-wp-theme' ); ?>">
									<?php echo $guide_is_done ? esc_html__( 'Completed', 'guide-wp-theme' ) : esc_html__( 'Mark complete', 'guide-wp-theme' ); ?>
								</button>
							<?php elseif ( ! $guide_user_id ) : ?>
								<a class="button is-small is-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
									<?php esc_html_e( 'Sign in to track progress', 'guide-wp-theme' ); ?>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</header>

				<?php if ( 'quiz' === $guide_lesson_type && ! $guide_locked ) : ?>
					<div id="guide-quiz-app" class="guide-quiz" data-lesson-id="<?php echo esc_attr( (string) $guide_lesson_id ); ?>">
						<p class="guide-empty__text"><?php esc_html_e( 'Loading quiz…', 'guide-wp-theme' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $guide_locked ) : ?>
					<div class="guide-empty mt-5">
						<span class="guide-empty__icon"><?php echo guide_icon( 'lock-fill' ); ?></span>
						<p class="guide-empty__title"><?php esc_html_e( 'This lesson is part of a paid course', 'guide-wp-theme' ); ?></p>
						<?php if ( 'login_required' === $guide_lock_reason ) : ?>
							<p class="guide-empty__text"><?php esc_html_e( 'Sign in to check whether your plan already includes it.', 'guide-wp-theme' ); ?></p>
							<a class="button is-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?></a>
						<?php else : ?>
							<p class="guide-empty__text"><?php esc_html_e( 'Get this course once and every lesson in it unlocks — or subscribe for access to everything.', 'guide-wp-theme' ); ?></p>
							<?php if ( $guide_course_id ) : ?>
								<a class="button is-primary" href="<?php echo esc_url( get_permalink( $guide_course_id ) ); ?>"><?php esc_html_e( 'View plans', 'guide-wp-theme' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="guide-prose mt-4" data-toc-source><?php the_content(); ?></div>
				<?php endif; ?>

				<?php // Below the lesson, never inside it: an ad between two paragraphs of an explanation is the worst possible interruption. ?>
				<?php get_template_part( 'template-parts/feedback', null, array( 'object_type' => 'lesson' ) ); ?>

				<?php comments_template(); ?>

				<?php guide_ad( 'page' ); ?>

				<nav class="guide-lesson-nav" aria-label="<?php esc_attr_e( 'Lesson navigation', 'guide-wp-theme' ); ?>">
					<?php if ( $guide_adjacent['prev'] ) : ?>
						<a class="guide-lesson-nav__link" href="<?php echo esc_url( get_permalink( $guide_adjacent['prev'] ) ); ?>">
							<?php echo guide_icon( 'arrow-left' ); ?>
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'Previous', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php echo esc_html( get_the_title( $guide_adjacent['prev'] ) ); ?></span>
							</span>
						</a>
					<?php else : ?>
						<span class="guide-lesson-nav__spacer"></span>
					<?php endif; ?>

					<span class="guide-lesson-nav__spacer"></span>

					<?php if ( $guide_adjacent['next'] ) : ?>
						<a class="guide-lesson-nav__link guide-lesson-nav__link--next" href="<?php echo esc_url( get_permalink( $guide_adjacent['next'] ) ); ?>" id="guide-next-link">
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'Up next', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php echo esc_html( get_the_title( $guide_adjacent['next'] ) ); ?></span>
							</span>
							<?php echo guide_icon( 'arrow-right' ); ?>
						</a>
					<?php elseif ( $guide_course_id ) : ?>
						<a class="guide-lesson-nav__link guide-lesson-nav__link--next" href="<?php echo esc_url( get_permalink( $guide_course_id ) ); ?>">
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'End of course', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php esc_html_e( 'Back to overview', 'guide-wp-theme' ); ?></span>
							</span>
							<?php echo guide_icon( 'trophy-fill' ); ?>
						</a>
					<?php endif; ?>
				</nav>
			</div>
		</article>

		<?php
		// Contents rail.
		//
		// Populated by the shared TOC script from the headings in the lesson,
		// and hidden outright when there are fewer than three of them — a
		// two-heading lesson does not need a table of contents, it needs
		// reading.
		//
		// It is a third column rather than an addition to the left sidebar
		// because that sidebar is the course: twenty lesson links, already
		// scrolling. Putting "where am I in this page" inside "where am I in
		// this course" makes both harder to read.
		?>
		<aside class="guide-player__toc guide-toc" data-toc
			aria-label="<?php esc_attr_e( 'On this page', 'guide-wp-theme' ); ?>">
			<p class="guide-filter-group__label"><?php esc_html_e( 'On this page', 'guide-wp-theme' ); ?></p>
			<nav class="guide-toc__list" data-toc-list></nav>
		</aside>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'guide-lesson', GUIDE_THEME_URI . '/assets/js/lesson.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/lesson.js' ), true );
wp_localize_script(
	'guide-lesson',
	'guideLesson',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'completed'   => __( 'Lesson complete', 'guide-wp-theme' ),
			'uncompleted' => __( 'Marked as not complete', 'guide-wp-theme' ),
			'undo'        => __( 'Undo', 'guide-wp-theme' ),
			'failed'      => __( 'Could not save — check your connection', 'guide-wp-theme' ),
		),
	)
);

get_footer( 'minimal' );
