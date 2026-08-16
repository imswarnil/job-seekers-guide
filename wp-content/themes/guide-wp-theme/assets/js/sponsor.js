/**
 * Sponsor portal: submit a campaign (with logo upload), and pay for an
 * approved one.
 */
( function () {
	'use strict';

	function cfg() {
		return window.guideSponsor || null;
	}

	function t( key ) {
		var c = cfg();
		return ( c && c.i18n && c.i18n[ key ] ) || '';
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! cfg() ) {
			return;
		}

		/* ---- Submit ---- */

		var form = document.getElementById( 'guide-sponsor-form' );

		if ( form ) {
			var status = document.getElementById( 'guide-sponsor-status' );

			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var submit = form.querySelector( 'button[type="submit"]' );
				submit.disabled = true;
				status.textContent = t( 'sending' );

				var file = document.getElementById( 'guide-sp-logo' ).files[ 0 ];

				// Upload the logo first if there is one, so the campaign is
				// created complete rather than needing a second edit.
				var uploaded = file ? uploadLogo( file ) : Promise.resolve( 0 );

				uploaded
					.then( function ( logoId ) {
						return fetch( cfg().restUrl + '/sponsorships', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg().nonce },
							body: JSON.stringify( {
								company: document.getElementById( 'guide-sp-company' ).value,
								slot: document.getElementById( 'guide-sp-slot' ).value,
								headline: document.getElementById( 'guide-sp-headline' ).value,
								body: document.getElementById( 'guide-sp-body' ).value,
								url: document.getElementById( 'guide-sp-url' ).value,
								months: parseInt( document.getElementById( 'guide-sp-months' ).value, 10 ) || 1,
								logo: logoId,
							} ),
						} );
					} )
					.then( function ( res ) {
						return res.json().then( function ( data ) {
							return { ok: res.ok, data: data };
						} );
					} )
					.then( function ( res ) {
						if ( res.ok ) {
							status.textContent = t( 'thanks' );
							window.location.href = cfg().portal;
							return;
						}
						status.textContent = res.data.error || t( 'failed' );
						submit.disabled = false;
					} )
					.catch( function () {
						status.textContent = t( 'failed' );
						submit.disabled = false;
					} );
			} );
		}

		function uploadLogo( file ) {
			var body = new FormData();
			body.append( 'file', file );

			return fetch( cfg().root + 'wp/v2/media', {
				method: 'POST',
				headers: { 'X-WP-Nonce': cfg().nonce },
				body: body,
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) { return data && data.id ? data.id : 0; } )
				.catch( function () { return 0; } );
		}

		/* ---- Pay ---- */

		document.querySelectorAll( '[data-sponsor-pay]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-sponsor-pay' );

				btn.disabled = true;
				btn.textContent = t( 'opening' );

				fetch( cfg().restUrl + '/sponsorships/' + id + '/pay', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg().nonce },
				} )
					.then( function ( res ) {
						return res.json().then( function ( data ) {
							return { ok: res.ok, data: data };
						} );
					} )
					.then( function ( res ) {
						if ( res.data.checkout_url ) {
							window.location.href = res.data.checkout_url;
							return;
						}
						if ( window.guideSnackbar ) {
							window.guideSnackbar( res.data.error || t( 'failed' ) );
						}
						btn.disabled = false;
					} )
					.catch( function () {
						if ( window.guideSnackbar ) {
							window.guideSnackbar( t( 'failed' ) );
						}
						btn.disabled = false;
					} );
			} );
		} );
	} );
} )();
