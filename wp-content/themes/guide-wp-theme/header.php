<?php
/**
 * Document head + the app shell's navigation.
 *
 * Two navigation components, chosen by width:
 *
 *   < 769px   — brand bar + a fixed bottom navigation bar
 *   >= 769px  — a single top bar with inline destinations
 *
 * The bottom bar rather than a hamburger is deliberate: most learners here are
 * on a phone, one-handed, and there are few enough destinations to show them
 * all permanently. A menu you have to open is a menu that gets used less.
 *
 * The lesson player replaces all of this with its own chrome
 * (header-player.php) — a course needs different navigation than a marketing
 * page.
 */

defined( 'ABSPATH' ) || exit;

$guide_nav = guide_primary_destinations();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'guide-has-bottom-nav' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#guide-main"><?php esc_html_e( 'Skip to content', 'guide-wp-theme' ); ?></a>

<header class="guide-header">
	<div class="guide-shell guide-header__inner">

		<a class="guide-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="guide-brand__mark"><?php echo guide_logo_mark(); ?></span>
			<span class="guide-brand__name"><?php bloginfo( 'name' ); ?></span>
		</a>

		<nav class="guide-nav" aria-label="<?php esc_attr_e( 'Primary', 'guide-wp-theme' ); ?>">
			<?php foreach ( $guide_nav as $guide_item ) : ?>
				<?php if ( ! empty( $guide_item['account'] ) ) { continue; } ?>
				<a class="guide-nav__link <?php echo $guide_item['here'] ? 'is-current' : ''; ?>"
					href="<?php echo esc_url( $guide_item['url'] ); ?>"
					<?php echo $guide_item['here'] ? 'aria-current="page"' : ''; ?>>
					<?php echo guide_icon( $guide_item['here'] ? $guide_item['icon'] . '-fill' : $guide_item['icon'] ); ?>
					<?php echo esc_html( $guide_item['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="guide-header__actions">
			<?php // A star is the cheapest way for someone who found this useful to help. ?>
			<a class="guide-star" href="https://github.com/imswarnil/job-seekers-guide" target="_blank" rel="noopener"
				aria-label="<?php esc_attr_e( 'Star this project on GitHub', 'guide-wp-theme' ); ?>">
				<?php echo guide_icon( 'star' ); ?>
				<span><?php esc_html_e( 'Star', 'guide-wp-theme' ); ?></span>
			</a>

			<button type="button" class="guide-icon-button" data-theme-toggle
				data-label-auto="<?php esc_attr_e( 'Theme: follow system', 'guide-wp-theme' ); ?>"
				data-label-light="<?php esc_attr_e( 'Theme: light', 'guide-wp-theme' ); ?>"
				data-label-dark="<?php esc_attr_e( 'Theme: dark', 'guide-wp-theme' ); ?>"
				aria-label="<?php esc_attr_e( 'Change theme', 'guide-wp-theme' ); ?>">
				<span data-mode-icon="auto"><?php echo guide_icon( 'circle-half' ); ?></span>
				<span data-mode-icon="light" hidden><?php echo guide_icon( 'sun-fill' ); ?></span>
				<span data-mode-icon="dark" hidden><?php echo guide_icon( 'moon-fill' ); ?></span>
			</button>

			<?php if ( is_user_logged_in() ) : ?>
				<?php $guide_user = wp_get_current_user(); ?>
				<div class="dropdown is-right" id="guide-account">
					<div class="dropdown-trigger">
						<button type="button" class="guide-icon-button" data-menu-trigger="guide-account"
							aria-haspopup="true" aria-expanded="false"
							aria-label="<?php esc_attr_e( 'Account', 'guide-wp-theme' ); ?>">
							<?php echo guide_icon( 'user-fill' ); ?>
						</button>
					</div>

					<div class="dropdown-menu" id="guide-account-menu" role="menu">
						<div class="dropdown-content">
							<div class="dropdown-item">
								<p class="is-size-7 has-text-weight-semibold"><?php echo esc_html( $guide_user->display_name ); ?></p>
							</div>
							<hr class="dropdown-divider">
							<a class="dropdown-item" role="menuitem" href="<?php echo esc_url( home_url( '/my-learning/' ) ); ?>">
								<?php esc_html_e( 'My Learning', 'guide-wp-theme' ); ?>
							</a>
							<?php foreach ( guide_secondary_destinations() as $guide_extra ) : ?>
								<a class="dropdown-item" role="menuitem" href="<?php echo esc_url( $guide_extra['url'] ); ?>">
									<?php echo esc_html( $guide_extra['label'] ); ?>
								</a>
							<?php endforeach; ?>
							<?php if ( current_user_can( 'edit_posts' ) ) : ?>
								<hr class="dropdown-divider">
								<a class="dropdown-item" role="menuitem" href="<?php echo esc_url( admin_url( 'admin.php?page=guide-lms' ) ); ?>">
									<?php esc_html_e( 'LMS console', 'guide-wp-theme' ); ?>
								</a>
							<?php endif; ?>
							<hr class="dropdown-divider">
							<a class="dropdown-item" role="menuitem" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
								<?php esc_html_e( 'Sign out', 'guide-wp-theme' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php else : ?>
				<a class="button is-primary is-small" href="<?php echo esc_url( wp_login_url() ); ?>">
					<?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<nav class="guide-bottom-nav" aria-label="<?php esc_attr_e( 'Primary', 'guide-wp-theme' ); ?>">
	<?php foreach ( guide_primary_destinations( true ) as $guide_item ) : ?>
		<a class="guide-bottom-nav__link <?php echo $guide_item['here'] ? 'is-current' : ''; ?>"
			href="<?php echo esc_url( $guide_item['url'] ); ?>"
			<?php echo $guide_item['here'] ? 'aria-current="page"' : ''; ?>>
			<?php echo guide_icon( $guide_item['here'] ? $guide_item['icon'] . '-fill' : $guide_item['icon'] ); ?>
			<span><?php echo esc_html( $guide_item['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>

<main id="guide-main" class="guide-main">
