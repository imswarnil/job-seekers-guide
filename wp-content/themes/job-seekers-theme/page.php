<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="jsl-container max-w-3xl py-12 md:py-16">
		<h1 class="m-0 text-3xl font-extrabold tracking-tight md:text-4xl"><?php the_title(); ?></h1>
		<div class="jsl-prose mt-6"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;

get_footer();
