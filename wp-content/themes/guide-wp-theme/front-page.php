<?php
/**
 * Front page.
 *
 * The page has one argument to make, and it is the argument in
 * abstract/02-problem-and-motive.md: everything a training institute charges
 * ₹80,000 for is already free on the internet — what is missing is the order,
 * the filter, and someone telling you what to do next. So the page leads with
 * that, not with feature bullets.
 *
 * The hero's right column shows the actual product: a real learning path
 * pulled from the database, or — for a signed-in learner — their own progress,
 * which is the most useful thing this page can possibly offer them.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_has_api      = class_exists( 'Guide\\Course_Api' );
$guide_paths        = get_posts(
	array(
		'post_type'      => 'learning_path',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);
$guide_course_count = (int) wp_count_posts( 'course' )->publish;
$guide_lesson_count = (int) wp_count_posts( 'lesson' )->publish;

/* ---- What goes in the hero's right column ---- */

$guide_resume = null;
if ( is_user_logged_in() && class_exists( 'Guide\\Progress\\Progress' ) ) {
	foreach ( \Guide\Progress\Progress::user_overview( get_current_user_id() ) as $guide_entry ) {
		if ( $guide_entry['percent'] < 100 && $guide_entry['resume'] ) {
			$guide_resume = $guide_entry;
			break;
		}
	}
}

$guide_hero_path  = $guide_paths[0] ?? null;
$guide_hero_steps = ( $guide_hero_path && $guide_has_api )
	? \Guide\Course_Api::get_path_steps( $guide_hero_path->ID )
	: array();

$guide_hero_minutes = 0;
foreach ( $guide_hero_steps as $guide_hs ) {
	$guide_hero_minutes += 'course' === $guide_hs['type']
		? (int) \Guide\Course_Api::get_stats( $guide_hs['id'] )['minutes']
		: (int) get_post_meta( $guide_hs['id'], 'jsl_duration_minutes', true );
}

// The hero always has a right column now: a resume card for somebody
// mid-course, and the animated sequence for everybody else.
$guide_has_visual = true;
?>

<!-- ============================= Hero ============================= -->
<section class="guide-hero">
	<?php
	// Ambient artwork behind the hero. Purely decorative and aria-hidden — the
	// hero's right column still carries the real content, because a homepage
	// that shows a drawing instead of the actual product is an advertisement.
	echo guide_illustration( 'hero-backdrop', 'guide-hero__backdrop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>

	<div class="guide-shell guide-hero__inner">
		<div class="guide-hero__grid <?php echo $guide_has_visual ? 'has-visual' : ''; ?>">

			<div>
				<span class="guide-chip guide-chip--spark">
					<?php echo guide_icon( 'sparkle-fill' ); ?>
					<?php echo esc_html( guide_option( 'guide_hero_eyebrow' ) ); ?>
				</span>

				<h1 class="guide-display mt-5">
					<?php echo esc_html( guide_option( 'guide_hero_heading' ) ); ?>
				</h1>

				<p class="guide-hero__lede">
					<?php echo esc_html( guide_option( 'guide_hero_lede' ) ); ?>
				</p>

				<div class="guide-hero__actions">
					<a class="button is-primary is-medium" href="#paths">
						<?php echo esc_html( guide_option( 'guide_hero_cta' ) ); ?>
					</a>
					<a class="button is-medium" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>">
						<?php echo esc_html( guide_option( 'guide_hero_cta_alt' ) ); ?>
					</a>
				</div>

				<?php if ( guide_shows( 'guide_show_stats' ) ) : ?>
				<div class="guide-hero__stats">
					<span><strong><?php echo esc_html( number_format_i18n( count( $guide_paths ) ) ); ?></strong> <?php esc_html_e( 'paths', 'guide-wp-theme' ); ?></span>
					<span><strong><?php echo esc_html( number_format_i18n( $guide_course_count ) ); ?></strong> <?php esc_html_e( 'courses', 'guide-wp-theme' ); ?></span>
					<span><strong><?php echo esc_html( number_format_i18n( $guide_lesson_count ) ); ?></strong> <?php esc_html_e( 'lessons', 'guide-wp-theme' ); ?></span>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $guide_resume ) : ?>
				<?php
				$guide_r_course = $guide_resume['course'];
				$guide_r_next   = $guide_resume['resume'];
				?>
				<div class="guide-hero-card">
					<span class="guide-chip guide-chip--primary"><?php esc_html_e( 'Welcome back', 'guide-wp-theme' ); ?></span>

					<h2 class="guide-hero-card__title mt-4"><?php echo esc_html( get_the_title( $guide_r_course ) ); ?></h2>

					<div class="mt-5">
						<div class="guide-progress-label">
							<span>
								<?php
								printf(
									/* translators: %d: completion percentage. */
									esc_html__( '%d%% complete', 'guide-wp-theme' ),
									(int) $guide_resume['percent']
								);
								?>
							</span>
						</div>
						<span class="guide-progress">
							<span class="guide-progress__bar" style="width:<?php echo esc_attr( (string) (int) $guide_resume['percent'] ); ?>%"></span>
						</span>
					</div>

					<div class="guide-hero-card__next mt-5">
						<p class="guide-filter-group__label"><?php esc_html_e( 'Up next', 'guide-wp-theme' ); ?></p>
						<p class="mt-1 has-text-weight-semibold"><?php echo esc_html( get_the_title( $guide_r_next ) ); ?></p>
					</div>

					<a class="button is-primary is-fullwidth mt-5" href="<?php echo esc_url( get_permalink( $guide_r_next ) ); ?>">
						<?php esc_html_e( 'Continue learning', 'guide-wp-theme' ); ?>
					</a>
				</div>

			<?php else : ?>
				<?php
				// The animated sequence, for anybody who has not started yet.
				//
				// Three scenes in the order the argument is made: applications
				// going out with nothing coming back, a path with the steps
				// ticked off one at a time, and the door. A returning learner
				// gets the resume card above instead — "carry on where you
				// stopped" beats any illustration.
				//
				// aria-hidden, and every claim it makes is also in the text
				// beside it, so nothing is lost with images off or motion
				// disabled.
				?>
				<div class="guide-hero-scenes" role="presentation">
					<?php echo guide_illustration( 'hero-scenes' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

					<div class="guide-hero-scenes__dots" aria-hidden="true">
						<span></span><span></span><span></span>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>

<!-- ====================== The problem, stated ====================== -->
<?php if ( guide_shows( 'guide_show_problem' ) ) : ?>
<section class="guide-section guide-section--alt" aria-labelledby="guide-problem">
	<div class="guide-shell">
		<div class="guide-problem">
			<div>
				<span class="guide-eyebrow"><?php esc_html_e( 'Why this exists', 'guide-wp-theme' ); ?></span>
				<h2 id="guide-problem" class="title is-3 mt-2">
					<?php esc_html_e( 'The content was always free. The map was not.', 'guide-wp-theme' ); ?>
				</h2>
				<div class="guide-prose mt-4">
					<p><?php esc_html_e( 'Training institutes are not selling knowledge. They are selling sequence — “learn this, then this” — filtering, and someone expecting you tomorrow. All three can be given away, so here they are.', 'guide-wp-theme' ); ?></p>
					<p><?php esc_html_e( 'This platform was built by someone who was rejected 33 times, took a ₹13,000-a-month job because the fresher tag is a door you only need opened once, and moved to a real salary three months later by treating the job hunt as a subject worth studying.', 'guide-wp-theme' ); ?></p>
				</div>
				<?php
				// Thirty-three crosses and one tick. The number is not decorative
				// — it is the actual count, and it is stated in the paragraph
				// directly above, so the drawing adds emphasis rather than
				// information.
				echo guide_illustration( 'rejection-arc', 'guide-illus--block' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>

				<?php if ( get_page_by_path( 'my-story' ) ) : ?>
					<a class="button is-primary mt-4" href="<?php echo esc_url( home_url( '/my-story/' ) ); ?>">
						<?php esc_html_e( 'Read the whole story', 'guide-wp-theme' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="guide-compare">
				<div class="guide-compare__col guide-compare__col--them">
					<h3><?php esc_html_e( 'A training institute', 'guide-wp-theme' ); ?></h3>
					<ul>
						<li><?php esc_html_e( '₹50,000–₹1,50,000 upfront', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'One syllabus for everyone', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'Teaches tools, skips foundations', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( '“Placement assistance”', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'Ends when the course ends', 'guide-wp-theme' ); ?></li>
					</ul>
				</div>
				<div class="guide-compare__col guide-compare__col--us">
					<h3><?php bloginfo( 'name' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Free, for the whole core path', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'A path built from your answers', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'Foundations first, tools second', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'Job search taught as its own subject', 'guide-wp-theme' ); ?></li>
						<li><?php esc_html_e( 'Continues through offer and first switch', 'guide-wp-theme' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ========================= How it works ========================= -->
<?php if ( guide_shows( 'guide_show_how' ) ) : ?>
<section class="guide-section guide-section--tight" aria-labelledby="guide-how">
	<div class="guide-shell">
		<h2 id="guide-how" class="is-sr-only"><?php esc_html_e( 'How it works', 'guide-wp-theme' ); ?></h2>

		<?php echo guide_illustration( 'path-climb', 'guide-illus--climb-wrap' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="guide-how">
			<?php
			$guide_steps_how = array(
				array(
					'icon'  => 'compass',
					'title' => __( 'Understand the industry first', 'guide-wp-theme' ),
					'text'  => __( 'Before any code: what software is, what each team does all day, what the roles actually pay. You cannot choose a destination you cannot see.', 'guide-wp-theme' ),
				),
				array(
					'icon'  => 'stack-fill',
					'title' => __( 'Build the foundation', 'guide-wp-theme' ),
					'text'  => __( 'Operating systems, databases, networks, OOP, DSA — the subjects that survive when the frameworks change. No maths required, and we mean that.', 'guide-wp-theme' ),
				),
				array(
					'icon'  => 'briefcase-fill',
					'title' => __( 'Learn the hunt itself', 'guide-wp-theme' ),
					'text'  => __( 'Résumé, referrals, using AI properly, HR rounds, negotiation, and the first switch. This is the part that turns everything above it into income.', 'guide-wp-theme' ),
				),
			);
			foreach ( $guide_steps_how as $guide_how ) :
				?>
				<div class="guide-how__item">
					<span class="guide-how__icon"><?php echo guide_icon( $guide_how['icon'] ); ?></span>
					<div>
						<h3 class="guide-how__title"><?php echo esc_html( $guide_how['title'] ); ?></h3>
						<p class="guide-how__text"><?php echo esc_html( $guide_how['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ========================= Learning paths ======================== -->
<div id="paths" class="guide-shell">
	<?php if ( empty( $guide_paths ) ) : ?>
		<div class="guide-empty" style="margin-block:3rem">
			<p class="guide-empty__title"><?php esc_html_e( 'No learning paths yet — check back soon.', 'guide-wp-theme' ); ?></p>
		</div>
	<?php endif; ?>

	<?php foreach ( $guide_paths as $guide_i => $guide_path ) : ?>
		<section class="guide-section guide-section--tight" aria-labelledby="path-<?php echo esc_attr( (string) $guide_path->ID ); ?>">

			<div class="guide-path-head">
				<div>
					<span class="guide-chip guide-chip--primary">
						<?php echo guide_icon( 'path-fill' ); ?>
						<?php
						printf(
							/* translators: %s: zero-padded path number. */
							esc_html__( 'Path %s', 'guide-wp-theme' ),
							esc_html( str_pad( (string) ( $guide_i + 1 ), 2, '0', STR_PAD_LEFT ) )
						);
						?>
					</span>

					<h2 id="path-<?php echo esc_attr( (string) $guide_path->ID ); ?>" class="title is-4 mt-3">
						<a href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>"><?php echo esc_html( get_the_title( $guide_path ) ); ?></a>
					</h2>

					<?php if ( $guide_path->post_excerpt ) : ?>
						<p class="mt-2" style="color:var(--bulma-text-weak);max-width:60ch"><?php echo esc_html( $guide_path->post_excerpt ); ?></p>
					<?php endif; ?>
				</div>

				<a class="button" href="<?php echo esc_url( get_permalink( $guide_path ) ); ?>">
					<?php esc_html_e( 'View path', 'guide-wp-theme' ); ?>
				</a>
			</div>

			<?php
			$guide_courses = $guide_has_api ? \Guide\Course_Api::get_path_courses( $guide_path->ID ) : array();
			if ( ! empty( $guide_courses ) ) :
				?>
				<div class="guide-grid mt-5">
					<?php
					global $post;
					foreach ( $guide_courses as $guide_course ) :
						$post = $guide_course; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/course-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p class="mt-4" style="color:var(--bulma-text-weak)"><?php esc_html_e( 'Courses for this path are coming soon.', 'guide-wp-theme' ); ?></p>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<?php
	// Pricing: only shown once a subscription is actually configured, so a site
	// that only sells individual courses never advertises a plan nobody can buy.
	$guide_sub_on = class_exists( 'Guide\\Payments\\Subscription' ) && \Guide\Payments\Subscription::is_enabled();
	if ( $guide_sub_on && guide_shows( 'guide_show_pricing' ) ) :
		$guide_sub_price  = \Guide\Payments\Subscription::price_label();
		$guide_sub_blurb  = \Guide\Payments\Subscription::blurb();
		$guide_all_access = class_exists( 'Guide\\Access\\Access' ) && \Guide\Access\Access::has_all_access();
		?>
		<section class="guide-section" aria-labelledby="guide-pricing-head">
			<div style="text-align:center">
				<span class="guide-eyebrow"><?php esc_html_e( 'Membership', 'guide-wp-theme' ); ?></span>
				<h2 id="guide-pricing-head" class="title is-3 mt-2"><?php esc_html_e( 'One subscription. No per-course prices.', 'guide-wp-theme' ); ?></h2>
				<p class="mt-3" style="max-width:56ch;margin-inline:auto;color:var(--bulma-text-weak)">
					<?php esc_html_e( 'Courses are never sold individually — being asked to buy something at every step of a path is exactly the paralysis this platform exists to remove. The foundations stay free. One subscription opens everything else and turns the ads off.', 'guide-wp-theme' ); ?>
				</p>
			</div>

			<div class="guide-pricing mt-6">
				<div class="guide-card" style="padding:1.75rem">
					<h3 class="guide-card__title"><?php esc_html_e( 'Free', 'guide-wp-theme' ); ?></h3>
					<p class="guide-enroll-card__price mt-3"><?php esc_html_e( '₹0', 'guide-wp-theme' ); ?></p>
					<p class="guide-card__excerpt mt-2"><?php esc_html_e( 'The core path — orientation, foundations, one language, projects, and the whole job-search module.', 'guide-wp-theme' ); ?></p>
					<ul class="guide-check-list mt-4">
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Every free course, in order', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Progress tracking and quizzes', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'No card, no trial, no expiry', 'guide-wp-theme' ); ?></li>
					</ul>
					<a class="button is-fullwidth mt-5" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'guide-wp-theme' ); ?></a>
				</div>

				<div class="guide-card guide-card--featured" style="padding:1.75rem">
					<span class="guide-card__ribbon"><?php esc_html_e( 'Members', 'guide-wp-theme' ); ?></span>
					<h3 class="guide-card__title"><?php esc_html_e( 'Everything', 'guide-wp-theme' ); ?></h3>
					<?php if ( $guide_sub_price ) : ?>
						<p class="guide-enroll-card__price mt-3"><?php echo esc_html( $guide_sub_price ); ?></p>
					<?php endif; ?>
					<p class="guide-card__excerpt mt-2">
						<?php echo $guide_sub_blurb ? esc_html( $guide_sub_blurb ) : esc_html__( 'Every course and every path, for as long as your subscription is active.', 'guide-wp-theme' ); ?>
					</p>
					<ul class="guide-check-list mt-4">
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Every course on the platform', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Every new course as it lands', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'No ads, anywhere', 'guide-wp-theme' ); ?></li>
						<li><?php echo guide_icon( 'check-circle-fill' ); ?><?php esc_html_e( 'Cancel the day you are hired', 'guide-wp-theme' ); ?></li>
					</ul>

					<?php if ( $guide_all_access ) : ?>
						<p class="guide-notice guide-notice--success mt-5">
							<?php echo guide_icon( 'check-circle-fill' ); ?>
							<span><?php esc_html_e( 'You have full access', 'guide-wp-theme' ); ?></span>
						</p>
					<?php elseif ( is_user_logged_in() ) : ?>
						<button type="button" class="button is-primary is-fullwidth mt-5" id="guide-home-subscribe"><?php esc_html_e( 'Subscribe', 'guide-wp-theme' ); ?></button>
						<p class="mt-2 is-size-7 has-text-centered" id="guide-home-subscribe-status" aria-live="polite"></p>
					<?php else : ?>
						<a class="button is-primary is-fullwidth mt-5" href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign in to subscribe', 'guide-wp-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// One slot on the homepage, above the closing call to action and below
	// everything that argues the case. Never between the hero and the paths —
	// the first screen has one job and selling advertising is not it.
	?>
	<div class="guide-shell">
		<?php guide_ad( 'feed' ); ?>
	</div>

	<!-- ============================ CTA ============================ -->
	<section class="guide-cta">
		<div class="guide-cta__inner">
			<h2 class="title is-2"><?php esc_html_e( 'Six months pass either way', 'guide-wp-theme' ); ?></h2>
			<p class="mt-3">
				<?php esc_html_e( 'You can spend them on somebody else’s syllabus, ₹80,000 lighter — or on this one, starting today, for free. Create an account and pick up exactly where you left off every time.', 'guide-wp-theme' ); ?>
			</p>
			<div class="guide-hero__actions" style="justify-content:center">
				<a class="button is-primary is-medium" href="<?php echo esc_url( is_user_logged_in() ? '#paths' : wp_registration_url() ); ?>">
					<?php esc_html_e( 'Get started free', 'guide-wp-theme' ); ?>
				</a>
				<a class="button is-medium" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>">
					<?php esc_html_e( 'See all paths', 'guide-wp-theme' ); ?>
				</a>
			</div>
		</div>
	</section>
</div>

<?php
if ( $guide_sub_on && is_user_logged_in() ) {
	wp_enqueue_script( 'guide-subscribe', GUIDE_THEME_URI . '/assets/js/subscribe.js', array(), guide_asset_version( '/assets/js/subscribe.js' ), true );
	wp_localize_script(
		'guide-subscribe',
		'guideSubscribe',
		array(
			'restUrl' => esc_url_raw( rest_url( 'guide/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);
}

get_footer();
