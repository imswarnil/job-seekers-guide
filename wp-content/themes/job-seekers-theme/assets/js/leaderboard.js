/**
 * Leaderboard opt-in toggle.
 *
 * The switch reads as "show me", so it is the inverse of the stored
 * opt-out flag — checked means opt_out: false.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.getElementById( 'jsl-lb-optin' );

		if ( ! toggle || ! window.jslLeaderboard ) {
			return;
		}

		toggle.addEventListener( 'change', function () {
			var shown = toggle.checked;
			toggle.disabled = true;

			fetch( window.jslLeaderboard.restUrl + '/leaderboard/opt-out', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.jslLeaderboard.nonce },
				body: JSON.stringify( { opt_out: ! shown } ),
			} )
				.then( function ( res ) {
					if ( ! res.ok ) {
						throw new Error( 'failed' );
					}
					if ( window.jslSnackbar ) {
						window.jslSnackbar( window.jslLeaderboard.i18n[ shown ? 'shown' : 'hidden' ] );
					}
				} )
				.catch( function () {
					// Put the switch back where it was: leaving it showing a
					// preference the server did not accept would be a lie.
					toggle.checked = ! shown;
				} )
				.then( function () {
					toggle.disabled = false;
				} );
		} );
	} );
} )();
