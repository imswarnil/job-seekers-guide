<?php
/**
 * Document head + site nav.
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#jsl-main"><?php esc_html_e( 'Skip to content', 'job-seekers-theme' ); ?></a>

<header class="jsl-nav jsl-container">
	<a class="jsl-nav__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php bloginfo( 'name' ); ?>
	</a>

	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => 'nav',
			'container_class'=> 'jsl-nav__links-wrap',
			'menu_class'     => 'jsl-nav__links',
			'fallback_cb'    => false,
		)
	);
	?>

	<button type="button" class="jsl-theme-toggle" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'job-seekers-theme' ); ?>">
		&#9788;
	</button>
</header>

<main id="jsl-main">
