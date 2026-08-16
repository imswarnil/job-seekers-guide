/**
 * Repeatable rows in the company guide metaboxes.
 *
 * Each repeater keeps a <template> of one empty row, so adding a row never
 * duplicates values the author already typed — cloning the last row would.
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var add = event.target.closest( '[data-add]' );

		if ( add ) {
			var key      = add.getAttribute( 'data-add' );
			var host     = document.querySelector( '[data-repeat="' + key + '"]' );
			var template = document.querySelector( '[data-template="' + key + '"]' );

			if ( host && template ) {
				host.appendChild( template.content.cloneNode( true ) );
				var inputs = host.lastElementChild.querySelectorAll( 'input, textarea' );
				if ( inputs.length ) {
					inputs[ 0 ].focus();
				}
			}
			return;
		}

		var remove = event.target.closest( '[data-remove]' );

		if ( remove ) {
			var row = remove.closest( '.guide-row' );
			if ( row ) {
				row.remove();
			}
		}
	} );
} )();
