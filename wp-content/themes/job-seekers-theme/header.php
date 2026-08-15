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
<body <?php body_class( 'font-sans bg-surface text-ink antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#jsl-main"><?php esc_html_e( 'Skip to content', 'job-seekers-theme' ); ?></a>

<header class="sticky top-0 z-30 border-b border-line bg-surface/85 backdrop-blur-md">
	<div class="jsl-container flex h-14 items-center justify-between gap-6">
		<a class="flex items-center gap-2.5 font-bold text-ink no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="grid h-9 w-9 place-items-center rounded-lg bg-accent text-on-accent shadow-accent">
				<?php echo jsl_icon( 'compass', 'w-5 h-5' ); ?>
			</span>
			<span class="text-[1.05rem] tracking-tight"><?php bloginfo( 'name' ); ?></span>
		</a>

		<nav class="hidden items-center gap-1 md:flex" data-nav aria-label="<?php esc_attr_e( 'Primary', 'job-seekers-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex items-center gap-1 list-none m-0 p-0 [&_a]:inline-block [&_a]:rounded-md [&_a]:px-3 [&_a]:py-2 [&_a]:text-sm [&_a]:font-medium [&_a]:text-ink-secondary [&_a:hover]:bg-subtle [&_a:hover]:text-ink',
					'fallback_cb'    => false,
				)
			);
			?>
			<a class="ml-1 inline-block rounded-md px-3 py-2 text-sm font-medium text-ink-secondary hover:bg-subtle hover:text-ink" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Courses', 'job-seekers-theme' ); ?></a>
			<a class="inline-block rounded-md px-3 py-2 text-sm font-medium text-ink-secondary hover:bg-subtle hover:text-ink" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'Paths', 'job-seekers-theme' ); ?></a>
		</nav>

		<div class="flex items-center gap-2.5">
			<button type="button" class="grid h-10 w-10 cursor-pointer place-items-center rounded-full border border-line bg-transparent text-ink hover:border-line-strong" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'job-seekers-theme' ); ?>">
				<span class="hidden dark:block"><?php echo jsl_icon( 'spark', 'w-4.5 h-4.5' ); ?></span>
				<span class="block dark:hidden"><svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5Z"/></svg></span>
			</button>

			<?php if ( is_user_logged_in() ) : ?>
				<a class="jsl-btn jsl-btn--primary jsl-btn--sm hidden sm:inline-flex" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>"><?php esc_html_e( 'My Learning', 'job-seekers-theme' ); ?></a>
			<?php else : ?>
				<a class="jsl-btn jsl-btn--primary jsl-btn--sm hidden sm:inline-flex" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a>
			<?php endif; ?>

			<button type="button" class="grid h-10 w-10 cursor-pointer place-items-center rounded-md border border-line bg-transparent text-ink md:hidden" data-mobile-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'job-seekers-theme' ); ?>">
				<?php echo jsl_icon( 'menu', 'w-5 h-5' ); ?>
			</button>
		</div>
	</div>

	<div class="hidden border-t border-line md:hidden" data-mobile-menu>
		<nav class="jsl-container flex flex-col gap-1 py-3" aria-label="<?php esc_attr_e( 'Mobile', 'job-seekers-theme' ); ?>">
			<a class="rounded-md px-3 py-2.5 font-medium text-ink hover:bg-subtle" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'Courses', 'job-seekers-theme' ); ?></a>
			<a class="rounded-md px-3 py-2.5 font-medium text-ink hover:bg-subtle" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'Learning Paths', 'job-seekers-theme' ); ?></a>
			<?php if ( is_user_logged_in() ) : ?>
				<a class="rounded-md px-3 py-2.5 font-medium text-accent hover:bg-subtle" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>"><?php esc_html_e( 'My Learning', 'job-seekers-theme' ); ?></a>
			<?php else : ?>
				<a class="rounded-md px-3 py-2.5 font-medium text-accent hover:bg-subtle" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a>
			<?php endif; ?>
		</nav>
	</div>
</header>

<main id="jsl-main">
