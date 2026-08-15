/**
 * Success-story submission.
 *
 * The server decides the story's status (always pending) — this only
 * collects the fields and reports back.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'jsl-story-form' );

		if ( ! form || ! window.jslStory ) {
			return;
		}

		var status = document.getElementById( 'jsl-story-status' );
		var submit = form.querySelector( 'button[type="submit"]' );

		function t( key ) {
			return ( window.jslStory.i18n && window.jslStory.i18n[ key ] ) || '';
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();

			submit.disabled = true;
			status.textContent = t( 'sending' );

			var payload = {
				title: document.getElementById( 'jsl-story-title' ).value.trim(),
				role: document.getElementById( 'jsl-story-role' ).value.trim(),
				company: document.getElementById( 'jsl-story-company' ).value.trim(),
				previous: document.getElementById( 'jsl-story-previous' ).value.trim(),
				weeks: parseInt( document.getElementById( 'jsl-story-weeks' ).value, 10 ) || 0,
				linkedin: document.getElementById( 'jsl-story-linkedin' ).value.trim(),
				story: document.getElementById( 'jsl-story-body' ).value.trim(),
			};

			fetch( window.jslStory.restUrl + '/stories', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.jslStory.nonce },
				body: JSON.stringify( payload ),
			} )
				.then( function ( res ) {
					return res.json().then( function ( data ) {
						return { ok: res.ok, data: data };
					} );
				} )
				.then( function ( result ) {
					if ( ! result.ok ) {
						status.textContent = result.data.error || t( 'failed' );
						submit.disabled = false;
						return;
					}

					// Replace the form outright: there is nothing useful left
					// to do here, and leaving it filled in invites a re-submit.
					form.innerHTML =
						'<p class="m-0 flex items-center gap-3 rounded-xl bg-tertiary-container px-5 py-4 font-semibold text-on-tertiary-container">' +
						t( 'thanks' ) + '</p>';

					if ( window.jslSnackbar ) {
						window.jslSnackbar( t( 'thanks' ) );
					}
				} )
				.catch( function () {
					status.textContent = t( 'failed' );
					submit.disabled = false;
				} );
		} );
	} );
} )();
