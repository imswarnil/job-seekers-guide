( function () {
	'use strict';

	var STORAGE_KEY = 'jsl-theme';

	function applyTheme( theme ) {
		document.documentElement.setAttribute( 'data-theme', theme );
	}

	function currentTheme() {
		var stored = localStorage.getItem( STORAGE_KEY );
		if ( stored === 'light' || stored === 'dark' ) {
			return stored;
		}
		return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
	}

	// Initial theme is already applied by the inline blocking script in wp_head
	// (see inc/dark-mode.php) to avoid a flash of the wrong theme. This file
	// only wires up the toggle button.
	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.querySelector( '[data-theme-toggle]' );
		if ( ! toggle ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			var next = document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			applyTheme( next );
			localStorage.setItem( STORAGE_KEY, next );
		} );
	} );
} )();
