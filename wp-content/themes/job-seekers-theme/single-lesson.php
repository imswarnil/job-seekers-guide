<?php
/**
 * Lesson player.
 *
 * Layout is the Material 3 "supporting pane" shape: a navigation drawer
 * holding the curriculum — modal on compact windows, standard (always
 * visible) from 1024px up — beside the lesson itself.
 *
 * Access is decided by the plugin, never here. The template only chooses
 * which state to draw; a locked lesson's body has already been stripped
 * server-side by JSL\Access\Access.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'player' );

while ( have_posts() ) :
	the_post();
	$lesson_id = get_the_ID();
	$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
	$has_api   = class_exists( 'JSL\\Course_Api' );
	$modules   = $course_id && $has_api ? \JSL\Course_Api::get_modules( $course_id ) : array();
	$adjacent  = $course_id && $has_api ? \JSL\Course_Api::adjacent_lessons( $course_id, $lesson_id ) : array( 'prev' => null, 'next' => null );

	$user_id   = get_current_user_id();
	$completed = $user_id && $course_id && class_exists( 'JSL\\Progress\\Progress' ) ? \JSL\Progress\Progress::completed_lesson_ids( $user_id, $course_id ) : array();

	$total = 0;
	foreach ( $modules as $module ) {
		$total += count( $module['lessons'] );
	}

	$percent = $total > 0 ? (int) round( count( $completed ) / $total * 100 ) : 0;
	$is_done = in_array( $lesson_id, $completed, true );

	$position = 0;
	if ( $course_id && $has_api ) {
		foreach ( \JSL\Course_Api::get_lessons_flat( $course_id ) as $i => $flat_lesson ) {
			if ( (int) $flat_lesson->ID === $lesson_id ) {
				$position = $i + 1;
				break;
			}
		}
	}

	$lock_reason = class_exists( 'JSL\\Access\\Access' ) ? \JSL\Access\Access::lesson_denial_reason( $lesson_id ) : 'ok';
	$locked      = ! in_array( $lock_reason, array( 'ok', 'not_found' ), true );

	$video_start = (int) get_post_meta( $lesson_id, 'jsl_video_start', true );
	$video_end   = (int) get_post_meta( $lesson_id, 'jsl_video_end', true );
	$video       = class_exists( 'JSL\\Lesson_Meta' ) ? \JSL\Lesson_Meta::embed_info( (string) get_post_meta( $lesson_id, 'jsl_video_url', true ), $video_start, $video_end ) : null;
	$duration    = (int) get_post_meta( $lesson_id, 'jsl_duration_minutes', true );
	$lesson_type = get_post_meta( $lesson_id, 'jsl_lesson_type', true ) ?: ( $video ? 'video' : 'article' );
	$poster      = get_the_post_thumbnail_url( $lesson_id, 'large' )
		?: ( $video && ! empty( $video['poster'] ) ? $video['poster'] : ( class_exists( 'JSL\\Media\\Placeholder' ) ? \JSL\Media\Placeholder::lesson( $lesson_id, $position ) : '' ) );
	?>

	<div class="flex items-stretch">

		<div class="md-scrim lg:hidden" data-drawer-scrim="curriculum"></div>

		<!-- Curriculum: modal drawer on compact, standard drawer from lg up -->
		<aside class="md-drawer md-drawer--standard" id="jsl-curriculum-drawer" data-drawer="curriculum"
			aria-label="<?php esc_attr_e( 'Course content', 'job-seekers-theme' ); ?>">

			<div class="md-drawer__modal-only flex items-center justify-between px-4 pt-3 lg:hidden">
				<span class="font-display text-sm font-bold"><?php esc_html_e( 'Course content', 'job-seekers-theme' ); ?></span>
				<button type="button" class="md-icon-btn" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'job-seekers-theme' ); ?>">
					<?php echo jsl_icon( 'x', 'w-5 h-5' ); ?>
				</button>
			</div>

			<?php if ( $course_id ) : ?>
				<div class="border-b border-outline-variant px-6 py-4">
					<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-on-surface-variant"><?php esc_html_e( 'Course', 'job-seekers-theme' ); ?></p>
					<a class="mt-1 block font-display text-sm font-bold leading-snug text-on-surface no-underline hover:text-primary" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">
						<?php echo esc_html( get_the_title( $course_id ) ); ?>
					</a>

					<?php if ( $user_id && $total ) : ?>
						<div class="mt-3">
							<div class="flex items-center justify-between text-[0.7rem] font-semibold text-on-surface-variant">
								<span data-progress-label><?php printf( esc_html__( '%1$d / %2$d complete', 'job-seekers-theme' ), count( $completed ), (int) $total ); ?></span>
								<span class="text-primary" data-progress-percent><?php echo esc_html( $percent ); ?>%</span>
							</div>
							<div class="md-linear-progress mt-2">
								<div class="md-linear-progress__bar" data-progress-bar style="width:<?php echo esc_attr( $percent ); ?>%"></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<nav class="flex-1 overflow-y-auto pb-6" aria-label="<?php esc_attr_e( 'Course lessons', 'job-seekers-theme' ); ?>">
				<?php
				foreach ( $modules as $module_index => $module ) :
					$module_has_current = false;
					foreach ( $module['lessons'] as $mod_lesson ) {
						if ( (int) $mod_lesson->ID === $lesson_id ) {
							$module_has_current = true;
							break;
						}
					}
					?>
					<details class="group border-b border-outline-variant" <?php echo $module_has_current ? 'open' : ''; ?>>
						<summary class="flex cursor-pointer list-none items-center gap-3 px-6 py-3 text-[0.8rem] font-bold text-on-surface hover:bg-surface-container [&::-webkit-details-marker]:hidden">
							<span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-surface-high font-mono text-[0.65rem] text-on-surface-variant">
								<?php echo esc_html( $module_index + 1 ); ?>
							</span>
							<span class="flex-1"><?php echo esc_html( $module['title'] ); ?></span>
							<span class="text-on-surface-variant transition-transform duration-200 group-open:rotate-90"><?php echo jsl_icon( 'caret-right', 'w-4 h-4' ); ?></span>
						</summary>

						<ol class="md-list !py-0">
							<?php
							foreach ( $module['lessons'] as $mod_lesson ) :
								$row_done    = in_array( (int) $mod_lesson->ID, $completed, true );
								$row_current = (int) $mod_lesson->ID === $lesson_id;
								$row_min     = (int) get_post_meta( $mod_lesson->ID, 'jsl_duration_minutes', true );
								$row_locked  = class_exists( 'JSL\\Access\\Access' ) && \JSL\Access\Access::is_locked( (int) $mod_lesson->ID );
								?>
								<li>
									<a class="md-list-item !min-h-[48px] !py-1.5 !pl-6 !pr-4"
										href="<?php echo esc_url( get_permalink( $mod_lesson ) ); ?>"
										<?php echo $row_current ? 'aria-current="page"' : ''; ?>
										data-lesson-row="<?php echo esc_attr( $mod_lesson->ID ); ?>">

										<span class="md-list-item__leading grid h-5 w-5 shrink-0 place-items-center rounded-full <?php echo $row_done ? 'bg-primary text-on-primary' : 'border border-outline text-on-surface-variant'; ?>" data-lesson-dot>
											<?php echo $row_done ? jsl_icon( 'check', 'w-3 h-3' ) : ( $row_current ? jsl_icon( 'play-fill', 'w-2.5 h-2.5' ) : '' ); ?>
										</span>

										<span class="md-list-item__content">
											<span class="block truncate text-[0.82rem] <?php echo $row_current ? 'font-bold' : 'font-medium'; ?>"><?php echo esc_html( get_the_title( $mod_lesson ) ); ?></span>
										</span>

										<span class="md-list-item__trailing flex items-center gap-1.5">
											<?php if ( $row_locked ) : ?>
												<?php echo jsl_icon( 'lock-simple', 'w-3.5 h-3.5' ); ?>
											<?php endif; ?>
											<?php if ( $row_min ) : ?>
												<span class="text-[0.65rem]"><?php echo esc_html( $row_min ); ?>m</span>
											<?php endif; ?>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ol>
					</details>
				<?php endforeach; ?>
			</nav>
		</aside>

		<!-- Lesson -->
		<article class="min-w-0 flex-1">

			<!-- Lesson action bar. Course title, position and progress live in
			     the app bar above; this holds only the action for THIS lesson. -->
			<div class="sticky top-16 z-20 flex items-center gap-3 border-b border-outline-variant bg-surface/90 px-4 py-2 backdrop-blur-md md:px-8">
				<?php if ( $duration ) : ?>
					<span class="md-chip md-chip--static !h-8 !px-3">
						<?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?>
						<?php printf( esc_html__( '%d min', 'job-seekers-theme' ), (int) $duration ); ?>
					</span>
				<?php endif; ?>

				<span class="md-chip md-chip--static !h-8 !px-3">
					<?php echo jsl_icon( 'quiz' === $lesson_type ? 'question' : ( 'video' === $lesson_type ? 'film-strip' : 'article' ), 'w-4 h-4' ); ?>
					<?php
					echo 'quiz' === $lesson_type
						? esc_html__( 'Quiz', 'job-seekers-theme' )
						: ( 'video' === $lesson_type ? esc_html__( 'Video', 'job-seekers-theme' ) : esc_html__( 'Article', 'job-seekers-theme' ) );
					?>
				</span>

				<div class="ml-auto flex items-center gap-2">
					<?php if ( $user_id && $course_id && ! $locked ) : ?>
						<button type="button"
							class="jsl-btn jsl-btn--sm <?php echo $is_done ? 'jsl-btn--tonal' : 'jsl-btn--primary'; ?>"
							id="jsl-complete-btn"
							data-lesson-id="<?php echo esc_attr( $lesson_id ); ?>"
							data-completed="<?php echo $is_done ? '1' : '0'; ?>"
							data-label-done="<?php esc_attr_e( 'Completed', 'job-seekers-theme' ); ?>"
							data-label-todo="<?php esc_attr_e( 'Mark complete', 'job-seekers-theme' ); ?>">
							<?php echo $is_done ? esc_html__( 'Completed', 'job-seekers-theme' ) : esc_html__( 'Mark complete', 'job-seekers-theme' ); ?>
						</button>
					<?php elseif ( ! $user_id ) : ?>
						<a class="jsl-btn jsl-btn--primary jsl-btn--sm" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in to track progress', 'job-seekers-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="px-4 py-6 md:px-8 lg:px-12">

				<?php if ( $video && 'quiz' !== $lesson_type && ! $locked ) : ?>
					<div class="jsl-player group relative overflow-hidden rounded-xl bg-black shadow-lg"
						data-embed-type="<?php echo esc_attr( $video['type'] ); ?>"
						data-embed-src="<?php echo esc_url( $video['src'] ); ?>"
						data-start="<?php echo esc_attr( $video['start'] ); ?>"
						data-end="<?php echo esc_attr( $video['end'] ); ?>"
						data-title="<?php the_title_attribute(); ?>">
						<div class="relative aspect-video max-h-[72vh]">
							<?php if ( $poster ) : ?>
								<img class="absolute inset-0 h-full w-full object-cover opacity-70" src="<?php echo jsl_img_src( $poster ); ?>" alt="" loading="lazy">
							<?php endif; ?>
							<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30"></div>
							<button type="button" class="jsl-player__play absolute inset-0 grid w-full cursor-pointer place-items-center border-0 bg-transparent" aria-label="<?php esc_attr_e( 'Play video', 'job-seekers-theme' ); ?>">
								<span class="md-fab !h-20 !w-20 !rounded-full transition-transform duration-200 group-hover:scale-110"><?php echo jsl_icon( 'play-fill', 'w-8 h-8' ); ?></span>
							</button>
							<?php if ( $video['start'] || $video['end'] ) : ?>
								<span class="absolute bottom-4 left-4 rounded-full bg-black/70 px-3 py-1 font-mono text-xs text-white backdrop-blur">
									<?php echo esc_html( gmdate( 'i:s', $video['start'] ) . ( $video['end'] ? ' – ' . gmdate( 'i:s', $video['end'] ) : '+' ) ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( 'quiz' === $lesson_type && ! $locked ) : ?>
					<div id="jsl-quiz-app" class="md-card md-card--filled p-6 md:p-8" data-lesson-id="<?php echo esc_attr( $lesson_id ); ?>">
						<p class="m-0 flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="md-circular-progress !h-5 !w-5 !border-2"></span>
							<?php esc_html_e( 'Loading quiz…', 'job-seekers-theme' ); ?>
						</p>
					</div>
				<?php endif; ?>

				<header class="<?php echo $video && ! $locked ? 'mt-6' : ''; ?>">
					<h1 class="m-0 font-display text-2xl font-extrabold tracking-tight md:text-3xl"><?php the_title(); ?></h1>
				</header>

				<?php if ( $locked ) : ?>
					<div class="md-card md-card--filled mt-6 max-w-[46rem] p-8 text-center">
						<span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-primary-container text-on-primary-container">
							<?php echo jsl_icon( 'lock-fill', 'w-7 h-7' ); ?>
						</span>
						<h2 class="mt-5 font-display text-xl font-bold"><?php esc_html_e( 'This lesson is part of a paid course', 'job-seekers-theme' ); ?></h2>
						<?php if ( 'login_required' === $lock_reason ) : ?>
							<p class="mt-2 text-on-surface-variant"><?php esc_html_e( 'Sign in to check whether your plan already includes it.', 'job-seekers-theme' ); ?></p>
							<a class="jsl-btn jsl-btn--primary mt-6" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a>
						<?php else : ?>
							<p class="mt-2 text-on-surface-variant"><?php esc_html_e( 'Get this course once and every lesson in it unlocks — or subscribe for access to everything on the platform.', 'job-seekers-theme' ); ?></p>
							<?php if ( $course_id ) : ?>
								<a class="jsl-btn jsl-btn--primary mt-6" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php esc_html_e( 'View plans', 'job-seekers-theme' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="jsl-prose mt-5 max-w-[76ch]"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>

			<!-- Prev / next -->
			<nav class="mt-auto border-t border-outline-variant bg-surface-container-low" aria-label="<?php esc_attr_e( 'Lesson navigation', 'job-seekers-theme' ); ?>">
				<div class="flex items-stretch justify-between gap-4 px-4 py-4 md:px-8 lg:px-12">
					<?php if ( $adjacent['prev'] ) : ?>
						<a class="group flex min-w-0 items-center gap-3 no-underline" href="<?php echo esc_url( get_permalink( $adjacent['prev'] ) ); ?>">
							<span class="md-icon-btn md-icon-btn--outlined"><?php echo jsl_icon( 'arrow-left', 'w-5 h-5' ); ?></span>
							<span class="hidden min-w-0 sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-on-surface-variant"><?php esc_html_e( 'Previous', 'job-seekers-theme' ); ?></span>
								<span class="block max-w-[16rem] truncate text-sm font-semibold text-on-surface group-hover:text-primary"><?php echo esc_html( get_the_title( $adjacent['prev'] ) ); ?></span>
							</span>
						</a>
					<?php else : ?>
						<span></span>
					<?php endif; ?>

					<?php if ( $adjacent['next'] ) : ?>
						<a class="group flex min-w-0 items-center gap-3 text-right no-underline" href="<?php echo esc_url( get_permalink( $adjacent['next'] ) ); ?>" id="jsl-next-link">
							<span class="hidden min-w-0 sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-on-surface-variant"><?php esc_html_e( 'Up next', 'job-seekers-theme' ); ?></span>
								<span class="block max-w-[16rem] truncate text-sm font-semibold text-on-surface group-hover:text-primary"><?php echo esc_html( get_the_title( $adjacent['next'] ) ); ?></span>
							</span>
							<span class="md-fab md-fab--small"><?php echo jsl_icon( 'arrow-right', 'w-5 h-5' ); ?></span>
						</a>
					<?php elseif ( $course_id ) : ?>
						<a class="group flex items-center gap-3 text-right no-underline" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">
							<span class="hidden sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-primary"><?php esc_html_e( 'End of course', 'job-seekers-theme' ); ?></span>
								<span class="block text-sm font-semibold text-on-surface"><?php esc_html_e( 'Back to overview', 'job-seekers-theme' ); ?></span>
							</span>
							<span class="md-fab md-fab--small"><?php echo jsl_icon( 'trophy-fill', 'w-5 h-5' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</nav>
		</article>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'jsl-lesson', JSL_THEME_URI . '/assets/js/lesson.js', array( 'jsl-md3' ), jsl_asset_version( '/assets/js/lesson.js' ), true );
wp_localize_script(
	'jsl-lesson',
	'jslLesson',
	array(
		'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'completed'   => __( 'Lesson complete', 'job-seekers-theme' ),
			'uncompleted' => __( 'Marked as not complete', 'job-seekers-theme' ),
			'undo'        => __( 'Undo', 'job-seekers-theme' ),
			'failed'      => __( 'Could not save — check your connection', 'job-seekers-theme' ),
		),
	)
);

get_footer( 'minimal' );
