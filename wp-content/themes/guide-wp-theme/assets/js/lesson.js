/**
 * Lesson player: quiz, click-to-play video, and completion.
 *
 * Opening and closing the curriculum drawer lives in ui.js — this file is
 * only about the lesson itself.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ---- Click-to-play video facade (custom player shell) ---- */
		var player = document.querySelector( '.guide-video' );
		if ( player ) {
			player.querySelector( '.guide-video__play' ).addEventListener( 'click', function () {
				var type  = player.getAttribute( 'data-embed-type' );
				var src   = player.getAttribute( 'data-embed-src' );
				var start = parseInt( player.getAttribute( 'data-start' ), 10 ) || 0;
				var end   = parseInt( player.getAttribute( 'data-end' ), 10 ) || 0;
				var stage = player;

				stage.innerHTML = '';
				if ( type === 'video' ) {
					var vid = document.createElement( 'video' );
					vid.src = src;
					vid.controls = true;
					vid.autoplay = true;
					vid.playsInline = true;
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
										frame.title = player.getAttribute( 'data-title' ) || 'Video';
					frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
					frame.allowFullscreen = true;
					stage.appendChild( frame );
				}
			} );
		}

		/* ---- Quiz app (questions fetched without answers; graded server-side) ---- */
		var quizApp = document.getElementById( 'guide-quiz-app' );
		if ( quizApp && window.guideLesson ) {
			var quizLessonId = quizApp.getAttribute( 'data-lesson-id' );

			var renderQuiz = function ( quiz ) {
				if ( ! quiz.questions || ! quiz.questions.length ) {
					quizApp.innerHTML = '<p class="guide-empty__text">This quiz has no questions yet.</p>';
					return;
				}

				var html = '<div class="guide-quiz__head">' +
					'<h2 class="guide-card__title">Check yourself</h2>' +
					'<span class="guide-chip guide-chip--spark">Pass: ' + quiz.pass + '%</span></div>' +
					'<form id="guide-quiz-form" class="mt-4">';

				quiz.questions.forEach( function ( q, qi ) {
					html += '<fieldset class="guide-quiz__question" data-q="' + qi + '">' +
						'<legend class="guide-quiz__prompt">' + ( qi + 1 ) + '. ' + escHTML( q.q ) + '</legend>';
					q.options.forEach( function ( opt, oi ) {
						html += '<label class="guide-quiz__option guide-quiz-choice" data-o="' + oi + '">' +
							'<input type="radio" name="q' + qi + '" value="' + oi + '" required>' +
							'<span>' + escHTML( opt ) + '</span></label>';
					} );
					html += '<p class="guide-quiz__explain guide-quiz-explain" hidden></p></fieldset>';
				} );

				html += '<div class="guide-quiz__actions"><button type="submit" class="button is-primary">Submit answers</button>' +
					'<span id="guide-quiz-result" class="guide-quiz__result" aria-live="polite"></span></div></form>';

				quizApp.innerHTML = html;

				document.getElementById( 'guide-quiz-form' ).addEventListener( 'submit', function ( e ) {
					e.preventDefault();
					var answers = quiz.questions.map( function ( q, qi ) {
						var checked = quizApp.querySelector( 'input[name="q' + qi + '"]:checked' );
						return checked ? parseInt( checked.value, 10 ) : -1;
					} );

					fetch( window.guideLesson.restUrl + '/lessons/' + quizLessonId + '/quiz/grade', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideLesson.nonce },
						body: JSON.stringify( { answers: answers } ),
					} )
						.then( function ( res ) { return res.json(); } )
						.then( function ( result ) {
							var out = document.getElementById( 'guide-quiz-result' );
							if ( result.error ) {
								out.textContent = result.error;
								return;
							}
							out.textContent = ( result.passed ? '✓ Passed — ' : '✗ Not yet — ' ) + result.score + '% (' + result.correct + '/' + result.total + ')';
							out.className = 'guide-quiz__result ' + ( result.passed ? 'is-pass' : 'is-fail' );

							( result.review || [] ).forEach( function ( r, qi ) {
								var fs = quizApp.querySelector( '[data-q="' + qi + '"]' );
								fs.querySelectorAll( '.guide-quiz-choice' ).forEach( function ( label ) {
									var oi      = parseInt( label.getAttribute( 'data-o' ), 10 );
									var picked  = label.querySelector( 'input' );
									label.classList.remove( 'is-correct', 'is-wrong' );
									if ( oi === r.correct_index ) {
										label.classList.add( 'is-correct' );
									} else if ( picked && picked.checked ) {
										// Showing only the right answer leaves the
										// learner guessing which one they picked.
										label.classList.add( 'is-wrong' );
									}
								} );
								var explain = fs.querySelector( '.guide-quiz-explain' );
								if ( r.explain ) {
									explain.textContent = r.explain;
									explain.hidden = false;
								}
							} );

							if ( result.passed ) {
								if ( result.progress ) {
									setProgress( result.progress );
								}
								setDot( quizLessonId, true );
								var completeBtn = document.getElementById( 'guide-complete-btn' );
								if ( completeBtn ) {
									completeBtn.setAttribute( 'data-completed', '1' );
									completeBtn.textContent = completeBtn.getAttribute( 'data-label-done' );
									completeBtn.classList.remove( 'is-primary' );
									completeBtn.classList.add( 'is-light' );
								}
							}
						} )
						.catch( function () {
							document.getElementById( 'guide-quiz-result' ).textContent = 'Network error — try again.';
						} );
				} );
			};

			var escHTML = function ( str ) {
				var d = document.createElement( 'span' );
				d.textContent = str == null ? '' : String( str );
				return d.innerHTML;
			};

			fetch( window.guideLesson.restUrl + '/lessons/' + quizLessonId + '/quiz' )
				.then( function ( res ) { return res.json(); } )
				.then( renderQuiz )
				.catch( function () {
					quizApp.innerHTML = '<p class="guide-empty__text">Could not load the quiz — refresh to retry.</p>';
				} );
		}

		var btn      = document.getElementById( 'guide-complete-btn' );
		var nextLink = document.getElementById( 'guide-next-link' );

		if ( ! window.guideLesson ) {
			return;
		}

		// Phosphor "check" and "circle", matching the server-rendered icons.
		var CHECK_ICON  = '<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm45.66,85.66-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35a8,8,0,0,1,11.32,11.32Z"/></svg>';
		var CIRCLE_ICON = '<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,40a88,88,0,1,0,88,88A88.1,88.1,0,0,0,128,40Zm0,160a72,72,0,1,1,72-72A72.08,72.08,0,0,1,128,200Z"/></svg>';

		function setDot( lessonId, done ) {
			var row = document.querySelector( '[data-lesson-row="' + lessonId + '"]' );
			var dot = row && row.querySelector( '[data-lesson-dot]' );
			if ( ! dot ) {
				return;
			}
			row.classList.toggle( 'is-complete', !! done );
			dot.innerHTML = done ? CHECK_ICON : CIRCLE_ICON;
		}

		/** Copy supplied by PHP so it stays translatable. */
		function t( key ) {
			return ( window.guideLesson.i18n && window.guideLesson.i18n[ key ] ) || '';
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
			btn.classList.toggle( 'is-primary', ! done );
			btn.classList.toggle( 'is-light', done );
		}

		/**
		 * @param {string} method POST to complete, DELETE to un-complete.
		 * @returns {Promise} Resolves once the server has confirmed.
		 */
		function setCompletion( lessonId, method ) {
			return fetch( window.guideLesson.restUrl + '/lessons/' + lessonId + '/complete', {
				method: method,
				headers: { 'X-WP-Nonce': window.guideLesson.nonce },
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
						if ( ! window.guideSnackbar ) {
							return;
						}
						// Confirm the change and offer a way back — toggling
						// completion by accident should cost one tap to undo.
						window.guideSnackbar( nowDone ? t( 'completed' ) : t( 'uncompleted' ), {
							actionLabel: t( 'undo' ),
							onAction: function () {
								setCompletion( lessonId, nowDone ? 'DELETE' : 'POST' ).catch( function () {} );
							},
						} );
					} )
					.catch( function () {
						if ( window.guideSnackbar ) {
							window.guideSnackbar( t( 'failed' ) );
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

				if ( nextLink.dataset.guideGo === '1' ) {
					return; // Second pass, after the write settled.
				}

				event.preventDefault();

				var lessonId = btn.getAttribute( 'data-lesson-id' );
				var go       = function () {
					nextLink.dataset.guideGo = '1';
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

/* -------------------------------------------------------------------------
 * Filtering the lesson list
 * -------------------------------------------------------------------------
 * The whole course is already in the page, so this is a match-and-hide rather
 * than a search: instant, works offline, and no request to search a list the
 * reader is already looking at.
 *
 * Section headings hide when nothing under them matches, otherwise the list
 * turns into a column of empty labels.
 * ---------------------------------------------------------------------- */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var input = document.querySelector( '[data-lesson-filter]' );
		var empty = document.querySelector( '[data-lesson-filter-empty]' );
		var nav   = document.querySelector( '.guide-player__modules' );

		if ( ! input || ! nav ) {
			return;
		}

		var rows = Array.prototype.slice.call( nav.querySelectorAll( '[data-lesson-row]' ) );
		var labels = Array.prototype.slice.call( nav.querySelectorAll( '.guide-player__module-label' ) );

		function apply() {
			var term = input.value.trim().toLowerCase();
			var hits = 0;

			rows.forEach( function ( row ) {
				var match = ! term || row.textContent.toLowerCase().indexOf( term ) !== -1;
				row.hidden = ! match;
				if ( match ) {
					hits++;
				}
			} );

			// A section heading is only useful while something below it shows.
			labels.forEach( function ( label ) {
				var node = label.nextElementSibling;
				var any  = false;

				while ( node && ! node.classList.contains( 'guide-player__module-label' ) ) {
					if ( node.hasAttribute( 'data-lesson-row' ) && ! node.hidden ) {
						any = true;
						break;
					}
					node = node.nextElementSibling;
				}

				label.hidden = ! any;
			} );

			if ( empty ) {
				empty.hidden = hits > 0;
			}
		}

		input.addEventListener( 'input', apply );

		// Escape clears, which is what every filter box on the internet does.
		input.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && input.value ) {
				e.preventDefault();
				input.value = '';
				apply();
			}
		} );
	} );
} )();
