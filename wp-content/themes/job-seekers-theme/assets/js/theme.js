/**
 * Light/dark mode toggle.
 *
 * The initial theme is applied by an inline script in inc/dark-mode.php
 * before paint, so there is no flash; this only handles the toggle.
 * (Navigation, ripple, drawers and snackbars live in md3.js.)
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'jsl-theme';

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-theme-toggle]' ).forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var next = document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
				document.documentElement.setAttribute( 'data-theme', next );
				try {
					localStorage.setItem( STORAGE_KEY, next );
				} catch ( e ) {}
			} );
		} );
	} );
} )();
