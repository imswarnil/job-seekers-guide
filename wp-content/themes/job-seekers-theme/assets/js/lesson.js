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
