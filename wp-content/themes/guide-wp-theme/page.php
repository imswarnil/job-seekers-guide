<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="guide-shell guide-shell--narrow guide-section">
		<h1 class="guide-display"><?php the_title(); ?></h1>
		<div class="guide-prose mt-5"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;

get_footer();
