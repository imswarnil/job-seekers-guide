( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn = document.getElementById( 'jsl-enroll-btn' );
		var box = document.getElementById( 'jsl-enroll-box' );
		var status = document.getElementById( 'jsl-enroll-status' );

		if ( ! btn || ! box || ! window.jslCourse ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			status.textContent = 'Please wait…';

			fetch( window.jslCourse.restUrl + '/enroll', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.jslCourse.nonce },
				body: JSON.stringify( { course_id: parseInt( box.getAttribute( 'data-course-id' ), 10 ) } ),
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( data.checkout_url ) {
						window.location.href = data.checkout_url;
						return;
					}
					if ( data.enrolled ) {
						status.textContent = 'Enrolled! Scroll down to start.';
						btn.textContent = 'Enrolled';
						return;
					}
					status.textContent = data.error || 'Something went wrong.';
					btn.disabled = false;
				} )
				.catch( function () {
					status.textContent = 'Network error — try again.';
					btn.disabled = false;
				} );
		} );
	} );
} )();
