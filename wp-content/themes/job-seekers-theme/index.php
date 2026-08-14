<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="jsl-container">
	<?php if ( have_posts() ) : ?>
		<div class="jsl-card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<a class="jsl-card" href="<?php the_permalink(); ?>">
					<h3 class="jsl-card__title"><?php the_title(); ?></h3>
					<p class="jsl-card__meta"><?php echo esc_html( get_the_excerpt() ); ?></p>
				</a>
				<?php
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="jsl-card__meta"><?php esc_html_e( 'Nothing here yet.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
