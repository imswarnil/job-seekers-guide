<?php
/**
 * Course landing page: dark hero with meta chips, sticky enroll card,
 * accordion curriculum with per-lesson duration/preview/completion state.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$course_id = get_the_ID();
	$has_api   = class_exists( 'JSL\\Course_Api' );
	$modules   = $has_api ? \JSL\Course_Api::get_modules( $course_id ) : array();
	$stats     = $has_api ? \JSL\Course_Api::get_stats( $course_id ) : array( 'modules' => 0, 'lessons' => 0, 'minutes' => 0 );
	$is_paid   = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $course_id );
	$price     = $is_paid ? \JSL\Payments\Course_Pricing::price_label( $course_id ) : '';

	$user_id = get_current_user_id();

	// "Enrolled" is no longer the only way in: a platform subscription (or
	// being staff) grants access too. Ask the access layer, not the
	// enrollment table, so every surface agrees.
	$has_access  = class_exists( 'JSL\\Access\\Access' )
		? \JSL\Access\Access::can_view_course_content( $course_id, $user_id )
		: true;
	$is_enrolled = $user_id && class_exists( 'JSL\\Enrollment\\Enrollment' ) && \JSL\Enrollment\Enrollment::is_enrolled( $user_id, $course_id );
	// Access to a paid course without having bought this specific course
	// means it came from a subscription (or staff privileges).
	$via_plan = $has_access && $is_paid && ! $is_enrolled;

	$subscription_on = class_exists( 'JSL\\Payments\\Subscription' ) && \JSL\Payments\Subscription::is_enabled();
	$sub_price       = $subscription_on ? \JSL\Payments\Subscription::price_label() : '';
	$completed   = $user_id && class_exists( 'JSL\\Progress\\Progress' ) ? \JSL\Progress\Progress::completed_lesson_ids( $user_id, $course_id ) : array();
	$progress    = $stats['lessons'] > 0 ? (int) round( count( $completed ) / $stats['lessons'] * 100 ) : 0;

	$first_lesson = $has_api ? ( \JSL\Course_Api::get_lessons_flat( $course_id )[0] ?? null ) : null;
	$resume       = $first_lesson;
	if ( $has_api && ! empty( $completed ) ) {
		foreach ( \JSL\Course_Api::get_lessons_flat( $course_id ) as $candidate ) {
			if ( ! in_array( (int) $candidate->ID, $completed, true ) ) {
				$resume = $candidate;
				break;
			}
		}
	}
	?>

	<section class="bg-hero text-on-hero">
		<div class="jsl-container grid items-start gap-10 py-14 md:grid-cols-[1fr_22rem] md:py-20">
			<div>
				<nav class="text-sm text-hero-muted" aria-label="<?php esc_attr_e( 'Breadcrumb', 'job-seekers-theme' ); ?>">
					<a class="text-hero-muted hover:text-on-hero" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'job-seekers-theme' ); ?></a>
					<span class="mx-1.5">/</span>
					<a class="text-hero-muted hover:text-on-hero" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Courses', 'job-seekers-theme' ); ?></a>
				</nav>

				<?php $course_code = class_exists( 'JSL\\Course_Meta' ) ? \JSL\Course_Meta::get_code( $course_id ) : ''; ?>
				<div class="mt-4 flex items-center gap-3">
					<?php if ( $course_code ) : ?>
						<span class="rounded-md border border-white/15 bg-white/5 px-2.5 py-1 font-mono text-xs font-semibold tracking-wider text-signal-300"><?php echo esc_html( $course_code ); ?></span>
					<?php endif; ?>
					<span class="jsl-badge <?php echo $is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>"><?php echo $is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?></span>
					<?php if ( $is_enrolled || $via_plan ) : ?>
						<span class="jsl-badge jsl-badge--free"><?php echo jsl_icon( 'check', 'w-3 h-3' ); ?> <?php echo $via_plan ? esc_html__( 'In your plan', 'job-seekers-theme' ) : esc_html__( 'Enrolled', 'job-seekers-theme' ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="mt-4 text-[clamp(2rem,1.4rem+2.5vw,3rem)] font-extrabold leading-tight tracking-tight"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="mt-4 max-w-2xl text-lg text-hero-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="mt-6 flex flex-wrap gap-3 text-sm text-hero-muted">
					<span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5"><?php echo jsl_icon( 'layers', 'w-4 h-4' ); ?><?php printf( esc_html( _n( '%d module', '%d modules', $stats['modules'], 'job-seekers-theme' ) ), (int) $stats['modules'] ); ?></span>
					<span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5"><?php echo jsl_icon( 'doc', 'w-4 h-4' ); ?><?php printf( esc_html( _n( '%d lesson', '%d lessons', $stats['lessons'], 'job-seekers-theme' ) ), (int) $stats['lessons'] ); ?></span>
					<?php if ( $stats['minutes'] ) : ?>
						<span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5"><?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?><?php printf( esc_html__( '%d min total', 'job-seekers-theme' ), (int) $stats['minutes'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $user_id && $has_access && $stats['lessons'] ) : ?>
					<div class="mt-7 max-w-md">
						<div class="flex items-center justify-between text-xs font-semibold text-hero-muted">
							<span><?php printf( esc_html__( '%1$d of %2$d lessons complete', 'job-seekers-theme' ), count( $completed ), (int) $stats['lessons'] ); ?></span>
							<span class="text-signal-300"><?php echo esc_html( $progress ); ?>%</span>
						</div>
						<div class="mt-2 h-2 overflow-hidden rounded-full bg-white/10">
							<div class="h-full rounded-full bg-signal-500 transition-all" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Enroll card -->
			<aside class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm md:sticky md:top-24" id="jsl-enroll-box" data-course-id="<?php echo esc_attr( $course_id ); ?>">
				<?php if ( $via_plan ) : ?>
					<p class="m-0 inline-flex items-center gap-2 text-lg font-extrabold text-on-hero">
						<?php echo jsl_icon( 'check-circle-fill', 'w-5 h-5' ); ?>
						<?php esc_html_e( 'Included in your plan', 'job-seekers-theme' ); ?>
					</p>
					<p class="mt-1 text-sm text-hero-muted"><?php esc_html_e( 'Your subscription covers this course.', 'job-seekers-theme' ); ?></p>
				<?php else : ?>
					<p class="m-0 text-3xl font-extrabold text-on-hero">
						<?php echo $is_paid ? esc_html( $price ?: '—' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
					</p>
					<p class="mt-1 text-sm text-hero-muted"><?php echo $is_paid ? esc_html__( 'One-time payment. Buy once and every lesson in this course unlocks.', 'job-seekers-theme' ) : esc_html__( 'Full access, no card required.', 'job-seekers-theme' ); ?></p>
				<?php endif; ?>

				<div class="mt-5 flex flex-col gap-3">
					<?php if ( $has_access && $resume ) : ?>
						<a class="jsl-btn jsl-btn--primary w-full" href="<?php echo esc_url( get_permalink( $resume ) ); ?>">
							<?php echo jsl_icon( 'play', 'w-4.5 h-4.5' ); ?>
							<?php echo $progress > 0 ? esc_html__( 'Continue learning', 'job-seekers-theme' ) : esc_html__( 'Start course', 'job-seekers-theme' ); ?>
						</a>
					<?php elseif ( is_user_logged_in() ) : ?>
						<button type="button" class="jsl-btn jsl-btn--primary w-full" id="jsl-enroll-btn">
							<?php echo $is_paid ? esc_html__( 'Get this course', 'job-seekers-theme' ) : esc_html__( 'Start free', 'job-seekers-theme' ); ?>
						</button>
						<p class="m-0 min-h-5 text-center text-sm text-hero-muted" id="jsl-enroll-status" aria-live="polite"></p>

						<?php if ( $is_paid && $subscription_on ) : ?>
							<div class="flex items-center gap-3 text-xs uppercase tracking-widest text-hero-muted">
								<span class="h-px flex-1 bg-white/15"></span><?php esc_html_e( 'or', 'job-seekers-theme' ); ?><span class="h-px flex-1 bg-white/15"></span>
							</div>
							<button type="button" class="jsl-btn jsl-btn--hero-ghost w-full" id="jsl-subscribe-btn">
								<?php echo jsl_icon( 'sparkle', 'w-4 h-4' ); ?>
								<?php
								echo $sub_price
									/* translators: %s: subscription price, e.g. "$19/month". */
									? esc_html( sprintf( __( 'Unlock everything — %s', 'job-seekers-theme' ), $sub_price ) )
									: esc_html__( 'Unlock every course', 'job-seekers-theme' );
								?>
							</button>
						<?php endif; ?>
					<?php else : ?>
						<a class="jsl-btn jsl-btn--primary w-full" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in to start', 'job-seekers-theme' ); ?></a>
					<?php endif; ?>
				</div>

				<ul class="m-0 mt-6 flex list-none flex-col gap-2.5 border-t border-white/10 p-0 pt-5 text-sm text-hero-muted">
					<li class="flex items-center gap-2.5"><span class="text-signal-300"><?php echo jsl_icon( 'check', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Structured, ordered curriculum', 'job-seekers-theme' ); ?></li>
					<li class="flex items-center gap-2.5"><span class="text-signal-300"><?php echo jsl_icon( 'check', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Progress tracking per lesson', 'job-seekers-theme' ); ?></li>
					<li class="flex items-center gap-2.5"><span class="text-signal-300"><?php echo jsl_icon( 'check', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Learn at your own pace', 'job-seekers-theme' ); ?></li>
				</ul>
			</aside>
		</div>
	</section>

	<div class="jsl-container grid items-start gap-12 py-14 lg:grid-cols-[1fr_22rem]">
		<div>
			<?php if ( get_the_content() ) : ?>
				<section class="mb-12">
					<h2 class="m-0 text-2xl font-bold tracking-tight"><?php esc_html_e( 'About this course', 'job-seekers-theme' ); ?></h2>
					<div class="jsl-prose mt-4"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>

			<section aria-labelledby="jsl-curriculum">
				<h2 id="jsl-curriculum" class="m-0 text-2xl font-bold tracking-tight"><?php esc_html_e( 'Curriculum', 'job-seekers-theme' ); ?></h2>

				<?php if ( empty( $modules ) ) : ?>
					<p class="mt-4 text-ink-muted"><?php esc_html_e( 'Content coming soon.', 'job-seekers-theme' ); ?></p>
				<?php else : ?>
					<div class="mt-6 flex flex-col gap-4">
						<?php foreach ( $modules as $m_index => $module ) : ?>
							<details class="group overflow-hidden rounded-xl border border-line bg-raised shadow-sm" <?php echo 0 === $m_index ? 'open' : ''; ?>>
								<summary class="flex cursor-pointer list-none items-center gap-4 px-5 py-4 [&::-webkit-details-marker]:hidden">
									<span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-accent-soft font-mono text-sm font-bold text-accent"><?php echo esc_html( str_pad( (string) ( $m_index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="flex-1 font-semibold text-ink"><?php echo esc_html( $module['title'] ); ?></span>
									<span class="text-xs font-medium text-ink-muted"><?php printf( esc_html( _n( '%d lesson', '%d lessons', count( $module['lessons'] ), 'job-seekers-theme' ) ), count( $module['lessons'] ) ); ?></span>
									<span class="text-ink-muted transition-transform group-open:rotate-90"><?php echo jsl_icon( 'arrow-r', 'w-4 h-4' ); ?></span>
								</summary>

								<ol class="m-0 list-none border-t border-line p-0">
									<?php
									foreach ( $module['lessons'] as $l_index => $lesson ) :
										$is_done    = in_array( (int) $lesson->ID, $completed, true );
										$duration   = (int) get_post_meta( $lesson->ID, 'jsl_duration_minutes', true );
										$is_preview = (bool) get_post_meta( $lesson->ID, 'jsl_is_preview', true );
										$locked     = ! $has_access && ! $is_preview;
										?>
										<li class="border-t border-line first:border-t-0">
											<a class="flex items-center gap-3.5 px-5 py-3.5 no-underline transition hover:bg-subtle" href="<?php echo esc_url( get_permalink( $lesson ) ); ?>">
												<span class="grid h-7 w-7 shrink-0 place-items-center rounded-full <?php echo $is_done ? 'bg-accent text-on-accent' : 'border border-line-strong text-ink-muted'; ?>">
													<?php echo $is_done ? jsl_icon( 'check', 'w-3.5 h-3.5' ) : ( $locked ? jsl_icon( 'lock', 'w-3.5 h-3.5' ) : jsl_icon( 'play', 'w-3 h-3' ) ); ?>
												</span>
												<span class="flex-1 text-sm font-medium text-ink"><?php echo esc_html( get_the_title( $lesson ) ); ?></span>
												<?php if ( $is_preview && ! $has_access ) : ?>
													<span class="jsl-badge jsl-badge--free"><?php esc_html_e( 'Preview', 'job-seekers-theme' ); ?></span>
												<?php endif; ?>
												<?php if ( $duration ) : ?>
													<span class="inline-flex items-center gap-1 text-xs text-ink-muted"><?php echo jsl_icon( 'clock', 'w-3.5 h-3.5' ); ?><?php echo esc_html( $duration ); ?>m</span>
												<?php endif; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ol>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		</div>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'jsl-course', JSL_THEME_URI . '/assets/js/course.js', array(), jsl_asset_version( '/assets/js/course.js' ), true );
wp_localize_script(
	'jsl-course',
	'jslCourse',
	array(
		'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	)
);

get_footer();
