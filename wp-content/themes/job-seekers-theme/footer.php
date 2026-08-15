<?php
/**
 * Site footer.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="mt-24 border-t border-line bg-subtle/60">
	<div class="jsl-container grid gap-10 py-14 md:grid-cols-[2fr_1fr_1fr]">
		<div>
			<a class="flex items-center gap-2.5 font-bold text-ink no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="grid h-8 w-8 place-items-center rounded-lg bg-accent text-on-accent"><?php echo jsl_icon( 'compass', 'w-4.5 h-4.5' ); ?></span>
				<?php bloginfo( 'name' ); ?>
			</a>
			<p class="mt-3 max-w-sm text-sm text-ink-muted">
				<?php esc_html_e( 'Structured learning paths, real practice, and progress tracking — everything a job seeker needs, open source and free to start.', 'job-seekers-theme' ); ?>
			</p>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Learn', 'job-seekers-theme' ); ?>">
			<h2 class="m-0 text-xs font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Learn', 'job-seekers-theme' ); ?></h2>
			<ul class="m-0 mt-4 flex list-none flex-col gap-2.5 p-0 text-sm">
				<li><a class="text-ink-secondary hover:text-accent" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>"><?php esc_html_e( 'Learning Paths', 'job-seekers-theme' ); ?></a></li>
				<li><a class="text-ink-secondary hover:text-accent" href="<?php echo esc_url( get_post_type_archive_link( 'course' ) ); ?>"><?php esc_html_e( 'All Courses', 'job-seekers-theme' ); ?></a></li>
			</ul>
		</nav>

		<nav aria-label="<?php esc_attr_e( 'Project', 'job-seekers-theme' ); ?>">
			<h2 class="m-0 text-xs font-bold uppercase tracking-widest text-ink-muted"><?php esc_html_e( 'Project', 'job-seekers-theme' ); ?></h2>
			<ul class="m-0 mt-4 flex list-none flex-col gap-2.5 p-0 text-sm">
				<li><a class="text-ink-secondary hover:text-accent" href="https://github.com/imswarnil/job-seekers-guide">GitHub</a></li>
				<li><a class="text-ink-secondary hover:text-accent" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'job-seekers-theme' ); ?></a></li>
			</ul>
		</nav>
	</div>

	<div class="border-t border-line">
		<div class="jsl-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-ink-muted">
			<p class="m-0">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'open source, GPL-2.0', 'job-seekers-theme' ); ?></p>
			<p class="m-0"><?php esc_html_e( 'Built in the open on', 'job-seekers-theme' ); ?> <a class="text-accent" href="https://github.com/imswarnil/job-seekers-guide">GitHub</a></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
