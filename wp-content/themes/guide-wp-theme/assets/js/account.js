/**
 * Account page: profile, picture, links, progress resets, and checkout.
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

		/* ---- Profile picture ---- */
		var avatarInput  = document.getElementById( 'guide-avatar-file' );
		var avatarImg    = document.getElementById( 'guide-avatar-preview' );
		var avatarRemove = document.getElementById( 'guide-avatar-remove' );
		var avatarStatus = document.getElementById( 'guide-avatar-status' );

		if ( avatarInput ) {
			avatarInput.addEventListener( 'change', function () {
				var file = avatarInput.files && avatarInput.files[ 0 ];

				if ( ! file ) {
					return;
				}

				// Check the size here as well as on the server, purely so
				// somebody on a slow connection is told immediately instead of
				// after uploading two megabytes.
				if ( file.size > 2097152 ) {
					avatarStatus.textContent = t( 'tooBig' );
					avatarInput.value = '';
					return;
				}

				var body = new FormData();
				body.append( 'file', file );

				avatarStatus.textContent = t( 'uploading' );

				fetch( window.guideAccount.restUrl + '/account/avatar', {
					method: 'POST',
					headers: { 'X-WP-Nonce': window.guideAccount.nonce },
					body: body,
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.saved && data.url ) {
							avatarImg.src = data.url;
							avatarStatus.textContent = t( 'saved' );
							if ( window.guideSnackbar ) {
								window.guideSnackbar( t( 'saved' ) );
							}
							return;
						}
						avatarStatus.textContent = data.error || t( 'failed' );
					} )
					.catch( function () {
						avatarStatus.textContent = t( 'failed' );
					} )
					.then( function () {
						avatarInput.value = '';
					} );
			} );
		}

		if ( avatarRemove ) {
			avatarRemove.addEventListener( 'click', function () {
				avatarRemove.disabled = true;
				avatarStatus.textContent = t( 'saving' );

				fetch( window.guideAccount.restUrl + '/account/avatar', {
					method: 'DELETE',
					headers: { 'X-WP-Nonce': window.guideAccount.nonce },
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function () {
						window.location.reload();
					} )
					.catch( function () {
						avatarStatus.textContent = t( 'failed' );
						avatarRemove.disabled = false;
					} );
			} );
		}

		/* ---- Links ---- */
		var linksForm   = document.getElementById( 'guide-links-form' );
		var linksStatus = document.getElementById( 'guide-links-status' );

		if ( linksForm ) {
			linksForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var submit = linksForm.querySelector( 'button[type="submit"]' );
				var body   = {};

				Array.prototype.forEach.call( linksForm.querySelectorAll( 'input[name]' ), function ( input ) {
					body[ input.name ] = input.value;
				} );

				submit.disabled = true;
				linksStatus.textContent = t( 'saving' );

				fetch( window.guideAccount.restUrl + '/account/links', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideAccount.nonce },
					body: JSON.stringify( body ),
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.saved ) {
							linksStatus.textContent = t( 'saved' );
							if ( window.guideSnackbar ) {
								window.guideSnackbar( t( 'saved' ) );
							}
							return;
						}
						linksStatus.textContent = data.error || t( 'failed' );
					} )
					.catch( function () {
						linksStatus.textContent = t( 'failed' );
					} )
					.then( function () {
						submit.disabled = false;
					} );
			} );
		}

		/* ---- Reset progress ---- */
		var resetStatus = document.getElementById( 'guide-reset-status' );

		Array.prototype.forEach.call( document.querySelectorAll( '.guide-reset-course' ), function ( button ) {
			button.addEventListener( 'click', function () {
				var title = button.getAttribute( 'data-title' ) || '';

				// Destructive and irreversible, so it asks first.
				if ( ! window.confirm( t( 'resetAsk' ).replace( '%s', title ) ) ) {
					return;
				}

				button.disabled = true;
				resetStatus.textContent = t( 'resetting' );

				fetch( window.guideAccount.restUrl + '/account/progress', {
					method: 'DELETE',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideAccount.nonce },
					body: JSON.stringify( { course_id: parseInt( button.getAttribute( 'data-course' ), 10 ) } ),
				} )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.reset ) {
							resetStatus.textContent = t( 'resetDone' );
							window.location.reload();
							return;
						}
						resetStatus.textContent = data.error || t( 'failed' );
						button.disabled = false;
					} )
					.catch( function () {
						resetStatus.textContent = t( 'failed' );
						button.disabled = false;
					} );
			} );
		} );

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
