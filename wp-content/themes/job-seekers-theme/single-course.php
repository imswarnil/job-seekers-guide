<?php
/**
 * Course landing page: hero, pricing/enroll box, module/lesson list.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$course_id = get_the_ID();
	$modules   = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_modules( $course_id ) : array();
	$is_paid   = class_exists( 'JSL\\Payments\\Course_Pricing' ) && \JSL\Payments\Course_Pricing::is_paid( $course_id );
	$price     = $is_paid && class_exists( 'JSL\\Payments\\Course_Pricing' ) ? \JSL\Payments\Course_Pricing::price_label( $course_id ) : '';
	?>

	<div class="jsl-container">
		<div class="jsl-hero" style="display:grid;grid-template-columns:2fr 1fr;gap:var(--jsl-space-6);align-items:start">
			<div>
				<span class="jsl-badge <?php echo $is_paid ? 'jsl-badge--paid' : 'jsl-badge--free'; ?>">
					<?php echo $is_paid ? esc_html__( 'Paid', 'job-seekers-theme' ) : esc_html__( 'Free', 'job-seekers-theme' ); ?>
				</span>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="jsl-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div><?php the_content(); ?></div>
			</div>

			<div class="jsl-price-box" id="jsl-enroll-box" data-course-id="<?php echo esc_attr( $course_id ); ?>">
				<?php if ( $is_paid ) : ?>
					<span class="jsl-price-box__amount"><?php echo esc_html( $price ?: '—' ); ?></span>
				<?php else : ?>
					<span class="jsl-price-box__amount"><?php esc_html_e( 'Free', 'job-seekers-theme' ); ?></span>
				<?php endif; ?>

				<?php if ( is_user_logged_in() ) : ?>
					<button type="button" class="jsl-btn jsl-btn--primary" id="jsl-enroll-btn" style="width:100%">
						<?php echo $is_paid ? esc_html__( 'Enroll now', 'job-seekers-theme' ) : esc_html__( 'Start free', 'job-seekers-theme' ); ?>
					</button>
					<p class="jsl-card__meta" id="jsl-enroll-status" style="margin-top:var(--jsl-space-3)"></p>
				<?php else : ?>
					<a class="jsl-btn jsl-btn--primary" style="width:100%" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
						<?php esc_html_e( 'Log in to enroll', 'job-seekers-theme' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<section aria-labelledby="jsl-course-modules" style="margin-top:var(--jsl-space-6)">
			<h2 id="jsl-course-modules"><?php esc_html_e( 'What you\'ll learn', 'job-seekers-theme' ); ?></h2>

			<?php if ( empty( $modules ) ) : ?>
				<p class="jsl-card__meta"><?php esc_html_e( 'Content coming soon.', 'job-seekers-theme' ); ?></p>
			<?php else : ?>
				<?php foreach ( $modules as $module ) : ?>
					<div class="jsl-module">
						<h3 class="jsl-module__title"><?php echo esc_html( $module['title'] ); ?></h3>
						<ol class="jsl-lesson-list">
							<?php foreach ( $module['lessons'] as $index => $lesson ) : ?>
								<li class="jsl-lesson-list__item">
									<span class="jsl-lesson-list__index"><?php echo esc_html( $index + 1 ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>"><?php echo get_the_title( $lesson ); ?></a>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</section>
	</div>

	<?php
endwhile;

wp_enqueue_script( 'jsl-course', JSL_THEME_URI . '/assets/js/course.js', array(), JSL_THEME_VERSION, true );
wp_localize_script(
	'jsl-course',
	'jslCourse',
	array(
		'restUrl' => esc_url_raw( rest_url( 'jsl/v1' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	)
);

get_footer();
