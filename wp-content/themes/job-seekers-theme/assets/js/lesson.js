/**
 * Lesson player: mark complete/incomplete (updates the sidebar progress
 * bar and lesson dots in place) + mobile course-nav toggle.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var navToggle = document.querySelector( '[data-player-nav-toggle]' );
		var navClose  = document.querySelector( '[data-player-nav-close]' );
		var scrim     = document.querySelector( '[data-player-scrim]' );
		var nav       = document.querySelector( '[data-player-nav]' );

		function setDrawer( open ) {
			if ( ! nav ) {
				return;
			}
			nav.classList.toggle( '-translate-x-full', ! open );
			if ( scrim ) {
				scrim.classList.toggle( 'hidden', ! open );
			}
			if ( navToggle ) {
				navToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			}
		}

		if ( navToggle && nav ) {
			navToggle.addEventListener( 'click', function () {
				setDrawer( nav.classList.contains( '-translate-x-full' ) );
			} );
		}
		if ( navClose ) {
			navClose.addEventListener( 'click', function () { setDrawer( false ); } );
		}
		if ( scrim ) {
			scrim.addEventListener( 'click', function () { setDrawer( false ); } );
		}

		var btn = document.getElementById( 'jsl-complete-btn' );
		if ( ! btn || ! window.jslLesson ) {
			return;
		}

		var checkIcon = '<svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';

		function setDot( lessonId, done ) {
			var row = document.querySelector( '[data-lesson-row="' + lessonId + '"]' );
			var dot = row && row.querySelector( '[data-lesson-dot]' );
			if ( ! dot ) {
				return;
			}
			if ( done ) {
				dot.className = 'grid h-5 w-5 shrink-0 place-items-center rounded-full bg-accent text-on-accent';
				dot.innerHTML = checkIcon;
			} else {
				dot.className = 'grid h-5 w-5 shrink-0 place-items-center rounded-full border border-line-strong text-ink-muted';
				dot.innerHTML = '';
			}
		}

		function setProgress( progress ) {
			var bar     = document.querySelector( '[data-progress-bar]' );
			var label   = document.querySelector( '[data-progress-label]' );
			var percent = document.querySelector( '[data-progress-percent]' );
			if ( bar ) {
				bar.style.width = progress.percent + '%';
			}
			if ( label ) {
				label.textContent = progress.completed + ' / ' + progress.total + ' complete';
			}
			if ( percent ) {
				percent.textContent = progress.percent + '%';
			}
		}

		btn.addEventListener( 'click', function () {
			var lessonId = btn.getAttribute( 'data-lesson-id' );
			var done     = btn.getAttribute( 'data-completed' ) === '1';

			btn.disabled = true;

			fetch( window.jslLesson.restUrl + '/lessons/' + lessonId + '/complete', {
				method: done ? 'DELETE' : 'POST',
				headers: { 'X-WP-Nonce': window.jslLesson.nonce },
			} )
				.then( function ( res ) {
					if ( ! res.ok ) {
						throw new Error( 'Request failed' );
					}
					return res.json();
				} )
				.then( function ( data ) {
					var nowDone = !! data.completed;
					btn.setAttribute( 'data-completed', nowDone ? '1' : '0' );
					btn.textContent = nowDone ? btn.getAttribute( 'data-label-done' ) : btn.getAttribute( 'data-label-todo' );
					btn.classList.toggle( 'jsl-btn--primary', ! nowDone );
					btn.classList.toggle( 'jsl-btn--ghost', nowDone );
					setDot( lessonId, nowDone );
					if ( data.progress ) {
						setProgress( data.progress );
					}
					btn.disabled = false;

					var next = document.getElementById( 'jsl-next-link' );
					if ( nowDone && next ) {
						next.classList.add( 'border-accent' );
					}
				} )
				.catch( function () {
					btn.disabled = false;
				} );
		} );
	} );
} )();
