<?php
/**
 * A single help article, with a table of contents and a "was this useful?"
 * prompt — the two things that make documentation improve over time.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$guide_sections = get_the_terms( get_the_ID(), \Guide\Community\Community_Types::HELP_TAX );
	?>

	<div class="guide-shell guide-section guide-section--tight">
		<nav class="guide-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'guide-wp-theme' ); ?>">
			<a href="<?php echo esc_url( get_post_type_archive_link( \Guide\Community\Community_Types::HELP ) ); ?>"><?php esc_html_e( 'Help centre', 'guide-wp-theme' ); ?></a>
			<?php if ( $guide_sections && ! is_wp_error( $guide_sections ) ) : ?>
				<span>/</span>
				<a href="<?php echo esc_url( get_term_link( $guide_sections[0] ) ); ?>"><?php echo esc_html( $guide_sections[0]->name ); ?></a>
			<?php endif; ?>
		</nav>

		<div class="guide-doc-layout mt-3">
			<article>
				<h1 class="guide-display"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="guide-page-head__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="guide-prose mt-5" data-toc-source><?php the_content(); ?></div>

				<?php get_template_part( 'template-parts/feedback', null, array( 'object_type' => 'help_article' ) ); ?>

				<?php comments_template(); ?>
			</article>

			<aside class="guide-toc" data-toc aria-label="<?php esc_attr_e( 'On this page', 'guide-wp-theme' ); ?>">
				<p class="guide-filter-group__label"><?php esc_html_e( 'On this page', 'guide-wp-theme' ); ?></p>
				<nav class="guide-toc__list" data-toc-list></nav>
			</aside>
		</div>
	</div>

	<?php
endwhile;

get_footer();
