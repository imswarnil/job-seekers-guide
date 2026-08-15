( function () {
	'use strict';

	var STORAGE_KEY = 'jsl-theme';

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.querySelector( '[data-theme-toggle]' );
		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var next = document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
				document.documentElement.setAttribute( 'data-theme', next );
				try {
					localStorage.setItem( STORAGE_KEY, next );
				} catch ( e ) {}
			} );
		}

		var mobileToggle = document.querySelector( '[data-mobile-toggle]' );
		var mobileMenu   = document.querySelector( '[data-mobile-menu]' );
		if ( mobileToggle && mobileMenu ) {
			mobileToggle.addEventListener( 'click', function () {
				var open = mobileMenu.classList.toggle( 'hidden' ) === false;
				mobileToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}
	} );
} )();
