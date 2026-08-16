<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="guide-shell guide-shell--narrow guide-section">
		<header>
			<p class="guide-eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
			<h1 class="guide-display mt-2"><?php the_title(); ?></h1>
		</header>
		<div class="guide-prose mt-5"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
