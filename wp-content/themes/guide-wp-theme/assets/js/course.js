/**
 * Course page actions: enroll in a free course, or start a subscription
 * checkout for a members-only one.
 *
 * There is no per-course purchase — the platform sells a single subscription,
 * so `/enroll` never returns a checkout URL any more. If it answers 402 the
 * course is members-only and the subscribe flow takes over.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var box    = document.getElementById( 'guide-enroll-box' );
		var btn    = document.getElementById( 'guide-enroll-btn' );
		var subBtn = document.getElementById( 'guide-subscribe-btn' );
		var status = document.getElementById( 'guide-enroll-status' );

		if ( ! box || ! window.guideCourse ) {
			return;
		}

		function say( message ) {
			if ( status ) {
				status.textContent = message;
			}
		}

		function startSubscription( trigger ) {
			if ( trigger ) {
				trigger.disabled = true;
			}
			say( 'Opening checkout…' );

			return fetch( window.guideCourse.restUrl + '/subscribe', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideCourse.nonce },
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
					say( data.error || 'Could not start checkout.' );
					if ( trigger ) {
						trigger.disabled = false;
					}
				} )
				.catch( function () {
					say( 'Network error — try again.' );
					if ( trigger ) {
						trigger.disabled = false;
					}
				} );
		}

		if ( subBtn ) {
			subBtn.addEventListener( 'click', function () {
				startSubscription( subBtn );
			} );
		}

		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			say( 'Please wait…' );

			fetch( window.guideCourse.restUrl + '/enroll', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideCourse.nonce },
				body: JSON.stringify( { course_id: parseInt( box.getAttribute( 'data-course-id' ), 10 ) } ),
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					// The course turned out to be members-only — hand straight over
					// to the subscribe flow rather than showing a dead end.
					if ( data.needsSubscription ) {
						startSubscription( btn );
						return;
					}
					if ( data.enrolled ) {
						say( 'Enrolled — scroll down to start.' );
						btn.textContent = 'Enrolled';
						return;
					}
					say( data.error || 'Something went wrong.' );
					btn.disabled = false;
				} )
				.catch( function () {
					say( 'Network error — try again.' );
					btn.disabled = false;
				} );
		} );
	} );
} )();
