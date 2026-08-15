<?php
/**
 * Document head + the site's Material 3 top app bar.
 *
 * The lesson player replaces this bar with its own (see header-player.php) —
 * a course needs different chrome than a marketing page.
 */

defined( 'ABSPATH' ) || exit;

$jsl_nav = array(
	array(
		'url'   => get_post_type_archive_link( 'learning_path' ),
		'label' => __( 'Paths', 'job-seekers-theme' ),
		'icon'  => 'path',
		'here'  => is_post_type_archive( 'learning_path' ) || is_singular( 'learning_path' ),
	),
	array(
		'url'   => get_post_type_archive_link( 'course' ),
		'label' => __( 'Courses', 'job-seekers-theme' ),
		'icon'  => 'graduation-cap',
		'here'  => is_post_type_archive( 'course' ) || is_singular( 'course' ),
	),
);

if ( is_user_logged_in() ) {
	$jsl_nav[] = array(
		'url'   => home_url( '/my-learning/' ),
		'label' => __( 'My Learning', 'job-seekers-theme' ),
		'icon'  => 'stack',
		'here'  => is_page( 'my-learning' ),
	);
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'font-sans bg-surface text-ink antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#jsl-main"><?php esc_html_e( 'Skip to content', 'job-seekers-theme' ); ?></a>

<header class="sticky top-0 z-30 border-b border-outline-variant bg-surface/85 backdrop-blur-md">
	<div class="jsl-container flex h-16 items-center gap-3">

		<a class="flex shrink-0 items-center gap-2.5 font-bold text-ink no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="grid h-10 w-10 place-items-center rounded-lg bg-primary text-on-primary">
				<?php echo jsl_logo_mark( 'w-6 h-6' ); ?>
			</span>
			<span class="hidden font-display text-[1.05rem] tracking-tight sm:block"><?php bloginfo( 'name' ); ?></span>
		</a>

		<nav class="ml-4 hidden items-center gap-1 md:flex" aria-label="<?php esc_attr_e( 'Primary', 'job-seekers-theme' ); ?>">
			<?php foreach ( $jsl_nav as $jsl_item ) : ?>
				<a class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium no-underline transition-colors <?php echo $jsl_item['here'] ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-high hover:text-ink'; ?>"
					href="<?php echo esc_url( $jsl_item['url'] ); ?>" <?php echo $jsl_item['here'] ? 'aria-current="page"' : ''; ?>>
					<?php echo jsl_icon( $jsl_item['here'] ? $jsl_item['icon'] . '-fill' : $jsl_item['icon'], 'w-4 h-4' ); ?>
					<?php echo esc_html( $jsl_item['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="ml-auto flex items-center gap-1.5">
			<button type="button" class="jsl-icon-btn" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'job-seekers-theme' ); ?>">
				<span class="hidden dark:block"><?php echo jsl_icon( 'sun', 'w-5 h-5' ); ?></span>
				<span class="block dark:hidden"><?php echo jsl_icon( 'moon', 'w-5 h-5' ); ?></span>
			</button>

			<?php if ( is_user_logged_in() ) : ?>
				<a class="jsl-btn jsl-btn--tonal jsl-btn--sm hidden sm:inline-flex" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>">
					<?php echo jsl_icon( 'user-circle', 'w-4 h-4' ); ?>
					<?php esc_html_e( 'My Learning', 'job-seekers-theme' ); ?>
				</a>
			<?php else : ?>
				<a class="jsl-btn jsl-btn--primary jsl-btn--sm hidden sm:inline-flex" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a>
			<?php endif; ?>

			<button type="button" class="jsl-icon-btn md:hidden" data-mobile-toggle aria-expanded="false" aria-controls="jsl-mobile-nav" aria-label="<?php esc_attr_e( 'Open menu', 'job-seekers-theme' ); ?>">
				<?php echo jsl_icon( 'list', 'w-5 h-5' ); ?>
			</button>
		</div>
	</div>

	<!-- Mobile navigation drawer -->
	<div class="hidden border-t border-outline-variant bg-surface md:hidden" id="jsl-mobile-nav" data-mobile-menu>
		<nav class="jsl-container flex flex-col gap-1 py-3" aria-label="<?php esc_attr_e( 'Mobile', 'job-seekers-theme' ); ?>">
			<?php foreach ( $jsl_nav as $jsl_item ) : ?>
				<a class="flex items-center gap-3 rounded-full px-4 py-3 font-medium no-underline <?php echo $jsl_item['here'] ? 'bg-secondary-container text-on-secondary-container' : 'text-ink hover:bg-surface-high'; ?>" href="<?php echo esc_url( $jsl_item['url'] ); ?>">
					<?php echo jsl_icon( $jsl_item['icon'], 'w-5 h-5' ); ?>
					<?php echo esc_html( $jsl_item['label'] ); ?>
				</a>
			<?php endforeach; ?>

			<?php if ( ! is_user_logged_in() ) : ?>
				<a class="jsl-btn jsl-btn--primary jsl-btn--block mt-2" href="<?php echo esc_url( wp_login_url() ); ?>">
					<?php echo jsl_icon( 'sign-in', 'w-4 h-4' ); ?>
					<?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?>
				</a>
			<?php endif; ?>
		</nav>
	</div>
</header>

<main id="jsl-main">
