/**
 * LMS Console SPA.
 *
 * Hash-routed views: #/ (dashboard analytics), #/courses (catalog + create),
 * #/courses/:id (drag-drop builder + inline lesson writing in a drawer),
 * #/learners (list), #/learners/:id (profile + progress). Data over
 * guide/v1 (LMS) and wp/v2 (post content) with the REST nonce.
 */
( function () {
	'use strict';

	if ( ! window.guideConsole ) {
		return;
	}

	var cfg  = window.guideConsole;
	var view = document.getElementById( 'guide-view' );

	/* ================= helpers ================= */

	function req( path, options ) {
		options = options || {};
		options.headers = Object.assign(
			{ 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
			options.headers || {}
		);
		if ( options.body && typeof options.body !== 'string' ) {
			options.body = JSON.stringify( options.body );
		}
		return fetch( cfg.root + path, options ).then( function ( res ) {
			if ( ! res.ok ) {
				return res.json().catch( function () { return {}; } ).then( function ( data ) {
					throw new Error( data.message || data.error || ( 'Request failed: ' + res.status ) );
				} );
			}
			return res.status === 204 ? null : res.json();
		} );
	}

	function el( tag, attrs, text ) {
		var node = document.createElement( tag );
		Object.keys( attrs || {} ).forEach( function ( key ) {
			node.setAttribute( key, attrs[ key ] );
		} );
		if ( text !== undefined && text !== null ) {
			node.textContent = String( text );
		}
		return node;
	}

	function esc( str ) {
		var d = document.createElement( 'span' );
		d.textContent = str == null ? '' : String( str );
		return d.innerHTML;
	}

	var ICONS = {
		grip: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.7"/><circle cx="15" cy="6" r="1.7"/><circle cx="9" cy="12" r="1.7"/><circle cx="15" cy="12" r="1.7"/><circle cx="9" cy="18" r="1.7"/><circle cx="15" cy="18" r="1.7"/></svg>',
		pencil: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/></svg>',
		trash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>',
		check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
		plus: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
		layers: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/></svg>',
		doc: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h7l5 5v13H7V3Z"/><path d="M14 3v5h5"/></svg>',
		users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>',
		flame: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21c-3.9 0-6.5-2.5-6.5-6 0-2.6 1.7-4.6 3-6.2C9.7 7.3 10.7 6 11 4c2.5 1.5 4 3.5 4 6 1-0.6 1.6-1.4 2-2.5 1 1.5 1.5 3.2 1.5 5 0 4.6-2.6 8.5-6.5 8.5Z"/></svg>',
		arrowL: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12H4m6-6-6 6 6 6"/></svg>',
		external: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M9 5H5v14h14v-4"/></svg>',
		path: '<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,168a32,32,0,1,0,32,32A32,32,0,0,0,216,168Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,216,216ZM40,88A32,32,0,1,0,8,56,32,32,0,0,0,40,88ZM40,40A16,16,0,1,1,24,56,16,16,0,0,1,40,40Zm128,72a40,40,0,0,1-40,40H88a24,24,0,0,0,0,48h48v16H88a40,40,0,0,1,0-80h40a24,24,0,0,0,0-48H80V72h48A40,40,0,0,1,168,112Z"/></svg>',
		video: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M10 9.5v5l4.5-2.5-4.5-2.5Z"/></svg>',
		quiz: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.2 2.4c-.6.2-.9.7-.9 1.3v.3"/><circle cx="12" cy="17" r=".6" fill="currentColor"/></svg>',
	};

	/**
	 * Where overlays (toasts, drawers, scrims) get attached.
	 *
	 * The console's stylesheet is compiled scoped under `.guide-admin`, so
	 * anything appended straight to <body> lands outside that scope and renders
	 * completely unstyled. Mounting inside the scope root keeps the CSS honest;
	 * <body> stays as a fallback so nothing breaks if the wrapper ever moves.
	 */
	function overlayHost() {
		return document.querySelector( '.guide-admin' ) || document.body;
	}

	function toast( message, isError ) {
		var host = document.querySelector( '.guide-toasts' );
		if ( ! host ) {
			host = el( 'div', { class: 'guide-toasts' } );
			overlayHost().appendChild( host );
		}
		var node = el( 'div', { class: 'guide-toast' + ( isError ? ' guide-toast--error' : '' ) } );
		node.innerHTML = '<span class="guide-toast__icon">' + ICONS.check + '</span>';
		node.appendChild( document.createTextNode( message ) );
		host.appendChild( node );
		setTimeout( function () { node.remove(); }, 3200 );
	}

	function fail( err ) {
		toast( err && err.message ? err.message : 'Something went wrong', true );
		throw err;
	}

	function progressHTML( pct ) {
		return '<span class="guide-progress"><span class="guide-progress__track"><span class="guide-progress__fill" style="width:' + ( pct || 0 ) + '%"></span></span><span class="guide-progress__pct">' + ( pct || 0 ) + '%</span></span>';
	}

	function sparkHTML( days ) {
		var max = Math.max.apply( null, days.map( function ( d ) { return d.count; } ).concat( [ 1 ] ) );
		var bars = days.map( function ( d ) {
			var h = Math.round( ( d.count / max ) * 100 );
			return '<span class="guide-spark__bar" title="' + esc( d.date + ': ' + d.count ) + '"><i style="height:' + Math.max( h, d.count ? 8 : 0 ) + '%"></i></span>';
		} ).join( '' );
		var first = days[ 0 ] ? days[ 0 ].date.slice( 5 ) : '';
		var last  = days.length ? days[ days.length - 1 ].date.slice( 5 ) : '';
		return '<div class="guide-spark">' + bars + '</div><div class="guide-spark-labels"><span>' + esc( first ) + '</span><span>' + esc( last ) + '</span></div>';
	}

	function statHTML( icon, label, value, hint ) {
		return '<div class="guide-stat"><span class="guide-stat__label">' + ICONS[ icon ] + esc( label ) + '</span><span class="guide-stat__value">' + esc( value ) + '</span>' + ( hint ? '<span class="guide-stat__hint">' + esc( hint ) + '</span>' : '' ) + '</div>';
	}

	function setNav( key ) {
		document.querySelectorAll( '.guide-console__links a' ).forEach( function ( a ) {
			a.classList.toggle( 'is-active', a.getAttribute( 'data-nav' ) === key );
		} );
	}

	function loading() {
		view.innerHTML = '<div class="guide-skeleton-page"><span class="guide-spinner"></span>Loading…</div>';
	}

	function editable( node, onSave ) {
		node.setAttribute( 'contenteditable', 'true' );
		node.setAttribute( 'spellcheck', 'false' );
		var original = node.textContent;
		node.addEventListener( 'focus', function () { original = node.textContent; } );
		node.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) { e.preventDefault(); node.blur(); }
			if ( e.key === 'Escape' ) { node.textContent = original; node.blur(); }
		} );
		node.addEventListener( 'blur', function () {
			var value = node.textContent.trim();
			if ( ! value ) { node.textContent = original; return; }
			if ( value !== original ) { node.textContent = value; onSave( value ); }
		} );
	}

	function confirmInline( host, message, onConfirm ) {
		if ( host.querySelector( '.guide-confirm' ) ) {
			return;
		}
		var bar = el( 'div', { class: 'guide-confirm' } );
		bar.appendChild( el( 'span', {}, message ) );
		var yes = el( 'button', { type: 'button', class: 'guide-confirm__yes' }, 'Yes, delete' );
		var no  = el( 'button', { type: 'button', class: 'guide-confirm__no' }, 'Cancel' );
		bar.appendChild( yes );
		bar.appendChild( no );
		yes.addEventListener( 'click', function () { bar.remove(); onConfirm(); } );
		no.addEventListener( 'click', function () { bar.remove(); } );
		host.appendChild( bar );
		yes.focus();
	}

	/* ================= router ================= */

	function route() {
		closeDrawer();
		var hash = ( window.location.hash || '#/' ).replace( /^#/, '' );
		var m;

		if ( ( m = hash.match( /^\/courses\/(\d+)/ ) ) ) {
			return renderCourseEditor( parseInt( m[ 1 ], 10 ) );
		}
		if ( hash.indexOf( '/courses' ) === 0 ) {
			return renderCourses();
		}
		if ( hash.indexOf( '/stories' ) === 0 ) {
			return renderStories();
		}
		if ( ( m = hash.match( /^\/paths\/(\d+)/ ) ) ) {
			return renderPathEditor( parseInt( m[ 1 ], 10 ) );
		}
		if ( hash.indexOf( '/paths' ) === 0 ) {
			return renderPaths();
		}
		if ( ( m = hash.match( /^\/learners\/(\d+)/ ) ) ) {
			return renderLearner( parseInt( m[ 1 ], 10 ) );
		}
		if ( hash.indexOf( '/learners' ) === 0 ) {
			return renderLearners();
		}
		return renderDashboard();
	}

	window.addEventListener( 'hashchange', route );

	/* ================= dashboard ================= */

	function renderDashboard() {
		setNav( 'dashboard' );
		loading();

		Promise.all( [
			req( 'guide/v1/analytics/overview' ),
			req( 'guide/v1/analytics/courses' ),
		] ).then( function ( results ) {
			var o = results[ 0 ];
			var courses = results[ 1 ].courses.filter( function ( c ) { return c.status === 'publish'; } );

			var courseRows = courses.map( function ( c ) {
				return '<tr class="is-clickable" data-href="#/courses/' + c.id + '">' +
					'<td><strong>' + esc( c.title ) + '</strong></td>' +
					'<td class="guide-num">' + c.enrolled + '</td>' +
					'<td class="guide-num">' + c.completions + '</td>' +
					'<td>' + progressHTML( c.avg_progress ) + '</td>' +
				'</tr>';
			} ).join( '' );

			var feed = o.activity.map( function ( a ) {
				return '<li><span class="guide-feed__dot"></span><span><strong>' + esc( a.user ) + '</strong> completed “' + esc( a.lesson ) + '” <span class="guide-feed__course">· ' + esc( a.course ) + '</span></span><span class="guide-feed__when">' + esc( a.when ) + ' ago</span></li>';
			} ).join( '' ) || '<li><span>No activity yet.</span></li>';

			// The funnel matters more than any single count: it is where people
			// stop. Registered -> enrolled -> started -> stuck with it.
			var f = o.funnel || {};
			var funnelHTML = [
				[ 'Registered', f.registered ],
				[ 'Enrolled', f.enrolled ],
				[ 'Started a lesson', f.started ],
				[ 'Finished 5+', f.engaged ],
			].map( function ( row ) {
				var pct = f.registered ? Math.round( ( row[ 1 ] / f.registered ) * 100 ) : 0;
				return '<div class="guide-funnel__row">' +
					'<span class="guide-funnel__label">' + esc( row[ 0 ] ) + '</span>' +
					'<span class="guide-funnel__track"><span class="guide-funnel__fill" style="width:' + pct + '%"></span></span>' +
					'<span class="guide-funnel__value">' + ( row[ 1 ] || 0 ) + '</span>' +
				'</div>';
			} ).join( '' );

			var fb  = o.feedback || {};
			var c   = o.content || {};

			view.innerHTML =
				'<header class="guide-page-head"><div><h1>Dashboard</h1><p class="guide-sub">What learners are doing across your LMS.</p></div></header>' +
				'<div class="guide-stat-grid">' +
					statHTML( 'users', 'Learners', o.learners ) +
					statHTML( 'layers', 'Enrollments', o.enrollments ) +
					statHTML( 'check', 'Lessons completed', o.completions ) +
					statHTML( 'flame', 'Active this week', o.active_7d, 'learners with completions' ) +
					statHTML( 'star', 'Subscribers', o.subscribers, 'active platform plans' ) +
					statHTML( 'doc', 'Published', ( c.courses || 0 ) + ' courses', ( c.lessons || 0 ) + ' lessons · ' + ( c.sections || 0 ) + ' sections' ) +
				'</div>' +

				'<div class="guide-grid-2">' +
					'<section class="guide-card"><div class="guide-card__head"><h2>Where people stop</h2>' +
						'<span class="guide-sub">Share of everyone who registered</span></div>' +
						'<div class="guide-card__body"><div class="guide-funnel">' + funnelHTML + '</div></div></section>' +

					'<section class="guide-card"><div class="guide-card__head"><h2>Feedback</h2>' +
						'<a class="guide-btn guide-btn--ghost guide-btn--sm" href="' + esc( cfg.adminUrl ) + 'admin.php?page=guide-feedback">Open queue</a></div>' +
						'<div class="guide-card__body">' +
							'<div class="guide-stat-grid" style="margin-bottom:0">' +
								statHTML( 'check', 'Useful', fb.up || 0 ) +
								statHTML( 'question', 'Not useful', fb.down || 0 ) +
								statHTML( 'doc', 'Unread notes', fb.unread || 0 ) +
							'</div>' +
							( fb.worst
								? '<p class="guide-help" style="margin-top:12px">Most flagged: <a href="' + esc( fb.worst.link ) + '" target="_blank" rel="noopener">' + esc( fb.worst.title ) + '</a> — ' + fb.worst.downs + ' said not useful.</p>'
								: '<p class="guide-help" style="margin-top:12px">Nothing flagged yet.</p>' ) +
						'</div></section>' +
				'</div>' +

				'<div class="guide-grid-2">' +
					'<div style="display:flex;flex-direction:column;gap:16px;min-width:0">' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Completions — last 14 days</h2></div><div class="guide-card__body">' + sparkHTML( o.completions_14d ) + '</div></section>' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Course performance</h2><a class="guide-btn guide-btn--ghost guide-btn--sm" href="#/courses">All courses</a></div><div class="guide-table-wrap"><table class="guide-table"><thead><tr><th>Course</th><th class="guide-num">Enrolled</th><th class="guide-num">Completions</th><th>Avg progress</th></tr></thead><tbody>' + ( courseRows || '<tr><td colspan="4">No published courses yet.</td></tr>' ) + '</tbody></table></div></section>' +
					'</div>' +
					'<section class="guide-card"><div class="guide-card__head"><h2>Recent activity</h2></div><ul class="guide-feed">' + feed + '</ul></section>' +
				'</div>';

			bindRowLinks();
		} ).catch( fail );
	}

	function bindRowLinks() {
		view.querySelectorAll( '[data-href]' ).forEach( function ( row ) {
			row.addEventListener( 'click', function () {
				window.location.hash = row.getAttribute( 'data-href' ).replace( /^#/, '' );
			} );
		} );
	}

	/* ================= courses ================= */

	function renderCourses() {
		setNav( 'courses' );
		loading();

		req( 'guide/v1/analytics/courses' ).then( function ( data ) {
			var cards = data.courses.map( function ( c ) {
				return '<div class="guide-course-card" data-href="#/courses/' + c.id + '" role="link" tabindex="0">' +
					'<div class="guide-course-card__top"><span class="guide-badge guide-badge--' + esc( c.status ) + '">' + esc( c.status ) + '</span>' +
					( c.enrolled ? '<span class="guide-badge">' + c.enrolled + ' enrolled</span>' : '' ) + '</div>' +
					'<h3>' + esc( c.title || '(untitled)' ) + '</h3>' +
					'<div style="max-width:180px">' + progressHTML( c.avg_progress ) + '</div>' +
					'<div class="guide-course-card__meta"><span>' + ICONS.layers + c.modules + ' modules</span><span>' + ICONS.doc + c.lessons + ' lessons</span></div>' +
				'</div>';
			} ).join( '' );

			view.innerHTML =
				'<header class="guide-page-head"><div><h1>Courses</h1><p class="guide-sub">Create, structure, and publish your curriculum.</p></div>' +
				'<div class="guide-page-head__actions"><form class="guide-inline-form" id="guide-new-course"><input class="guide-input" type="text" placeholder="New course title…" required style="width:240px"><button class="guide-btn guide-btn--primary" type="submit">' + ICONS.plus + 'Create course</button></form></div></header>' +
				( cards
					? '<div class="guide-course-grid">' + cards + '</div>'
					: '<div class="guide-empty-state"><h3>No courses yet</h3><p>Create your first course above — it starts as a draft.</p></div>' );

			bindRowLinks();

			var form  = document.getElementById( 'guide-new-course' );
			var input = form.querySelector( 'input' );
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var title = input.value.trim();
				if ( ! title ) {
					return;
				}
				form.querySelector( 'button' ).disabled = true;
				req( 'wp/v2/courses', { method: 'POST', body: { title: title, status: 'draft' } } )
					.then( function ( course ) {
						toast( 'Course created' );
						window.location.hash = '/courses/' + course.id;
					} )
					.catch( function ( err ) {
						form.querySelector( 'button' ).disabled = false;
						fail( err );
					} );
			} );
		} ).catch( fail );
	}

	/* ================= course editor (builder) ================= */

	var dragged   = null;
	var indicator = el( 'li', { class: 'guide-drop-indicator' } );
	var saveTimer = null;

	function setSaveState( state ) {
		var eln = document.getElementById( 'guide-save-state' );
		if ( ! eln ) {
			return;
		}
		clearTimeout( saveTimer );
		eln.classList.toggle( 'is-error', state === 'error' );
		eln.textContent = state === 'saving' ? 'Saving…' : state === 'saved' ? 'Saved' : 'Error';
		if ( state !== 'saving' ) {
			saveTimer = setTimeout( function () { eln.textContent = ''; }, 2200 );
		}
	}

	function api( path, options ) {
		setSaveState( 'saving' );
		return req( path, options ).then( function ( data ) {
			setSaveState( 'saved' );
			return data;
		} ).catch( function ( err ) {
			setSaveState( 'error' );
			toast( err.message || 'Save failed', true );
			throw err;
		} );
	}

	function renderCourseEditor( courseId ) {
		setNav( 'courses' );
		loading();

		Promise.all( [
			req( 'wp/v2/courses/' + courseId + '?context=edit' ),
			req( 'guide/v1/courses/' + courseId + '/structure' ),
			req( 'wp/v2/course-categories?per_page=100' ).catch( function () { return []; } ),
		] ).then( function ( results ) {
			var course     = results[ 0 ];
			var structure  = results[ 1 ];
			var categories = results[ 2 ];
			var isPublish = course.status === 'publish';

			view.innerHTML =
				'<nav class="guide-breadcrumb"><a href="#/courses">' + ICONS.arrowL + ' Courses</a></nav>' +
				'<header class="guide-page-head">' +
					'<div style="flex:1;min-width:240px"><h1 class="guide-title-input" id="guide-course-title"></h1>' +
					'<p class="guide-sub"><span class="guide-badge guide-badge--' + esc( course.status ) + '" id="guide-course-status">' + esc( course.status ) + '</span> <span id="guide-builder-stats"></span></p></div>' +
					'<div class="guide-page-head__actions">' +
						'<span class="guide-save-state" id="guide-save-state" aria-live="polite"></span>' +
						'<button class="guide-btn guide-btn--ghost" id="guide-toggle-status">' + ( isPublish ? 'Unpublish' : 'Publish' ) + '</button>' +
						'<a class="guide-btn guide-btn--ghost" href="' + esc( course.link ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'</div>' +
				'</header>' +
				'<div class="guide-editor-layout">' +
					'<div id="guide-builder-root"></div>' +
					'<aside style="display:flex;flex-direction:column;gap:16px">' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Course settings</h2></div><div class="guide-card__body">' +
							'<div class="guide-field"><label for="guide-course-code">Course code</label>' +
							'<input class="guide-input" id="guide-course-code" type="text" maxlength="12" placeholder="JSG-101" style="max-width:160px;font-family:ui-monospace,Menlo,monospace;text-transform:uppercase">' +
							'<span class="guide-help">Shown on cards, placeholder art, and JSON-LD.</span></div>' +
							'<div class="guide-field" style="margin-bottom:0"><label>Categories</label><div id="guide-course-cats"></div>' +
							'<form class="guide-inline-form" id="guide-new-cat" style="margin-top:6px"><input class="guide-input" type="text" placeholder="New category…"><button class="guide-btn guide-btn--ghost guide-btn--sm" type="submit">Add</button></form></div>' +
						'</div></section>' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Course card text</h2></div><div class="guide-card__body">' +
							'<div class="guide-field"><label for="guide-course-excerpt">Short description</label>' +
							'<textarea class="guide-input" id="guide-course-excerpt" rows="4" placeholder="One or two sentences shown on course cards…"></textarea>' +
							'<span class="guide-help">Saved when you click away.</span></div>' +
						'</div></section>' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Course page</h2></div><div class="guide-card__body" style="display:flex;flex-direction:column;gap:8px">' +
							'<button class="guide-btn guide-btn--primary" id="guide-open-details" type="button">' + ICONS.pencil + 'Edit details &amp; description</button>' +
							'<span class="guide-help">Full description, image, level, outcomes, requirements and access.</span>' +
						'</div></section>' +
					'</aside>' +
				'</div>';

			var titleEl = document.getElementById( 'guide-course-title' );
			titleEl.textContent = course.title.raw || '(untitled)';
			editable( titleEl, function ( value ) {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { title: value } } );
			} );

			var excerptEl = document.getElementById( 'guide-course-excerpt' );
			excerptEl.value = course.excerpt && course.excerpt.raw ? course.excerpt.raw : '';
			excerptEl.addEventListener( 'blur', function () {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { excerpt: excerptEl.value } } );
			} );

			document.getElementById( 'guide-toggle-status' ).addEventListener( 'click', function () {
				var next = course.status === 'publish' ? 'draft' : 'publish';
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { status: next } } ).then( function () {
					course.status = next;
					document.getElementById( 'guide-course-status' ).textContent = next;
					document.getElementById( 'guide-course-status' ).className = 'guide-badge guide-badge--' + next;
					document.getElementById( 'guide-toggle-status' ).textContent = next === 'publish' ? 'Unpublish' : 'Publish';
					toast( next === 'publish' ? 'Course published' : 'Course set to draft' );
				} );
			} );

			document.getElementById( 'guide-open-details' ).addEventListener( 'click', function () {
				openCourseDrawer( courseId, course );
			} );

			/* Course code */
			var codeInput = document.getElementById( 'guide-course-code' );
			codeInput.value = ( course.meta && course.meta.jsl_course_code ) || '';
			codeInput.addEventListener( 'blur', function () {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { meta: { jsl_course_code: codeInput.value.trim().toUpperCase() } } } );
			} );

			/* Categories */
			var catHost  = document.getElementById( 'guide-course-cats' );
			var selected = ( course[ 'course-categories' ] || [] ).slice();

			function renderCats() {
				catHost.innerHTML = '';
				if ( ! categories.length ) {
					catHost.appendChild( el( 'span', { class: 'guide-help' }, 'No categories yet — add one below.' ) );
				}
				categories.forEach( function ( cat ) {
					var label = el( 'label', { class: 'guide-cat-check' } );
					var box   = el( 'input', { type: 'checkbox' } );
					box.checked = selected.indexOf( cat.id ) !== -1;
					box.addEventListener( 'change', function () {
						if ( box.checked ) {
							selected.push( cat.id );
						} else {
							selected = selected.filter( function ( id ) { return id !== cat.id; } );
						}
						api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { 'course-categories': selected } } );
					} );
					label.appendChild( box );
					label.appendChild( document.createTextNode( ' ' + cat.name ) );
					catHost.appendChild( label );
				} );
			}
			renderCats();

			var catForm = document.getElementById( 'guide-new-cat' );
			catForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var input = catForm.querySelector( 'input' );
				var name  = input.value.trim();
				if ( ! name ) {
					return;
				}
				api( 'wp/v2/course-categories', { method: 'POST', body: { name: name } } ).then( function ( cat ) {
					categories.push( cat );
					selected.push( cat.id );
					renderCats();
					input.value = '';
					api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { 'course-categories': selected } } );
				} );
			} );

			renderBuilder( courseId, structure );
		} ).catch( fail );
	}

	/* ---- builder internals ---- */

	function renderBuilder( courseId, structure ) {
		var root = document.getElementById( 'guide-builder-root' );
		root.innerHTML = '';

		if ( ! structure.modules.length ) {
			root.appendChild( el( 'div', { class: 'guide-empty-state' }, 'No modules yet — create your first module below to start structuring this course.' ) );
		}

		structure.modules.forEach( function ( module, index ) {
			root.appendChild( moduleCard( courseId, module, index ) );
		} );

		var form  = el( 'form', { class: 'guide-add-module' } );
		var input = el( 'input', { type: 'text', class: 'guide-input', placeholder: 'New module title (e.g. Week 1 — Foundations)…', required: 'required', 'aria-label': 'Add module' } );
		var btn   = el( 'button', { type: 'submit', class: 'guide-btn guide-btn--primary' }, '+ Add module' );
		form.appendChild( input );
		form.appendChild( btn );

		// Reuse: pull in a section that already exists in another course or
		// path, rather than rebuilding the same six lessons by hand.
		var reuse = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost' }, 'Reuse a section' );
		reuse.addEventListener( 'click', function () {
			openSectionLibrary( 'course', courseId, function () {
				renderCourseEditor( courseId );
			} );
		} );
		form.appendChild( reuse );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var value = input.value.trim();
			if ( ! value ) {
				return;
			}
			btn.disabled = true;
			api( 'guide/v1/modules', { method: 'POST', body: { course_id: courseId, title: value } } ).then( function ( module ) {
				module.lessons = [];
				var empty = root.querySelector( '.guide-empty-state' );
				if ( empty ) { empty.remove(); }
				root.insertBefore( moduleCard( courseId, module, root.querySelectorAll( '.guide-module-card' ).length ), form );
				refreshCounts();
				input.value = '';
				btn.disabled = false;
				input.focus();
			} ).catch( function () { btn.disabled = false; } );
		} );
		root.appendChild( form );
		refreshCounts();
	}

	function refreshCounts() {
		var root = document.getElementById( 'guide-builder-root' );
		var statsEl = document.getElementById( 'guide-builder-stats' );
		if ( ! root ) {
			return;
		}
		var total = 0;
		root.querySelectorAll( '.guide-module-card' ).forEach( function ( card ) {
			var n = card.querySelectorAll( '.guide-lesson' ).length;
			total += n;
			card.querySelector( '.guide-module-card__count' ).textContent = n + ( n === 1 ? ' lesson' : ' lessons' );
		} );
		if ( statsEl ) {
			var modules = root.querySelectorAll( '.guide-module-card' ).length;
			statsEl.textContent = modules + ( modules === 1 ? ' module · ' : ' modules · ' ) + total + ( total === 1 ? ' lesson' : ' lessons' );
		}
	}

	function renumberModules() {
		document.querySelectorAll( '#guide-builder-root .guide-module-card' ).forEach( function ( card, i ) {
			card.querySelector( '.guide-module-card__index' ).textContent = String( i + 1 ).padStart( 2, '0' );
		} );
	}

	function refreshEmptyStates( list ) {
		var empty = list.parentNode.querySelector( '.guide-lessons__empty' );
		var has   = !! list.querySelector( '.guide-lesson' );
		if ( has && empty ) { empty.remove(); }
		if ( ! has && ! empty ) {
			list.parentNode.insertBefore( el( 'div', { class: 'guide-lessons__empty' }, 'Drop lessons here or add one below.' ), list.nextSibling );
		}
	}

	function armHandle( handle, node ) {
		handle.addEventListener( 'mousedown', function () { node.setAttribute( 'draggable', 'true' ); } );
		document.addEventListener( 'mouseup', function () { node.removeAttribute( 'draggable' ); } );
	}

	function clearIndicator() {
		if ( indicator.parentNode ) { indicator.parentNode.removeChild( indicator ); }
	}

	function persistLessons( courseId, list ) {
		var lessonIds = Array.prototype.map.call( list.querySelectorAll( '.guide-lesson' ), function ( row ) {
			return parseInt( row.dataset.lessonId, 10 );
		} );
		api( 'guide/v1/lessons/reorder', { method: 'POST', body: { course_id: courseId, module_id: parseInt( list.dataset.moduleId, 10 ), lesson_ids: lessonIds } } );
	}

	function persistModules( courseId ) {
		var ids = Array.prototype.map.call( document.querySelectorAll( '#guide-builder-root .guide-module-card' ), function ( card ) {
			return parseInt( card.dataset.moduleId, 10 );
		} );
		api( 'guide/v1/modules/reorder', { method: 'POST', body: { course_id: courseId, module_ids: ids } } );
	}

	function lessonRow( courseId, lesson ) {
		var row = el( 'li', { class: 'guide-lesson', 'data-lesson-id': lesson.id } );

		var handle = el( 'span', { class: 'guide-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		row.appendChild( handle );
		row.appendChild( el( 'span', { class: 'guide-lesson__dot', 'aria-hidden': 'true' } ) );

		var title = el( 'button', { type: 'button', class: 'guide-lesson__title', title: 'Write this lesson' }, lesson.title );
		title.addEventListener( 'click', function () {
			openLessonDrawer( courseId, lesson.id, row );
		} );
		row.appendChild( title );

		var actions = el( 'span', { class: 'guide-lesson__actions' } );
		var write = el( 'button', { type: 'button', class: 'guide-icon-btn', title: 'Write lesson' } );
		write.innerHTML = ICONS.pencil;
		write.addEventListener( 'click', function () { openLessonDrawer( courseId, lesson.id, row ); } );
		actions.appendChild( write );

		var del = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Delete lesson' } );
		del.innerHTML = ICONS.trash;
		del.addEventListener( 'click', function () {
			confirmInline( row.closest( '.guide-module-card' ), 'Move this lesson to trash?', function () {
				var list = row.closest( '.guide-lessons' );
				row.remove();
				refreshEmptyStates( list );
				refreshCounts();
				api( 'guide/v1/lessons/' + lesson.id, { method: 'DELETE' } );
			} );
		} );
		actions.appendChild( del );
		row.appendChild( actions );

		/* drag */
		armHandle( handle, row );
		row.addEventListener( 'dragstart', function ( e ) {
			dragged = { type: 'lesson', node: row, fromList: row.closest( '.guide-lessons' ) };
			row.classList.add( 'is-dragging' );
			e.dataTransfer.effectAllowed = 'move';
			try { e.dataTransfer.setData( 'text/plain', String( lesson.id ) ); } catch ( err ) {}
			e.stopPropagation();
		} );
		row.addEventListener( 'dragend', function () {
			row.classList.remove( 'is-dragging' );
			row.removeAttribute( 'draggable' );
			clearIndicator();
			dragged = null;
		} );

		return row;
	}

	function moduleCard( courseId, module, index ) {
		var card = el( 'section', { class: 'guide-module-card', 'data-module-id': module.id } );

		var head = el( 'header', { class: 'guide-module-card__head' } );
		var handle = el( 'span', { class: 'guide-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		head.appendChild( handle );
		head.appendChild( el( 'span', { class: 'guide-module-card__index' }, String( index + 1 ).padStart( 2, '0' ) ) );

		var title = el( 'h2', { class: 'guide-module-card__title' }, module.title );
		editable( title, function ( value ) {
			api( 'guide/v1/modules/' + module.id, { method: 'PATCH', body: { title: value } } );
		} );
		head.appendChild( title );
		head.appendChild( el( 'span', { class: 'guide-module-card__count' }, '' ) );

		var del = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Delete module' } );
		del.innerHTML = ICONS.trash;
		del.addEventListener( 'click', function () {
			confirmInline( card, 'Delete this module? Its lessons are kept but unassigned.', function () {
				card.remove();
				renumberModules();
				refreshCounts();
				api( 'guide/v1/modules/' + module.id, { method: 'DELETE' } );
			} );
		} );
		head.appendChild( del );
		card.appendChild( head );

		var list = el( 'ul', { class: 'guide-lessons', 'data-module-id': module.id } );
		module.lessons.forEach( function ( lesson ) {
			list.appendChild( lessonRow( courseId, lesson ) );
		} );

		list.addEventListener( 'dragover', function ( e ) {
			if ( ! dragged || dragged.type !== 'lesson' ) {
				return;
			}
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			var rows = Array.prototype.filter.call( list.querySelectorAll( '.guide-lesson' ), function ( r ) { return r !== dragged.node; } );
			var after = null;
			for ( var i = 0; i < rows.length; i++ ) {
				var rect = rows[ i ].getBoundingClientRect();
				if ( e.clientY < rect.top + rect.height / 2 ) { after = rows[ i ]; break; }
			}
			if ( after ) { list.insertBefore( indicator, after ); } else { list.appendChild( indicator ); }
		} );

		list.addEventListener( 'drop', function ( e ) {
			if ( ! dragged || dragged.type !== 'lesson' ) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			var fromList = dragged.fromList;
			if ( indicator.parentNode === list ) { list.insertBefore( dragged.node, indicator ); } else { list.appendChild( dragged.node ); }
			clearIndicator();
			persistLessons( courseId, list );
			if ( fromList && fromList !== list ) {
				persistLessons( courseId, fromList );
				refreshEmptyStates( fromList );
			}
			refreshEmptyStates( list );
			refreshCounts();
		} );

		card.appendChild( list );
		refreshEmptyStates( list );

		var form  = el( 'form', { class: 'guide-add-lesson' } );
		// Placed below with the rest of the row; declared here so the reuse
		// button can close over the module id.
		var reuseLesson = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost guide-btn--sm' }, 'Add existing' );
		reuseLesson.addEventListener( 'click', function () {
			openLessonLibrary( module.id, function () {
				renderCourseEditor( courseId );
			} );
		} );
		var input = el( 'input', { type: 'text', class: 'guide-input', placeholder: 'New lesson title…', required: 'required', 'aria-label': 'Add lesson' } );
		var btn   = el( 'button', { type: 'submit', class: 'guide-btn guide-btn--ghost guide-btn--sm' }, '+ Add lesson' );
		form.appendChild( input );
		form.appendChild( btn );
		form.appendChild( reuseLesson );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var value = input.value.trim();
			if ( ! value ) {
				return;
			}
			btn.disabled = true;
			api( 'guide/v1/lessons', { method: 'POST', body: { course_id: courseId, module_id: module.id, title: value } } ).then( function ( lesson ) {
				var row = lessonRow( courseId, lesson );
				row.classList.add( 'is-new' );
				list.appendChild( row );
				refreshEmptyStates( list );
				refreshCounts();
				input.value = '';
				btn.disabled = false;
				input.focus();
			} ).catch( function () { btn.disabled = false; } );
		} );
		card.appendChild( form );

		/* module drag */
		armHandle( handle, card );
		card.addEventListener( 'dragstart', function ( e ) {
			if ( dragged ) {
				return;
			}
			dragged = { type: 'module', node: card };
			card.classList.add( 'is-dragging' );
			e.dataTransfer.effectAllowed = 'move';
		} );
		card.addEventListener( 'dragend', function () {
			card.classList.remove( 'is-dragging' );
			card.removeAttribute( 'draggable' );
			dragged = null;
			renumberModules();
		} );
		card.addEventListener( 'dragover', function ( e ) {
			if ( ! dragged || dragged.type !== 'module' || dragged.node === card ) {
				return;
			}
			e.preventDefault();
			var rect  = card.getBoundingClientRect();
			var after = ( e.clientY - rect.top ) > rect.height / 2;
			card.parentNode.insertBefore( dragged.node, after ? card.nextSibling : card );
		} );
		card.addEventListener( 'drop', function ( e ) {
			if ( ! dragged || dragged.type !== 'module' ) {
				return;
			}
			e.preventDefault();
			persistModules( courseId );
			renumberModules();
		} );

		return card;
	}

	/* ================= in-house rich text editor ================= */

	function richEditor( host, initialHTML ) {
		host.innerHTML = '';
		host.className = 'guide-rte';

		var toolbar = el( 'div', { class: 'guide-rte__toolbar', role: 'toolbar' } );
		var area    = el( 'div', { class: 'guide-rte__area', contenteditable: 'true', spellcheck: 'true' } );
		area.innerHTML = initialHTML || '<p></p>';

		var savedRange = null;
		function saveSel() {
			var sel = window.getSelection();
			if ( sel.rangeCount && area.contains( sel.anchorNode ) ) {
				savedRange = sel.getRangeAt( 0 ).cloneRange();
			}
		}
		function restoreSel() {
			if ( savedRange ) {
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange( savedRange );
			}
		}
		function exec( cmd, value ) {
			area.focus();
			restoreSel();
			document.execCommand( cmd, false, value || null );
			saveSel();
		}

		var urlBar = el( 'div', { class: 'guide-rte__urlbar', hidden: 'hidden' } );
		var urlInput = el( 'input', { class: 'guide-input', type: 'url', placeholder: 'https://…' } );
		var urlOk = el( 'button', { type: 'button', class: 'guide-btn guide-btn--primary guide-btn--sm' }, 'Apply' );
		var urlCancel = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost guide-btn--sm' }, 'Cancel' );
		var urlMode = 'link';
		urlBar.appendChild( urlInput );
		urlBar.appendChild( urlOk );
		urlBar.appendChild( urlCancel );

		function openUrlBar( mode ) {
			urlMode = mode;
			saveSel();
			urlBar.hidden = false;
			urlInput.value = '';
			urlInput.placeholder = mode === 'img' ? 'Image URL…' : 'https://…';
			urlInput.focus();
		}
		urlOk.addEventListener( 'click', function () {
			var url = urlInput.value.trim();
			urlBar.hidden = true;
			if ( ! url ) {
				return;
			}
			if ( urlMode === 'img' ) {
				exec( 'insertImage', url );
			} else {
				exec( 'createLink', url );
			}
		} );
		urlCancel.addEventListener( 'click', function () { urlBar.hidden = true; } );
		urlInput.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) { e.preventDefault(); urlOk.click(); }
			if ( e.key === 'Escape' ) { urlBar.hidden = true; }
		} );

		var BTNS = [
			{ label: 'P', title: 'Paragraph', fn: function () { exec( 'formatBlock', '<p>' ); } },
			{ label: 'H2', title: 'Heading 2', fn: function () { exec( 'formatBlock', '<h2>' ); } },
			{ label: 'H3', title: 'Heading 3', fn: function () { exec( 'formatBlock', '<h3>' ); } },
			{ sep: true },
			{ label: '<b>B</b>', title: 'Bold', fn: function () { exec( 'bold' ); } },
			{ label: '<i>I</i>', title: 'Italic', fn: function () { exec( 'italic' ); } },
			{ sep: true },
			{ label: '&bull; List', title: 'Bullet list', fn: function () { exec( 'insertUnorderedList' ); } },
			{ label: '1. List', title: 'Numbered list', fn: function () { exec( 'insertOrderedList' ); } },
			{ label: '&ldquo;&nbsp;&rdquo;', title: 'Quote', fn: function () { exec( 'formatBlock', '<blockquote>' ); } },
			{ label: '&lt;/&gt;', title: 'Code block', fn: function () { exec( 'formatBlock', '<pre>' ); } },
			{ sep: true },
			{ label: 'Link', title: 'Insert link', fn: function () { openUrlBar( 'link' ); } },
			{ label: 'Image', title: 'Insert image', fn: function () {
				if ( window.wp && wp.media ) {
					saveSel();
					var frame = wp.media( { title: 'Insert image', multiple: false, library: { type: 'image' } } );
					frame.on( 'select', function () {
						var att = frame.state().get( 'selection' ).first().toJSON();
						exec( 'insertImage', ( att.sizes && att.sizes.large ? att.sizes.large.url : att.url ) );
					} );
					frame.open();
				} else {
					openUrlBar( 'img' );
				}
			} },
			{ label: '&mdash;', title: 'Divider', fn: function () { exec( 'insertHorizontalRule' ); } },
			{ sep: true },
			{ label: 'Clear', title: 'Clear formatting', fn: function () { exec( 'removeFormat' ); } },
		];

		BTNS.forEach( function ( b ) {
			if ( b.sep ) {
				toolbar.appendChild( el( 'span', { class: 'guide-rte__sep' } ) );
				return;
			}
			var btn = el( 'button', { type: 'button', class: 'guide-rte__btn', title: b.title } );
			btn.innerHTML = b.label;
			btn.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); } );
			btn.addEventListener( 'click', b.fn );
			toolbar.appendChild( btn );
		} );

		area.addEventListener( 'keyup', saveSel );
		area.addEventListener( 'mouseup', saveSel );
		area.addEventListener( 'blur', saveSel );

		host.appendChild( toolbar );
		host.appendChild( urlBar );
		host.appendChild( area );

		return {
			getHTML: function () {
				return area.innerHTML.replace( /<p><\/p>/g, '' ).trim();
			},
		};
	}

	/* ================= quiz builder ================= */

	function quizBuilder( host, quiz ) {
		host.innerHTML = '';
		host.className = 'guide-quiz-builder';

		var passRow = el( 'div', { class: 'guide-field guide-field--row' } );
		passRow.appendChild( el( 'label', {}, 'Pass mark (%)' ) );
		var passInput = el( 'input', { class: 'guide-input', type: 'number', min: '1', max: '100', style: 'width:90px' } );
		passInput.value = quiz.pass || 70;
		passRow.appendChild( passInput );
		host.appendChild( passRow );

		var list = el( 'div', { class: 'guide-quiz-builder__list' } );
		host.appendChild( list );

		function optionRow( card, text, isCorrect ) {
			var row = el( 'div', { class: 'guide-quiz-opt' } );
			var radio = el( 'input', { type: 'radio', title: 'Correct answer' } );
			radio.name = 'correct-' + Math.abs( ( card.dataset.qid || '0' ).split( '' ).reduce( function ( a, c ) { return a + c.charCodeAt( 0 ); }, 0 ) ) + '-' + card.dataset.qid;
			radio.checked = !! isCorrect;
			var input = el( 'input', { class: 'guide-input', type: 'text', placeholder: 'Answer option…' } );
			input.value = text || '';
			var rm = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Remove option' } );
			rm.innerHTML = ICONS.trash;
			rm.addEventListener( 'click', function () { row.remove(); } );
			row.appendChild( radio );
			row.appendChild( input );
			row.appendChild( rm );
			return row;
		}

		var qCounter = 0;

		function questionCard( q ) {
			qCounter++;
			var card = el( 'div', { class: 'guide-quiz-q', 'data-qid': String( qCounter ) } );

			var head = el( 'div', { class: 'guide-quiz-q__head' } );
			head.appendChild( el( 'span', { class: 'guide-quiz-q__num' }, 'Q' ) );
			var qInput = el( 'input', { class: 'guide-input', type: 'text', placeholder: 'Question…' } );
			qInput.value = q.q || '';
			qInput.setAttribute( 'data-role', 'question' );
			head.appendChild( qInput );
			var rm = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Remove question' } );
			rm.innerHTML = ICONS.trash;
			rm.addEventListener( 'click', function () { card.remove(); } );
			head.appendChild( rm );
			card.appendChild( head );

			var opts = el( 'div', { class: 'guide-quiz-q__opts' } );
			( q.options && q.options.length ? q.options : [ '', '' ] ).forEach( function ( opt, i ) {
				opts.appendChild( optionRow( card, opt, i === ( q.correct || 0 ) ) );
			} );
			card.appendChild( opts );

			var addOpt = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost guide-btn--sm' }, '+ Option' );
			addOpt.addEventListener( 'click', function () {
				if ( opts.children.length < 6 ) {
					opts.appendChild( optionRow( card, '', false ) );
				}
			} );

			var explain = el( 'input', { class: 'guide-input', type: 'text', placeholder: 'Explanation shown after answering (optional)…' } );
			explain.value = q.explain || '';
			explain.setAttribute( 'data-role', 'explain' );

			var foot = el( 'div', { class: 'guide-quiz-q__foot' } );
			foot.appendChild( explain );
			foot.appendChild( addOpt );
			card.appendChild( foot );

			return card;
		}

		( quiz.questions && quiz.questions.length ? quiz.questions : [] ).forEach( function ( q ) {
			list.appendChild( questionCard( q ) );
		} );

		var addQ = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost' }, '+ Add question' );
		addQ.addEventListener( 'click', function () {
			list.appendChild( questionCard( { options: [ '', '' ] } ) );
		} );
		host.appendChild( addQ );

		return {
			getData: function () {
				var questions = [];
				list.querySelectorAll( '.guide-quiz-q' ).forEach( function ( card ) {
					var options = [];
					var correct = 0;
					card.querySelectorAll( '.guide-quiz-opt' ).forEach( function ( row, i ) {
						var text = row.querySelector( 'input[type="text"]' ).value.trim();
						if ( ! text ) {
							return;
						}
						if ( row.querySelector( 'input[type="radio"]' ).checked ) {
							correct = options.length;
						}
						options.push( text );
					} );
					var qText = card.querySelector( '[data-role="question"]' ).value.trim();
					if ( qText && options.length >= 2 ) {
						questions.push( {
							q: qText,
							options: options,
							correct: correct,
							explain: card.querySelector( '[data-role="explain"]' ).value.trim(),
						} );
					}
				} );
				return {
					pass: parseInt( passInput.value, 10 ) || 70,
					questions: questions,
				};
			},
		};
	}

	/* ================= lesson drawer (writing) ================= */

	function parseTime( value ) {
		value = String( value || '' ).trim();
		if ( ! value ) {
			return 0;
		}
		if ( value.indexOf( ':' ) !== -1 ) {
			var parts = value.split( ':' ).map( Number );
			return parts.reduce( function ( acc, n ) { return acc * 60 + ( n || 0 ); }, 0 );
		}
		return parseInt( value, 10 ) || 0;
	}

	function fmtTime( seconds ) {
		seconds = parseInt( seconds, 10 ) || 0;
		if ( ! seconds ) {
			return '';
		}
		var m = Math.floor( seconds / 60 );
		var s = seconds % 60;
		return m + ':' + String( s ).padStart( 2, '0' );
	}


	/* =====================================================================
	 * Reuse library
	 *
	 * The whole point of the loose coupling: a section can live in several
	 * courses and paths at once, and a lesson can live in several sections.
	 * These pickers are how that gets used — without them the model is
	 * technically correct and practically inert.
	 *
	 * Placing something never copies it. Editing a lesson updates it
	 * everywhere it appears, which is the behaviour people expect and the
	 * reason duplicating content was worth eliminating.
	 * ================================================================== */

	function libraryDrawer( title, subtitle ) {
		closeDrawer();

		var scrim = el( 'div', { class: 'guide-drawer-scrim' } );
		scrim.addEventListener( 'click', closeDrawer );
		overlayHost().appendChild( scrim );

		var drawer = el( 'aside', { class: 'guide-drawer', role: 'dialog', 'aria-label': title } );
		drawer.innerHTML =
			'<header class="guide-drawer__head">' +
				'<div style="flex:1">' +
					'<span class="guide-drawer__eyebrow">' + esc( subtitle ) + '</span>' +
					'<h2 style="font-size:1.15rem;font-weight:800;margin-top:2px">' + esc( title ) + '</h2>' +
				'</div>' +
				'<button class="guide-icon-btn" type="button" title="Close" data-lib-close>&#10005;</button>' +
			'</header>' +
			'<div class="guide-drawer__body">' +
				'<div class="guide-field"><input class="guide-input" type="search" placeholder="Search…" data-lib-search></div>' +
				'<div data-lib-create></div>' +
				'<div data-lib-list><p class="guide-help">Loading…</p></div>' +
			'</div>';

		overlayHost().appendChild( drawer );
		drawer.querySelector( '[data-lib-close]' ).addEventListener( 'click', closeDrawer );
		document.addEventListener( 'keydown', escClose );

		return drawer;
	}

	/** One row in a picker: what it is, how reused it is, and a button. */
	function libraryRow( heading, meta, actionLabel, onAction ) {
		var row = el( 'div', { class: 'guide-lib-row' } );

		var text = el( 'div', { class: 'guide-lib-row__text' } );
		text.appendChild( el( 'strong', {}, heading ) );
		if ( meta ) {
			text.appendChild( el( 'span', { class: 'guide-help' }, meta ) );
		}
		row.appendChild( text );

		var btn = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost guide-btn--sm' }, actionLabel );
		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			onAction( btn );
		} );
		row.appendChild( btn );

		return row;
	}

	/**
	 * Pick a section to reuse, or create a new one, for a course or a path.
	 */
	function openSectionLibrary( containerType, containerId, onDone ) {
		var drawer = libraryDrawer( 'Add a section', containerType === 'path' ? 'Learning path' : 'Course' );
		var list   = drawer.querySelector( '[data-lib-list]' );
		var search = drawer.querySelector( '[data-lib-search]' );

		/* Create-new form, first: the common case is a brand-new section. */
		var createHost = drawer.querySelector( '[data-lib-create]' );
		var form  = el( 'form', { class: 'guide-inline-form', style: 'margin-bottom:14px' } );
		var input = el( 'input', { type: 'text', class: 'guide-input', placeholder: 'New section title…', 'aria-label': 'New section title' } );
		var save  = el( 'button', { type: 'submit', class: 'guide-btn guide-btn--primary guide-btn--sm' }, '+ New section' );
		form.appendChild( input );
		form.appendChild( save );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var value = input.value.trim();
			if ( ! value ) { return; }
			save.disabled = true;
			api( 'guide/v1/sections', {
				method: 'POST',
				body: { title: value, container_type: containerType, container_id: containerId },
			} ).then( function () {
				toast( 'Section created' );
				closeDrawer();
				onDone();
			} ).catch( function () { save.disabled = false; } );
		} );
		createHost.appendChild( form );

		function load() {
			var q = search.value.trim();
			list.innerHTML = '<p class="guide-help">Loading…</p>';

			req( 'guide/v1/sections' + ( q ? '?search=' + encodeURIComponent( q ) : '' ) ).then( function ( sections ) {
				list.innerHTML = '';

				if ( ! sections.length ) {
					list.appendChild( el( 'p', { class: 'guide-help' }, q ? 'No sections match that.' : 'No sections yet — create one above.' ) );
					return;
				}

				list.appendChild( el( 'p', { class: 'guide-help', style: 'margin-bottom:8px' }, 'Reuse an existing section. It is not copied — editing it changes it everywhere it appears.' ) );

				sections.forEach( function ( s ) {
					var meta = s.lesson_count + ( s.lesson_count === 1 ? ' lesson' : ' lessons' );
					if ( s.used_in > 0 ) {
						meta += ' · used in ' + s.used_in + ( s.used_in === 1 ? ' place' : ' places' );
					}

					list.appendChild( libraryRow( s.title, meta, 'Add', function ( btn ) {
						api( 'guide/v1/outline/' + containerType + '/' + containerId + '/place', {
							method: 'POST',
							body: { item_type: 'section', item_id: s.id },
						} ).then( function () {
							toast( 'Section added' );
							closeDrawer();
							onDone();
						} ).catch( function () { btn.disabled = false; } );
					} ) );
				} );
			} ).catch( function () {
				list.innerHTML = '<p class="guide-help">Could not load sections.</p>';
			} );
		}

		search.addEventListener( 'input', debounce( load, 250 ) );
		load();
	}

	/**
	 * Pick a lesson — from any course — to place into a section.
	 */
	function openLessonLibrary( sectionId, onDone ) {
		var drawer = libraryDrawer( 'Add an existing lesson', 'From any course' );
		var list   = drawer.querySelector( '[data-lib-list]' );
		var search = drawer.querySelector( '[data-lib-search]' );

		function load() {
			var q = search.value.trim();
			list.innerHTML = '<p class="guide-help">Loading…</p>';

			req( 'guide/v1/lesson-library' + ( q ? '?search=' + encodeURIComponent( q ) : '' ) ).then( function ( lessons ) {
				list.innerHTML = '';

				if ( ! lessons.length ) {
					list.appendChild( el( 'p', { class: 'guide-help' }, 'No lessons match that.' ) );
					return;
				}

				lessons.forEach( function ( l ) {
					var bits = [];
					if ( l.course ) { bits.push( 'from ' + l.course ); }
					if ( l.status !== 'publish' ) { bits.push( l.status ); }
					if ( l.used_in > 1 ) { bits.push( 'used in ' + l.used_in + ' sections' ); }

					list.appendChild( libraryRow( l.title, bits.join( ' · ' ), 'Add', function ( btn ) {
						api( 'guide/v1/outline/section/' + sectionId + '/place', {
							method: 'POST',
							body: { item_type: 'lesson', item_id: l.id },
						} ).then( function () {
							toast( 'Lesson added' );
							closeDrawer();
							onDone();
						} ).catch( function () { btn.disabled = false; } );
					} ) );
				} );
			} ).catch( function () {
				list.innerHTML = '<p class="guide-help">Could not load lessons.</p>';
			} );
		}

		search.addEventListener( 'input', debounce( load, 250 ) );
		load();
	}

	function debounce( fn, wait ) {
		var timer = null;
		return function () {
			window.clearTimeout( timer );
			timer = window.setTimeout( fn, wait );
		};
	}

	function closeDrawer() {
		document.querySelectorAll( '.guide-drawer, .guide-drawer-scrim' ).forEach( function ( n ) { n.remove(); } );
		document.querySelectorAll( '.guide-lesson.is-editing' ).forEach( function ( n ) { n.classList.remove( 'is-editing' ); } );
		document.removeEventListener( 'keydown', escClose );
	}

	function escClose( e ) {
		if ( e.key === 'Escape' && ! document.querySelector( '.guide-rte__urlbar:not([hidden])' ) ) {
			closeDrawer();
		}
	}

	/**
	 * Repeatable single-line list (used for "what you'll learn" and
	 * "requirements"). Returns a reader for the current values.
	 */
	function lineListEditor( host, values, placeholder ) {
		host.innerHTML = '';
		host.className = 'guide-linelist';

		function addRow( value ) {
			var row = el( 'div', { class: 'guide-linelist__row' } );
			var input = el( 'input', { class: 'guide-input', type: 'text', placeholder: placeholder } );
			input.value = value || '';

			var remove = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Remove' } );
			remove.innerHTML = ICONS.trash;
			remove.addEventListener( 'click', function () { row.remove(); } );

			// Enter adds the next row, so a list can be typed without reaching
			// for the mouse between items.
			input.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' ) {
					e.preventDefault();
					var next = addRow( '' );
					next.querySelector( 'input' ).focus();
				}
			} );

			row.appendChild( input );
			row.appendChild( remove );
			host.insertBefore( row, addBtn );
			return row;
		}

		var addBtn = el( 'button', { type: 'button', class: 'guide-btn guide-btn--ghost guide-btn--sm' } );
		addBtn.innerHTML = ICONS.plus + 'Add line';
		addBtn.addEventListener( 'click', function () {
			addRow( '' ).querySelector( 'input' ).focus();
		} );
		host.appendChild( addBtn );

		( values && values.length ? values : [ '' ] ).forEach( addRow );

		return {
			getValues: function () {
				return Array.prototype.map.call( host.querySelectorAll( 'input' ), function ( i ) {
					return i.value.trim();
				} ).filter( Boolean );
			},
		};
	}

	/**
	 * Everything about a course that isn't its lesson structure: the
	 * description learners actually read, the card image, level, outcomes,
	 * requirements and access tier. This is what the classic editor used to be
	 * for.
	 */
	function openCourseDrawer( courseId, course ) {
		closeDrawer();

		var scrim = el( 'div', { class: 'guide-drawer-scrim' } );
		scrim.addEventListener( 'click', closeDrawer );
		overlayHost().appendChild( scrim );

		var drawer = el( 'aside', { class: 'guide-drawer guide-drawer--wide', role: 'dialog', 'aria-label': 'Course details' } );
		var meta   = course.meta || {};

		drawer.innerHTML =
			'<header class="guide-drawer__head">' +
				'<div style="flex:1"><span class="guide-drawer__eyebrow">Course details</span>' +
				'<input class="guide-drawer__title" id="guide-cd-title" type="text" placeholder="Course title"></div>' +
				'<button class="guide-icon-btn" id="guide-cd-close" title="Close" type="button">✕</button>' +
			'</header>' +

			'<div class="guide-drawer__body">' +
				'<div class="guide-field"><label for="guide-cd-excerpt">Short description</label>' +
					'<textarea class="guide-input" id="guide-cd-excerpt" rows="3" placeholder="One or two sentences shown on cards and in search results…"></textarea></div>' +

				'<div class="guide-field"><label>Course image</label>' +
					'<div class="guide-media-pick" id="guide-cd-image">' +
						'<div class="guide-media-pick__preview" id="guide-cd-image-preview"></div>' +
						'<div class="guide-media-pick__actions">' +
							'<button class="guide-btn guide-btn--ghost guide-btn--sm" id="guide-cd-image-set" type="button">Choose image</button>' +
							'<button class="guide-btn guide-btn--danger guide-btn--sm" id="guide-cd-image-clear" type="button">Remove</button>' +
						'</div>' +
					'</div>' +
					'<span class="guide-help">Leave empty to use the generated placeholder art.</span></div>' +

				'<div class="guide-field"><label for="guide-cd-level">Level</label>' +
					'<select class="guide-input" id="guide-cd-level" style="max-width:220px">' +
						'<option value="">Not specified</option>' +
						'<option value="beginner">Beginner</option>' +
						'<option value="intermediate">Intermediate</option>' +
						'<option value="advanced">Advanced</option>' +
					'</select></div>' +

				'<div class="guide-field"><label for="guide-cd-header">Header style</label>' +
					'<select class="guide-input" id="guide-cd-header" style="max-width:340px">' +
						'<option value="classic">Classic — dark slab, enrol card right</option>' +
						'<option value="split">Split — artwork beside the title</option>' +
						'<option value="centred">Centred — title centred, card beneath</option>' +
						'<option value="minimal">Minimal — light and compact</option>' +
						'<option value="spotlight">Spotlight — gradient with the code behind the title</option>' +
					'</select>' +
					'<span class="guide-help">Every style shows the same information — only the layout changes. ' +
						'Split and Spotlight use the course image, so set one above first.</span></div>' +

				'<div class="guide-field"><label>Full description</label>' +
					'<div id="guide-cd-editor"></div>' +
					'<span class="guide-help">Shown on the course page under the “About” tab.</span></div>' +

				'<div class="guide-field"><label>What you’ll learn</label><div id="guide-cd-outcomes"></div></div>' +

				'<div class="guide-field"><label>Requirements</label><div id="guide-cd-reqs"></div></div>' +

				'<div class="guide-field"><label>Access</label>' +
					'<div class="guide-segmented" id="guide-cd-tier">' +
						'<button type="button" data-tier="free">Free</button>' +
						'<button type="button" data-tier="premium">Members</button>' +
					'</div>' +
					'<span class="guide-help">There is no per-course price. “Members” means the course is part of the platform subscription; subscribers get every one of them.</span></div>' +
			'</div>' +

			'<footer class="guide-drawer__foot">' +
				'<span class="guide-help" id="guide-cd-state"></span>' +
				'<div style="margin-left:auto;display:flex;gap:8px">' +
					'<button class="guide-btn guide-btn--ghost" id="guide-cd-cancel" type="button">Cancel</button>' +
					'<button class="guide-btn guide-btn--primary" id="guide-cd-save" type="button">Save details</button>' +
				'</div>' +
			'</footer>';

		overlayHost().appendChild( drawer );
		document.addEventListener( 'keydown', escClose );

		document.getElementById( 'guide-cd-title' ).value = ( course.title && course.title.raw ) || '';
		document.getElementById( 'guide-cd-excerpt' ).value = ( course.excerpt && course.excerpt.raw ) || '';
		document.getElementById( 'guide-cd-level' ).value = meta.jsl_course_level || '';
		document.getElementById( 'guide-cd-header' ).value = meta.jsl_course_header || 'classic';

		var editor = richEditor(
			document.getElementById( 'guide-cd-editor' ),
			( course.content && course.content.raw ) || ''
		);

		var outcomes = lineListEditor( document.getElementById( 'guide-cd-outcomes' ), meta.jsl_course_outcomes || [], 'e.g. Write a résumé that survives an ATS' );
		var reqs = lineListEditor( document.getElementById( 'guide-cd-reqs' ), meta.jsl_course_requirements || [], 'e.g. No prior experience needed' );

		/* ---- Featured image ---- */
		var imageId = course.featured_media || 0;
		var preview = document.getElementById( 'guide-cd-image-preview' );

		function paintImage() {
			preview.innerHTML = '';
			if ( ! imageId ) {
				preview.appendChild( el( 'span', { class: 'guide-help' }, 'No image set' ) );
				return;
			}
			req( 'wp/v2/media/' + imageId )
				.then( function ( m ) {
					var img = el( 'img', { src: m.source_url, alt: '' } );
					preview.innerHTML = '';
					preview.appendChild( img );
				} )
				.catch( function () {
					preview.appendChild( el( 'span', { class: 'guide-help' }, 'Image #' + imageId ) );
				} );
		}
		paintImage();

		document.getElementById( 'guide-cd-image-set' ).addEventListener( 'click', function () {
			if ( ! window.wp || ! wp.media ) {
				toast( 'Media library unavailable', true );
				return;
			}
			var frame = wp.media( { title: 'Choose course image', multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function () {
				imageId = frame.state().get( 'selection' ).first().toJSON().id;
				paintImage();
			} );
			frame.open();
		} );

		document.getElementById( 'guide-cd-image-clear' ).addEventListener( 'click', function () {
			imageId = 0;
			paintImage();
		} );

		/* ---- Access tier ---- */
		// 'paid' is the legacy value from the per-course pricing model; it
		// means the same thing as 'premium' now.
		var tier = ( meta.jsl_pricing_type === 'premium' || meta.jsl_pricing_type === 'paid' ) ? 'premium' : 'free';

		function paintTier() {
			document.querySelectorAll( '#guide-cd-tier button' ).forEach( function ( b ) {
				var on = b.dataset.tier === tier;
				b.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				b.classList.toggle( 'is-active', on );
			} );
		}

		document.querySelectorAll( '#guide-cd-tier button' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				tier = b.dataset.tier;
				paintTier();
			} );
		} );
		paintTier();

		/* ---- Save ---- */
		document.getElementById( 'guide-cd-close' ).addEventListener( 'click', closeDrawer );
		document.getElementById( 'guide-cd-cancel' ).addEventListener( 'click', closeDrawer );

		document.getElementById( 'guide-cd-save' ).addEventListener( 'click', function () {
			var btn = document.getElementById( 'guide-cd-save' );
			btn.disabled = true;
			document.getElementById( 'guide-cd-state' ).textContent = 'Saving…';

			var body = {
				title: document.getElementById( 'guide-cd-title' ).value.trim(),
				excerpt: document.getElementById( 'guide-cd-excerpt' ).value,
				content: editor.getHTML(),
				featured_media: imageId,
				meta: {
					jsl_course_level: document.getElementById( 'guide-cd-level' ).value,
					jsl_course_header: document.getElementById( 'guide-cd-header' ).value,
					jsl_course_outcomes: outcomes.getValues(),
					jsl_course_requirements: reqs.getValues(),
					jsl_pricing_type: tier,
				},
			};

			req( 'wp/v2/courses/' + courseId, { method: 'POST', body: body } )
				.then( function () {
					toast( 'Course details saved' );
					closeDrawer();
					renderCourseEditor( courseId );
				} )
				.catch( function ( err ) {
					btn.disabled = false;
					document.getElementById( 'guide-cd-state' ).textContent = '';
					fail( err );
				} );
		} );

		document.getElementById( 'guide-cd-title' ).focus();
	}

	function openLessonDrawer( courseId, lessonId, row ) {
		closeDrawer();
		row.classList.add( 'is-editing' );

		var scrim = el( 'div', { class: 'guide-drawer-scrim' } );
		scrim.addEventListener( 'click', closeDrawer );
		overlayHost().appendChild( scrim );

		var drawer = el( 'aside', { class: 'guide-drawer', role: 'dialog', 'aria-label': 'Lesson editor' } );
		drawer.innerHTML = '<div class="guide-skeleton-page"><span class="guide-spinner"></span>Loading lesson…</div>';
		overlayHost().appendChild( drawer );
		document.addEventListener( 'keydown', escClose );

		Promise.all( [
			req( 'wp/v2/lessons/' + lessonId + '?context=edit' ),
			req( 'guide/v1/lessons/' + lessonId + '/quiz-admin' ),
		] ).then( function ( results ) {
			var lesson = results[ 0 ];
			var quiz   = results[ 1 ];
			var meta   = lesson.meta || {};
			var type   = meta.jsl_lesson_type || ( meta.jsl_video_url ? 'video' : 'article' );

			drawer.innerHTML =
				'<header class="guide-drawer__head">' +
					'<div style="flex:1;min-width:0"><span class="guide-eyebrow">Lesson</span>' +
					'<input class="guide-drawer__title" id="guide-drawer-title" type="text" value="" placeholder="Lesson title"></div>' +
					'<select class="guide-input" id="guide-drawer-type" style="width:auto" title="Lesson type">' +
						'<option value="article">Article</option><option value="video">Video</option><option value="quiz">Quiz</option>' +
					'</select>' +
					'<a class="guide-btn guide-btn--ghost guide-btn--sm" href="' + esc( lesson.link ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'<button class="guide-icon-btn" id="guide-drawer-close" title="Close (Esc)" style="width:32px;height:32px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>' +
				'</header>' +
				'<div class="guide-drawer__body">' +
					'<div class="guide-drawer__meta-grid" data-section="video">' +
						'<div class="guide-field"><label for="guide-drawer-video">Video URL</label><input class="guide-input" id="guide-drawer-video" type="url" placeholder="YouTube, Vimeo, or .mp4…"></div>' +
						'<div class="guide-field"><label for="guide-drawer-vstart">Start at</label><input class="guide-input" id="guide-drawer-vstart" type="text" placeholder="0:00"><span class="guide-help">mm:ss — plays from here</span></div>' +
						'<div class="guide-field"><label for="guide-drawer-vend">End at</label><input class="guide-input" id="guide-drawer-vend" type="text" placeholder="full"><span class="guide-help">mm:ss — stops here</span></div>' +
					'</div>' +
					'<div class="guide-drawer__meta-grid">' +
						'<div class="guide-field"><label for="guide-drawer-duration">Duration (min)</label><input class="guide-input" id="guide-drawer-duration" type="number" min="0"></div>' +
						'<div class="guide-field"><label>&nbsp;</label><label class="guide-field--row" style="margin:0"><input type="checkbox" id="guide-drawer-preview"> Free preview</label></div>' +
						'<div></div>' +
					'</div>' +
					'<div class="guide-field" data-section="quiz"><label>Quiz</label><div id="guide-drawer-quiz"></div></div>' +
					'<div class="guide-field"><label>Lesson content</label><div id="guide-drawer-rte"></div></div>' +
				'</div>' +
				'<footer class="guide-drawer__foot">' +
					'<button class="guide-btn guide-btn--danger" id="guide-drawer-delete">' + ICONS.trash + 'Delete lesson</button>' +
					'<div style="display:flex;gap:8px;align-items:center">' +
						'<button class="guide-btn guide-btn--ghost" id="guide-drawer-cancel">Close</button>' +
						'<button class="guide-btn guide-btn--primary" id="guide-drawer-save">Save lesson</button>' +
					'</div>' +
				'</footer>';

			document.getElementById( 'guide-drawer-title' ).value = lesson.title.raw || '';
			document.getElementById( 'guide-drawer-type' ).value = type;
			document.getElementById( 'guide-drawer-video' ).value = meta.jsl_video_url || '';
			document.getElementById( 'guide-drawer-vstart' ).value = fmtTime( meta.jsl_video_start );
			document.getElementById( 'guide-drawer-vend' ).value = fmtTime( meta.jsl_video_end );
			document.getElementById( 'guide-drawer-duration' ).value = meta.jsl_duration_minutes || '';
			document.getElementById( 'guide-drawer-preview' ).checked = !! meta.jsl_is_preview;

			var editor = richEditor( document.getElementById( 'guide-drawer-rte' ), lesson.content.raw || '' );
			var qb     = quizBuilder( document.getElementById( 'guide-drawer-quiz' ), quiz );

			function syncSections() {
				var t = document.getElementById( 'guide-drawer-type' ).value;
				drawer.querySelector( '[data-section="video"]' ).style.display = t === 'video' ? '' : 'none';
				drawer.querySelector( '[data-section="quiz"]' ).style.display = t === 'quiz' ? '' : 'none';
			}
			document.getElementById( 'guide-drawer-type' ).addEventListener( 'change', syncSections );
			syncSections();

			document.getElementById( 'guide-drawer-close' ).addEventListener( 'click', closeDrawer );
			document.getElementById( 'guide-drawer-cancel' ).addEventListener( 'click', closeDrawer );

			document.getElementById( 'guide-drawer-delete' ).addEventListener( 'click', function () {
				confirmInline( drawer.querySelector( '.guide-drawer__foot' ), 'Move this lesson to trash?', function () {
					api( 'guide/v1/lessons/' + lessonId, { method: 'DELETE' } ).then( function () {
						var list = row.closest( '.guide-lessons' );
						row.remove();
						if ( list ) { refreshEmptyStates( list ); }
						refreshCounts();
						closeDrawer();
						toast( 'Lesson trashed' );
					} );
				} );
			} );

			document.getElementById( 'guide-drawer-save' ).addEventListener( 'click', function () {
				var btn = document.getElementById( 'guide-drawer-save' );
				btn.disabled = true;
				var lessonType = document.getElementById( 'guide-drawer-type' ).value;
				var title = document.getElementById( 'guide-drawer-title' ).value.trim() || '(untitled)';

				var saves = [
					api( 'wp/v2/lessons/' + lessonId, {
						method: 'POST',
						body: {
							title:   title,
							content: editor.getHTML(),
							meta: {
								jsl_lesson_type:      lessonType,
								jsl_video_url:        document.getElementById( 'guide-drawer-video' ).value.trim(),
								jsl_video_start:      parseTime( document.getElementById( 'guide-drawer-vstart' ).value ),
								jsl_video_end:        parseTime( document.getElementById( 'guide-drawer-vend' ).value ),
								jsl_duration_minutes: parseInt( document.getElementById( 'guide-drawer-duration' ).value, 10 ) || 0,
								jsl_is_preview:       document.getElementById( 'guide-drawer-preview' ).checked,
							},
						},
					} ),
				];

				if ( lessonType === 'quiz' ) {
					saves.push( api( 'guide/v1/lessons/' + lessonId + '/quiz-admin', { method: 'POST', body: qb.getData() } ) );
				}

				Promise.all( saves ).then( function () {
					row.querySelector( '.guide-lesson__title' ).textContent = title;
					toast( 'Lesson saved' );
					btn.disabled = false;
				} ).catch( function () { btn.disabled = false; } );
			} );
		} ).catch( function ( err ) {
			closeDrawer();
			fail( err );
		} );
	}

	/* ================= learning paths ================= */

	/**
	 * A path is an ordered arrangement of things a learner works through:
	 * whole courses, or single standalone pieces (article / video / quiz)
	 * written right here. All of it is drag-ordered — there is no editor
	 * to leave for.
	 */
	function renderPaths() {
		setNav( 'paths' );
		loading();

		req( 'guide/v1/paths' ).then( function ( data ) {
			var cards = data.paths.map( function ( p ) {
				return '<div class="guide-course-card" data-href="#/paths/' + p.id + '" role="link" tabindex="0">' +
					'<div class="guide-course-card__top"><span class="guide-badge guide-badge--' + esc( p.status ) + '">' + esc( p.status ) + '</span></div>' +
					'<h3>' + esc( p.title || '(untitled)' ) + '</h3>' +
					( p.excerpt ? '<p class="guide-sub">' + esc( p.excerpt ) + '</p>' : '' ) +
					'<div class="guide-course-card__meta"><span>' + ICONS.path + p.steps + ( p.steps === 1 ? ' step' : ' steps' ) + '</span></div>' +
				'</div>';
			} ).join( '' );

			view.innerHTML =
				'<header class="guide-page-head"><div><h1>Learning paths</h1><p class="guide-sub">Arrange courses, articles and videos into a route from A to B.</p></div>' +
				'<div class="guide-page-head__actions"><form class="guide-inline-form" id="guide-new-path"><input class="guide-input" type="text" placeholder="New path title…" required style="width:240px"><button class="guide-btn guide-btn--primary" type="submit">' + ICONS.plus + 'Create path</button></form></div></header>' +
				( cards
					? '<div class="guide-course-grid">' + cards + '</div>'
					: '<div class="guide-empty-state"><h3>No learning paths yet</h3><p>A path is the spine of the site — create one above, then drop courses and lessons into it.</p></div>' );

			bindRowLinks();

			var form  = document.getElementById( 'guide-new-path' );
			var input = form.querySelector( 'input' );
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var title = input.value.trim();
				if ( ! title ) {
					return;
				}
				form.querySelector( 'button' ).disabled = true;
				req( 'guide/v1/paths', { method: 'POST', body: { title: title } } )
					.then( function ( path ) {
						toast( 'Path created' );
						window.location.hash = '/paths/' + path.id;
					} )
					.catch( function ( err ) {
						form.querySelector( 'button' ).disabled = false;
						fail( err );
					} );
			} );
		} ).catch( fail );
	}

	function stepIcon( step ) {
		if ( step.type === 'course' ) {
			return ICONS.layers;
		}
		if ( step.lesson_type === 'video' ) {
			return ICONS.video;
		}
		if ( step.lesson_type === 'quiz' ) {
			return ICONS.quiz;
		}
		return ICONS.doc;
	}

	function stepKindLabel( step ) {
		if ( step.type === 'course' ) {
			return 'Course';
		}
		return step.lesson_type === 'video' ? 'Video' : step.lesson_type === 'quiz' ? 'Quiz' : 'Article';
	}

	function persistSteps( pathId ) {
		// Send the whole new order — a path can hold courses, sections and
		// lessons, whose ids come from different sequences, so a list of bare
		// numbers would be ambiguous.
		var items = Array.prototype.map.call( document.querySelectorAll( '#guide-path-steps .guide-step' ), function ( row ) {
			return { item_type: row.dataset.itemType, item_id: parseInt( row.dataset.itemId, 10 ) };
		} );
		api( 'guide/v1/paths/' + pathId + '/steps/reorder', { method: 'POST', body: { items: items } } );
	}

	function renumberSteps() {
		Array.prototype.forEach.call( document.querySelectorAll( '#guide-path-steps .guide-step' ), function ( row, i ) {
			var num = row.querySelector( '.guide-step__num' );
			if ( num ) {
				num.textContent = String( i + 1 ).padStart( 2, '0' );
			}
		} );
	}

	function stepRow( pathId, step ) {
		var row = el( 'li', { class: 'guide-step', 'data-item-type': step.type, 'data-item-id': step.id } );

		var handle = el( 'span', { class: 'guide-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		row.appendChild( handle );

		row.appendChild( el( 'span', { class: 'guide-step__num' }, '00' ) );

		var kind = el( 'span', { class: 'guide-step__kind' } );
		kind.innerHTML = stepIcon( step ) + '<span>' + esc( stepKindLabel( step ) ) + '</span>';
		row.appendChild( kind );

		var title = el( 'span', { class: 'guide-step__title' }, step.title || '(untitled)' );
		row.appendChild( title );

		// Courses are renamed in the course editor; a standalone step is
		// renamed right here, since this is the only place it exists.
		if ( step.type === 'lesson' ) {
			editable( title, function ( value ) {
				api( 'wp/v2/lessons/' + step.id, { method: 'POST', body: { title: value } } );
			} );
		}

		if ( step.status !== 'publish' ) {
			row.appendChild( el( 'span', { class: 'guide-badge guide-badge--draft' }, 'draft' ) );
		}

		var actions = el( 'span', { class: 'guide-step__actions' } );

		var open = el( 'a', {
			class: 'guide-icon-btn',
			title: step.type === 'course' ? 'Open course builder' : 'Write this step',
			href: step.type === 'course' ? '#/courses/' + step.id : step.permalink,
		} );
		if ( step.type !== 'course' ) {
			open.setAttribute( 'target', '_blank' );
			open.setAttribute( 'rel', 'noopener' );
		}
		open.innerHTML = step.type === 'course' ? ICONS.external : ICONS.pencil;
		actions.appendChild( open );

		var del = el( 'button', { type: 'button', class: 'guide-icon-btn guide-icon-btn--danger', title: 'Remove from this path' } );
		del.innerHTML = ICONS.trash;
		del.addEventListener( 'click', function () {
			confirmInline(
				row.parentNode.parentNode,
				step.type === 'course'
					? 'Remove this course from the path? The course itself is kept.'
					: 'Remove this step from the path?',
				function () {
					row.remove();
					renumberSteps();
					api( 'guide/v1/paths/' + pathId + '/steps/remove', {
						method: 'POST',
						body: { item_type: step.type, item_id: step.id },
					} );
				}
			);
		} );
		actions.appendChild( del );
		row.appendChild( actions );

		/* drag */
		armHandle( handle, row );
		row.addEventListener( 'dragstart', function ( e ) {
			dragged = { type: 'step', node: row };
			row.classList.add( 'is-dragging' );
			e.dataTransfer.effectAllowed = 'move';
			try { e.dataTransfer.setData( 'text/plain', step.type + ':' + step.id ); } catch ( err ) {}
		} );
		row.addEventListener( 'dragend', function () {
			row.classList.remove( 'is-dragging' );
			row.removeAttribute( 'draggable' );
			clearIndicator();
			dragged = null;
			renumberSteps();
			persistSteps( pathId );
		} );

		return row;
	}

	function renderPathEditor( pathId ) {
		setNav( 'paths' );
		loading();

		req( 'guide/v1/paths/' + pathId ).then( function ( path ) {
			view.innerHTML =
				'<header class="guide-page-head">' +
					'<div>' +
						'<button class="guide-btn guide-btn--ghost guide-btn--sm" id="guide-path-back">' + ICONS.arrowL + 'All paths</button>' +
						'<h1 id="guide-path-title" style="margin-top:10px">' + esc( path.title ) + '</h1>' +
						'<p class="guide-sub" id="guide-path-excerpt">' + esc( path.excerpt || 'Add a one-line description…' ) + '</p>' +
					'</div>' +
					'<div class="guide-page-head__actions">' +
						'<span id="guide-save-state" class="guide-save-state"></span>' +
						'<label class="guide-switch"><input type="checkbox" id="guide-path-status"' + ( path.status === 'publish' ? ' checked' : '' ) + '><span>Published</span></label>' +
						'<a class="guide-btn guide-btn--ghost guide-btn--sm" href="' + esc( path.permalink ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'</div>' +
				'</header>' +

				'<div class="guide-card">' +
					'<div class="guide-card__head"><h2>Path steps</h2><span class="guide-sub" id="guide-step-count"></span></div>' +
					'<div class="guide-card__body">' +
						'<ol class="guide-steps" id="guide-path-steps"></ol>' +
						'<div class="guide-step-add" id="guide-step-add">' +
							'<form class="guide-inline-form" id="guide-add-course">' +
								'<select class="guide-input" id="guide-course-picker" style="min-width:220px"><option value="">Add an existing course…</option></select>' +
								'<button class="guide-btn guide-btn--ghost guide-btn--sm" type="submit">' + ICONS.plus + 'Add course</button>' +
							'</form>' +
							'<form class="guide-inline-form" id="guide-add-inline">' +
								'<select class="guide-input" id="guide-inline-type"><option value="article">Article</option><option value="video">Video</option><option value="quiz">Quiz</option></select>' +
								'<input class="guide-input" id="guide-inline-title" type="text" placeholder="Step title…" required style="width:220px">' +
								'<button class="guide-btn guide-btn--primary guide-btn--sm" type="submit">' + ICONS.plus + 'Add step</button>' +
							'</form>' +
							'<div class="guide-inline-form">' +
								'<button class="guide-btn guide-btn--ghost guide-btn--sm" type="button" id="guide-add-section">' + ICONS.layers + 'Reuse or create a section</button>' +
								'<span class="guide-help">Bring a section in from any course, or build one for this path alone.</span>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>';

			var list = document.getElementById( 'guide-path-steps' );

			function paint( steps ) {
				list.innerHTML = '';
				steps.forEach( function ( step ) {
					list.appendChild( stepRow( pathId, step ) );
				} );
				renumberSteps();
				document.getElementById( 'guide-step-count' ).textContent =
					steps.length + ( steps.length === 1 ? ' step' : ' steps' );
				if ( ! steps.length ) {
					list.innerHTML = '<li class="guide-empty-row">Nothing in this path yet — add a course or write a step below.</li>';
				}
			}

			paint( path.steps );

			document.getElementById( 'guide-path-back' ).addEventListener( 'click', function () {
				window.location.hash = '/paths';
			} );

			editable( document.getElementById( 'guide-path-title' ), function ( value ) {
				api( 'guide/v1/paths/' + pathId, { method: 'PATCH', body: { title: value } } );
			} );

			editable( document.getElementById( 'guide-path-excerpt' ), function ( value ) {
				api( 'guide/v1/paths/' + pathId, { method: 'PATCH', body: { excerpt: value } } );
			} );

			document.getElementById( 'guide-path-status' ).addEventListener( 'change', function ( e ) {
				api( 'guide/v1/paths/' + pathId, {
					method: 'PATCH',
					body: { status: e.target.checked ? 'publish' : 'draft' },
				} );
			} );

			/* Drop target for reordering */
			list.addEventListener( 'dragover', function ( e ) {
				if ( ! dragged || dragged.type !== 'step' ) {
					return;
				}
				e.preventDefault();
				var rows = Array.prototype.filter.call( list.querySelectorAll( '.guide-step' ), function ( r ) {
					return r !== dragged.node;
				} );
				var after = rows.find( function ( r ) {
					var box = r.getBoundingClientRect();
					return e.clientY < box.top + box.height / 2;
				} );
				if ( after ) {
					list.insertBefore( indicator, after );
				} else {
					list.appendChild( indicator );
				}
			} );

			list.addEventListener( 'drop', function ( e ) {
				if ( ! dragged || dragged.type !== 'step' ) {
					return;
				}
				e.preventDefault();
				if ( indicator.parentNode ) {
					list.insertBefore( dragged.node, indicator );
				}
				clearIndicator();
				renumberSteps();
				persistSteps( pathId );
			} );

			/* Course picker */
			var picker = document.getElementById( 'guide-course-picker' );
			req( 'guide/v1/paths/' + pathId + '/available-courses' ).then( function ( data ) {
				data.courses.forEach( function ( c ) {
					var opt = el( 'option', { value: String( c.id ) }, c.title + ( c.status !== 'publish' ? ' (draft)' : '' ) );
					picker.appendChild( opt );
				} );
			} ).catch( function () {} );

			// A path can curate its own sections — reuse one from a course, or
			// make a new one and fill it with lessons borrowed from anywhere.
			var addSection = document.getElementById( 'guide-add-section' );
			if ( addSection ) {
				addSection.addEventListener( 'click', function () {
					openSectionLibrary( 'path', pathId, function () {
						renderPathEditor( pathId );
					} );
				} );
			}

			document.getElementById( 'guide-add-course' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var courseId = parseInt( picker.value, 10 );
				if ( ! courseId ) {
					return;
				}
				api( 'guide/v1/paths/' + pathId + '/steps', {
					method: 'POST',
					body: { step_type: 'course', object_id: courseId },
				} ).then( function () {
					toast( 'Course added to path' );
					renderPathEditor( pathId );
				} ).catch( function () {} );
			} );

			document.getElementById( 'guide-add-inline' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var titleInput = document.getElementById( 'guide-inline-title' );
				var title = titleInput.value.trim();
				if ( ! title ) {
					return;
				}
				api( 'guide/v1/paths/' + pathId + '/steps', {
					method: 'POST',
					body: { step_type: document.getElementById( 'guide-inline-type' ).value, title: title },
				} ).then( function () {
					titleInput.value = '';
					toast( 'Step added' );
					renderPathEditor( pathId );
				} ).catch( function () {} );
			} );
		} ).catch( fail );
	}

	/* ================= success stories ================= */

	/**
	 * Moderation queue. Stories arrive pending; nothing a learner writes is
	 * public until it is approved here, so pending sorts to the top.
	 */
	function renderStories() {
		setNav( 'stories' );
		loading();

		req( 'guide/v1/stories' ).then( function ( data ) {
			var pending = data.stories.filter( function ( s ) { return s.status === 'pending'; } ).length;

			function card( s ) {
				var actions = s.status === 'publish'
					? '<button class="guide-btn guide-btn--ghost guide-btn--sm" data-act="pending" data-id="' + s.id + '">Unpublish</button>'
					: '<button class="guide-btn guide-btn--primary guide-btn--sm" data-act="publish" data-id="' + s.id + '">' + ICONS.check + 'Approve</button>';

				return '<article class="guide-card guide-story" data-story="' + s.id + '">' +
					'<div class="guide-card__body">' +
						'<div class="guide-story__top">' +
							'<span class="guide-badge guide-badge--' + esc( s.status ) + '">' + esc( s.status ) + '</span>' +
							'<span class="guide-sub">' + esc( s.author ) + ' · ' + esc( s.date ) + '</span>' +
						'</div>' +
						'<h3>' + esc( s.title || '(untitled)' ) + '</h3>' +
						( s.role || s.company
							? '<p class="guide-sub">' + esc( [ s.role, s.company ].filter( Boolean ).join( ' · ' ) ) + '</p>'
							: '' ) +
						'<p class="guide-story__excerpt">' + esc( s.excerpt ) + '</p>' +
						'<div class="guide-story__actions">' +
							actions +
							'<a class="guide-btn guide-btn--ghost guide-btn--sm" href="' + esc( s.permalink ) + '" target="_blank" rel="noopener">' + ICONS.external + 'Preview</a>' +
							'<button class="guide-btn guide-btn--danger guide-btn--sm" data-act="trash" data-id="' + s.id + '">' + ICONS.trash + 'Delete</button>' +
						'</div>' +
					'</div>' +
				'</article>';
			}

			view.innerHTML =
				'<header class="guide-page-head"><div><h1>Success stories</h1>' +
				'<p class="guide-sub">' + ( pending
					? pending + ( pending === 1 ? ' story waiting for review' : ' stories waiting for review' )
					: 'Nothing waiting for review.' ) + '</p></div></header>' +
				( data.stories.length
					? '<div class="guide-story-grid">' + data.stories.map( card ).join( '' ) + '</div>'
					: '<div class="guide-empty-state"><h3>No stories yet</h3><p>When a learner submits one it lands here for approval.</p></div>' );

			view.querySelectorAll( '[data-act]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var id  = btn.getAttribute( 'data-id' );
					var act = btn.getAttribute( 'data-act' );

					function run() {
						api( 'guide/v1/stories/' + id + '/status', { method: 'POST', body: { status: act } } )
							.then( function () {
								toast( act === 'publish' ? 'Story published' : act === 'trash' ? 'Story deleted' : 'Story unpublished' );
								renderStories();
							} )
							.catch( function () {} );
					}

					if ( act === 'trash' ) {
						confirmInline( btn.closest( '.guide-card__body' ), 'Delete this story?', run );
					} else {
						run();
					}
				} );
			} );
		} ).catch( fail );
	}

	/* ================= learners ================= */

	function renderLearners() {
		setNav( 'learners' );
		loading();

		req( 'guide/v1/analytics/learners' ).then( function ( data ) {
			var rows = data.learners.map( function ( u ) {
				return '<tr class="is-clickable" data-href="#/learners/' + u.id + '">' +
					'<td><span class="guide-user-cell"><img class="guide-avatar" src="' + esc( u.avatar ) + '" alt=""><span><strong>' + esc( u.name ) + '</strong><small>' + esc( u.email ) + '</small></span></span></td>' +
					'<td class="guide-num">' + u.enrollments + '</td>' +
					'<td class="guide-num">' + u.completed + '</td>' +
					'<td>' + ( u.last_active ? esc( u.last_active ) + ' ago' : '—' ) + '</td>' +
					'<td>' + esc( u.registered ) + '</td>' +
				'</tr>';
			} ).join( '' );

			view.innerHTML =
				'<header class="guide-page-head"><div><h1>Learners</h1><p class="guide-sub">Everyone enrolled in at least one course.</p></div></header>' +
				'<section class="guide-card"><div class="guide-table-wrap"><table class="guide-table"><thead><tr><th>Learner</th><th class="guide-num">Courses</th><th class="guide-num">Lessons done</th><th>Last active</th><th>Joined</th></tr></thead><tbody>' +
				( rows || '<tr><td colspan="5">No learners yet — they appear here after their first enrollment.</td></tr>' ) +
				'</tbody></table></div></section>';

			bindRowLinks();
		} ).catch( fail );
	}

	function renderLearner( userId ) {
		setNav( 'learners' );
		loading();

		req( 'guide/v1/analytics/learners/' + userId ).then( function ( u ) {
			var courses = u.courses.map( function ( c ) {
				return '<tr class="is-clickable" data-href="#/courses/' + c.id + '">' +
					'<td><strong>' + esc( c.title ) + '</strong><br><small style="color:var(--ink-500)">enrolled ' + esc( c.enrolled_at ) + ' · ' + esc( c.source ) + '</small></td>' +
					'<td class="guide-num">' + c.completed + '/' + c.total + '</td>' +
					'<td>' + progressHTML( c.percent ) + '</td>' +
				'</tr>';
			} ).join( '' );

			var feed = u.activity.map( function ( a ) {
				return '<li><span class="guide-feed__dot"></span><span>Completed <strong>' + esc( a.lesson ) + '</strong> <span class="guide-feed__course">· ' + esc( a.course ) + '</span></span><span class="guide-feed__when">' + esc( a.when ) + ' ago</span></li>';
			} ).join( '' ) || '<li><span>No completions yet.</span></li>';

			view.innerHTML =
				'<nav class="guide-breadcrumb"><a href="#/learners">' + ICONS.arrowL + ' Learners</a></nav>' +
				'<header class="guide-page-head">' +
					'<div class="guide-user-cell"><img class="guide-avatar guide-avatar--lg" src="' + esc( u.avatar ) + '" alt="">' +
					'<span><h1>' + esc( u.name ) + '</h1><p class="guide-sub">' + esc( u.email ) + ' · joined ' + esc( u.registered ) + '</p></span></div>' +
				'</header>' +
				'<div class="guide-stat-grid">' +
					statHTML( 'layers', 'Enrolled courses', u.courses.length ) +
					statHTML( 'check', 'Lessons completed', u.total_done ) +
					statHTML( 'flame', 'Last active', u.last_active ? u.last_active + ' ago' : '—' ) +
				'</div>' +
				'<div class="guide-grid-2">' +
					'<div style="display:flex;flex-direction:column;gap:16px;min-width:0">' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Course progress</h2></div><div class="guide-table-wrap"><table class="guide-table"><thead><tr><th>Course</th><th class="guide-num">Lessons</th><th>Progress</th></tr></thead><tbody>' + ( courses || '<tr><td colspan="3">No enrollments.</td></tr>' ) + '</tbody></table></div></section>' +
						'<section class="guide-card"><div class="guide-card__head"><h2>Activity — last 14 days</h2></div><div class="guide-card__body">' + sparkHTML( u.days_14 ) + '</div></section>' +
					'</div>' +
					'<section class="guide-card"><div class="guide-card__head"><h2>Recent completions</h2></div><ul class="guide-feed">' + feed + '</ul></section>' +
				'</div>';

			bindRowLinks();
		} ).catch( fail );
	}

	/* ================= boot ================= */

	route();
} )();
