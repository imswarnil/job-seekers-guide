<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="guide-shell guide-section">
	<?php if ( have_posts() ) : ?>
		<div class="guide-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article class="guide-card guide-card--link">
					<div class="guide-card__body">
						<h2 class="guide-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php if ( has_excerpt() ) : ?>
							<p class="guide-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<div class="mt-6"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<div class="guide-empty">
			<p class="guide-empty__title"><?php esc_html_e( 'Nothing here yet.', 'guide-wp-theme' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
