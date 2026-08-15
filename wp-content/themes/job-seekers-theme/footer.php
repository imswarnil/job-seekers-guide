<?php
/**
 * Site footer + the compact-window navigation bar.
 */

defined( 'ABSPATH' ) || exit;

$jsl_bottom_nav = jsl_primary_destinations( true );
?>
</main>

<footer class="mt-24 border-t border-outline-variant bg-surface-container-low">
	<div class="jsl-container grid gap-10 py-14 md:grid-cols-[2fr_1fr_1fr]">
		<div>
			<a class="flex items-center gap-2.5 font-bold text-on-surface no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="grid h-9 w-9 place-items-center rounded-lg bg-primary text-on-primary"><?php echo jsl_logo_mark( 'w-5 h-5' ); ?></span>
				<span class="font-display"><?php bloginfo( 'name' ); ?></span>
			</a>
			<p class="mt-3 max-w-sm text-sm text-on-surface-variant">
				<?php esc_html_e( 'Structured learning paths, real practice, and progress tracking — everything a job seeker needs, open source and free to start.', 'job-seekers-theme' ); ?>
			</p>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Learn', 'job-seekers-theme' ); ?>">
			<h2 class="m-0 text-xs font-bold uppercase tracking-widest text-on-surface-variant"><?php esc_html_e( 'Learn', 'job-seekers-theme' ); ?></h2>
			<ul class="m-0 mt-4 flex list-none flex-col gap-2.5 p-0 text-sm">
				<li><a class="text-on-surface-variant hover:text-primary" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'Learning Paths', 'job-seekers-theme' ); ?></a></li>
				<li><a class="text-on-surface-variant hover:text-primary" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'All Courses', 'job-seekers-theme' ); ?></a></li>
			</ul>
		</nav>

		<nav aria-label="<?php esc_attr_e( 'Project', 'job-seekers-theme' ); ?>">
			<h2 class="m-0 text-xs font-bold uppercase tracking-widest text-on-surface-variant"><?php esc_html_e( 'Project', 'job-seekers-theme' ); ?></h2>
			<ul class="m-0 mt-4 flex list-none flex-col gap-2.5 p-0 text-sm">
				<li><a class="text-on-surface-variant hover:text-primary" href="https://github.com/imswarnil/job-seekers-guide">GitHub</a></li>
				<?php if ( ! is_user_logged_in() ) : ?>
					<li><a class="text-on-surface-variant hover:text-primary" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</div>

	<div class="border-t border-outline-variant">
		<div class="jsl-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-on-surface-variant">
			<p class="m-0">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'open source, GPL-2.0', 'job-seekers-theme' ); ?></p>
			<p class="m-0"><?php esc_html_e( 'Built in the open on', 'job-seekers-theme' ); ?> <a class="text-primary" href="https://github.com/imswarnil/job-seekers-guide">GitHub</a></p>
		</div>
	</div>
</footer>

<!-- Navigation bar: the compact-window equivalent of the inline
     destinations in the top app bar. -->
<nav class="md-nav-bar md:hidden" aria-label="<?php esc_attr_e( 'Primary', 'job-seekers-theme' ); ?>">
	<?php foreach ( $jsl_bottom_nav as $jsl_item ) : ?>
		<a class="md-nav-bar__item" href="<?php echo esc_url( $jsl_item['url'] ); ?>" <?php echo $jsl_item['here'] ? 'aria-current="page"' : ''; ?>>
			<span class="md-nav-bar__indicator">
				<?php echo jsl_icon( $jsl_item['here'] ? $jsl_item['icon'] . '-fill' : $jsl_item['icon'], 'w-6 h-6' ); ?>
			</span>
			<span class="md-nav-bar__label"><?php echo esc_html( $jsl_item['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>
