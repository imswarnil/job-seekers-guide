<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="jsl-container max-w-3xl py-12 md:py-16">
		<header>
			<p class="m-0 text-sm text-ink-muted"><?php echo esc_html( get_the_date() ); ?></p>
			<h1 class="m-0 mt-2 text-3xl font-extrabold tracking-tight md:text-4xl"><?php the_title(); ?></h1>
		</header>
		<div class="jsl-prose mt-6"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
