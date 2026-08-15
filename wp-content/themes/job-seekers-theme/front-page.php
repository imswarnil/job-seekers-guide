<?php
/**
 * Front page: hero + stats, then each learning path as a numbered section
 * of course cards (with a lesson peek inside every card), then a CTA band.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jsl_paths        = get_posts( array( 'post_type' => 'learning_path', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$jsl_course_count = (int) wp_count_posts( 'course' )->publish;
$jsl_lesson_count = (int) wp_count_posts( 'lesson' )->publish;
$jsl_has_api      = class_exists( 'JSL\\Course_Api' );
?>

<!-- Hero -->
<section class="relative overflow-hidden bg-hero text-on-hero">
	<svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.14]" aria-hidden="true">
		<defs>
			<pattern id="jsl-grid" width="44" height="44" patternUnits="userSpaceOnUse">
				<circle cx="1.5" cy="1.5" r="1.5" fill="currentColor" />
			</pattern>
		</defs>
		<rect width="100%" height="100%" fill="url(#jsl-grid)" />
	</svg>
	<svg class="pointer-events-none absolute -right-24 top-1/2 hidden h-[130%] -translate-y-1/2 text-signal-500 opacity-25 lg:block" width="500" viewBox="0 0 500 600" fill="none" aria-hidden="true">
		<path d="M60 580 C 200 480, 40 360, 220 280 S 460 140, 420 30" stroke="currentColor" stroke-width="3" stroke-dasharray="2 14" stroke-linecap="round"/>
		<circle cx="60" cy="580" r="8" fill="currentColor"/>
		<circle cx="220" cy="280" r="8" fill="currentColor"/>
		<circle cx="420" cy="30" r="10" fill="var(--jsl-spark-500)"/>
	</svg>

	<div class="jsl-container relative py-20 md:py-28">
		<div class="max-w-2xl">
			<span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-semibold tracking-wide text-signal-300">
				<?php echo jsl_icon( 'spark', 'w-3.5 h-3.5' ); ?>
				<?php esc_html_e( 'Open source · free to start', 'job-seekers-theme' ); ?>
			</span>

			<h1 class="mt-6 text-[clamp(2.4rem,1.6rem+3.5vw,4rem)] font-extrabold leading-[1.08] tracking-tight">
				<?php esc_html_e( 'Your path from', 'job-seekers-theme' ); ?>
				<span class="bg-gradient-to-r from-signal-300 to-spark-300 bg-clip-text text-transparent"><?php esc_html_e( 'application to offer', 'job-seekers-theme' ); ?></span>
			</h1>

			<p class="mt-5 max-w-xl text-lg leading-relaxed text-hero-muted">
				<?php esc_html_e( 'Follow guided learning paths, practice the skills interviewers actually test, and track every lesson you complete on the way to your next job.', 'job-seekers-theme' ); ?>
			</p>

			<div class="mt-8 flex flex-wrap items-center gap-3.5">
				<a class="jsl-btn jsl-btn--primary" href="#paths">
					<?php esc_html_e( 'Start learning', 'job-seekers-theme' ); ?>
					<?php echo jsl_icon( 'arrow-r', 'w-4.5 h-4.5' ); ?>
				</a>
				<a class="jsl-btn jsl-btn--hero-ghost" href="https://github.com/imswarnil/job-seekers-guide">
					<?php esc_html_e( 'View on GitHub', 'job-seekers-theme' ); ?>
				</a>
			</div>

			<dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-white/10 pt-8">
				<div>
					<dt class="order-2 text-xs font-medium uppercase tracking-widest text-hero-muted"><?php esc_html_e( 'Paths', 'job-seekers-theme' ); ?></dt>
					<dd class="m-0 text-3xl font-extrabold text-on-hero"><?php echo esc_html( count( $jsl_paths ) ); ?></dd>
				</div>
				<div>
					<dt class="text-xs font-medium uppercase tracking-widest text-hero-muted"><?php esc_html_e( 'Courses', 'job-seekers-theme' ); ?></dt>
					<dd class="m-0 text-3xl font-extrabold text-on-hero"><?php echo esc_html( $jsl_course_count ); ?></dd>
				</div>
				<div>
					<dt class="text-xs font-medium uppercase tracking-widest text-hero-muted"><?php esc_html_e( 'Lessons', 'job-seekers-theme' ); ?></dt>
					<dd class="m-0 text-3xl font-extrabold text-on-hero"><?php echo esc_html( $jsl_lesson_count ); ?></dd>
				</div>
			</dl>
		</div>
	</div>
</section>

<?php
// Logged-in learners: resume strip right under the hero.
$jsl_my = is_user_logged_in() && class_exists( 'JSL\\Progress\\Progress' )
	? array_filter(
		\JSL\Progress\Progress::user_overview( get_current_user_id() ),
		function ( $entry ) {
			return $entry['percent'] < 100 && $entry['resume'];
		}
	)
	: array();
$jsl_my = array_slice( $jsl_my, 0, 3 );
?>
<?php if ( ! empty( $jsl_my ) ) : ?>
	<section class="border-b border-outline-variant bg-surface-container-low" aria-labelledby="jsl-resume-head">
		<div class="jsl-container py-8">
			<div class="flex items-center justify-between gap-4">
				<h2 id="jsl-resume-head" class="m-0 text-lg font-bold tracking-tight"><?php esc_html_e( 'Jump back in', 'job-seekers-theme' ); ?></h2>
				<a class="md-chip !h-9" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>"><?php esc_html_e( 'My Learning →', 'job-seekers-theme' ); ?></a>
			</div>
			<div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
				<?php foreach ( $jsl_my as $jsl_entry ) : ?>
					<a class="md-card md-card--elevated group !flex-row items-center gap-4 p-4" href="<?php echo esc_url( get_permalink( $jsl_entry['resume'] ) ); ?>">
						<span class="md-fab md-fab--small !shadow-none"><?php echo jsl_icon( 'play-fill', 'w-4 h-4' ); ?></span>
						<span class="min-w-0 flex-1">
							<span class="block truncate text-sm font-bold text-on-surface group-hover:text-primary"><?php echo esc_html( get_the_title( $jsl_entry['course'] ) ); ?></span>
							<span class="md-linear-progress mt-2 block"><span class="md-linear-progress__bar block" style="width:<?php echo esc_attr( $jsl_entry['percent'] ); ?>%"></span></span>
							<span class="mt-1 block truncate text-xs text-on-surface-variant"><?php esc_html_e( 'Next:', 'job-seekers-theme' ); ?> <?php echo esc_html( get_the_title( $jsl_entry['resume'] ) ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- Learning paths -->
<div id="paths" class="jsl-container">
	<?php if ( empty( $jsl_paths ) ) : ?>
		<p class="py-16 text-center text-on-surface-variant"><?php esc_html_e( 'No learning paths yet — check back soon.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>

	<?php foreach ( $jsl_paths as $jsl_i => $jsl_path ) : ?>
		<section class="pt-16 md:pt-24" aria-labelledby="path-<?php echo esc_attr( $jsl_path->ID ); ?>">
			<div class="flex items-start gap-5">
				<span class="mt-1 grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-primary-container font-mono text-lg font-bold text-accent">
					<?php echo esc_html( str_pad( (string) ( $jsl_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?>
				</span>
				<div>
					<span class="jsl-eyebrow"><?php esc_html_e( 'Learning path', 'job-seekers-theme' ); ?></span>
					<h2 id="path-<?php echo esc_attr( $jsl_path->ID ); ?>" class="m-0 mt-1 font-display text-2xl font-bold tracking-tight md:text-3xl">
						<a class="text-ink no-underline hover:text-accent" href="<?php echo esc_url( get_permalink( $jsl_path ) ); ?>"><?php echo esc_html( get_the_title( $jsl_path ) ); ?></a>
					</h2>
					<?php if ( $jsl_path->post_excerpt ) : ?>
						<p class="mt-2 max-w-2xl text-on-surface-variant"><?php echo esc_html( $jsl_path->post_excerpt ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php
			$jsl_courses = $jsl_has_api ? \JSL\Course_Api::get_path_courses( $jsl_path->ID ) : array();
			if ( ! empty( $jsl_courses ) ) :
				?>
				<div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
					<?php
					foreach ( $jsl_courses as $jsl_step => $jsl_course ) :
						$jsl_is_paid = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $jsl_course->ID );
						$jsl_stats   = $jsl_has_api ? \JSL\Course_Api::get_stats( $jsl_course->ID ) : array( 'modules' => 0, 'lessons' => 0, 'minutes' => 0 );
						$jsl_peek    = $jsl_has_api ? array_slice( \JSL\Course_Api::get_lessons_flat( $jsl_course->ID ), 0, 3 ) : array();
						$jsl_img     = get_the_post_thumbnail_url( $jsl_course->ID, 'medium_large' )
							?: ( class_exists( 'JSL\\Media\\Placeholder' ) ? \JSL\Media\Placeholder::course( $jsl_course->ID ) : '' );
						$jsl_code    = class_exists( 'JSL\\Course_Meta' ) ? \JSL\Course_Meta::get_code( $jsl_course->ID ) : '';
						?>
						<a class="md-card md-card--elevated group relative" href="<?php echo esc_url( get_permalink( $jsl_course ) ); ?>">
							<?php if ( $jsl_img ) : ?>
								<img class="md-card__media" src="<?php echo jsl_img_src( $jsl_img ); ?>" alt="" loading="lazy">
							<?php endif; ?>
							<div class="md-card__body flex flex-1 flex-col !p-6">
							<div class="flex items-center justify-between gap-3">
								<span class="font-mono text-xs font-semibold text-on-surface-variant"><?php echo $jsl_code ? esc_html( $jsl_code ) : sprintf( esc_html__( 'Step %s', 'job-seekers-theme' ), esc_html( $jsl_step + 1 ) ); ?></span>
								<span class="jsl-badge <?php echo $jsl_is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>">
									<?php echo $jsl_is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
								</span>
							</div>

							<h3 class="m-0 mt-3 font-display text-lg font-bold leading-snug text-on-surface group-hover:text-primary"><?php echo esc_html( get_the_title( $jsl_course ) ); ?></h3>
							<?php if ( $jsl_course->post_excerpt ) : ?>
								<p class="mt-2 line-clamp-2 text-sm text-on-surface-variant"><?php echo esc_html( $jsl_course->post_excerpt ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $jsl_peek ) ) : ?>
								<ul class="jsl-path-line m-0 mt-4 flex list-none flex-col gap-2 border-t border-outline-variant p-0 pl-3 pt-4">
									<?php foreach ( $jsl_peek as $jsl_lesson ) : ?>
										<li class="flex items-center gap-2.5 text-sm text-ink-secondary">
											<span class="grid h-5 w-5 shrink-0 -ml-[1.35rem] place-items-center rounded-full border border-outline-variant-strong bg-raised text-accent"><?php echo jsl_icon( 'play', 'w-2.5 h-2.5' ); ?></span>
											<span class="truncate"><?php echo esc_html( get_the_title( $jsl_lesson ) ); ?></span>
										</li>
									<?php endforeach; ?>
									<?php if ( $jsl_stats['lessons'] > 3 ) : ?>
										<li class="-ml-3 pl-3 text-xs font-medium text-on-surface-variant"><?php printf( esc_html__( '+ %d more lessons', 'job-seekers-theme' ), (int) $jsl_stats['lessons'] - 3 ); ?></li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>

							<div class="mt-auto flex items-center gap-4 pt-5 text-xs font-medium text-on-surface-variant">
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'layers', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d module', '%d modules', $jsl_stats['modules'], 'job-seekers-theme' ) ), (int) $jsl_stats['modules'] ); ?></span>
								<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'doc', 'w-3.5 h-3.5' ); ?><?php printf( esc_html( _n( '%d lesson', '%d lessons', $jsl_stats['lessons'], 'job-seekers-theme' ) ), (int) $jsl_stats['lessons'] ); ?></span>
								<?php if ( $jsl_stats['minutes'] ) : ?>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-3.5 h-3.5' ); ?><?php printf( esc_html__( '%d min', 'job-seekers-theme' ), (int) $jsl_stats['minutes'] ); ?></span>
								<?php endif; ?>
								<span class="ml-auto text-accent opacity-0 transition group-hover:opacity-100"><?php echo jsl_icon( 'arrow-r', 'w-4 h-4' ); ?></span>
							</div>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="mt-6 text-sm text-on-surface-variant"><?php esc_html_e( 'Courses for this path are coming soon.', 'job-seekers-theme' ); ?></p>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<?php
	// Pricing: only shown once a subscription is actually configured, so a
	// site that only sells individual courses doesn't advertise a plan that
	// cannot be bought.
	$jsl_sub_on = class_exists( 'JSL\\Payments\\Subscription' ) && \JSL\Payments\Subscription::is_enabled();
	if ( $jsl_sub_on ) :
		$jsl_sub_price = \JSL\Payments\Subscription::price_label();
		$jsl_sub_blurb = \JSL\Payments\Subscription::blurb();
		$jsl_all_access = class_exists( 'JSL\\Access\\Access' ) && \JSL\Access\Access::has_all_access();
		?>
		<section class="mt-20 md:mt-28" aria-labelledby="jsl-pricing-head">
			<div class="text-center">
				<span class="jsl-eyebrow"><?php esc_html_e( 'Pricing', 'job-seekers-theme' ); ?></span>
				<h2 id="jsl-pricing-head" class="m-0 mt-2 font-display text-2xl font-extrabold tracking-tight md:text-3xl"><?php esc_html_e( 'Two ways in', 'job-seekers-theme' ); ?></h2>
				<p class="mx-auto mt-3 max-w-lg text-on-surface-variant"><?php esc_html_e( 'Buy a single course and keep it, or subscribe and get everything on the platform while you’re job hunting.', 'job-seekers-theme' ); ?></p>
			</div>

			<div class="mx-auto mt-10 grid max-w-4xl gap-6 md:grid-cols-2">
				<!-- Per course -->
				<div class="jsl-card flex flex-col p-8">
					<h3 class="m-0 font-display text-lg font-bold"><?php esc_html_e( 'A single course', 'job-seekers-theme' ); ?></h3>
					<p class="mt-2 text-sm text-on-surface-variant"><?php esc_html_e( 'Pay once for the course you need. Every lesson in it unlocks immediately, and it stays yours.', 'job-seekers-theme' ); ?></p>
					<ul class="m-0 mt-6 flex flex-1 list-none flex-col gap-3 p-0 text-sm">
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'All lessons in that course', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Progress tracking and quizzes', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'No recurring charge', 'job-seekers-theme' ); ?></li>
					</ul>
					<a class="jsl-btn jsl-btn--outlined mt-7" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'job-seekers-theme' ); ?></a>
				</div>

				<!-- Everything -->
				<div class="relative flex flex-col rounded-xl border-2 border-primary bg-surface-lowest p-8 shadow-md">
					<span class="absolute -top-3 left-8 rounded-full bg-primary px-3 py-1 text-xs font-bold text-on-primary"><?php esc_html_e( 'Best value', 'job-seekers-theme' ); ?></span>
					<h3 class="m-0 font-display text-lg font-bold"><?php esc_html_e( 'Everything', 'job-seekers-theme' ); ?></h3>
					<?php if ( $jsl_sub_price ) : ?>
						<p class="m-0 mt-3 font-display text-3xl font-extrabold tracking-tight"><?php echo esc_html( $jsl_sub_price ); ?></p>
					<?php endif; ?>
					<p class="mt-2 text-sm text-on-surface-variant">
						<?php echo $jsl_sub_blurb ? esc_html( $jsl_sub_blurb ) : esc_html__( 'Every course, every path, for as long as your subscription is active.', 'job-seekers-theme' ); ?>
					</p>
					<ul class="m-0 mt-6 flex flex-1 list-none flex-col gap-3 p-0 text-sm">
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Every course on the platform', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Every new course as it lands', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Cancel whenever you’re hired', 'job-seekers-theme' ); ?></li>
					</ul>

					<?php if ( $jsl_all_access ) : ?>
						<p class="mt-7 inline-flex items-center justify-center gap-2 rounded-full bg-tertiary-container px-4 py-3 text-sm font-semibold text-on-tertiary-container">
							<?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?>
							<?php esc_html_e( 'You have full access', 'job-seekers-theme' ); ?>
						</p>
					<?php elseif ( is_user_logged_in() ) : ?>
						<button type="button" class="jsl-btn jsl-btn--primary mt-7" id="jsl-home-subscribe"><?php esc_html_e( 'Subscribe', 'job-seekers-theme' ); ?></button>
						<p class="m-0 mt-2 min-h-5 text-center text-xs text-on-surface-variant" id="jsl-home-subscribe-status" aria-live="polite"></p>
					<?php else : ?>
						<a class="jsl-btn jsl-btn--primary mt-7" href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign in to subscribe', 'job-seekers-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- CTA band -->
	<section class="mt-20 overflow-hidden rounded-2xl bg-hero px-8 py-12 text-center text-on-hero md:mt-28 md:px-16 md:py-16">
		<h2 class="m-0 text-2xl font-extrabold tracking-tight md:text-3xl"><?php esc_html_e( 'Ready to start your path?', 'job-seekers-theme' ); ?></h2>
		<p class="mx-auto mt-3 max-w-md text-hero-muted"><?php esc_html_e( 'Create a free account, pick a path, and track your progress lesson by lesson.', 'job-seekers-theme' ); ?></p>
		<div class="mt-7 flex flex-wrap items-center justify-center gap-3.5">
			<a class="jsl-btn jsl-btn--primary" href="<?php echo esc_url( is_user_logged_in() ? '#paths' : wp_registration_url() ); ?>"><?php esc_html_e( 'Get started free', 'job-seekers-theme' ); ?></a>
			<a class="jsl-btn jsl-btn--hero-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'job-seekers-theme' ); ?></a>
		</div>
	</section>
</div>

<?php
if ( $jsl_sub_on && is_user_logged_in() ) {
	wp_enqueue_script( 'jsl-subscribe', JSL_THEME_URI . '/assets/js/subscribe.js', array(), jsl_asset_version( '/assets/js/subscribe.js' ), true );
	wp_localize_script(
		'jsl-subscribe',
		'jslSubscribe',
		array(
			'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);
}

get_footer();
?>
