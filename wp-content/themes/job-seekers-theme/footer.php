<?php
/**
 * Site footer.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="jsl-container" style="padding-block:var(--jsl-space-6);border-top:1px solid var(--jsl-color-border);margin-top:var(--jsl-space-8);color:var(--jsl-color-text-muted);font-size:var(--jsl-text-sm)">
	<p>
		<?php bloginfo( 'name' ); ?> &mdash;
		<?php esc_html_e( 'open source on', 'job-seekers-theme' ); ?>
		<a href="https://github.com/imswarnil/job-seekers-guide">GitHub</a>.
	</p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
