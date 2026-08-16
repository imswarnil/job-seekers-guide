/**
 * Account page: save the profile, and start a subscription checkout.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! window.guideAccount ) {
			return;
		}

		function t( key ) {
			return ( window.guideAccount.i18n && window.guideAccount.i18n[ key ] ) || '';
		}

		/* ---- Profile ---- */
		var form   = document.getElementById( 'guide-profile-form' );
		var status = document.getElementById( 'guide-profile-status' );

		if ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var submit = form.querySelector( 'button[type="submit"]' );
				submit.disabled = true;
				status.textContent = t( 'saving' );

				fetch( window.guideAccount.restUrl + '/account/profile', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideAccount.nonce },
					body: JSON.stringify( {
						display_name: document.getElementById( 'guide-display-name' ).value,
						description: document.getElementById( 'guide-description' ).value,
					} ),
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.saved ) {
							status.textContent = t( 'saved' );
							if ( window.guideSnackbar ) {
								window.guideSnackbar( t( 'saved' ) );
							}
							return;
						}
						status.textContent = data.error || t( 'failed' );
					} )
					.catch( function () {
						status.textContent = t( 'failed' );
					} )
					.then( function () {
						submit.disabled = false;
					} );
			} );
		}

		/* ---- Subscribe ---- */
		var subBtn    = document.getElementById( 'guide-account-subscribe' );
		var subStatus = document.getElementById( 'guide-account-subscribe-status' );

		if ( subBtn ) {
			subBtn.addEventListener( 'click', function () {
				subBtn.disabled = true;
				subStatus.textContent = t( 'opening' );

				fetch( window.guideAccount.restUrl + '/subscribe', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideAccount.nonce },
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.checkout_url ) {
							window.location.href = data.checkout_url;
							return;
						}
						if ( data.already_subscribed ) {
							window.location.reload();
							return;
						}
						subStatus.textContent = data.error || t( 'failed' );
						subBtn.disabled = false;
					} )
					.catch( function () {
						subStatus.textContent = t( 'failed' );
						subBtn.disabled = false;
					} );
			} );
		}
	} );
} )();
