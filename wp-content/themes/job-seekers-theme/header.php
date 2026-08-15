<?php
/**
 * Document head + the app shell's Material 3 navigation.
 *
 * M3 asks for a different navigation component per window size class, and
 * that is what this does:
 *
 *   compact (< 768px)  — top app bar + a bottom navigation bar
 *   medium and up      — top app bar with inline destinations
 *
 * The lesson player replaces all of this with its own chrome
 * (header-player.php) — a course needs different navigation than a
 * marketing page.
 */

defined( 'ABSPATH' ) || exit;

$jsl_nav = jsl_primary_destinations();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'font-sans bg-surface text-on-surface antialiased md-nav-bar-offset md:pb-0' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#jsl-main"><?php esc_html_e( 'Skip to content', 'job-seekers-theme' ); ?></a>

<!-- Top app bar -->
<header class="md-top-app-bar">
	<div class="jsl-container flex w-full items-center gap-2">

		<a class="flex shrink-0 items-center gap-2.5 no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="grid h-10 w-10 place-items-center rounded-lg bg-primary text-on-primary">
				<?php echo jsl_logo_mark( 'w-6 h-6' ); ?>
			</span>
			<span class="md-top-app-bar__headline hidden sm:block"><?php bloginfo( 'name' ); ?></span>
		</a>

		<!-- Inline destinations (medium and up) -->
		<nav class="ml-6 hidden items-center gap-1 md:flex" aria-label="<?php esc_attr_e( 'Primary', 'job-seekers-theme' ); ?>">
			<?php foreach ( $jsl_nav as $jsl_item ) : ?>
				<a class="md-chip <?php echo $jsl_item['here'] ? 'md-chip--selected' : ''; ?> h-10 !rounded-full"
					href="<?php echo esc_url( $jsl_item['url'] ); ?>"
					<?php echo $jsl_item['here'] ? 'aria-current="page"' : ''; ?>>
					<?php echo jsl_icon( $jsl_item['here'] ? $jsl_item['icon'] . '-fill' : $jsl_item['icon'], 'w-[18px] h-[18px]' ); ?>
					<?php echo esc_html( $jsl_item['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="ml-auto flex items-center gap-1">
			<button type="button" class="md-icon-btn" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'job-seekers-theme' ); ?>">
				<span class="hidden dark:block"><?php echo jsl_icon( 'sun', 'w-5 h-5' ); ?></span>
				<span class="block dark:hidden"><?php echo jsl_icon( 'moon', 'w-5 h-5' ); ?></span>
			</button>

			<?php if ( is_user_logged_in() ) : ?>
				<?php $jsl_user = wp_get_current_user(); ?>
				<div class="relative">
					<button type="button" class="md-icon-btn md-icon-btn--tonal" data-menu-trigger="jsl-account-menu"
						aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'Account', 'job-seekers-theme' ); ?>">
						<?php echo jsl_icon( 'user-fill', 'w-5 h-5' ); ?>
					</button>

					<div class="md-menu right-0 top-12" id="jsl-account-menu" role="menu">
						<p class="m-0 px-3 pb-2 text-xs text-on-surface-variant">
							<?php echo esc_html( $jsl_user->display_name ); ?>
						</p>
						<hr class="md-divider mb-1">
						<a class="md-menu__item" role="menuitem" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>">
							<?php echo jsl_icon( 'stack', 'w-5 h-5' ); ?>
							<?php esc_html_e( 'My Learning', 'job-seekers-theme' ); ?>
						</a>
						<?php if ( current_user_can( 'edit_posts' ) ) : ?>
							<a class="md-menu__item" role="menuitem" href="<?php echo esc_url( admin_url( 'admin.php?page=jsl-lms' ) ); ?>">
								<?php echo jsl_icon( 'gear', 'w-5 h-5' ); ?>
								<?php esc_html_e( 'LMS console', 'job-seekers-theme' ); ?>
							</a>
						<?php endif; ?>
						<a class="md-menu__item" role="menuitem" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
							<?php echo jsl_icon( 'sign-out', 'w-5 h-5' ); ?>
							<?php esc_html_e( 'Sign out', 'job-seekers-theme' ); ?>
						</a>
					</div>
				</div>
			<?php else : ?>
				<a class="jsl-btn jsl-btn--primary jsl-btn--sm ml-1" href="<?php echo esc_url( wp_login_url() ); ?>">
					<?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="jsl-main">
