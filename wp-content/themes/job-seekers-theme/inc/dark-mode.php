<?php
/**
 * Dark mode: FOUC-safe inline bootstrap script.
 *
 * Runs before first paint so the correct data-theme attribute is set on
 * <html> before any CSS is applied. The click-handling logic lives in
 * assets/js/dark-mode.js, loaded normally (non-blocking).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'jsl_theme_dark_mode_bootstrap', 0 );

function jsl_theme_dark_mode_bootstrap() {
	?>
	<script>
	(function () {
		try {
			var stored = localStorage.getItem('jsl-theme');
			var theme = (stored === 'light' || stored === 'dark')
				? stored
				: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
			document.documentElement.setAttribute('data-theme', theme);
		} catch (e) {}
	})();
	</script>
	<?php
}
