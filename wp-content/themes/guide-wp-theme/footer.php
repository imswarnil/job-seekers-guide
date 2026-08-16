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

			<?php
			// Only linked once the pages actually exist — a footer link to a
			// 404 is worse than no link. Both are built as regular pages.
			$guide_community = array_filter(
				array(
					'my-story'  => __( 'My Story', 'guide-wp-theme' ),
					'resources' => __( 'Resources', 'guide-wp-theme' ),
				),
				static function ( $label, $slug ) {
					return (bool) get_page_by_path( $slug );
				},
				ARRAY_FILTER_USE_BOTH
			);
			?>
			<?php if ( $guide_community ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Community', 'guide-wp-theme' ); ?>">
					<h2 class="guide-footer__heading"><?php esc_html_e( 'Community', 'guide-wp-theme' ); ?></h2>
					<div class="guide-footer__list">
						<?php foreach ( $guide_community as $guide_slug => $guide_label ) : ?>
							<a href="<?php echo esc_url( home_url( '/' . $guide_slug . '/' ) ); ?>"><?php echo esc_html( $guide_label ); ?></a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<nav aria-label="<?php esc_attr_e( 'Project', 'guide-wp-theme' ); ?>">
				<h2 class="guide-footer__heading"><?php esc_html_e( 'Project', 'guide-wp-theme' ); ?></h2>
				<div class="guide-footer__list">
					<?php
					// Set in Appearance → Customize → Guide theme → Footer and
					// links. Each one is omitted entirely when blank, rather
					// than rendering a link to nowhere.
					$guide_links = array(
						'guide_github_url'   => 'GitHub',
						'guide_linkedin_url' => 'LinkedIn',
						'guide_youtube_url'  => 'YouTube',
					);

					foreach ( $guide_links as $guide_key => $guide_label ) :
						$guide_url = (string) guide_option( $guide_key );

						if ( '' === $guide_url ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $guide_url ); ?>" rel="noopener"><?php echo esc_html( $guide_label ); ?></a>
					<?php endforeach; ?>

					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'guide-wp-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</nav>
		</div>

		<div class="guide-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>

			<?php $guide_tagline = (string) guide_option( 'guide_footer_tagline' ); ?>
			<?php if ( '' !== $guide_tagline ) : ?>
				<p class="guide-footer__tagline"><?php echo esc_html( $guide_tagline ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<div class="guide-snackbar" id="guide-snackbar" role="status" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
