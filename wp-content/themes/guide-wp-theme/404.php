<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="guide-shell guide-section" style="text-align:center">
	<p style="font-family:var(--bulma-family-code);font-size:4rem;font-weight:700;line-height:1;color:var(--bulma-primary)">404</p>
	<h1 class="title is-3 mt-4"><?php esc_html_e( 'This path leads nowhere', 'guide-wp-theme' ); ?></h1>
	<p class="mt-3" style="max-width:44ch;margin-inline:auto;color:var(--bulma-text-weak)">
		<?php esc_html_e( 'The page you were looking for has moved, or never existed. Let\'s get you back on track.', 'guide-wp-theme' ); ?>
	</p>
	<div class="mt-5">
		<a class="button is-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'guide-wp-theme' ); ?></a>
		<a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Browse courses', 'guide-wp-theme' ); ?></a>
	</div>
</div>

<?php get_footer(); ?>
