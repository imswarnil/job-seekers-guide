<?php
/**
 * Template Name: My Learning
 *
 * The learner's personal dashboard: stats (lessons done, minutes, streak),
 * enrolled courses with progress + resume links, and a 14-day activity strip.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

get_header();

$user_id  = get_current_user_id();
$user     = wp_get_current_user();
$has_lms  = class_exists( 'JSL\\Progress\\Progress' );
$overview = $has_lms ? \JSL\Progress\Progress::user_overview( $user_id ) : array();
$minutes  = $has_lms ? \JSL\Progress\Progress::minutes_completed( $user_id ) : 0;
$streak   = $has_lms ? \JSL\Progress\Progress::streak_days( $user_id ) : 0;
$days     = $has_lms && class_exists( 'JSL\\Analytics\\Analytics' ) ? \JSL\Analytics\Analytics::completions_per_day( 14, $user_id ) : array();

$total_done = 0;
foreach ( $overview as $entry ) {
	$total_done += $entry['completed'];
}
$max_day = 1;
foreach ( $days as $d ) {
	$max_day = max( $max_day, $d['count'] );
}
?>

<div class="jsl-container py-8 md:py-10">
	<header class="flex flex-wrap items-center justify-between gap-4">
		<div class="flex items-center gap-4">
			<img class="h-14 w-14 rounded-full border border-line" src="<?php echo esc_url( get_avatar_url( $user_id, array( 'size' => 112 ) ) ); ?>" alt="">
			<div>
				<p class="m-0 text-sm text-ink-muted"><?php esc_html_e( 'Welcome back', 'job-seekers-theme' ); ?></p>
				<h1 class="m-0 text-2xl font-extrabold tracking-tight"><?php echo esc_html( $user->display_name ); ?></h1>
			</div>
		</div>
		<a class="jsl-btn jsl-btn--ghost jsl-btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'job-seekers-theme' ); ?></a>
	</header>

	<!-- Stats -->
	<div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
		<div class="rounded-xl border border-line bg-raised p-5 shadow-sm">
			<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Courses', 'job-seekers-theme' ); ?></p>
			<p class="m-0 mt-1 text-3xl font-extrabold tabular-nums"><?php echo esc_html( count( $overview ) ); ?></p>
		</div>
		<div class="rounded-xl border border-line bg-raised p-5 shadow-sm">
			<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Lessons done', 'job-seekers-theme' ); ?></p>
			<p class="m-0 mt-1 text-3xl font-extrabold tabular-nums text-accent"><?php echo esc_html( $total_done ); ?></p>
		</div>
		<div class="rounded-xl border border-line bg-raised p-5 shadow-sm">
			<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Minutes learned', 'job-seekers-theme' ); ?></p>
			<p class="m-0 mt-1 text-3xl font-extrabold tabular-nums"><?php echo esc_html( $minutes ); ?></p>
		</div>
		<div class="rounded-xl border border-line bg-raised p-5 shadow-sm">
			<p class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Day streak', 'job-seekers-theme' ); ?></p>
			<p class="m-0 mt-1 text-3xl font-extrabold tabular-nums text-spark"><?php echo esc_html( $streak ); ?><span class="text-base font-bold">🔥</span></p>
		</div>
	</div>

	<div class="mt-8 grid items-start gap-6 lg:grid-cols-[2fr_1fr]">
		<!-- Courses -->
		<section aria-labelledby="jsl-my-courses">
			<h2 id="jsl-my-courses" class="m-0 text-lg font-bold tracking-tight"><?php esc_html_e( 'Continue learning', 'job-seekers-theme' ); ?></h2>

			<?php if ( empty( $overview ) ) : ?>
				<div class="mt-4 rounded-xl border border-dashed border-line-strong bg-raised p-10 text-center">
					<p class="m-0 font-semibold text-ink"><?php esc_html_e( 'You are not enrolled in any course yet.', 'job-seekers-theme' ); ?></p>
					<p class="mt-1 text-sm text-ink-muted"><?php esc_html_e( 'Pick a learning path and start your first lesson — it takes a minute.', 'job-seekers-theme' ); ?></p>
					<a class="jsl-btn jsl-btn--primary mt-5" href="<?php echo esc_url( home_url( '/#paths' ) ); ?>"><?php esc_html_e( 'Explore paths', 'job-seekers-theme' ); ?></a>
				</div>
			<?php else : ?>
				<div class="mt-4 flex flex-col gap-4">
					<?php foreach ( $overview as $entry ) : ?>
						<div class="flex flex-wrap items-center gap-4 rounded-xl border border-line bg-raised p-5 shadow-sm sm:flex-nowrap">
							<div class="min-w-0 flex-1">
								<a class="text-base font-bold text-ink no-underline hover:text-accent" href="<?php echo esc_url( get_permalink( $entry['course'] ) ); ?>"><?php echo esc_html( get_the_title( $entry['course'] ) ); ?></a>
								<div class="mt-2 flex items-center gap-3">
									<div class="h-2 flex-1 overflow-hidden rounded-full bg-inset">
										<div class="h-full rounded-full bg-accent" style="width:<?php echo esc_attr( $entry['percent'] ); ?>%"></div>
									</div>
									<span class="text-xs font-bold tabular-nums text-ink-muted"><?php echo esc_html( $entry['percent'] ); ?>%</span>
								</div>
								<p class="m-0 mt-1.5 text-xs text-ink-muted">
									<?php printf( esc_html__( '%1$d of %2$d lessons', 'job-seekers-theme' ), (int) $entry['completed'], (int) $entry['total'] ); ?>
									<?php if ( $entry['resume'] ) : ?>
										· <?php esc_html_e( 'next:', 'job-seekers-theme' ); ?> <?php echo esc_html( get_the_title( $entry['resume'] ) ); ?>
									<?php endif; ?>
								</p>
							</div>
							<?php if ( 100 === $entry['percent'] ) : ?>
								<span class="jsl-badge jsl-badge--free shrink-0"><?php echo jsl_icon( 'check', 'w-3 h-3' ); ?> <?php esc_html_e( 'Completed', 'job-seekers-theme' ); ?></span>
							<?php elseif ( $entry['resume'] ) : ?>
								<a class="jsl-btn jsl-btn--primary jsl-btn--sm shrink-0" href="<?php echo esc_url( get_permalink( $entry['resume'] ) ); ?>">
									<?php echo jsl_icon( 'play', 'w-3.5 h-3.5' ); ?>
									<?php echo $entry['completed'] ? esc_html__( 'Resume', 'job-seekers-theme' ) : esc_html__( 'Start', 'job-seekers-theme' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<!-- Activity -->
		<aside class="rounded-xl border border-line bg-raised p-5 shadow-sm">
			<h2 class="m-0 text-[0.7rem] font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Last 14 days', 'job-seekers-theme' ); ?></h2>
			<?php if ( ! empty( $days ) ) : ?>
				<div class="mt-4 flex h-24 items-end gap-1.5">
					<?php foreach ( $days as $d ) : ?>
						<span class="relative flex-1 rounded-t bg-accent-soft" style="height:100%" title="<?php echo esc_attr( $d['date'] . ': ' . $d['count'] ); ?>">
							<span class="absolute inset-x-0 bottom-0 rounded-t bg-accent" style="height:<?php echo esc_attr( $d['count'] ? max( 8, (int) round( $d['count'] / $max_day * 100 ) ) : 0 ); ?>%"></span>
						</span>
					<?php endforeach; ?>
				</div>
				<div class="mt-2 flex justify-between text-[0.65rem] text-ink-muted">
					<span><?php echo esc_html( substr( $days[0]['date'], 5 ) ); ?></span>
					<span><?php esc_html_e( 'today', 'job-seekers-theme' ); ?></span>
				</div>
			<?php endif; ?>
			<p class="mt-4 border-t border-line pt-4 text-xs text-ink-muted">
				<?php
				if ( $streak > 0 ) {
					printf( esc_html( _n( 'You\'re on a %d-day streak — keep it going with one lesson today.', 'You\'re on a %d-day streak — keep it going with one lesson today.', $streak, 'job-seekers-theme' ) ), (int) $streak );
				} else {
					esc_html_e( 'Complete a lesson today to start a streak.', 'job-seekers-theme' );
				}
				?>
			</p>
		</aside>
	</div>
</div>

<?php get_footer(); ?>
