<?php
/**
 * Theme mode: auto / light / dark.
 *
 * The stored preference is one of three values. "auto" (the default) means
 * follow the OS, and keeps following it if the OS flips while the page is
 * open — a site that ignores the system switching to dark at sunset is a
 * site that got dark mode wrong.
 *
 * This runs inline before first paint so data-theme is set before any CSS
 * applies; the toggle itself lives in assets/js/theme.js.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'jsl_theme_dark_mode_bootstrap', 0 );

function jsl_theme_dark_mode_bootstrap() {
	?>
	<script>
	(function () {
		try {
			var stored = localStorage.getItem('jsl-theme');
			var mode = (stored === 'light' || stored === 'dark' || stored === 'auto') ? stored : 'auto';
			var query = window.matchMedia('(prefers-color-scheme: dark)');

			function apply() {
				var resolved = mode === 'auto' ? (query.matches ? 'dark' : 'light') : mode;
				document.documentElement.setAttribute('data-theme', resolved);
				document.documentElement.setAttribute('data-theme-mode', mode);
			}

			apply();

			// Track the OS only while the preference is "auto".
			query.addEventListener('change', function () {
				if (mode === 'auto') { apply(); }
			});

			// theme.js changes the mode; re-resolve without a reload.
			window.jslSetThemeMode = function (next) {
				mode = next;
				try { localStorage.setItem('jsl-theme', next); } catch (e) {}
				apply();
			};
		} catch (e) {}
	})();
	</script>
	<?php
}
