<?php
/**
 * Learning-path player.
 *
 * Same shell as the course player, different spine: the sidebar is the path's
 * curated sequence, which may cut across several courses. Prev/next follow the
 * path, not the course a lesson happens to live in — otherwise a learner would
 * fall out of the path at the end of every borrowed section.
 */

defined( 'ABSPATH' ) || exit;

$guide_ctx = \Guide\Structure\Path_Player::current();

if ( ! $guide_ctx ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

$guide_path    = $guide_ctx['path'];
$guide_lesson  = $guide_ctx['lesson'];
$guide_lessons = $guide_ctx['lessons'];
$guide_index   = $guide_ctx['index'];
$guide_total   = count( $guide_lessons );
$guide_user_id = get_current_user_id();

$guide_prev = $guide_lessons[ $guide_index - 1 ] ?? null;
$guide_next = $guide_lessons[ $guide_index + 1 ] ?? null;

$guide_percent = \Guide\Structure\Path_Player::percent( $guide_user_id, $guide_lessons );
$guide_groups  = \Guide\Structure\Path_Player::sidebar( (int) $guide_path->ID, (int) $guide_lesson->ID, $guide_user_id );

$guide_locked = class_exists( 'Guide\\Access\\Access' )
	&& \Guide\Access\Access::is_locked( (int) $guide_lesson->ID );

$guide_done = class_exists( 'Guide\\Progress\\Progress' )
	&& isset( \Guide\Structure\Path_Player::completed_ids( $guide_user_id, array( $guide_lesson ) )[ (int) $guide_lesson->ID ] );

// The lesson's own course, for the "you are inside X" line and the escape hatch
// to the canonical lesson URL.
$guide_home_course = (int) get_post_meta( $guide_lesson->ID, 'jsl_course_id', true );

$guide_video_start = (int) get_post_meta( $guide_lesson->ID, 'jsl_video_start', true );
$guide_video_end   = (int) get_post_meta( $guide_lesson->ID, 'jsl_video_end', true );
$guide_video       = class_exists( 'Guide\\Lesson_Meta' )
	? \Guide\Lesson_Meta::embed_info(
		(string) get_post_meta( $guide_lesson->ID, 'jsl_video_url', true ),
		$guide_video_start,
		$guide_video_end
	)
	: null;

$guide_type = (string) get_post_meta( $guide_lesson->ID, 'jsl_lesson_type', true );
$guide_type = $guide_type ? $guide_type : ( $guide_video ? 'video' : 'article' );

$guide_poster = get_the_post_thumbnail_url( $guide_lesson->ID, 'large' );
if ( ! $guide_poster && $guide_video && ! empty( $guide_video['poster'] ) ) {
	$guide_poster = $guide_video['poster'];
}

$guide_duration = (int) get_post_meta( $guide_lesson->ID, 'jsl_duration_minutes', true );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'guide-player-body' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#guide-main"><?php esc_html_e( 'Skip to lesson', 'guide-wp-theme' ); ?></a>

<header class="guide-player__toolbar">
	<button type="button" class="guide-icon-button guide-player__drawer-toggle"
		data-drawer-toggle="guide-path-drawer"
		aria-expanded="false" aria-controls="guide-path-drawer"
		aria-label="<?php esc_attr_e( 'Path contents', 'guide-wp-theme' ); ?>">
		<?php echo guide_icon( 'list' ); ?>
	</button>

	<a class="guide-icon-button" href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>"
		aria-label="<?php esc_attr_e( 'Back to path overview', 'guide-wp-theme' ); ?>">
		<?php echo guide_icon( 'arrow-left' ); ?>
	</a>

	<div style="min-width:0;flex:1">
		<a class="guide-player__course-title" href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>">
			<?php echo esc_html( get_the_title( $guide_path ) ); ?>
		</a>
		<p class="guide-player__toolbar-title">
			<?php
			printf(
				/* translators: 1: current step, 2: total steps in the path. */
				esc_html__( 'Step %1$d of %2$d on this path', 'guide-wp-theme' ),
				(int) $guide_index + 1,
				(int) $guide_total
			);
			?>
		</p>
	</div>

	<?php if ( $guide_user_id ) : ?>
		<div class="guide-player__progress">
			<span class="guide-progress" style="width:7rem">
				<span class="guide-progress__bar" data-progress-bar style="width:<?php echo esc_attr( (string) $guide_percent ); ?>%"></span>
			</span>
			<span class="guide-player__percent" data-progress-percent><?php echo esc_html( (string) $guide_percent ); ?>%</span>
		</div>
	<?php endif; ?>

	<button type="button" class="guide-icon-button" data-theme-toggle
		data-label-auto="<?php esc_attr_e( 'Theme: follow system', 'guide-wp-theme' ); ?>"
		data-label-light="<?php esc_attr_e( 'Theme: light', 'guide-wp-theme' ); ?>"
		data-label-dark="<?php esc_attr_e( 'Theme: dark', 'guide-wp-theme' ); ?>"
		aria-label="<?php esc_attr_e( 'Change theme', 'guide-wp-theme' ); ?>">
		<span data-mode-icon="auto"><?php echo guide_icon( 'circle-half' ); ?></span>
		<span data-mode-icon="light" hidden><?php echo guide_icon( 'sun-fill' ); ?></span>
		<span data-mode-icon="dark" hidden><?php echo guide_icon( 'moon-fill' ); ?></span>
	</button>
</header>

<main id="guide-main" class="guide-main">
	<div class="guide-player">

		<div class="guide-player__scrim" data-drawer-scrim></div>

		<aside class="guide-player__sidebar" id="guide-path-drawer" data-drawer
			aria-label="<?php esc_attr_e( 'Path contents', 'guide-wp-theme' ); ?>">

			<div class="guide-player__sidebar-head">
				<p class="guide-filter-group__label"><?php esc_html_e( 'Learning path', 'guide-wp-theme' ); ?></p>
				<a class="guide-player__course-title mt-1" href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>">
					<?php echo esc_html( get_the_title( $guide_path ) ); ?>
				</a>

				<?php if ( $guide_user_id ) : ?>
					<div class="mt-3">
						<div class="guide-progress-label">
							<span data-progress-label>
								<?php
								printf(
									/* translators: 1: current step, 2: total steps. */
									esc_html__( '%1$d / %2$d complete', 'guide-wp-theme' ),
									(int) round( $guide_percent / 100 * $guide_total ),
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

			<nav class="guide-player__modules" aria-label="<?php esc_attr_e( 'Path lessons', 'guide-wp-theme' ); ?>">
				<?php foreach ( $guide_groups as $guide_group ) : ?>
					<?php if ( $guide_group['title'] ) : ?>
						<p class="guide-player__module-label">
							<?php echo esc_html( $guide_group['title'] ); ?>
							<?php if ( ! empty( $guide_group['course'] ) ) : ?>
								<?php // Say which course a borrowed section came from, so nobody wonders where they are. ?>
								<span class="guide-player__from"><?php echo esc_html( $guide_group['course'] ); ?></span>
							<?php endif; ?>
						</p>
					<?php endif; ?>

					<?php foreach ( $guide_group['lessons'] as $guide_row ) : ?>
						<a class="guide-player__lesson<?php echo $guide_row['complete'] ? ' is-complete' : ''; ?><?php echo $guide_row['current'] ? ' is-current' : ''; ?>"
							href="<?php echo esc_url( $guide_row['url'] ); ?>"
							<?php echo $guide_row['current'] ? 'aria-current="page"' : ''; ?>
							data-lesson-row="<?php echo esc_attr( (string) $guide_row['id'] ); ?>">

							<span class="guide-player__lesson-icon" data-lesson-dot>
								<?php
								if ( $guide_row['complete'] ) {
									echo guide_icon( 'check-circle-fill' );
								} elseif ( $guide_row['locked'] ) {
									echo guide_icon( 'lock-simple' );
								} else {
									echo guide_icon( 'circle' );
								}
								?>
							</span>

							<span style="min-width:0;flex:1"><?php echo esc_html( $guide_row['title'] ); ?></span>

							<?php if ( $guide_row['minutes'] ) : ?>
								<span class="guide-player__lesson-duration"><?php echo esc_html( (string) $guide_row['minutes'] ); ?>m</span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</nav>

			<?php // A quiet slot at the foot of the path navigation. ?>
			<?php guide_ad( 'side', false ); ?>
		</aside>

		<article class="guide-player__main">
			<div class="guide-player__body">

				<?php if ( $guide_video && 'quiz' !== $guide_type && ! $guide_locked ) : ?>
					<div class="guide-video"
						data-embed-type="<?php echo esc_attr( $guide_video['type'] ); ?>"
						data-embed-src="<?php echo esc_url( $guide_video['src'] ); ?>"
						data-start="<?php echo esc_attr( (string) $guide_video['start'] ); ?>"
						data-end="<?php echo esc_attr( (string) $guide_video['end'] ); ?>"
						data-title="<?php echo esc_attr( get_the_title( $guide_lesson ) ); ?>">
						<?php if ( $guide_poster ) : ?>
							<img class="guide-video__poster" src="<?php echo guide_img_src( $guide_poster ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<button type="button" class="guide-video__play" aria-label="<?php esc_attr_e( 'Play video', 'guide-wp-theme' ); ?>">
							<?php echo guide_icon( 'play-fill' ); ?>
						</button>
					</div>
				<?php endif; ?>

				<header>
					<h1 class="guide-lesson-title"><?php echo esc_html( get_the_title( $guide_lesson ) ); ?></h1>

					<div class="guide-lesson-meta">
						<span class="guide-card__meta-item">
							<?php echo guide_icon( 'quiz' === $guide_type ? 'list-checks' : ( 'video' === $guide_type ? 'film-strip' : 'article' ) ); ?>
							<?php
							echo 'quiz' === $guide_type
								? esc_html__( 'Quiz', 'guide-wp-theme' )
								: ( 'video' === $guide_type ? esc_html__( 'Video', 'guide-wp-theme' ) : esc_html__( 'Article', 'guide-wp-theme' ) );
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

						<?php if ( $guide_home_course ) : ?>
							<span class="guide-card__meta-item">
								<?php echo guide_icon( 'stack' ); ?>
								<a href="<?php echo esc_url( get_permalink( $guide_home_course ) ); ?>">
									<?php echo esc_html( get_the_title( $guide_home_course ) ); ?>
								</a>
							</span>
						<?php endif; ?>

						<span style="margin-left:auto">
							<?php if ( $guide_user_id && ! $guide_locked ) : ?>
								<button type="button"
									class="button is-small <?php echo $guide_done ? 'is-light' : 'is-primary'; ?>"
									id="guide-complete-btn"
									data-lesson-id="<?php echo esc_attr( (string) $guide_lesson->ID ); ?>"
									data-completed="<?php echo $guide_done ? '1' : '0'; ?>"
									data-label-done="<?php esc_attr_e( 'Completed', 'guide-wp-theme' ); ?>"
									data-label-todo="<?php esc_attr_e( 'Mark complete', 'guide-wp-theme' ); ?>">
									<?php echo $guide_done ? esc_html__( 'Completed', 'guide-wp-theme' ) : esc_html__( 'Mark complete', 'guide-wp-theme' ); ?>
								</button>
							<?php elseif ( ! $guide_user_id ) : ?>
								<a class="button is-small is-primary" href="<?php echo esc_url( wp_login_url( \Guide\Structure\Path_Player::lesson_url( (int) $guide_path->ID, (int) $guide_lesson->ID ) ) ); ?>">
									<?php esc_html_e( 'Sign in to track progress', 'guide-wp-theme' ); ?>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</header>

				<?php if ( 'quiz' === $guide_type && ! $guide_locked ) : ?>
					<div id="guide-quiz-app" class="guide-quiz" data-lesson-id="<?php echo esc_attr( (string) $guide_lesson->ID ); ?>">
						<p class="guide-empty__text"><?php esc_html_e( 'Loading quiz…', 'guide-wp-theme' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $guide_locked ) : ?>
					<div class="guide-empty mt-5">
						<span class="guide-empty__icon"><?php echo guide_icon( 'lock-fill' ); ?></span>
						<p class="guide-empty__title"><?php esc_html_e( 'This lesson is part of a members course', 'guide-wp-theme' ); ?></p>
						<p class="guide-empty__text"><?php esc_html_e( 'A subscription opens every course on the platform — including the rest of this path.', 'guide-wp-theme' ); ?></p>
						<a class="button is-primary" href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( 'See the plan', 'guide-wp-theme' ); ?></a>
					</div>
				<?php else : ?>
					<div class="guide-prose mt-4">
						<?php
						// The lesson body, run through the same filters a normal
						// post gets so shortcodes and embeds behave identically.
						echo apply_filters( 'the_content', $guide_lesson->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</div>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/feedback', null, array( 'object_type' => 'lesson', 'object_id' => (int) $guide_lesson->ID ) ); ?>

				<?php comments_template(); ?>

				<?php guide_ad( 'page' ); ?>

				<nav class="guide-lesson-nav" aria-label="<?php esc_attr_e( 'Path navigation', 'guide-wp-theme' ); ?>">
					<?php if ( $guide_prev ) : ?>
						<a class="guide-lesson-nav__link" href="<?php echo esc_url( \Guide\Structure\Path_Player::lesson_url( (int) $guide_path->ID, (int) $guide_prev->ID ) ); ?>">
							<?php echo guide_icon( 'arrow-left' ); ?>
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'Previous', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php echo esc_html( get_the_title( $guide_prev ) ); ?></span>
							</span>
						</a>
					<?php else : ?>
						<span class="guide-lesson-nav__spacer"></span>
					<?php endif; ?>

					<span class="guide-lesson-nav__spacer"></span>

					<?php if ( $guide_next ) : ?>
						<a class="guide-lesson-nav__link guide-lesson-nav__link--next" id="guide-next-link"
							href="<?php echo esc_url( \Guide\Structure\Path_Player::lesson_url( (int) $guide_path->ID, (int) $guide_next->ID ) ); ?>">
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'Up next', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php echo esc_html( get_the_title( $guide_next ) ); ?></span>
							</span>
							<?php echo guide_icon( 'arrow-right' ); ?>
						</a>
					<?php else : ?>
						<a class="guide-lesson-nav__link guide-lesson-nav__link--next" href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>">
							<span>
								<span class="guide-lesson-nav__label"><?php esc_html_e( 'End of path', 'guide-wp-theme' ); ?></span>
								<span class="guide-lesson-nav__title"><?php esc_html_e( 'Back to overview', 'guide-wp-theme' ); ?></span>
							</span>
							<?php echo guide_icon( 'trophy-fill' ); ?>
						</a>
					<?php endif; ?>
				</nav>
			</div>
		</article>
	</div>
</main>

<div class="guide-snackbar" id="guide-snackbar" role="status" aria-live="polite"></div>

<?php
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

wp_footer();
?>
</body>
</html>
