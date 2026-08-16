<?php
/**
 * Help centre.
 *
 * Grouped by section rather than listed flat: someone arriving here has a
 * problem, not a browsing habit, and a wall of 40 titles is not an answer.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_sections = get_terms(
	array(
		'taxonomy'   => \Guide\Community\Community_Types::HELP_TAX,
		'hide_empty' => true,
		'parent'     => 0,
	)
);

$guide_q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="guide-shell">
	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Help centre', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'How can we help?', 'guide-wp-theme' ); ?></h1>
		<p class="guide-page-head__lede">
			<?php esc_html_e( 'Answers about learning here, the subscription, and getting unstuck. If none of it helps, tell us — that is how this page gets better.', 'guide-wp-theme' ); ?>
		</p>

		<form class="guide-help-search mt-4" method="get" action="<?php echo esc_url( get_post_type_archive_link( \Guide\Community\Community_Types::HELP ) ); ?>" role="search">
			<div class="field has-addons">
				<div class="control is-expanded">
					<input class="input" type="search" name="q" value="<?php echo esc_attr( $guide_q ); ?>"
						placeholder="<?php esc_attr_e( 'Search help…', 'guide-wp-theme' ); ?>"
						aria-label="<?php esc_attr_e( 'Search help articles', 'guide-wp-theme' ); ?>">
				</div>
				<div class="control">
					<button class="button is-primary" type="submit"><?php esc_html_e( 'Search', 'guide-wp-theme' ); ?></button>
				</div>
			</div>
		</form>
	</header>

	<div class="guide-section guide-section--tight">
		<?php if ( $guide_q ) : ?>
			<?php
			$guide_results = new WP_Query(
				array(
					'post_type'      => \Guide\Community\Community_Types::HELP,
					's'              => $guide_q,
					'posts_per_page' => 30,
				)
			);
			?>
			<h2 class="title is-5">
				<?php
				printf(
					/* translators: %s: the search term. */
					esc_html__( 'Results for “%s”', 'guide-wp-theme' ),
					esc_html( $guide_q )
				);
				?>
			</h2>

			<?php if ( $guide_results->have_posts() ) : ?>
				<div class="guide-help-list mt-4">
					<?php while ( $guide_results->have_posts() ) : ?>
						<?php $guide_results->the_post(); ?>
						<a class="guide-help-row" href="<?php the_permalink(); ?>">
							<span class="guide-help-row__title"><?php the_title(); ?></span>
							<?php if ( has_excerpt() ) : ?>
								<span class="guide-help-row__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></span>
							<?php endif; ?>
						</a>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<div class="guide-empty mt-4">
					<p class="guide-empty__title"><?php esc_html_e( 'Nothing matched that', 'guide-wp-theme' ); ?></p>
					<p class="guide-empty__text"><?php esc_html_e( 'Try fewer words, or browse the sections below.', 'guide-wp-theme' ); ?></p>
					<a class="button" href="<?php echo esc_url( get_post_type_archive_link( \Guide\Community\Community_Types::HELP ) ); ?>"><?php esc_html_e( 'All help', 'guide-wp-theme' ); ?></a>
				</div>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>

		<?php elseif ( ! empty( $guide_sections ) && ! is_wp_error( $guide_sections ) ) : ?>
			<div class="guide-help-grid">
				<?php foreach ( $guide_sections as $guide_section ) : ?>
					<?php
					$guide_articles = get_posts(
						array(
							'post_type'      => \Guide\Community\Community_Types::HELP,
							'posts_per_page' => 8,
							'orderby'        => 'menu_order title',
							'order'          => 'ASC',
							'tax_query'      => array(
								array(
									'taxonomy' => \Guide\Community\Community_Types::HELP_TAX,
									'field'    => 'term_id',
									'terms'    => $guide_section->term_id,
								),
							),
						)
					);
					?>
					<section class="guide-card" style="padding:1.5rem">
						<h2 class="guide-card__title"><?php echo esc_html( $guide_section->name ); ?></h2>
						<?php if ( $guide_section->description ) : ?>
							<p class="guide-card__excerpt mt-1"><?php echo esc_html( $guide_section->description ); ?></p>
						<?php endif; ?>

						<div class="guide-footer__list mt-3">
							<?php foreach ( $guide_articles as $guide_article ) : ?>
								<a href="<?php echo esc_url( get_permalink( $guide_article ) ); ?>"><?php echo esc_html( get_the_title( $guide_article ) ); ?></a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>

		<?php elseif ( have_posts() ) : ?>
			<?php // No sections defined yet — a flat list still beats nothing. ?>
			<div class="guide-help-list">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<a class="guide-help-row" href="<?php the_permalink(); ?>">
						<span class="guide-help-row__title"><?php the_title(); ?></span>
					</a>
				<?php endwhile; ?>
			</div>

		<?php else : ?>
			<div class="guide-empty">
				<span class="guide-empty__icon"><?php echo guide_icon( 'question' ); ?></span>
				<p class="guide-empty__title"><?php esc_html_e( 'No help articles yet', 'guide-wp-theme' ); ?></p>
				<p class="guide-empty__text"><?php esc_html_e( 'They are written in the admin under LMS → Help centre.', 'guide-wp-theme' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
