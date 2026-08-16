/**
 * Theme mode toggle: auto → light → dark → auto.
 *
 * The resolution logic (and the OS listener) lives in the inline bootstrap
 * from inc/dark-mode.php, which exposes window.guideSetThemeMode. This file
 * only cycles the value and keeps the button's label honest.
 */
( function () {
	'use strict';

	var ORDER = [ 'auto', 'light', 'dark' ];

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggles = document.querySelectorAll( '[data-theme-toggle]' );

		if ( ! toggles.length ) {
			return;
		}

		function currentMode() {
			return document.documentElement.getAttribute( 'data-theme-mode' ) || 'auto';
		}

		function paint() {
			var mode = currentMode();

			toggles.forEach( function ( toggle ) {
				var label = toggle.getAttribute( 'data-label-' + mode );
				if ( label ) {
					toggle.setAttribute( 'aria-label', label );
					toggle.setAttribute( 'title', label );
				}

				// Show only the icon for the active mode.
				toggle.querySelectorAll( '[data-mode-icon]' ).forEach( function ( icon ) {
					icon.hidden = icon.getAttribute( 'data-mode-icon' ) !== mode;
				} );
			} );
		}

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var next = ORDER[ ( ORDER.indexOf( currentMode() ) + 1 ) % ORDER.length ];

				if ( window.guideSetThemeMode ) {
					window.guideSetThemeMode( next );
				}

				paint();

				if ( window.guideSnackbar ) {
					var said = toggle.getAttribute( 'data-label-' + next );
					if ( said ) {
						window.guideSnackbar( said, { duration: 1800 } );
					}
				}
			} );
		} );

		paint();
	} );
} )();
