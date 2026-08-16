<?php
/**
 * Site footer.
 *
 * The compact-window navigation bar is rendered by header.php so it sits
 * outside <main> and stays put while the page scrolls.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="guide-footer">
	<div class="guide-shell">
		<div class="guide-footer__grid">
			<div>
				<a class="guide-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="guide-brand__mark"><?php echo guide_logo_mark(); ?></span>
					<span class="guide-brand__name"><?php bloginfo( 'name' ); ?></span>
				</a>
				<p class="mt-3" style="max-width:34ch;color:var(--bulma-text-weak)">
					<?php esc_html_e( 'The order, the filter, and the accountability — free. Everything else was always free; nobody put it in the right sequence.', 'guide-wp-theme' ); ?>
				</p>
			</div>

			<nav aria-label="<?php esc_attr_e( 'Learn', 'guide-wp-theme' ); ?>">
				<h2 class="guide-footer__heading"><?php esc_html_e( 'Learn', 'guide-wp-theme' ); ?></h2>
				<div class="guide-footer__list">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'Learning Paths', 'guide-wp-theme' ); ?></a>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'All Courses', 'guide-wp-theme' ); ?></a>
					<?php foreach ( guide_secondary_destinations() as $guide_extra ) : ?>
						<a href="<?php echo esc_url( $guide_extra['url'] ); ?>"><?php echo esc_html( $guide_extra['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</nav>

			<nav aria-label="<?php esc_attr_e( 'Community', 'guide-wp-theme' ); ?>">
				<h2 class="guide-footer__heading"><?php esc_html_e( 'Community', 'guide-wp-theme' ); ?></h2>
				<div class="guide-footer__list">
					<a href="<?php echo esc_url( home_url( '/my-story/' ) ); ?>"><?php esc_html_e( 'My Story', 'guide-wp-theme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/resources/' ) ); ?>"><?php esc_html_e( 'Resources', 'guide-wp-theme' ); ?></a>
				</div>
			</nav>

			<nav aria-label="<?php esc_attr_e( 'Project', 'guide-wp-theme' ); ?>">
				<h2 class="guide-footer__heading"><?php esc_html_e( 'Project', 'guide-wp-theme' ); ?></h2>
				<div class="guide-footer__list">
					<a href="https://github.com/imswarnil/job-seekers-guide">GitHub</a>
					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</nav>
		</div>

		<div class="guide-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'open source, GPL-2.0', 'guide-wp-theme' ); ?></p>
			<p><?php esc_html_e( 'Built in the open on', 'guide-wp-theme' ); ?> <a href="https://github.com/imswarnil/job-seekers-guide">GitHub</a></p>
		</div>
	</div>
</footer>

<div class="guide-snackbar" id="guide-snackbar" role="status" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
