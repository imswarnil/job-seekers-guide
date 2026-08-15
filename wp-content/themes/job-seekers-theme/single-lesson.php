<?php
/**
 * Lesson player — full-width app shell (no marketing container/footer).
 *
 * Layout: fixed-width course sidebar (progress + module accordions,
 * mobile: slide-over drawer) + fluid main column that uses the full
 * remaining viewport width. Prose is capped at a readable measure but
 * anchored left inside the shell, not centered on the page.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$lesson_id = get_the_ID();
	$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
	$has_api   = class_exists( 'JSL\\Course_Api' );
	$modules   = $course_id && $has_api ? \JSL\Course_Api::get_modules( $course_id ) : array();
	$adjacent  = $course_id && $has_api ? \JSL\Course_Api::adjacent_lessons( $course_id, $lesson_id ) : array( 'prev' => null, 'next' => null );

	$user_id   = get_current_user_id();
	$completed = $user_id && $course_id && class_exists( 'JSL\\Progress\\Progress' ) ? \JSL\Progress\Progress::completed_lesson_ids( $user_id, $course_id ) : array();
	$total     = 0;
	foreach ( $modules as $module ) {
		$total += count( $module['lessons'] );
	}
	$percent = $total > 0 ? (int) round( count( $completed ) / $total * 100 ) : 0;
	$is_done = in_array( $lesson_id, $completed, true );

	$video    = class_exists( 'JSL\\Lesson_Meta' ) ? \JSL\Lesson_Meta::embed_info( (string) get_post_meta( $lesson_id, 'jsl_video_url', true ) ) : null;
	$duration = (int) get_post_meta( $lesson_id, 'jsl_duration_minutes', true );

	// Position of this lesson in the flat course order (for "Lesson N of M").
	$position = 0;
	if ( $course_id && $has_api ) {
		foreach ( \JSL\Course_Api::get_lessons_flat( $course_id ) as $i => $flat_lesson ) {
			if ( (int) $flat_lesson->ID === $lesson_id ) {
				$position = $i + 1;
				break;
			}
		}
	}
	?>

	<div class="flex min-h-[calc(100vh-3.5rem)] items-stretch">

		<!-- Course sidebar (desktop: static column, mobile: slide-over) -->
		<aside class="fixed inset-y-0 left-0 z-40 w-[300px] -translate-x-full border-r border-line bg-raised transition-transform duration-200 lg:sticky lg:top-[3.5rem] lg:z-10 lg:h-[calc(100vh-3.5rem)] lg:translate-x-0 lg:self-start flex flex-col" data-player-nav>
			<div class="flex items-center justify-between border-b border-line bg-subtle/60 p-4 lg:hidden">
				<span class="text-sm font-bold"><?php esc_html_e( 'Course content', 'job-seekers-theme' ); ?></span>
				<button type="button" class="grid h-8 w-8 place-items-center rounded-md text-ink-muted hover:bg-subtle" data-player-nav-close aria-label="<?php esc_attr_e( 'Close', 'job-seekers-theme' ); ?>"><?php echo jsl_icon( 'x', 'w-4 h-4' ); ?></button>
			</div>

			<?php if ( $course_id ) : ?>
				<div class="border-b border-line p-4">
					<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Course', 'job-seekers-theme' ); ?></p>
					<a class="mt-0.5 block text-sm font-bold leading-snug text-ink no-underline hover:text-accent" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>

					<?php if ( $user_id && $total ) : ?>
						<div class="mt-3">
							<div class="flex items-center justify-between text-[0.7rem] font-semibold text-ink-muted">
								<span data-progress-label><?php printf( esc_html__( '%1$d / %2$d complete', 'job-seekers-theme' ), count( $completed ), (int) $total ); ?></span>
								<span class="text-accent" data-progress-percent><?php echo esc_html( $percent ); ?>%</span>
							</div>
							<div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-inset">
								<div class="h-full rounded-full bg-accent transition-all duration-500" data-progress-bar style="width:<?php echo esc_attr( $percent ); ?>%"></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<nav class="flex-1 overflow-y-auto pb-6" aria-label="<?php esc_attr_e( 'Course lessons', 'job-seekers-theme' ); ?>">
				<?php
				foreach ( $modules as $module ) :
					$module_has_current = false;
					foreach ( $module['lessons'] as $mod_lesson ) {
						if ( (int) $mod_lesson->ID === $lesson_id ) {
							$module_has_current = true;
							break;
						}
					}
					?>
					<details class="group border-b border-line" <?php echo $module_has_current ? 'open' : ''; ?>>
						<summary class="flex cursor-pointer list-none items-center gap-2.5 px-4 py-2.5 text-[0.8rem] font-semibold text-ink hover:bg-subtle [&::-webkit-details-marker]:hidden">
							<span class="flex-1"><?php echo esc_html( $module['title'] ); ?></span>
							<span class="text-ink-muted transition-transform group-open:rotate-90"><?php echo jsl_icon( 'arrow-r', 'w-3.5 h-3.5' ); ?></span>
						</summary>
						<ol class="m-0 list-none p-0 pb-1.5">
							<?php
							foreach ( $module['lessons'] as $mod_lesson ) :
								$row_done    = in_array( (int) $mod_lesson->ID, $completed, true );
								$row_current = (int) $mod_lesson->ID === $lesson_id;
								$row_min     = (int) get_post_meta( $mod_lesson->ID, 'jsl_duration_minutes', true );
								?>
								<li>
									<a class="flex items-center gap-2.5 px-4 py-1.5 text-[0.8rem] no-underline <?php echo $row_current ? 'border-r-2 border-accent bg-accent-softer font-semibold text-accent' : 'text-ink-secondary hover:bg-subtle'; ?>"
										href="<?php echo esc_url( get_permalink( $mod_lesson ) ); ?>" <?php echo $row_current ? 'aria-current="page"' : ''; ?>
										data-lesson-row="<?php echo esc_attr( $mod_lesson->ID ); ?>">
										<span class="grid h-4.5 w-4.5 shrink-0 place-items-center rounded-full <?php echo $row_done ? 'bg-accent text-on-accent' : 'border border-line-strong text-ink-muted'; ?>" data-lesson-dot>
											<?php echo $row_done ? jsl_icon( 'check', 'w-2.5 h-2.5' ) : ( $row_current ? jsl_icon( 'play', 'w-2 h-2' ) : '' ); ?>
										</span>
										<span class="flex-1 truncate"><?php echo esc_html( get_the_title( $mod_lesson ) ); ?></span>
										<?php if ( $row_min ) : ?>
											<span class="text-[0.65rem] text-ink-muted"><?php echo esc_html( $row_min ); ?>m</span>
										<?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ol>
					</details>
				<?php endforeach; ?>
			</nav>
		</aside>

		<div class="fixed inset-0 z-30 hidden bg-ink-950/50 lg:hidden" data-player-scrim></div>

		<!-- Main column -->
		<article class="min-w-0 flex-1">
			<!-- Player toolbar -->
			<div class="sticky top-[3.5rem] z-20 flex items-center gap-3 border-b border-line bg-surface/90 px-4 py-2 backdrop-blur-md md:px-8">
				<button type="button" class="grid h-8 w-8 shrink-0 cursor-pointer place-items-center rounded-md border border-line text-ink lg:hidden" data-player-nav-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Course content', 'job-seekers-theme' ); ?>">
					<?php echo jsl_icon( 'layers', 'w-4 h-4' ); ?>
				</button>

				<?php if ( $course_id ) : ?>
					<a class="hidden items-center gap-1.5 truncate text-sm font-medium text-ink-muted hover:text-accent sm:inline-flex" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">
						<?php echo jsl_icon( 'arrow-l', 'w-4 h-4' ); ?>
						<span class="truncate"><?php echo esc_html( get_the_title( $course_id ) ); ?></span>
					</a>
					<?php if ( $position && $total ) : ?>
						<span class="text-xs text-ink-muted"><?php printf( esc_html__( 'Lesson %1$d of %2$d', 'job-seekers-theme' ), (int) $position, (int) $total ); ?></span>
					<?php endif; ?>
				<?php endif; ?>

				<div class="ml-auto flex items-center gap-2">
					<?php if ( $user_id && $course_id ) : ?>
						<button type="button"
							class="jsl-btn jsl-btn--sm <?php echo $is_done ? 'jsl-btn--ghost' : 'jsl-btn--primary'; ?>"
							id="jsl-complete-btn"
							data-lesson-id="<?php echo esc_attr( $lesson_id ); ?>"
							data-completed="<?php echo $is_done ? '1' : '0'; ?>"
							data-label-done="<?php esc_attr_e( 'Completed ✓', 'job-seekers-theme' ); ?>"
							data-label-todo="<?php esc_attr_e( 'Mark complete', 'job-seekers-theme' ); ?>">
							<?php echo $is_done ? esc_html__( 'Completed ✓', 'job-seekers-theme' ) : esc_html__( 'Mark complete', 'job-seekers-theme' ); ?>
						</button>
					<?php elseif ( ! $user_id ) : ?>
						<a class="jsl-btn jsl-btn--primary jsl-btn--sm" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log in to track progress', 'job-seekers-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="px-4 py-6 md:px-8 lg:px-12">
				<?php if ( $video ) : ?>
					<div class="overflow-hidden rounded-xl bg-ink-950 shadow-lg">
						<div class="relative aspect-video max-h-[72vh]">
							<?php if ( 'video' === $video['type'] ) : ?>
								<video class="absolute inset-0 h-full w-full" src="<?php echo esc_url( $video['src'] ); ?>" controls playsinline></video>
							<?php else : ?>
								<iframe class="absolute inset-0 h-full w-full border-0" src="<?php echo esc_url( $video['src'] ); ?>" title="<?php the_title_attribute(); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<header class="<?php echo $video ? 'mt-6' : ''; ?>">
					<h1 class="m-0 text-2xl font-extrabold tracking-tight md:text-3xl"><?php the_title(); ?></h1>
					<?php if ( $duration ) : ?>
						<p class="mt-1 inline-flex items-center gap-1.5 text-sm text-ink-muted"><?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?><?php printf( esc_html__( '%d min', 'job-seekers-theme' ), $duration ); ?></p>
					<?php endif; ?>
				</header>

				<div class="jsl-prose mt-5 max-w-[76ch]"><?php the_content(); ?></div>
			</div>

			<!-- Prev / next bar -->
			<nav class="mt-auto border-t border-line bg-raised" aria-label="<?php esc_attr_e( 'Lesson navigation', 'job-seekers-theme' ); ?>">
				<div class="flex items-stretch justify-between gap-4 px-4 py-4 md:px-8 lg:px-12">
					<?php if ( $adjacent['prev'] ) : ?>
						<a class="group flex min-w-0 items-center gap-3 no-underline" href="<?php echo esc_url( get_permalink( $adjacent['prev'] ) ); ?>">
							<span class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-line text-ink-muted transition group-hover:border-accent group-hover:text-accent"><?php echo jsl_icon( 'arrow-l', 'w-4 h-4' ); ?></span>
							<span class="hidden min-w-0 sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-ink-muted"><?php esc_html_e( 'Previous', 'job-seekers-theme' ); ?></span>
								<span class="block max-w-[16rem] truncate text-sm font-semibold text-ink group-hover:text-accent"><?php echo esc_html( get_the_title( $adjacent['prev'] ) ); ?></span>
							</span>
						</a>
					<?php else : ?>
						<span></span>
					<?php endif; ?>

					<?php if ( $adjacent['next'] ) : ?>
						<a class="group flex min-w-0 items-center gap-3 text-right no-underline" href="<?php echo esc_url( get_permalink( $adjacent['next'] ) ); ?>" id="jsl-next-link">
							<span class="hidden min-w-0 sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-ink-muted"><?php esc_html_e( 'Up next', 'job-seekers-theme' ); ?></span>
								<span class="block max-w-[16rem] truncate text-sm font-semibold text-ink group-hover:text-accent"><?php echo esc_html( get_the_title( $adjacent['next'] ) ); ?></span>
							</span>
							<span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-accent text-on-accent shadow-accent transition group-hover:bg-accent-strong"><?php echo jsl_icon( 'arrow-r', 'w-4 h-4' ); ?></span>
						</a>
					<?php elseif ( $course_id ) : ?>
						<a class="group flex items-center gap-3 text-right no-underline" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">
							<span class="hidden sm:block">
								<span class="block text-[0.65rem] font-bold uppercase tracking-wider text-accent"><?php esc_html_e( 'End of course', 'job-seekers-theme' ); ?></span>
								<span class="block text-sm font-semibold text-ink"><?php esc_html_e( 'Back to overview', 'job-seekers-theme' ); ?></span>
							</span>
							<span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-accent text-on-accent"><?php echo jsl_icon( 'check', 'w-4 h-4' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</nav>
		</article>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'jsl-lesson', JSL_THEME_URI . '/assets/js/lesson.js', array(), JSL_THEME_VERSION, true );
wp_localize_script(
	'jsl-lesson',
	'jslLesson',
	array(
		'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	)
);

get_footer( 'minimal' );
