<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="jsl-container jsl-container--narrow">
		<article>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</article>
	</div>
	<?php
endwhile;

get_footer();
