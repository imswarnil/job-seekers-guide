/**
 * Lesson player: quiz, click-to-play video, and completion.
 *
 * The curriculum drawer is a standard M3 navigation drawer, so opening,
 * closing, the scrim and focus trapping all come from md3.js — this file
 * is only about the lesson itself.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ---- Click-to-play video facade (custom player shell) ---- */
		var player = document.querySelector( '.jsl-player' );
		if ( player ) {
			player.querySelector( '.jsl-player__play' ).addEventListener( 'click', function () {
				var type  = player.getAttribute( 'data-embed-type' );
				var src   = player.getAttribute( 'data-embed-src' );
				var start = parseInt( player.getAttribute( 'data-start' ), 10 ) || 0;
				var end   = parseInt( player.getAttribute( 'data-end' ), 10 ) || 0;
				var stage = player.querySelector( '.aspect-video' );

				stage.innerHTML = '';
				if ( type === 'video' ) {
					var vid = document.createElement( 'video' );
					vid.src = src;
					vid.controls = true;
					vid.autoplay = true;
					vid.playsInline = true;
					vid.className = 'absolute inset-0 h-full w-full';
					if ( start ) {
						vid.addEventListener( 'loadedmetadata', function () { vid.currentTime = start; } );
					}
					if ( end ) {
						vid.addEventListener( 'timeupdate', function () {
							if ( vid.currentTime >= end ) {
								vid.pause();
							}
						} );
					}
					stage.appendChild( vid );
					vid.play().catch( function () {} );
				} else {
					var frame = document.createElement( 'iframe' );
					frame.src = src;
					frame.className = 'absolute inset-0 h-full w-full border-0';
					frame.title = player.getAttribute( 'data-title' ) || 'Video';
					frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
					frame.allowFullscreen = true;
					stage.appendChild( frame );
				}
			} );
		}

		/* ---- Quiz app (questions fetched without answers; graded server-side) ---- */
		var quizApp = document.getElementById( 'jsl-quiz-app' );
		if ( quizApp && window.jslLesson ) {
			var quizLessonId = quizApp.getAttribute( 'data-lesson-id' );

			var renderQuiz = function ( quiz ) {
				if ( ! quiz.questions || ! quiz.questions.length ) {
					quizApp.innerHTML = '<p class="m-0 text-ink-muted">This quiz has no questions yet.</p>';
					return;
				}

				var html = '<div class="flex items-center justify-between gap-4">' +
					'<h2 class="m-0 text-lg font-bold">Check your knowledge</h2>' +
					'<span class="rounded-full bg-accent-soft px-3 py-1 text-xs font-bold text-accent">Pass: ' + quiz.pass + '%</span></div>' +
					'<form id="jsl-quiz-form" class="mt-5 flex flex-col gap-6">';

				quiz.questions.forEach( function ( q, qi ) {
					html += '<fieldset class="m-0 border-0 p-0" data-q="' + qi + '">' +
						'<legend class="mb-2.5 font-semibold text-ink">' + ( qi + 1 ) + '. ' + escHTML( q.q ) + '</legend>' +
						'<div class="flex flex-col gap-2">';
					q.options.forEach( function ( opt, oi ) {
						html += '<label class="jsl-quiz-choice flex cursor-pointer items-center gap-3 rounded-lg border border-line bg-surface px-4 py-2.5 text-sm text-ink-secondary transition hover:border-accent" data-o="' + oi + '">' +
							'<input class="accent-[var(--jsl-color-accent)]" type="radio" name="q' + qi + '" value="' + oi + '" required>' +
							'<span>' + escHTML( opt ) + '</span></label>';
					} );
					html += '</div><p class="jsl-quiz-explain mt-2 hidden rounded-lg bg-subtle px-4 py-2 text-sm text-ink-muted"></p></fieldset>';
				} );

				html += '<div class="flex items-center gap-4"><button type="submit" class="jsl-btn jsl-btn--primary">Submit answers</button>' +
					'<span id="jsl-quiz-result" class="text-sm font-semibold" aria-live="polite"></span></div></form>';

				quizApp.innerHTML = html;

				document.getElementById( 'jsl-quiz-form' ).addEventListener( 'submit', function ( e ) {
					e.preventDefault();
					var answers = quiz.questions.map( function ( q, qi ) {
						var checked = quizApp.querySelector( 'input[name="q' + qi + '"]:checked' );
						return checked ? parseInt( checked.value, 10 ) : -1;
					} );

					fetch( window.jslLesson.restUrl + '/lessons/' + quizLessonId + '/quiz/grade', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.jslLesson.nonce },
						body: JSON.stringify( { answers: answers } ),
					} )
						.then( function ( res ) { return res.json(); } )
						.then( function ( result ) {
							var out = document.getElementById( 'jsl-quiz-result' );
							if ( result.error ) {
								out.textContent = result.error;
								return;
							}
							out.textContent = ( result.passed ? '✓ Passed — ' : '✗ Not yet — ' ) + result.score + '% (' + result.correct + '/' + result.total + ')';
							out.className = 'text-sm font-bold ' + ( result.passed ? 'text-accent' : 'text-ink-muted' );

							( result.review || [] ).forEach( function ( r, qi ) {
								var fs = quizApp.querySelector( '[data-q="' + qi + '"]' );
								fs.querySelectorAll( '.jsl-quiz-choice' ).forEach( function ( label ) {
									var oi = parseInt( label.getAttribute( 'data-o' ), 10 );
									label.classList.remove( 'border-accent', 'bg-accent-softer', 'border-spark' );
									if ( oi === r.correct_index ) {
										label.classList.add( 'border-accent', 'bg-accent-softer' );
									}
								} );
								var explain = fs.querySelector( '.jsl-quiz-explain' );
								if ( r.explain ) {
									explain.textContent = r.explain;
									explain.classList.remove( 'hidden' );
								}
							} );

							if ( result.passed ) {
								if ( result.progress ) {
									setProgress( result.progress );
								}
								setDot( quizLessonId, true );
								var completeBtn = document.getElementById( 'jsl-complete-btn' );
								if ( completeBtn ) {
									completeBtn.setAttribute( 'data-completed', '1' );
									completeBtn.textContent = completeBtn.getAttribute( 'data-label-done' );
									completeBtn.classList.remove( 'jsl-btn--primary' );
									completeBtn.classList.add( 'jsl-btn--ghost' );
								}
							}
						} )
						.catch( function () {
							document.getElementById( 'jsl-quiz-result' ).textContent = 'Network error — try again.';
						} );
				} );
			};

			var escHTML = function ( str ) {
				var d = document.createElement( 'span' );
				d.textContent = str == null ? '' : String( str );
				return d.innerHTML;
			};

			fetch( window.jslLesson.restUrl + '/lessons/' + quizLessonId + '/quiz' )
				.then( function ( res ) { return res.json(); } )
				.then( renderQuiz )
				.catch( function () {
					quizApp.innerHTML = '<p class="m-0 text-ink-muted">Could not load the quiz — refresh to retry.</p>';
				} );
		}

		var btn      = document.getElementById( 'jsl-complete-btn' );
		var nextLink = document.getElementById( 'jsl-next-link' );

		if ( ! window.jslLesson ) {
			return;
		}

		// Phosphor "check", matching the server-rendered icons.
		var checkIcon = '<svg class="w-3 h-3" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>';

		function setDot( lessonId, done ) {
			var row = document.querySelector( '[data-lesson-row="' + lessonId + '"]' );
			var dot = row && row.querySelector( '[data-lesson-dot]' );
			if ( ! dot ) {
				return;
			}
			if ( done ) {
				dot.className = 'md-list-item__leading grid h-5 w-5 shrink-0 place-items-center rounded-full bg-primary text-on-primary';
				dot.innerHTML = checkIcon;
			} else {
				dot.className = 'md-list-item__leading grid h-5 w-5 shrink-0 place-items-center rounded-full border border-outline text-on-surface-variant';
				dot.innerHTML = '';
			}
		}

		/** Copy supplied by PHP so it stays translatable. */
		function t( key ) {
			return ( window.jslLesson.i18n && window.jslLesson.i18n[ key ] ) || '';
		}

		/**
		 * Progress is shown in two places at once — the app bar and the
		 * curriculum sidebar — so update every instance, not just the first.
		 */
		function setProgress( progress ) {
			document.querySelectorAll( '[data-progress-bar]' ).forEach( function ( bar ) {
				bar.style.width = progress.percent + '%';
			} );
			document.querySelectorAll( '[data-progress-percent]' ).forEach( function ( el ) {
				el.textContent = progress.percent + '%';
			} );
			document.querySelectorAll( '[data-progress-label]' ).forEach( function ( el ) {
				el.textContent = progress.completed + ' / ' + progress.total + ' complete';
			} );
		}

		function paintButton( done ) {
			if ( ! btn ) {
				return;
			}
			btn.setAttribute( 'data-completed', done ? '1' : '0' );
			btn.textContent = done ? btn.getAttribute( 'data-label-done' ) : btn.getAttribute( 'data-label-todo' );
			btn.classList.toggle( 'jsl-btn--primary', ! done );
			btn.classList.toggle( 'jsl-btn--tonal', done );
		}

		/**
		 * @param {string} method POST to complete, DELETE to un-complete.
		 * @returns {Promise} Resolves once the server has confirmed.
		 */
		function setCompletion( lessonId, method ) {
			return fetch( window.jslLesson.restUrl + '/lessons/' + lessonId + '/complete', {
				method: method,
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
					paintButton( nowDone );
					setDot( lessonId, nowDone );
					if ( data.progress ) {
						setProgress( data.progress );
					}
					return nowDone;
				} );
		}

		if ( btn ) {
			btn.addEventListener( 'click', function () {
				var lessonId = btn.getAttribute( 'data-lesson-id' );
				var done     = btn.getAttribute( 'data-completed' ) === '1';

				btn.disabled = true;

				setCompletion( lessonId, done ? 'DELETE' : 'POST' )
					.then( function ( nowDone ) {
						if ( ! window.jslSnackbar ) {
							return;
						}
						// Confirm the change and offer a way back — toggling
						// completion by accident should cost one tap to undo.
						window.jslSnackbar( nowDone ? t( 'completed' ) : t( 'uncompleted' ), {
							actionLabel: t( 'undo' ),
							onAction: function () {
								setCompletion( lessonId, nowDone ? 'DELETE' : 'POST' ).catch( function () {} );
							},
						} );
					} )
					.catch( function () {
						if ( window.jslSnackbar ) {
							window.jslSnackbar( t( 'failed' ) );
						}
					} )
					.then( function () {
						btn.disabled = false;
					} );
			} );
		}

		/**
		 * Moving on IS finishing the lesson. Clicking "Next" marks the current
		 * lesson complete first, then navigates — so a learner working through
		 * a course never has to also remember to press a button.
		 *
		 * The navigation is not held hostage to the request: if the write
		 * fails or is slow, we still go to the next lesson after a short
		 * grace period rather than stranding the learner on a dead link.
		 */
		if ( nextLink && btn && btn.getAttribute( 'data-completed' ) !== '1' ) {
			nextLink.addEventListener( 'click', function ( event ) {
				// Let modified clicks (new tab/window) behave normally.
				if ( event.metaKey || event.ctrlKey || event.shiftKey || 1 === event.button ) {
					return;
				}

				if ( nextLink.dataset.jslGo === '1' ) {
					return; // Second pass, after the write settled.
				}

				event.preventDefault();

				var lessonId = btn.getAttribute( 'data-lesson-id' );
				var go       = function () {
					nextLink.dataset.jslGo = '1';
					window.location.href = nextLink.href;
				};

				var settled = false;
				var once    = function () {
					if ( ! settled ) {
						settled = true;
						go();
					}
				};

				setCompletion( lessonId, 'POST' ).catch( function () {} ).then( once );
				window.setTimeout( once, 1200 );
			} );
		}
	} );
} )();
