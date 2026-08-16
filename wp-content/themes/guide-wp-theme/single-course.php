<?php
/**
 * Course landing page: dark hero with meta, sticky enrol card, and an
 * accordion curriculum showing per-lesson duration, preview and completion.
 *
 * Locked lessons stay visible rather than being hidden — seeing what is behind
 * the wall is more motivating than being shown a shorter list.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$guide_course_id = get_the_ID();
	$guide_has_api   = class_exists( 'Guide\\Course_Api' );
	$guide_modules   = $guide_has_api ? \Guide\Course_Api::get_modules( $guide_course_id ) : array();
	$guide_stats     = $guide_has_api
		? \Guide\Course_Api::get_stats( $guide_course_id )
		: array(
			'modules' => 0,
			'lessons' => 0,
			'minutes' => 0,
		);
	$guide_is_premium = class_exists( 'Guide\\Payments\\Course_Access' )
		&& \Guide\Payments\Course_Access::is_premium( $guide_course_id );

	$guide_user_id = get_current_user_id();

	// "Enrolled" is not the only way in: a subscription (or being staff) grants
	// access too. Ask the access layer, not the enrollment table, so every
	// surface agrees about who can see what.
	$guide_has_access = class_exists( 'Guide\\Access\\Access' )
		? \Guide\Access\Access::can_view_course_content( $guide_course_id, $guide_user_id )
		: true;
	$guide_is_enrolled = $guide_user_id
		&& class_exists( 'Guide\\Enrollment\\Enrollment' )
		&& \Guide\Enrollment\Enrollment::is_enrolled( $guide_user_id, $guide_course_id );
	// Access to a premium course comes from the subscription (or staff), since
	// courses cannot be bought individually any more.
	$guide_via_plan = $guide_has_access && $guide_is_premium;

	$guide_subscription_on = class_exists( 'Guide\\Payments\\Subscription' )
		&& \Guide\Payments\Subscription::is_enabled();
	$guide_sub_price = $guide_subscription_on ? \Guide\Payments\Subscription::price_label() : '';

	$guide_completed = $guide_user_id && class_exists( 'Guide\\Progress\\Progress' )
		? \Guide\Progress\Progress::completed_lesson_ids( $guide_user_id, $guide_course_id )
		: array();
	$guide_progress = $guide_stats['lessons'] > 0
		? (int) round( count( $guide_completed ) / $guide_stats['lessons'] * 100 )
		: 0;

	$guide_resume = $guide_has_api ? ( \Guide\Course_Api::get_lessons_flat( $guide_course_id )[0] ?? null ) : null;
	if ( $guide_has_api && ! empty( $guide_completed ) ) {
		foreach ( \Guide\Course_Api::get_lessons_flat( $guide_course_id ) as $guide_candidate ) {
			if ( ! in_array( (int) $guide_candidate->ID, $guide_completed, true ) ) {
				$guide_resume = $guide_candidate;
				break;
			}
		}
	}

	$guide_code  = class_exists( 'Guide\\Course_Meta' ) ? \Guide\Course_Meta::get_code( $guide_course_id ) : '';
	$guide_level = class_exists( 'Guide\\Course_Meta' ) ? \Guide\Course_Meta::get_level( $guide_course_id ) : '';
	?>

	<section class="guide-course-hero">
		<div class="guide-shell">
			<nav class="guide-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'guide-wp-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'guide-wp-theme' ); ?></a>
				<span>/</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Courses', 'guide-wp-theme' ); ?></a>
			</nav>

			<div class="guide-course-hero__grid">
				<div>
					<div class="guide-course-hero__meta" style="margin-top:1rem">
						<?php if ( $guide_code ) : ?>
							<span class="guide-chip guide-chip--spark"><?php echo esc_html( $guide_code ); ?></span>
						<?php endif; ?>
						<span class="guide-price-tag <?php echo $guide_is_premium ? 'guide-price-tag--paid' : 'guide-price-tag--free'; ?>">
							<?php echo $guide_is_premium ? esc_html__( 'Members', 'guide-wp-theme' ) : esc_html__( 'Free', 'guide-wp-theme' ); ?>
						</span>
						<?php if ( $guide_is_enrolled || $guide_via_plan ) : ?>
							<span class="guide-chip guide-chip--primary">
								<?php echo guide_icon( 'check' ); ?>
								<?php echo $guide_via_plan ? esc_html__( 'In your plan', 'guide-wp-theme' ) : esc_html__( 'Enrolled', 'guide-wp-theme' ); ?>
							</span>
						<?php endif; ?>
					</div>

					<h1 class="guide-display mt-3"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<p class="guide-course-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<div class="guide-course-hero__meta">
						<span class="guide-chip guide-chip--outline">
							<?php echo guide_icon( 'stack' ); ?>
							<?php
							printf(
								/* translators: %d: number of modules. */
								esc_html( _n( '%d module', '%d modules', (int) $guide_stats['modules'], 'guide-wp-theme' ) ),
								(int) $guide_stats['modules']
							);
							?>
						</span>
						<span class="guide-chip guide-chip--outline">
							<?php echo guide_icon( 'article' ); ?>
							<?php
							printf(
								/* translators: %d: number of lessons. */
								esc_html( _n( '%d lesson', '%d lessons', (int) $guide_stats['lessons'], 'guide-wp-theme' ) ),
								(int) $guide_stats['lessons']
							);
							?>
						</span>
						<?php if ( $guide_stats['minutes'] ) : ?>
							<span class="guide-chip guide-chip--outline">
								<?php echo guide_icon( 'clock' ); ?>
								<?php
								printf(
									/* translators: %d: total minutes. */
									esc_html__( '%d min total', 'guide-wp-theme' ),
									(int) $guide_stats['minutes']
								);
								?>
							</span>
						<?php endif; ?>
						<?php
						if ( $guide_level ) :
							$guide_level_labels = array(
								'beginner'     => __( 'Beginner', 'guide-wp-theme' ),
								'intermediate' => __( 'Intermediate', 'guide-wp-theme' ),
								'advanced'     => __( 'Advanced', 'guide-wp-theme' ),
							);
							?>
							<span class="guide-chip guide-chip--outline">
								<?php echo guide_icon( 'target' ); ?>
								<?php echo esc_html( $guide_level_labels[ $guide_level ] ?? $guide_level ); ?>
							</span>
						<?php endif; ?>
					</div>

					<?php if ( $guide_user_id && $guide_has_access && $guide_stats['lessons'] ) : ?>
						<div class="mt-5" style="max-width:26rem">
							<div class="guide-progress-label">
								<span>
									<?php
									printf(
										/* translators: 1: completed lessons, 2: total lessons. */
										esc_html__( '%1$d of %2$d lessons complete', 'guide-wp-theme' ),
										(int) count( $guide_completed ),
										(int) $guide_stats['lessons']
									);
									?>
								</span>
								<span><?php echo esc_html( (string) $guide_progress ); ?>%</span>
							</div>
							<span class="guide-progress">
								<span class="guide-progress__bar<?php echo 100 === $guide_progress ? ' guide-progress__bar--complete' : ''; ?>" style="width:<?php echo esc_attr( (string) $guide_progress ); ?>%"></span>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<aside class="guide-enroll-card" id="guide-enroll-box" data-course-id="<?php echo esc_attr( (string) $guide_course_id ); ?>">
					<?php if ( $guide_via_plan ) : ?>
						<p class="guide-enroll-card__price"><?php esc_html_e( 'Included in your plan', 'guide-wp-theme' ); ?></p>
						<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)"><?php esc_html_e( 'Your subscription covers this course.', 'guide-wp-theme' ); ?></p>
					<?php elseif ( $guide_is_premium ) : ?>
						<p class="guide-enroll-card__price">
							<?php echo $guide_sub_price ? esc_html( $guide_sub_price ) : esc_html__( 'Members', 'guide-wp-theme' ); ?>
						</p>
						<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
							<?php esc_html_e( 'Part of the subscription — one price, every course on the platform.', 'guide-wp-theme' ); ?>
						</p>
					<?php else : ?>
						<p class="guide-enroll-card__price"><?php esc_html_e( 'Free', 'guide-wp-theme' ); ?></p>
						<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
							<?php esc_html_e( 'Full access, no card required.', 'guide-wp-theme' ); ?>
						</p>
					<?php endif; ?>

					<div class="mt-4">
						<?php if ( $guide_has_access && $guide_resume ) : ?>
							<a class="button is-primary is-fullwidth" href="<?php echo esc_url( get_permalink( $guide_resume ) ); ?>">
								<?php echo $guide_progress > 0 ? esc_html__( 'Continue learning', 'guide-wp-theme' ) : esc_html__( 'Start course', 'guide-wp-theme' ); ?>
							</a>
						<?php elseif ( ! is_user_logged_in() ) : ?>
							<a class="button is-primary is-fullwidth" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
								<?php esc_html_e( 'Sign in to start', 'guide-wp-theme' ); ?>
							</a>
						<?php elseif ( $guide_is_premium ) : ?>
							<?php if ( $guide_subscription_on ) : ?>
								<button type="button" class="button is-primary is-fullwidth" id="guide-subscribe-btn">
									<?php esc_html_e( 'Subscribe for full access', 'guide-wp-theme' ); ?>
								</button>
								<p class="mt-2 is-size-7 has-text-centered" id="guide-enroll-status" aria-live="polite"></p>
							<?php else : ?>
								<p class="guide-notice guide-notice--info">
									<span><?php esc_html_e( 'Subscriptions are not open yet — check back shortly.', 'guide-wp-theme' ); ?></span>
								</p>
							<?php endif; ?>
						<?php else : ?>
							<button type="button" class="button is-primary is-fullwidth" id="guide-enroll-btn">
								<?php esc_html_e( 'Start free', 'guide-wp-theme' ); ?>
							</button>
							<p class="mt-2 is-size-7 has-text-centered" id="guide-enroll-status" aria-live="polite"></p>
						<?php endif; ?>
					</div>

					<ul class="guide-check-list mt-5" style="border-top:1px solid var(--bulma-border-weak);padding-top:1.25rem">
						<li><?php echo guide_icon( 'check' ); ?><?php esc_html_e( 'Structured, ordered curriculum', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check' ); ?><?php esc_html_e( 'Progress tracked per lesson', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check' ); ?><?php esc_html_e( 'Learn at your own pace', 'guide-wp-theme' ); ?></li>
					</ul>
				</aside>
			</div>
		</div>
	</section>

	<?php
	$guide_has_about = (bool) trim( (string) get_the_content() );
	$guide_outcomes  = class_exists( 'Guide\\Course_Meta' ) ? \Guide\Course_Meta::get_outcomes( $guide_course_id ) : array();
	$guide_reqs      = class_exists( 'Guide\\Course_Meta' ) ? \Guide\Course_Meta::get_requirements( $guide_course_id ) : array();
	?>

	<div class="guide-shell guide-section">
		<div style="max-width:56rem;margin-inline:auto">

			<?php if ( ! empty( $guide_outcomes ) ) : ?>
				<?php // Outcomes go above the tabs: it is the question every prospective learner asks first. ?>
				<section class="guide-card" style="padding:1.75rem;margin-bottom:2.5rem" aria-labelledby="guide-outcomes">
					<h2 id="guide-outcomes" class="guide-card__title"><?php esc_html_e( 'What you’ll learn', 'guide-wp-theme' ); ?></h2>
					<ul class="guide-check-list guide-check-list--two mt-4">
						<?php foreach ( $guide_outcomes as $guide_outcome ) : ?>
							<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php echo esc_html( $guide_outcome ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<div class="tabs" data-tabs>
				<ul role="tablist" aria-label="<?php esc_attr_e( 'Course sections', 'guide-wp-theme' ); ?>">
					<li class="is-active">
						<a role="tab" id="tab-curriculum" href="#panel-curriculum" aria-controls="panel-curriculum" aria-selected="true">
							<?php esc_html_e( 'Curriculum', 'guide-wp-theme' ); ?>
						</a>
					</li>
					<?php if ( $guide_has_about ) : ?>
						<li>
							<a role="tab" id="tab-about" href="#panel-about" aria-controls="panel-about" aria-selected="false" tabindex="-1">
								<?php esc_html_e( 'About', 'guide-wp-theme' ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<section id="panel-curriculum" role="tabpanel" aria-labelledby="tab-curriculum" tabindex="0">
				<?php if ( empty( $guide_modules ) ) : ?>
					<div class="guide-empty">
						<p class="guide-empty__title"><?php esc_html_e( 'Content coming soon', 'guide-wp-theme' ); ?></p>
					</div>
				<?php else : ?>
					<div class="guide-curriculum">
						<?php foreach ( $guide_modules as $guide_m_index => $guide_module ) : ?>
							<details class="guide-module" <?php echo 0 === $guide_m_index ? 'open' : ''; ?>>
								<summary class="guide-module__summary">
									<span class="guide-module__index"><?php echo esc_html( str_pad( (string) ( $guide_m_index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="guide-module__title"><?php echo esc_html( $guide_module['title'] ); ?></span>
									<span class="guide-module__count">
										<?php
										printf(
											/* translators: %d: number of lessons in the module. */
											esc_html( _n( '%d lesson', '%d lessons', count( $guide_module['lessons'] ), 'guide-wp-theme' ) ),
											(int) count( $guide_module['lessons'] )
										);
										?>
									</span>
									<span class="guide-module__chevron"><?php echo guide_icon( 'caret-down' ); ?></span>
								</summary>

								<?php
								foreach ( $guide_module['lessons'] as $guide_lesson ) :
									$guide_is_done    = in_array( (int) $guide_lesson->ID, $guide_completed, true );
									$guide_duration   = (int) get_post_meta( $guide_lesson->ID, 'jsl_duration_minutes', true );
									$guide_is_preview = (bool) get_post_meta( $guide_lesson->ID, 'jsl_is_preview', true );
									$guide_locked     = ! $guide_has_access && ! $guide_is_preview;
									$guide_type       = get_post_meta( $guide_lesson->ID, 'jsl_lesson_type', true );
									$guide_type       = $guide_type ? $guide_type : 'article';

									$guide_row_icon = $guide_is_done
										? 'check-circle-fill'
										: ( $guide_locked
											? 'lock-simple'
											: ( 'video' === $guide_type ? 'play-fill' : ( 'quiz' === $guide_type ? 'list-checks' : 'article' ) ) );

									$guide_row_class = 'guide-lesson-row'
										. ( $guide_is_done ? ' is-complete' : '' )
										. ( $guide_locked ? ' is-locked' : '' );
									?>
									<?php if ( $guide_locked ) : ?>
										<div class="<?php echo esc_attr( $guide_row_class ); ?>" aria-disabled="true">
									<?php else : ?>
										<a class="<?php echo esc_attr( $guide_row_class ); ?>" href="<?php echo esc_url( get_permalink( $guide_lesson ) ); ?>">
									<?php endif; ?>

										<span class="guide-lesson-row__icon"><?php echo guide_icon( $guide_row_icon ); ?></span>
										<span class="guide-lesson-row__title"><?php echo esc_html( get_the_title( $guide_lesson ) ); ?></span>

										<?php if ( $guide_is_preview && ! $guide_has_access ) : ?>
											<span class="guide-chip guide-chip--spark"><?php esc_html_e( 'Preview', 'guide-wp-theme' ); ?></span>
										<?php endif; ?>

										<?php if ( $guide_duration ) : ?>
											<span class="guide-lesson-row__duration"><?php echo esc_html( (string) $guide_duration ); ?>m</span>
										<?php endif; ?>

									<?php if ( $guide_locked ) : ?>
										</div>
									<?php else : ?>
										</a>
									<?php endif; ?>
								<?php endforeach; ?>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<?php guide_ad( 'page' ); ?>

			<?php if ( $guide_has_about ) : ?>
				<section id="panel-about" role="tabpanel" aria-labelledby="tab-about" tabindex="0" hidden>
					<div class="guide-prose"><?php the_content(); ?></div>

					<?php if ( ! empty( $guide_reqs ) ) : ?>
						<div class="guide-card mt-6" style="padding:1.5rem">
							<h3 class="guide-card__title"><?php esc_html_e( 'Requirements', 'guide-wp-theme' ); ?></h3>
							<ul class="guide-check-list mt-3">
								<?php foreach ( $guide_reqs as $guide_req ) : ?>
									<li><?php echo guide_icon( 'circle' ); ?><?php echo esc_html( $guide_req ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'guide-course', GUIDE_THEME_URI . '/assets/js/course.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/course.js' ), true );
wp_localize_script(
	'guide-course',
	'guideCourse',
	array(
		'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	)
);

get_footer();
