<?php
/**
 * Archive: a sidebar filter rail beside a grid of cards.
 *
 * The rail only appears for the course catalogue and course-category archives,
 * where filtering means something. Everything else (learning paths, blog
 * archives) gets the plain grid.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_filterable = is_post_type_archive( 'course' ) || is_tax( 'course_category' );
$guide_state      = $guide_filterable ? guide_course_filter_state() : array();
?>

<div class="guide-shell">
	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Browse', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php the_archive_title(); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="guide-page-head__lede"><?php the_archive_description(); ?></div>
		<?php elseif ( is_post_type_archive( 'course' ) ) : ?>
			<p class="guide-page-head__lede">
				<?php esc_html_e( 'Every course on the platform. Start with the foundations — they carry every role you might end up in — then pick a language and a track.', 'guide-wp-theme' ); ?>
			</p>
		<?php endif; ?>
	</header>

	<div class="<?php echo $guide_filterable ? 'guide-with-rail' : ''; ?>" style="padding-bottom:4rem">

		<?php if ( $guide_filterable ) : ?>
			<div class="guide-rail">
				<?php get_template_part( 'template-parts/course-filters' ); ?>
			</div>
		<?php endif; ?>

		<div>
			<?php if ( $guide_filterable ) : ?>
				<div class="guide-toolbar">
					<span class="guide-toolbar__count">
						<?php
						global $wp_query;
						$guide_found = (int) $wp_query->found_posts;
						printf(
							/* translators: %s: number of matching courses. */
							esc_html( _n( '%s course', '%s courses', $guide_found, 'guide-wp-theme' ) ),
							esc_html( number_format_i18n( $guide_found ) )
						);
						?>
					</span>
				</div>

				<?php if ( guide_course_filters_active() ) : ?>
					<div class="guide-active-filters">
						<?php foreach ( $guide_state['topics'] as $guide_topic_id ) : ?>
							<?php
							$guide_term = get_term( $guide_topic_id, 'course_category' );
							if ( ! $guide_term || is_wp_error( $guide_term ) ) {
								continue;
							}
							?>
							<a class="guide-active-filter" href="<?php echo esc_url( guide_course_filter_url( 'topic', $guide_topic_id ) ); ?>">
								<?php echo esc_html( $guide_term->name ); ?>
								<?php echo guide_icon( 'x' ); ?>
							</a>
						<?php endforeach; ?>

						<?php if ( $guide_state['level'] ) : ?>
							<a class="guide-active-filter" href="<?php echo esc_url( guide_course_filter_url( 'level', $guide_state['level'] ) ); ?>">
								<?php echo esc_html( ucfirst( $guide_state['level'] ) ); ?>
								<?php echo guide_icon( 'x' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $guide_state['price'] ) : ?>
							<a class="guide-active-filter" href="<?php echo esc_url( guide_course_filter_url( 'price', $guide_state['price'] ) ); ?>">
								<?php echo esc_html( ucfirst( $guide_state['price'] ) ); ?>
								<?php echo guide_icon( 'x' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $guide_state['q'] ) : ?>
							<a class="guide-active-filter" href="<?php echo esc_url( guide_course_filter_url( 'q', '' ) ); ?>">
								<?php echo esc_html( sprintf( '“%s”', $guide_state['q'] ) ); ?>
								<?php echo guide_icon( 'x' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="guide-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/course-card' );
					endwhile;
					?>
				</div>

				<div class="mt-6">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'class'     => 'pagination is-centered',
							'prev_text' => esc_html__( 'Previous', 'guide-wp-theme' ),
							'next_text' => esc_html__( 'Next', 'guide-wp-theme' ),
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="guide-empty">
					<span class="guide-empty__icon"><?php echo guide_icon( 'magnifying-glass' ); ?></span>
					<p class="guide-empty__title"><?php esc_html_e( 'Nothing matches those filters', 'guide-wp-theme' ); ?></p>
					<p class="guide-empty__text">
						<?php esc_html_e( 'Try widening one of them — or clear them all and browse the whole catalogue. Being unsure what to pick is normal; that is what the foundations are for.', 'guide-wp-theme' ); ?>
					</p>
					<a class="button is-primary" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>">
						<?php esc_html_e( 'Clear filters', 'guide-wp-theme' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
