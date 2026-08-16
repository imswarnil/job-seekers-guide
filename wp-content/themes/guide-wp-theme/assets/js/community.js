/**
 * Feedback reactions, roadmap voting, and feature suggestions.
 */
( function () {
	'use strict';

	function cfg() {
		return window.guideCommunity || null;
	}

	function t( key ) {
		var c = cfg();
		return ( c && c.i18n && c.i18n[ key ] ) || '';
	}

	function post( path, body, method ) {
		return fetch( cfg().restUrl + path, {
			method: method || 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg().nonce },
			body: body ? JSON.stringify( body ) : undefined,
		} ).then( function ( res ) {
			return res.json().then( function ( data ) {
				return { ok: res.ok, data: data };
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! cfg() ) {
			return;
		}

		/* ---------------- Feedback ---------------- */

		document.querySelectorAll( '[data-feedback]' ).forEach( function ( box ) {
			var type    = box.getAttribute( 'data-object-type' );
			var id      = parseInt( box.getAttribute( 'data-object-id' ), 10 );
			var status  = box.querySelector( '[data-feedback-status]' );
			var note    = box.querySelector( '[data-feedback-note]' );
			var message = box.querySelector( 'textarea' );
			var send    = box.querySelector( '[data-feedback-send]' );

			function say( text ) {
				if ( status ) {
					status.textContent = text;
				}
			}

			box.querySelectorAll( '[data-react]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var sentiment = btn.getAttribute( 'data-react' );

					// Paint immediately — a reaction that waits on a round trip
					// feels broken, and the failure path re-paints anyway.
					box.querySelectorAll( '[data-react]' ).forEach( function ( other ) {
						var on = other === btn;
						other.classList.toggle( 'is-on', on );
						other.classList.toggle( 'is-down', on && sentiment === 'down' );
						other.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
					} );

					if ( note ) {
						note.hidden = false;
					}

					post( '/feedback', { object_type: type, object_id: id, sentiment: sentiment } )
						.then( function ( res ) {
							say( res.ok ? t( 'noted' ) : ( res.data.error || t( 'failed' ) ) );
						} )
						.catch( function () { say( t( 'failed' ) ); } );
				} );
			} );

			if ( send && message ) {
				send.addEventListener( 'click', function () {
					var value = message.value.trim();

					if ( ! value ) {
						return;
					}

					send.disabled = true;
					say( t( 'sending' ) );

					var active = box.querySelector( '[data-react].is-on' );

					post( '/feedback', {
						object_type: type,
						object_id: id,
						sentiment: active ? active.getAttribute( 'data-react' ) : 'up',
						message: value,
					} )
						.then( function ( res ) {
							say( res.ok ? t( 'noted' ) : ( res.data.error || t( 'failed' ) ) );
							send.disabled = false;
						} )
						.catch( function () {
							say( t( 'failed' ) );
							send.disabled = false;
						} );
				} );
			}
		} );

		/* ---------------- Roadmap votes ---------------- */

		document.querySelectorAll( '[data-vote]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id    = btn.getAttribute( 'data-vote' );
				var voted = btn.classList.contains( 'is-voted' );
				var count = btn.querySelector( '.guide-vote__count' );

				btn.disabled = true;

				post( '/roadmap/' + id + '/vote', null, voted ? 'DELETE' : 'POST' )
					.then( function ( res ) {
						if ( res.ok ) {
							btn.classList.toggle( 'is-voted', !! res.data.voted );
							btn.setAttribute( 'aria-pressed', res.data.voted ? 'true' : 'false' );
							if ( count ) {
								count.textContent = res.data.votes;
							}
						} else if ( window.guideSnackbar ) {
							window.guideSnackbar( res.data.error || t( 'failed' ) );
						}
						btn.disabled = false;
					} )
					.catch( function () {
						btn.disabled = false;
						if ( window.guideSnackbar ) {
							window.guideSnackbar( t( 'failed' ) );
						}
					} );
			} );
		} );

		/* ---------------- Suggestions ---------------- */

		var form = document.getElementById( 'guide-suggest-form' );

		if ( form ) {
			var status = document.getElementById( 'guide-suggest-status' );

			document.querySelectorAll( '[data-suggest-open]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					form.hidden = false;
					form.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					document.getElementById( 'guide-suggest-title' ).focus();
				} );
			} );

			document.querySelectorAll( '[data-suggest-cancel]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () { form.hidden = true; } );
			} );

			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var submit = form.querySelector( 'button[type="submit"]' );
				submit.disabled = true;
				status.textContent = t( 'sending' );

				post( '/roadmap/suggest', {
					title: document.getElementById( 'guide-suggest-title' ).value,
					body: document.getElementById( 'guide-suggest-body' ).value,
				} )
					.then( function ( res ) {
						if ( res.ok ) {
							form.reset();
							form.hidden = true;
							if ( window.guideSnackbar ) {
								window.guideSnackbar( t( 'thanks' ) );
							}
							status.textContent = '';
						} else {
							status.textContent = res.data.error || t( 'failed' );
						}
						submit.disabled = false;
					} )
					.catch( function () {
						status.textContent = t( 'failed' );
						submit.disabled = false;
					} );
			} );
		}
	} );
} )();
