<?php
/**
 * Company guides.
 *
 * "How do I get into Accenture" is the question this audience actually asks,
 * so the cards lead with the three things that decide whether to read on: what
 * kind of company it is, roughly what it pays a fresher, and how hard it is.
 */

defined( 'ABSPATH' ) || exit;

use Guide\Companies\Companies;

get_header();

$guide_types = get_terms(
	array(
		'taxonomy'   => Companies::TAXONOMY,
		'hide_empty' => true,
	)
);

$guide_type_filter = isset( $_GET['type'] ) ? sanitize_title( wp_unslash( (string) $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="guide-shell">
	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Company guides', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'How to get in', 'guide-wp-theme' ); ?></h1>
		<p class="guide-page-head__lede">
			<?php esc_html_e( 'How each company actually hires, the rounds in the order you meet them, what they pay, and which skills to have ready — with the course that teaches each one.', 'guide-wp-theme' ); ?>
		</p>

		<?php if ( $guide_types && ! is_wp_error( $guide_types ) ) : ?>
			<div class="guide-active-filters mt-4">
				<a class="guide-chip <?php echo $guide_type_filter ? 'guide-chip--outline' : 'guide-chip--primary'; ?>"
					href="<?php echo esc_url( get_post_type_archive_link( Companies::POST_TYPE ) ); ?>">
					<?php esc_html_e( 'All', 'guide-wp-theme' ); ?>
				</a>
				<?php foreach ( $guide_types as $guide_type ) : ?>
					<a class="guide-chip <?php echo $guide_type_filter === $guide_type->slug ? 'guide-chip--primary' : 'guide-chip--outline'; ?>"
						href="<?php echo esc_url( add_query_arg( 'type', $guide_type->slug, get_post_type_archive_link( Companies::POST_TYPE ) ) ); ?>">
						<?php echo esc_html( $guide_type->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

	<div class="guide-section guide-section--tight">
		<?php
		$guide_args = array(
			'post_type'      => Companies::POST_TYPE,
			'posts_per_page' => 60,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( $guide_type_filter ) {
			$guide_args['tax_query'] = array(
				array(
					'taxonomy' => Companies::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $guide_type_filter,
				),
			);
		}

		$guide_companies = new WP_Query( $guide_args );
		?>

		<?php if ( $guide_companies->have_posts() ) : ?>
			<div class="guide-grid">
				<?php while ( $guide_companies->have_posts() ) : ?>
					<?php
					$guide_companies->the_post();
					$guide_id    = get_the_ID();
					$guide_band  = Companies::fresher_band( $guide_id );
					$guide_diff  = Companies::difficulty( $guide_id );
					$guide_terms = get_the_terms( $guide_id, Companies::TAXONOMY );
					?>
					<article class="guide-card guide-card--link">
						<div class="guide-card__body">
							<div class="guide-company-card__top">
								<?php if ( has_post_thumbnail() ) : ?>
									<span class="guide-company-logo"><?php the_post_thumbnail( 'thumbnail', array( 'alt' => '' ) ); ?></span>
								<?php else : ?>
									<span class="guide-company-logo guide-company-logo--letter"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></span>
								<?php endif; ?>

								<div style="min-width:0">
									<h2 class="guide-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
									<?php if ( $guide_terms && ! is_wp_error( $guide_terms ) ) : ?>
										<p class="guide-story-card__role"><?php echo esc_html( $guide_terms[0]->name ); ?></p>
									<?php endif; ?>
								</div>
							</div>

							<?php if ( has_excerpt() ) : ?>
								<p class="guide-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
							<?php endif; ?>

							<dl class="guide-company-facts">
								<?php if ( $guide_band ) : ?>
									<div>
										<dt><?php esc_html_e( 'Fresher band', 'guide-wp-theme' ); ?></dt>
										<dd><?php echo esc_html( Companies::format_band( $guide_band['min'], $guide_band['max'] ) ); ?></dd>
									</div>
								<?php endif; ?>
								<div>
									<dt><?php esc_html_e( 'Difficulty', 'guide-wp-theme' ); ?></dt>
									<dd>
										<span class="guide-difficulty" aria-label="<?php echo esc_attr( Companies::difficulty_label( $guide_id ) ); ?>">
											<?php for ( $guide_i = 1; $guide_i <= 5; $guide_i++ ) : ?>
												<span class="guide-difficulty__pip<?php echo $guide_i <= $guide_diff ? ' is-on' : ''; ?>"></span>
											<?php endfor; ?>
										</span>
									</dd>
								</div>
							</dl>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="guide-empty">
				<span class="guide-empty__icon"><?php echo guide_icon( 'buildings' ); ?></span>
				<p class="guide-empty__title"><?php esc_html_e( 'No company guides yet', 'guide-wp-theme' ); ?></p>
				<p class="guide-empty__text"><?php esc_html_e( 'They are written in the admin under LMS → Companies.', 'guide-wp-theme' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
