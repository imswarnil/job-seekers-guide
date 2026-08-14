( function () {
	'use strict';

	var STORAGE_KEY = 'jsl-theme';

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.querySelector( '[data-theme-toggle]' );
		if ( ! toggle ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			var next = document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			document.documentElement.setAttribute( 'data-theme', next );
			localStorage.setItem( STORAGE_KEY, next );
		} );
	} );
} )();
