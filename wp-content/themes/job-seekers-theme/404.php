<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="jsl-container flex flex-col items-center py-24 text-center md:py-32">
	<p class="m-0 font-mono text-6xl font-bold text-accent">404</p>
	<h1 class="m-0 mt-4 text-2xl font-extrabold tracking-tight md:text-3xl"><?php esc_html_e( 'This path leads nowhere', 'job-seekers-theme' ); ?></h1>
	<p class="mt-3 max-w-md text-ink-muted"><?php esc_html_e( 'The page you were looking for has moved or never existed. Let\'s get you back on track.', 'job-seekers-theme' ); ?></p>
	<a class="jsl-btn jsl-btn--primary mt-7" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'job-seekers-theme' ); ?></a>
</div>

<?php get_footer(); ?>
