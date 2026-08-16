<?php
/**
 * Template Name: My Learning
 *
 * The learner's dashboard. Its whole job is to answer one question in under
 * two seconds: what do I do right now? So "continue" comes first, stats
 * second, and everything else after that.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

get_header();

$guide_user_id  = get_current_user_id();
$guide_user     = wp_get_current_user();
$guide_has_lms  = class_exists( 'Guide\\Progress\\Progress' );
$guide_overview = $guide_has_lms ? \Guide\Progress\Progress::user_overview( $guide_user_id ) : array();
$guide_minutes  = $guide_has_lms ? \Guide\Progress\Progress::minutes_completed( $guide_user_id ) : 0;
$guide_streak   = $guide_has_lms ? \Guide\Progress\Progress::streak_days( $guide_user_id ) : 0;
$guide_days     = $guide_has_lms && class_exists( 'Guide\\Analytics\\Analytics' )
	? \Guide\Analytics\Analytics::completions_per_day( 14, $guide_user_id )
	: array();

$guide_total_done = 0;
foreach ( $guide_overview as $guide_entry ) {
	$guide_total_done += $guide_entry['completed'];
}

$guide_max_day = 1;
foreach ( $guide_days as $guide_d ) {
	$guide_max_day = max( $guide_max_day, $guide_d['count'] );
}

// The single most useful thing on the page: the next unfinished lesson.
$guide_next = null;
foreach ( $guide_overview as $guide_entry ) {
	if ( $guide_entry['percent'] < 100 && $guide_entry['resume'] ) {
		$guide_next = $guide_entry;
		break;
	}
}
?>

<div class="guide-shell guide-section guide-section--tight">

	<header class="guide-dash-head">
		<div class="is-flex is-align-items-center" style="gap:1rem">
			<?php echo guide_avatar( $guide_user_id, 56 ); ?>
			<div>
				<p class="guide-filter-group__label"><?php esc_html_e( 'Welcome back', 'guide-wp-theme' ); ?></p>
				<h1 class="title is-4 mt-1"><?php echo esc_html( $guide_user->display_name ); ?></h1>
			</div>
		</div>
		<a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'guide-wp-theme' ); ?></a>
	</header>

	<?php if ( $guide_next ) : ?>
		<section class="guide-resume-card mt-5" aria-labelledby="guide-resume">
			<div>
				<p class="guide-eyebrow" id="guide-resume"><?php esc_html_e( 'Continue', 'guide-wp-theme' ); ?></p>
				<h2 class="guide-hero-card__title mt-1"><?php echo esc_html( get_the_title( $guide_next['resume'] ) ); ?></h2>
				<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
					<?php echo esc_html( get_the_title( $guide_next['course'] ) ); ?>
					·
					<?php
					printf(
						/* translators: 1: completed lessons, 2: total lessons. */
						esc_html__( 'lesson %1$d of %2$d', 'guide-wp-theme' ),
						(int) $guide_next['completed'] + 1,
						(int) $guide_next['total']
					);
					?>
				</p>
			</div>
			<a class="button is-primary" href="<?php echo esc_url( get_permalink( $guide_next['resume'] ) ); ?>">
				<?php esc_html_e( 'Resume', 'guide-wp-theme' ); ?>
			</a>
		</section>
	<?php endif; ?>

	<div class="guide-stats mt-5">
		<div class="guide-stat">
			<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( count( $guide_overview ) ) ); ?></p>
			<p class="guide-stat__label"><?php esc_html_e( 'Courses', 'guide-wp-theme' ); ?></p>
		</div>
		<div class="guide-stat">
			<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( $guide_total_done ) ); ?></p>
			<p class="guide-stat__label"><?php esc_html_e( 'Lessons done', 'guide-wp-theme' ); ?></p>
		</div>
		<div class="guide-stat">
			<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( $guide_minutes ) ); ?></p>
			<p class="guide-stat__label"><?php esc_html_e( 'Minutes learned', 'guide-wp-theme' ); ?></p>
		</div>
		<div class="guide-stat guide-stat--streak">
			<p class="guide-stat__value"><?php echo esc_html( number_format_i18n( $guide_streak ) ); ?></p>
			<p class="guide-stat__label"><?php esc_html_e( 'Day streak', 'guide-wp-theme' ); ?></p>
		</div>
	</div>

	<div class="guide-dash-grid mt-6">

		<section aria-labelledby="guide-my-courses">
			<h2 id="guide-my-courses" class="title is-5"><?php esc_html_e( 'Your courses', 'guide-wp-theme' ); ?></h2>

			<?php if ( empty( $guide_overview ) ) : ?>
				<div class="guide-empty mt-4">
					<span class="guide-empty__icon"><?php echo guide_icon( 'compass' ); ?></span>
					<p class="guide-empty__title"><?php esc_html_e( 'You have not started anything yet', 'guide-wp-theme' ); ?></p>
					<p class="guide-empty__text"><?php esc_html_e( 'Pick a path and start the first lesson. It takes a minute, and the first one is deliberately easy.', 'guide-wp-theme' ); ?></p>
					<a class="button is-primary" href="<?php echo esc_url( home_url( '/#paths' ) ); ?>"><?php esc_html_e( 'Explore paths', 'guide-wp-theme' ); ?></a>
				</div>
			<?php else : ?>
				<div class="mt-4" style="display:flex;flex-direction:column;gap:.85rem">
					<?php foreach ( $guide_overview as $guide_entry ) : ?>
						<div class="guide-course-row">
							<div style="min-width:0;flex:1">
								<a class="guide-card__title" href="<?php echo esc_url( get_permalink( $guide_entry['course'] ) ); ?>">
									<?php echo esc_html( get_the_title( $guide_entry['course'] ) ); ?>
								</a>

								<div class="is-flex is-align-items-center mt-2" style="gap:.75rem">
									<span class="guide-progress" style="flex:1">
										<span class="guide-progress__bar<?php echo 100 === (int) $guide_entry['percent'] ? ' guide-progress__bar--complete' : ''; ?>" style="width:<?php echo esc_attr( (string) (int) $guide_entry['percent'] ); ?>%"></span>
									</span>
									<span class="is-size-7 has-text-weight-bold" style="color:var(--bulma-text-weak)"><?php echo esc_html( (string) (int) $guide_entry['percent'] ); ?>%</span>
								</div>

								<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
									<?php
									printf(
										/* translators: 1: completed lessons, 2: total lessons. */
										esc_html__( '%1$d of %2$d lessons', 'guide-wp-theme' ),
										(int) $guide_entry['completed'],
										(int) $guide_entry['total']
									);
									?>
								</p>
							</div>

							<?php if ( 100 === (int) $guide_entry['percent'] ) : ?>
								<span class="guide-chip guide-chip--primary">
									<?php echo guide_icon( 'check' ); ?>
									<?php esc_html_e( 'Completed', 'guide-wp-theme' ); ?>
								</span>
							<?php elseif ( $guide_entry['resume'] ) : ?>
								<a class="button is-small is-primary" href="<?php echo esc_url( get_permalink( $guide_entry['resume'] ) ); ?>">
									<?php echo $guide_entry['completed'] ? esc_html__( 'Resume', 'guide-wp-theme' ) : esc_html__( 'Start', 'guide-wp-theme' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<aside class="guide-card" style="padding:1.25rem">
			<h2 class="guide-filter-group__label"><?php esc_html_e( 'Last 14 days', 'guide-wp-theme' ); ?></h2>

			<?php if ( ! empty( $guide_days ) ) : ?>
				<div class="guide-chart mt-3">
					<?php foreach ( $guide_days as $guide_d ) : ?>
						<?php
						$guide_height = $guide_d['count']
							? max( 8, (int) round( $guide_d['count'] / $guide_max_day * 100 ) )
							: 2;
						?>
						<span class="guide-chart__bar"
							style="height:<?php echo esc_attr( (string) $guide_height ); ?>%<?php echo $guide_d['count'] ? '' : ';opacity:.25'; ?>"
							title="<?php echo esc_attr( $guide_d['date'] . ': ' . $guide_d['count'] ); ?>"></span>
					<?php endforeach; ?>
				</div>
				<div class="guide-chart__axis">
					<span><?php echo esc_html( substr( $guide_days[0]['date'], 5 ) ); ?></span>
					<span><?php esc_html_e( 'today', 'guide-wp-theme' ); ?></span>
				</div>
			<?php endif; ?>

			<p class="mt-4 is-size-7" style="border-top:1px solid var(--bulma-border-weak);padding-top:1rem;color:var(--bulma-text-weak);line-height:1.55">
				<?php
				if ( $guide_streak > 0 ) {
					printf(
						/* translators: %d: number of consecutive days. */
						esc_html( _n( 'A %d-day streak. One lesson today keeps it alive.', 'A %d-day streak. One lesson today keeps it alive.', (int) $guide_streak, 'guide-wp-theme' ) ),
						(int) $guide_streak
					);
				} else {
					esc_html_e( 'Complete one lesson today to start a streak. Consistency beats intensity — this is a six-month thing, not a weekend thing.', 'guide-wp-theme' );
				}
				?>
			</p>
		</aside>
	</div>
</div>

<?php get_footer(); ?>
