<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="jsl-container py-12">
	<?php if ( have_posts() ) : ?>
		<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<a class="group flex flex-col rounded-xl border border-line bg-raised p-6 no-underline shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="<?php the_permalink(); ?>">
					<h2 class="m-0 text-lg font-bold leading-snug text-ink group-hover:text-accent"><?php the_title(); ?></h2>
					<?php if ( has_excerpt() ) : ?>
						<p class="mt-2 text-sm text-ink-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</a>
				<?php
			endwhile;
			?>
		</div>
		<div class="mt-10"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p class="text-ink-muted"><?php esc_html_e( 'Nothing here yet.', 'job-seekers-theme' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
