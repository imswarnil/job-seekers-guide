<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="jsl-container jsl-container--narrow" style="padding-block:var(--jsl-space-8)">
	<h1><?php esc_html_e( 'Page not found', 'job-seekers-theme' ); ?></h1>
	<p><?php esc_html_e( "The page you're looking for doesn't exist.", 'job-seekers-theme' ); ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go home', 'job-seekers-theme' ); ?></a>.
	</p>
</div>
<?php get_footer(); ?>
