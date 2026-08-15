/**
 * Homepage pricing card: start a platform-subscription checkout.
 *
 * The server decides everything that matters (whether subscriptions are on,
 * whether this user already has one, what the product is) — this only asks
 * and follows the checkout URL it gets back.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn = document.getElementById( 'jsl-home-subscribe' );
		var status = document.getElementById( 'jsl-home-subscribe-status' );

		if ( ! btn || ! window.jslSubscribe ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			status.textContent = 'Opening checkout…';

			fetch( window.jslSubscribe.restUrl + '/subscribe', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.jslSubscribe.nonce },
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
					status.textContent = data.error || 'Could not start checkout.';
					btn.disabled = false;
				} )
				.catch( function () {
					status.textContent = 'Network error — try again.';
					btn.disabled = false;
				} );
		} );
	} );
} )();
