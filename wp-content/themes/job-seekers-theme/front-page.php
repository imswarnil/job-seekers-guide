<?php
/**
 * Front page.
 *
 * The hero is a two-column split: the pitch on the left, and on the right a
 * live preview of a real learning path — its actual steps, pulled from the
 * database. A product like this is better sold by showing the thing than by
 * decorating around it, and for a signed-in learner the same slot becomes
 * their "continue where you left off" card, which is the most useful thing
 * the page can offer them.
 *
 * Below: each path as a section of course cards, pricing (when a
 * subscription is configured), and a closing call to action.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jsl_has_api      = class_exists( 'JSL\\Course_Api' );
$jsl_paths        = get_posts( array( 'post_type' => 'learning_path', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$jsl_course_count = (int) wp_count_posts( 'course' )->publish;
$jsl_lesson_count = (int) wp_count_posts( 'lesson' )->publish;

/* ---- What goes in the hero's right column ---- */

// A signed-in learner mid-course sees their own progress instead of a pitch.
$jsl_resume = null;
if ( is_user_logged_in() && class_exists( 'JSL\\Progress\\Progress' ) ) {
	foreach ( \JSL\Progress\Progress::user_overview( get_current_user_id() ) as $jsl_entry ) {
		if ( $jsl_entry['percent'] < 100 && $jsl_entry['resume'] ) {
			$jsl_resume = $jsl_entry;
			break;
		}
	}
}

// Otherwise, preview the first path.
$jsl_hero_path  = $jsl_paths[0] ?? null;
$jsl_hero_steps = ( $jsl_hero_path && $jsl_has_api ) ? \JSL\Course_Api::get_path_steps( $jsl_hero_path->ID ) : array();

$jsl_hero_minutes = 0;
foreach ( $jsl_hero_steps as $jsl_hs ) {
	$jsl_hero_minutes += 'course' === $jsl_hs['type']
		? (int) \JSL\Course_Api::get_stats( $jsl_hs['id'] )['minutes']
		: (int) get_post_meta( $jsl_hs['id'], 'jsl_duration_minutes', true );
}

$jsl_has_visual = $jsl_resume || ! empty( $jsl_hero_steps );
?>

<!-- ============================= Hero ============================= -->
<section class="relative overflow-hidden bg-hero text-on-hero">

	<!-- A soft primary glow instead of pattern decoration: it gives the
	     surface depth without competing with the content. -->
	<div class="pointer-events-none absolute inset-0" aria-hidden="true"
		style="background:
			radial-gradient(60rem 40rem at 15% -10%, color-mix(in srgb, var(--md-primary-40) 55%, transparent), transparent 70%),
			radial-gradient(45rem 35rem at 90% 110%, color-mix(in srgb, var(--md-tertiary-40) 30%, transparent), transparent 65%);">
	</div>

	<div class="jsl-container relative">
		<div class="grid items-center gap-14 py-16 md:py-20 <?php echo $jsl_has_visual ? 'lg:grid-cols-[1.05fr_1fr] lg:gap-16 lg:py-24' : ''; ?>">

			<!-- Pitch -->
			<div class="<?php echo $jsl_has_visual ? '' : 'mx-auto max-w-3xl text-center'; ?>">
				<span class="md-chip !h-8 border-white/25 !text-white/85">
					<?php echo jsl_icon( 'sparkle-fill', 'w-4 h-4' ); ?>
					<?php esc_html_e( 'Open source · free to start', 'job-seekers-theme' ); ?>
				</span>

				<!-- No forced line break: at this size the headline has to be
				     free to wrap to the column it lands in. text-balance keeps
				     the resulting lines even instead of leaving an orphan. -->
				<h1 class="mt-6 max-w-[15ch] text-balance font-display text-[clamp(2.25rem,1.5rem+2.4vw,3.15rem)] font-extrabold leading-[1.08] tracking-[-0.03em] <?php echo $jsl_has_visual ? '' : 'mx-auto'; ?>">
					<?php esc_html_e( 'Stop guessing your way through the job hunt', 'job-seekers-theme' ); ?>
				</h1>

				<p class="mt-6 max-w-xl text-lg leading-relaxed text-hero-muted <?php echo $jsl_has_visual ? '' : 'mx-auto'; ?>">
					<?php esc_html_e( 'Structured paths that take you from résumé to signed offer — one ordered lesson at a time, with your progress tracked the whole way.', 'job-seekers-theme' ); ?>
				</p>

				<div class="mt-9 flex flex-wrap items-center gap-3 <?php echo $jsl_has_visual ? '' : 'justify-center'; ?>">
					<a class="jsl-btn jsl-btn--primary jsl-btn--lg" href="#paths">
						<?php esc_html_e( 'Start learning', 'job-seekers-theme' ); ?>
						<?php echo jsl_icon( 'arrow-right', 'w-5 h-5' ); ?>
					</a>
					<a class="jsl-btn jsl-btn--hero-ghost jsl-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>">
						<?php esc_html_e( 'Browse courses', 'job-seekers-theme' ); ?>
					</a>
				</div>

				<!-- Stats, inline and quiet — supporting detail, not a feature -->
				<div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-white/10 pt-6 text-sm <?php echo $jsl_has_visual ? '' : 'justify-center'; ?>">
					<span class="inline-flex items-baseline gap-2">
						<strong class="font-display text-xl font-extrabold text-on-hero"><?php echo esc_html( count( $jsl_paths ) ); ?></strong>
						<span class="text-hero-muted"><?php esc_html_e( 'paths', 'job-seekers-theme' ); ?></span>
					</span>
					<span class="inline-flex items-baseline gap-2">
						<strong class="font-display text-xl font-extrabold text-on-hero"><?php echo esc_html( $jsl_course_count ); ?></strong>
						<span class="text-hero-muted"><?php esc_html_e( 'courses', 'job-seekers-theme' ); ?></span>
					</span>
					<span class="inline-flex items-baseline gap-2">
						<strong class="font-display text-xl font-extrabold text-on-hero"><?php echo esc_html( $jsl_lesson_count ); ?></strong>
						<span class="text-hero-muted"><?php esc_html_e( 'lessons', 'job-seekers-theme' ); ?></span>
					</span>
				</div>
			</div>

			<!-- Product preview -->
			<?php if ( $jsl_resume ) : ?>
				<?php
				$jsl_r_course = $jsl_resume['course'];
				$jsl_r_next   = $jsl_resume['resume'];
				?>
				<div class="md-card md-card--elevated relative !bg-surface-lowest p-7 shadow-lg lg:p-8">
					<span class="md-chip md-chip--static md-chip--selected !h-7 self-start !text-xs"><?php esc_html_e( 'Welcome back', 'job-seekers-theme' ); ?></span>

					<h2 class="m-0 mt-4 font-display text-xl font-extrabold leading-snug text-on-surface">
						<?php echo esc_html( get_the_title( $jsl_r_course ) ); ?>
					</h2>

					<div class="mt-5">
						<div class="flex items-center justify-between text-xs font-semibold text-on-surface-variant">
							<span><?php printf( esc_html__( '%d%% complete', 'job-seekers-theme' ), (int) $jsl_resume['percent'] ); ?></span>
						</div>
						<div class="md-linear-progress mt-2">
							<div class="md-linear-progress__bar" style="width:<?php echo esc_attr( $jsl_resume['percent'] ); ?>%"></div>
						</div>
					</div>

					<div class="mt-6 rounded-xl bg-surface-container p-4">
						<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-on-surface-variant"><?php esc_html_e( 'Up next', 'job-seekers-theme' ); ?></p>
						<p class="m-0 mt-1 font-semibold text-on-surface"><?php echo esc_html( get_the_title( $jsl_r_next ) ); ?></p>
					</div>

					<a class="jsl-btn jsl-btn--primary jsl-btn--block mt-6" href="<?php echo esc_url( get_permalink( $jsl_r_next ) ); ?>">
						<?php echo jsl_icon( 'play-fill', 'w-4 h-4' ); ?>
						<?php esc_html_e( 'Continue learning', 'job-seekers-theme' ); ?>
					</a>
				</div>

			<?php elseif ( ! empty( $jsl_hero_steps ) ) : ?>
				<div class="md-card md-card--elevated relative !bg-surface-lowest p-7 shadow-lg lg:p-8">
					<div class="flex items-start justify-between gap-4">
						<div>
							<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-primary"><?php esc_html_e( 'Learning path', 'job-seekers-theme' ); ?></p>
							<h2 class="m-0 mt-1 font-display text-xl font-extrabold leading-snug text-on-surface">
								<?php echo esc_html( get_the_title( $jsl_hero_path ) ); ?>
							</h2>
						</div>
						<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-container text-on-primary-container">
							<?php echo jsl_icon( 'path-fill', 'w-6 h-6' ); ?>
						</span>
					</div>

					<ol class="m-0 mt-6 list-none space-y-1 p-0">
						<?php
						foreach ( array_slice( $jsl_hero_steps, 0, 4 ) as $jsl_i => $jsl_step ) :
							$jsl_is_course = 'course' === $jsl_step['type'];
							$jsl_icon_name = $jsl_is_course
								? 'stack'
								: ( 'video' === $jsl_step['lesson_type'] ? 'film-strip' : ( 'quiz' === $jsl_step['lesson_type'] ? 'question' : 'article' ) );
							?>
							<li class="flex items-center gap-3 rounded-lg px-2 py-2">
								<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full <?php echo $jsl_is_course ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container'; ?>">
									<?php echo jsl_icon( $jsl_icon_name, 'w-4 h-4' ); ?>
								</span>
								<span class="min-w-0 flex-1">
									<span class="block truncate text-sm font-semibold text-on-surface"><?php echo esc_html( $jsl_step['title'] ); ?></span>
								</span>
								<span class="font-mono text-[0.65rem] text-on-surface-variant"><?php echo esc_html( str_pad( (string) ( $jsl_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							</li>
						<?php endforeach; ?>

						<?php if ( count( $jsl_hero_steps ) > 4 ) : ?>
							<li class="px-2 pt-1 text-xs font-medium text-on-surface-variant">
								<?php printf( esc_html__( '+ %d more steps', 'job-seekers-theme' ), count( $jsl_hero_steps ) - 4 ); ?>
							</li>
						<?php endif; ?>
					</ol>

					<div class="mt-6 flex items-center gap-4 border-t border-outline-variant pt-5 text-xs font-medium text-on-surface-variant">
						<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'stack', 'w-4 h-4' ); ?><?php printf( esc_html( _n( '%d step', '%d steps', count( $jsl_hero_steps ), 'job-seekers-theme' ) ), count( $jsl_hero_steps ) ); ?></span>
						<?php if ( $jsl_hero_minutes ) : ?>
							<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?><?php printf( esc_html__( '%d min', 'job-seekers-theme' ), (int) $jsl_hero_minutes ); ?></span>
						<?php endif; ?>
						<a class="ml-auto inline-flex items-center gap-1.5 font-bold text-primary no-underline hover:underline" href="<?php echo esc_url( get_permalink( $jsl_hero_path ) ); ?>">
							<?php esc_html_e( 'View path', 'job-seekers-theme' ); ?>
							<?php echo jsl_icon( 'arrow-right', 'w-4 h-4' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- ========================= How it works ========================= -->
<section class="border-b border-outline-variant bg-surface-container-low" aria-labelledby="jsl-how">
	<div class="jsl-container py-14">
		<h2 id="jsl-how" class="sr-only"><?php esc_html_e( 'How it works', 'job-seekers-theme' ); ?></h2>
		<div class="grid gap-8 md:grid-cols-3">
			<?php
			$jsl_steps_how = array(
				array(
					'icon'  => 'path-fill',
					'title' => __( 'Pick a path', 'job-seekers-theme' ),
					'text'  => __( 'Each one is an ordered route to a specific outcome — not a pile of videos to sift through.', 'job-seekers-theme' ),
				),
				array(
					'icon'  => 'play-fill',
					'title' => __( 'Work through it', 'job-seekers-theme' ),
					'text'  => __( 'Short lessons, real examples, and quizzes that check you actually got it before you move on.', 'job-seekers-theme' ),
				),
				array(
					'icon'  => 'trophy-fill',
					'title' => __( 'Track every step', 'job-seekers-theme' ),
					'text'  => __( 'Progress saves as you go, so you always know exactly where you left off and what’s next.', 'job-seekers-theme' ),
				),
			);
			foreach ( $jsl_steps_how as $jsl_how ) :
				?>
				<div class="flex gap-4">
					<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-container text-on-primary-container">
						<?php echo jsl_icon( $jsl_how['icon'], 'w-5 h-5' ); ?>
					</span>
					<div>
						<h3 class="m-0 font-display text-base font-bold text-on-surface"><?php echo esc_html( $jsl_how['title'] ); ?></h3>
						<p class="m-0 mt-1.5 text-sm leading-relaxed text-on-surface-variant"><?php echo esc_html( $jsl_how['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ========================= Learning paths ======================== -->
<div id="paths" class="jsl-container">
	<?php if ( empty( $jsl_paths ) ) : ?>
		<p class="py-16 text-center text-on-surface-variant"><?php esc_html_e( 'No learning paths yet — check back soon.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>

	<?php foreach ( $jsl_paths as $jsl_i => $jsl_path ) : ?>
		<section class="pt-16 md:pt-20" aria-labelledby="path-<?php echo esc_attr( $jsl_path->ID ); ?>">

			<div class="flex flex-wrap items-end justify-between gap-4">
				<div class="max-w-2xl">
					<span class="md-chip md-chip--static md-chip--selected !h-7 !text-xs">
						<?php echo jsl_icon( 'path-fill', 'w-3.5 h-3.5' ); ?>
						<?php printf( esc_html__( 'Path %s', 'job-seekers-theme' ), esc_html( str_pad( (string) ( $jsl_i + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?>
					</span>

					<h2 id="path-<?php echo esc_attr( $jsl_path->ID ); ?>" class="m-0 mt-3 font-display text-2xl font-extrabold tracking-tight md:text-3xl">
						<a class="text-on-surface no-underline hover:text-primary" href="<?php echo esc_url( get_permalink( $jsl_path ) ); ?>"><?php echo esc_html( get_the_title( $jsl_path ) ); ?></a>
					</h2>

					<?php if ( $jsl_path->post_excerpt ) : ?>
						<p class="mt-2 text-on-surface-variant"><?php echo esc_html( $jsl_path->post_excerpt ); ?></p>
					<?php endif; ?>
				</div>

				<a class="md-chip !h-10" href="<?php echo esc_url( get_permalink( $jsl_path ) ); ?>">
					<?php esc_html_e( 'View path', 'job-seekers-theme' ); ?>
					<?php echo jsl_icon( 'arrow-right', 'w-4 h-4' ); ?>
				</a>
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
						$jsl_img     = get_the_post_thumbnail_url( $jsl_course->ID, 'medium_large' )
							?: ( class_exists( 'JSL\\Media\\Placeholder' ) ? \JSL\Media\Placeholder::course( $jsl_course->ID ) : '' );
						$jsl_code    = class_exists( 'JSL\\Course_Meta' ) ? \JSL\Course_Meta::get_code( $jsl_course->ID ) : '';
						?>
						<a class="md-card md-card--elevated group" href="<?php echo esc_url( get_permalink( $jsl_course ) ); ?>">
							<?php if ( $jsl_img ) : ?>
								<img class="md-card__media" src="<?php echo jsl_img_src( $jsl_img ); ?>" alt="" loading="lazy">
							<?php endif; ?>

							<div class="md-card__body flex flex-1 flex-col !p-6">
								<div class="flex items-center justify-between gap-3">
									<span class="font-mono text-xs font-semibold text-on-surface-variant">
										<?php echo $jsl_code ? esc_html( $jsl_code ) : sprintf( esc_html__( 'Step %s', 'job-seekers-theme' ), esc_html( $jsl_step + 1 ) ); ?>
									</span>
									<span class="md-chip md-chip--static !h-7 !px-3 !text-xs <?php echo $jsl_is_paid ? 'md-chip--tertiary' : 'md-chip--selected'; ?>">
										<?php echo $jsl_is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
									</span>
								</div>

								<h3 class="m-0 mt-3 font-display text-lg font-bold leading-snug text-on-surface group-hover:text-primary"><?php echo esc_html( get_the_title( $jsl_course ) ); ?></h3>

								<?php if ( $jsl_course->post_excerpt ) : ?>
									<p class="mt-2 line-clamp-2 text-sm text-on-surface-variant"><?php echo esc_html( $jsl_course->post_excerpt ); ?></p>
								<?php endif; ?>

								<div class="mt-auto flex items-center gap-4 pt-6 text-xs font-medium text-on-surface-variant">
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'stack', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['modules'] ); ?></span>
									<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'article', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['lessons'] ); ?></span>
									<?php if ( $jsl_stats['minutes'] ) : ?>
										<span class="inline-flex items-center gap-1.5"><?php echo jsl_icon( 'clock', 'w-4 h-4' ); ?><?php echo esc_html( $jsl_stats['minutes'] ); ?>m</span>
									<?php endif; ?>
									<span class="ml-auto text-primary opacity-0 transition-opacity group-hover:opacity-100"><?php echo jsl_icon( 'arrow-right', 'w-4 h-4' ); ?></span>
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
		$jsl_sub_price  = \JSL\Payments\Subscription::price_label();
		$jsl_sub_blurb  = \JSL\Payments\Subscription::blurb();
		$jsl_all_access = class_exists( 'JSL\\Access\\Access' ) && \JSL\Access\Access::has_all_access();
		?>
		<section class="mt-20 md:mt-28" aria-labelledby="jsl-pricing-head">
			<div class="text-center">
				<span class="jsl-eyebrow"><?php esc_html_e( 'Pricing', 'job-seekers-theme' ); ?></span>
				<h2 id="jsl-pricing-head" class="m-0 mt-2 font-display text-2xl font-extrabold tracking-tight md:text-3xl"><?php esc_html_e( 'Two ways in', 'job-seekers-theme' ); ?></h2>
				<p class="mx-auto mt-3 max-w-lg text-on-surface-variant"><?php esc_html_e( 'Buy a single course and keep it, or subscribe and get everything on the platform while you’re job hunting.', 'job-seekers-theme' ); ?></p>
			</div>

			<div class="mx-auto mt-10 grid max-w-4xl gap-6 md:grid-cols-2">
				<div class="md-card flex flex-col p-8">
					<h3 class="m-0 font-display text-lg font-bold"><?php esc_html_e( 'A single course', 'job-seekers-theme' ); ?></h3>
					<p class="mt-2 text-sm text-on-surface-variant"><?php esc_html_e( 'Pay once for the course you need. Every lesson in it unlocks immediately, and it stays yours.', 'job-seekers-theme' ); ?></p>
					<ul class="m-0 mt-6 flex flex-1 list-none flex-col gap-3 p-0 text-sm">
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'All lessons in that course', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'Progress tracking and quizzes', 'job-seekers-theme' ); ?></li>
						<li class="flex items-start gap-2.5"><span class="mt-0.5 text-primary"><?php echo jsl_icon( 'check-circle-fill', 'w-4 h-4' ); ?></span><?php esc_html_e( 'No recurring charge', 'job-seekers-theme' ); ?></li>
					</ul>
					<a class="jsl-btn jsl-btn--outlined mt-7" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'job-seekers-theme' ); ?></a>
				</div>

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

	<!-- ============================ CTA ============================ -->
	<section class="relative mt-20 overflow-hidden rounded-3xl bg-hero px-8 py-14 text-center text-on-hero md:mt-28 md:px-16 md:py-20">
		<div class="pointer-events-none absolute inset-0" aria-hidden="true"
			style="background: radial-gradient(40rem 25rem at 50% 0%, color-mix(in srgb, var(--md-primary-40) 60%, transparent), transparent 70%);"></div>

		<div class="relative">
			<h2 class="m-0 font-display text-2xl font-extrabold tracking-tight md:text-4xl"><?php esc_html_e( 'Your next offer starts here', 'job-seekers-theme' ); ?></h2>
			<p class="mx-auto mt-4 max-w-md text-hero-muted"><?php esc_html_e( 'Create a free account, pick a path, and pick up exactly where you left off every time.', 'job-seekers-theme' ); ?></p>
			<div class="mt-8 flex flex-wrap items-center justify-center gap-3">
				<a class="jsl-btn jsl-btn--primary jsl-btn--lg" href="<?php echo esc_url( is_user_logged_in() ? '#paths' : wp_registration_url() ); ?>"><?php esc_html_e( 'Get started free', 'job-seekers-theme' ); ?></a>
				<a class="jsl-btn jsl-btn--hero-ghost jsl-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'See all paths', 'job-seekers-theme' ); ?></a>
			</div>
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
