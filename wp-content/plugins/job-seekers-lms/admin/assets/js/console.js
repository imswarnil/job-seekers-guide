/**
 * LMS Console SPA.
 *
 * Hash-routed views: #/ (dashboard analytics), #/courses (catalog + create),
 * #/courses/:id (drag-drop builder + inline lesson writing in a drawer),
 * #/learners (list), #/learners/:id (profile + progress). Data over
 * jsl/v1 (LMS) and wp/v2 (post content) with the REST nonce.
 */
( function () {
	'use strict';

	if ( ! window.jslConsole ) {
		return;
	}

	var cfg  = window.jslConsole;
	var view = document.getElementById( 'jsl-view' );

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

	function toast( message, isError ) {
		var host = document.querySelector( '.jsl-toasts' );
		if ( ! host ) {
			host = el( 'div', { class: 'jsl-toasts' } );
			document.body.appendChild( host );
		}
		var node = el( 'div', { class: 'jsl-toast' + ( isError ? ' jsl-toast--error' : '' ) } );
		node.innerHTML = '<span class="jsl-toast__icon">' + ICONS.check + '</span>';
		node.appendChild( document.createTextNode( message ) );
		host.appendChild( node );
		setTimeout( function () { node.remove(); }, 3200 );
	}

	function fail( err ) {
		toast( err && err.message ? err.message : 'Something went wrong', true );
		throw err;
	}

	function progressHTML( pct ) {
		return '<span class="jsl-progress"><span class="jsl-progress__track"><span class="jsl-progress__fill" style="width:' + ( pct || 0 ) + '%"></span></span><span class="jsl-progress__pct">' + ( pct || 0 ) + '%</span></span>';
	}

	function sparkHTML( days ) {
		var max = Math.max.apply( null, days.map( function ( d ) { return d.count; } ).concat( [ 1 ] ) );
		var bars = days.map( function ( d ) {
			var h = Math.round( ( d.count / max ) * 100 );
			return '<span class="jsl-spark__bar" title="' + esc( d.date + ': ' + d.count ) + '"><i style="height:' + Math.max( h, d.count ? 8 : 0 ) + '%"></i></span>';
		} ).join( '' );
		var first = days[ 0 ] ? days[ 0 ].date.slice( 5 ) : '';
		var last  = days.length ? days[ days.length - 1 ].date.slice( 5 ) : '';
		return '<div class="jsl-spark">' + bars + '</div><div class="jsl-spark-labels"><span>' + esc( first ) + '</span><span>' + esc( last ) + '</span></div>';
	}

	function statHTML( icon, label, value, hint ) {
		return '<div class="jsl-stat"><span class="jsl-stat__label">' + ICONS[ icon ] + esc( label ) + '</span><span class="jsl-stat__value">' + esc( value ) + '</span>' + ( hint ? '<span class="jsl-stat__hint">' + esc( hint ) + '</span>' : '' ) + '</div>';
	}

	function setNav( key ) {
		document.querySelectorAll( '.jsl-console__links a' ).forEach( function ( a ) {
			a.classList.toggle( 'is-active', a.getAttribute( 'data-nav' ) === key );
		} );
	}

	function loading() {
		view.innerHTML = '<div class="jsl-skeleton-page"><span class="jsl-spinner"></span>Loading…</div>';
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
		if ( host.querySelector( '.jsl-confirm' ) ) {
			return;
		}
		var bar = el( 'div', { class: 'jsl-confirm' } );
		bar.appendChild( el( 'span', {}, message ) );
		var yes = el( 'button', { type: 'button', class: 'jsl-confirm__yes' }, 'Yes, delete' );
		var no  = el( 'button', { type: 'button', class: 'jsl-confirm__no' }, 'Cancel' );
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
			req( 'jsl/v1/analytics/overview' ),
			req( 'jsl/v1/analytics/courses' ),
		] ).then( function ( results ) {
			var o = results[ 0 ];
			var courses = results[ 1 ].courses.filter( function ( c ) { return c.status === 'publish'; } );

			var courseRows = courses.map( function ( c ) {
				return '<tr class="is-clickable" data-href="#/courses/' + c.id + '">' +
					'<td><strong>' + esc( c.title ) + '</strong></td>' +
					'<td class="jsl-num">' + c.enrolled + '</td>' +
					'<td class="jsl-num">' + c.completions + '</td>' +
					'<td>' + progressHTML( c.avg_progress ) + '</td>' +
				'</tr>';
			} ).join( '' );

			var feed = o.activity.map( function ( a ) {
				return '<li><span class="jsl-feed__dot"></span><span><strong>' + esc( a.user ) + '</strong> completed “' + esc( a.lesson ) + '” <span class="jsl-feed__course">· ' + esc( a.course ) + '</span></span><span class="jsl-feed__when">' + esc( a.when ) + ' ago</span></li>';
			} ).join( '' ) || '<li><span>No activity yet.</span></li>';

			view.innerHTML =
				'<header class="jsl-page-head"><div><h1>Dashboard</h1><p class="jsl-sub">What learners are doing across your LMS.</p></div></header>' +
				'<div class="jsl-stat-grid">' +
					statHTML( 'users', 'Learners', o.learners ) +
					statHTML( 'layers', 'Enrollments', o.enrollments ) +
					statHTML( 'check', 'Lessons completed', o.completions ) +
					statHTML( 'flame', 'Active this week', o.active_7d, 'learners with completions' ) +
				'</div>' +
				'<div class="jsl-grid-2">' +
					'<div style="display:flex;flex-direction:column;gap:16px;min-width:0">' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Completions — last 14 days</h2></div><div class="jsl-card__body">' + sparkHTML( o.completions_14d ) + '</div></section>' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course performance</h2><a class="jsl-btn jsl-btn--ghost jsl-btn--sm" href="#/courses">All courses</a></div><div class="jsl-table-wrap"><table class="jsl-table"><thead><tr><th>Course</th><th class="jsl-num">Enrolled</th><th class="jsl-num">Completions</th><th>Avg progress</th></tr></thead><tbody>' + ( courseRows || '<tr><td colspan="4">No published courses yet.</td></tr>' ) + '</tbody></table></div></section>' +
					'</div>' +
					'<section class="jsl-card"><div class="jsl-card__head"><h2>Recent activity</h2></div><ul class="jsl-feed">' + feed + '</ul></section>' +
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

		req( 'jsl/v1/analytics/courses' ).then( function ( data ) {
			var cards = data.courses.map( function ( c ) {
				return '<div class="jsl-course-card" data-href="#/courses/' + c.id + '" role="link" tabindex="0">' +
					'<div class="jsl-course-card__top"><span class="jsl-badge jsl-badge--' + esc( c.status ) + '">' + esc( c.status ) + '</span>' +
					( c.enrolled ? '<span class="jsl-badge">' + c.enrolled + ' enrolled</span>' : '' ) + '</div>' +
					'<h3>' + esc( c.title || '(untitled)' ) + '</h3>' +
					'<div style="max-width:180px">' + progressHTML( c.avg_progress ) + '</div>' +
					'<div class="jsl-course-card__meta"><span>' + ICONS.layers + c.modules + ' modules</span><span>' + ICONS.doc + c.lessons + ' lessons</span></div>' +
				'</div>';
			} ).join( '' );

			view.innerHTML =
				'<header class="jsl-page-head"><div><h1>Courses</h1><p class="jsl-sub">Create, structure, and publish your curriculum.</p></div>' +
				'<div class="jsl-page-head__actions"><form class="jsl-inline-form" id="jsl-new-course"><input class="jsl-input" type="text" placeholder="New course title…" required style="width:240px"><button class="jsl-btn jsl-btn--primary" type="submit">' + ICONS.plus + 'Create course</button></form></div></header>' +
				( cards
					? '<div class="jsl-course-grid">' + cards + '</div>'
					: '<div class="jsl-empty-state"><h3>No courses yet</h3><p>Create your first course above — it starts as a draft.</p></div>' );

			bindRowLinks();

			var form  = document.getElementById( 'jsl-new-course' );
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
	var indicator = el( 'li', { class: 'jsl-drop-indicator' } );
	var saveTimer = null;

	function setSaveState( state ) {
		var eln = document.getElementById( 'jsl-save-state' );
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
			req( 'jsl/v1/courses/' + courseId + '/structure' ),
			req( 'wp/v2/course-categories?per_page=100' ).catch( function () { return []; } ),
		] ).then( function ( results ) {
			var course     = results[ 0 ];
			var structure  = results[ 1 ];
			var categories = results[ 2 ];
			var isPublish = course.status === 'publish';

			view.innerHTML =
				'<nav class="jsl-breadcrumb"><a href="#/courses">' + ICONS.arrowL + ' Courses</a></nav>' +
				'<header class="jsl-page-head">' +
					'<div style="flex:1;min-width:240px"><h1 class="jsl-title-input" id="jsl-course-title"></h1>' +
					'<p class="jsl-sub"><span class="jsl-badge jsl-badge--' + esc( course.status ) + '" id="jsl-course-status">' + esc( course.status ) + '</span> <span id="jsl-builder-stats"></span></p></div>' +
					'<div class="jsl-page-head__actions">' +
						'<span class="jsl-save-state" id="jsl-save-state" aria-live="polite"></span>' +
						'<button class="jsl-btn jsl-btn--ghost" id="jsl-toggle-status">' + ( isPublish ? 'Unpublish' : 'Publish' ) + '</button>' +
						'<a class="jsl-btn jsl-btn--ghost" href="' + esc( course.link ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'</div>' +
				'</header>' +
				'<div class="jsl-editor-layout">' +
					'<div id="jsl-builder-root"></div>' +
					'<aside style="display:flex;flex-direction:column;gap:16px">' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course settings</h2></div><div class="jsl-card__body">' +
							'<div class="jsl-field"><label for="jsl-course-code">Course code</label>' +
							'<input class="jsl-input" id="jsl-course-code" type="text" maxlength="12" placeholder="JSG-101" style="max-width:160px;font-family:ui-monospace,Menlo,monospace;text-transform:uppercase">' +
							'<span class="jsl-help">Shown on cards, placeholder art, and JSON-LD.</span></div>' +
							'<div class="jsl-field" style="margin-bottom:0"><label>Categories</label><div id="jsl-course-cats"></div>' +
							'<form class="jsl-inline-form" id="jsl-new-cat" style="margin-top:6px"><input class="jsl-input" type="text" placeholder="New category…"><button class="jsl-btn jsl-btn--ghost jsl-btn--sm" type="submit">Add</button></form></div>' +
						'</div></section>' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course card text</h2></div><div class="jsl-card__body">' +
							'<div class="jsl-field"><label for="jsl-course-excerpt">Short description</label>' +
							'<textarea class="jsl-input" id="jsl-course-excerpt" rows="4" placeholder="One or two sentences shown on course cards…"></textarea>' +
							'<span class="jsl-help">Saved when you click away.</span></div>' +
						'</div></section>' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course page</h2></div><div class="jsl-card__body" style="display:flex;flex-direction:column;gap:8px">' +
							'<button class="jsl-btn jsl-btn--primary" id="jsl-open-details" type="button">' + ICONS.pencil + 'Edit details &amp; description</button>' +
							'<span class="jsl-help">Full description, image, level, outcomes, requirements and pricing.</span>' +
						'</div></section>' +
					'</aside>' +
				'</div>';

			var titleEl = document.getElementById( 'jsl-course-title' );
			titleEl.textContent = course.title.raw || '(untitled)';
			editable( titleEl, function ( value ) {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { title: value } } );
			} );

			var excerptEl = document.getElementById( 'jsl-course-excerpt' );
			excerptEl.value = course.excerpt && course.excerpt.raw ? course.excerpt.raw : '';
			excerptEl.addEventListener( 'blur', function () {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { excerpt: excerptEl.value } } );
			} );

			document.getElementById( 'jsl-toggle-status' ).addEventListener( 'click', function () {
				var next = course.status === 'publish' ? 'draft' : 'publish';
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { status: next } } ).then( function () {
					course.status = next;
					document.getElementById( 'jsl-course-status' ).textContent = next;
					document.getElementById( 'jsl-course-status' ).className = 'jsl-badge jsl-badge--' + next;
					document.getElementById( 'jsl-toggle-status' ).textContent = next === 'publish' ? 'Unpublish' : 'Publish';
					toast( next === 'publish' ? 'Course published' : 'Course set to draft' );
				} );
			} );

			document.getElementById( 'jsl-open-details' ).addEventListener( 'click', function () {
				openCourseDrawer( courseId, course );
			} );

			/* Course code */
			var codeInput = document.getElementById( 'jsl-course-code' );
			codeInput.value = ( course.meta && course.meta.jsl_course_code ) || '';
			codeInput.addEventListener( 'blur', function () {
				api( 'wp/v2/courses/' + courseId, { method: 'POST', body: { meta: { jsl_course_code: codeInput.value.trim().toUpperCase() } } } );
			} );

			/* Categories */
			var catHost  = document.getElementById( 'jsl-course-cats' );
			var selected = ( course[ 'course-categories' ] || [] ).slice();

			function renderCats() {
				catHost.innerHTML = '';
				if ( ! categories.length ) {
					catHost.appendChild( el( 'span', { class: 'jsl-help' }, 'No categories yet — add one below.' ) );
				}
				categories.forEach( function ( cat ) {
					var label = el( 'label', { class: 'jsl-cat-check' } );
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

			var catForm = document.getElementById( 'jsl-new-cat' );
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
		var root = document.getElementById( 'jsl-builder-root' );
		root.innerHTML = '';

		if ( ! structure.modules.length ) {
			root.appendChild( el( 'div', { class: 'jsl-empty-state' }, 'No modules yet — create your first module below to start structuring this course.' ) );
		}

		structure.modules.forEach( function ( module, index ) {
			root.appendChild( moduleCard( courseId, module, index ) );
		} );

		var form  = el( 'form', { class: 'jsl-add-module' } );
		var input = el( 'input', { type: 'text', class: 'jsl-input', placeholder: 'New module title (e.g. Week 1 — Foundations)…', required: 'required', 'aria-label': 'Add module' } );
		var btn   = el( 'button', { type: 'submit', class: 'jsl-btn jsl-btn--primary' }, '+ Add module' );
		form.appendChild( input );
		form.appendChild( btn );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var value = input.value.trim();
			if ( ! value ) {
				return;
			}
			btn.disabled = true;
			api( 'jsl/v1/modules', { method: 'POST', body: { course_id: courseId, title: value } } ).then( function ( module ) {
				module.lessons = [];
				var empty = root.querySelector( '.jsl-empty-state' );
				if ( empty ) { empty.remove(); }
				root.insertBefore( moduleCard( courseId, module, root.querySelectorAll( '.jsl-module-card' ).length ), form );
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
		var root = document.getElementById( 'jsl-builder-root' );
		var statsEl = document.getElementById( 'jsl-builder-stats' );
		if ( ! root ) {
			return;
		}
		var total = 0;
		root.querySelectorAll( '.jsl-module-card' ).forEach( function ( card ) {
			var n = card.querySelectorAll( '.jsl-lesson' ).length;
			total += n;
			card.querySelector( '.jsl-module-card__count' ).textContent = n + ( n === 1 ? ' lesson' : ' lessons' );
		} );
		if ( statsEl ) {
			var modules = root.querySelectorAll( '.jsl-module-card' ).length;
			statsEl.textContent = modules + ( modules === 1 ? ' module · ' : ' modules · ' ) + total + ( total === 1 ? ' lesson' : ' lessons' );
		}
	}

	function renumberModules() {
		document.querySelectorAll( '#jsl-builder-root .jsl-module-card' ).forEach( function ( card, i ) {
			card.querySelector( '.jsl-module-card__index' ).textContent = String( i + 1 ).padStart( 2, '0' );
		} );
	}

	function refreshEmptyStates( list ) {
		var empty = list.parentNode.querySelector( '.jsl-lessons__empty' );
		var has   = !! list.querySelector( '.jsl-lesson' );
		if ( has && empty ) { empty.remove(); }
		if ( ! has && ! empty ) {
			list.parentNode.insertBefore( el( 'div', { class: 'jsl-lessons__empty' }, 'Drop lessons here or add one below.' ), list.nextSibling );
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
		var lessonIds = Array.prototype.map.call( list.querySelectorAll( '.jsl-lesson' ), function ( row ) {
			return parseInt( row.dataset.lessonId, 10 );
		} );
		api( 'jsl/v1/lessons/reorder', { method: 'POST', body: { course_id: courseId, module_id: parseInt( list.dataset.moduleId, 10 ), lesson_ids: lessonIds } } );
	}

	function persistModules( courseId ) {
		var ids = Array.prototype.map.call( document.querySelectorAll( '#jsl-builder-root .jsl-module-card' ), function ( card ) {
			return parseInt( card.dataset.moduleId, 10 );
		} );
		api( 'jsl/v1/modules/reorder', { method: 'POST', body: { course_id: courseId, module_ids: ids } } );
	}

	function lessonRow( courseId, lesson ) {
		var row = el( 'li', { class: 'jsl-lesson', 'data-lesson-id': lesson.id } );

		var handle = el( 'span', { class: 'jsl-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		row.appendChild( handle );
		row.appendChild( el( 'span', { class: 'jsl-lesson__dot', 'aria-hidden': 'true' } ) );

		var title = el( 'button', { type: 'button', class: 'jsl-lesson__title', title: 'Write this lesson' }, lesson.title );
		title.addEventListener( 'click', function () {
			openLessonDrawer( courseId, lesson.id, row );
		} );
		row.appendChild( title );

		var actions = el( 'span', { class: 'jsl-lesson__actions' } );
		var write = el( 'button', { type: 'button', class: 'jsl-icon-btn', title: 'Write lesson' } );
		write.innerHTML = ICONS.pencil;
		write.addEventListener( 'click', function () { openLessonDrawer( courseId, lesson.id, row ); } );
		actions.appendChild( write );

		var del = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Delete lesson' } );
		del.innerHTML = ICONS.trash;
		del.addEventListener( 'click', function () {
			confirmInline( row.closest( '.jsl-module-card' ), 'Move this lesson to trash?', function () {
				var list = row.closest( '.jsl-lessons' );
				row.remove();
				refreshEmptyStates( list );
				refreshCounts();
				api( 'jsl/v1/lessons/' + lesson.id, { method: 'DELETE' } );
			} );
		} );
		actions.appendChild( del );
		row.appendChild( actions );

		/* drag */
		armHandle( handle, row );
		row.addEventListener( 'dragstart', function ( e ) {
			dragged = { type: 'lesson', node: row, fromList: row.closest( '.jsl-lessons' ) };
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
		var card = el( 'section', { class: 'jsl-module-card', 'data-module-id': module.id } );

		var head = el( 'header', { class: 'jsl-module-card__head' } );
		var handle = el( 'span', { class: 'jsl-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		head.appendChild( handle );
		head.appendChild( el( 'span', { class: 'jsl-module-card__index' }, String( index + 1 ).padStart( 2, '0' ) ) );

		var title = el( 'h2', { class: 'jsl-module-card__title' }, module.title );
		editable( title, function ( value ) {
			api( 'jsl/v1/modules/' + module.id, { method: 'PATCH', body: { title: value } } );
		} );
		head.appendChild( title );
		head.appendChild( el( 'span', { class: 'jsl-module-card__count' }, '' ) );

		var del = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Delete module' } );
		del.innerHTML = ICONS.trash;
		del.addEventListener( 'click', function () {
			confirmInline( card, 'Delete this module? Its lessons are kept but unassigned.', function () {
				card.remove();
				renumberModules();
				refreshCounts();
				api( 'jsl/v1/modules/' + module.id, { method: 'DELETE' } );
			} );
		} );
		head.appendChild( del );
		card.appendChild( head );

		var list = el( 'ul', { class: 'jsl-lessons', 'data-module-id': module.id } );
		module.lessons.forEach( function ( lesson ) {
			list.appendChild( lessonRow( courseId, lesson ) );
		} );

		list.addEventListener( 'dragover', function ( e ) {
			if ( ! dragged || dragged.type !== 'lesson' ) {
				return;
			}
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			var rows = Array.prototype.filter.call( list.querySelectorAll( '.jsl-lesson' ), function ( r ) { return r !== dragged.node; } );
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

		var form  = el( 'form', { class: 'jsl-add-lesson' } );
		var input = el( 'input', { type: 'text', class: 'jsl-input', placeholder: 'New lesson title…', required: 'required', 'aria-label': 'Add lesson' } );
		var btn   = el( 'button', { type: 'submit', class: 'jsl-btn jsl-btn--ghost jsl-btn--sm' }, '+ Add lesson' );
		form.appendChild( input );
		form.appendChild( btn );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var value = input.value.trim();
			if ( ! value ) {
				return;
			}
			btn.disabled = true;
			api( 'jsl/v1/lessons', { method: 'POST', body: { course_id: courseId, module_id: module.id, title: value } } ).then( function ( lesson ) {
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
		host.className = 'jsl-rte';

		var toolbar = el( 'div', { class: 'jsl-rte__toolbar', role: 'toolbar' } );
		var area    = el( 'div', { class: 'jsl-rte__area', contenteditable: 'true', spellcheck: 'true' } );
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

		var urlBar = el( 'div', { class: 'jsl-rte__urlbar', hidden: 'hidden' } );
		var urlInput = el( 'input', { class: 'jsl-input', type: 'url', placeholder: 'https://…' } );
		var urlOk = el( 'button', { type: 'button', class: 'jsl-btn jsl-btn--primary jsl-btn--sm' }, 'Apply' );
		var urlCancel = el( 'button', { type: 'button', class: 'jsl-btn jsl-btn--ghost jsl-btn--sm' }, 'Cancel' );
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
				toolbar.appendChild( el( 'span', { class: 'jsl-rte__sep' } ) );
				return;
			}
			var btn = el( 'button', { type: 'button', class: 'jsl-rte__btn', title: b.title } );
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
		host.className = 'jsl-quiz-builder';

		var passRow = el( 'div', { class: 'jsl-field jsl-field--row' } );
		passRow.appendChild( el( 'label', {}, 'Pass mark (%)' ) );
		var passInput = el( 'input', { class: 'jsl-input', type: 'number', min: '1', max: '100', style: 'width:90px' } );
		passInput.value = quiz.pass || 70;
		passRow.appendChild( passInput );
		host.appendChild( passRow );

		var list = el( 'div', { class: 'jsl-quiz-builder__list' } );
		host.appendChild( list );

		function optionRow( card, text, isCorrect ) {
			var row = el( 'div', { class: 'jsl-quiz-opt' } );
			var radio = el( 'input', { type: 'radio', title: 'Correct answer' } );
			radio.name = 'correct-' + Math.abs( ( card.dataset.qid || '0' ).split( '' ).reduce( function ( a, c ) { return a + c.charCodeAt( 0 ); }, 0 ) ) + '-' + card.dataset.qid;
			radio.checked = !! isCorrect;
			var input = el( 'input', { class: 'jsl-input', type: 'text', placeholder: 'Answer option…' } );
			input.value = text || '';
			var rm = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Remove option' } );
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
			var card = el( 'div', { class: 'jsl-quiz-q', 'data-qid': String( qCounter ) } );

			var head = el( 'div', { class: 'jsl-quiz-q__head' } );
			head.appendChild( el( 'span', { class: 'jsl-quiz-q__num' }, 'Q' ) );
			var qInput = el( 'input', { class: 'jsl-input', type: 'text', placeholder: 'Question…' } );
			qInput.value = q.q || '';
			qInput.setAttribute( 'data-role', 'question' );
			head.appendChild( qInput );
			var rm = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Remove question' } );
			rm.innerHTML = ICONS.trash;
			rm.addEventListener( 'click', function () { card.remove(); } );
			head.appendChild( rm );
			card.appendChild( head );

			var opts = el( 'div', { class: 'jsl-quiz-q__opts' } );
			( q.options && q.options.length ? q.options : [ '', '' ] ).forEach( function ( opt, i ) {
				opts.appendChild( optionRow( card, opt, i === ( q.correct || 0 ) ) );
			} );
			card.appendChild( opts );

			var addOpt = el( 'button', { type: 'button', class: 'jsl-btn jsl-btn--ghost jsl-btn--sm' }, '+ Option' );
			addOpt.addEventListener( 'click', function () {
				if ( opts.children.length < 6 ) {
					opts.appendChild( optionRow( card, '', false ) );
				}
			} );

			var explain = el( 'input', { class: 'jsl-input', type: 'text', placeholder: 'Explanation shown after answering (optional)…' } );
			explain.value = q.explain || '';
			explain.setAttribute( 'data-role', 'explain' );

			var foot = el( 'div', { class: 'jsl-quiz-q__foot' } );
			foot.appendChild( explain );
			foot.appendChild( addOpt );
			card.appendChild( foot );

			return card;
		}

		( quiz.questions && quiz.questions.length ? quiz.questions : [] ).forEach( function ( q ) {
			list.appendChild( questionCard( q ) );
		} );

		var addQ = el( 'button', { type: 'button', class: 'jsl-btn jsl-btn--ghost' }, '+ Add question' );
		addQ.addEventListener( 'click', function () {
			list.appendChild( questionCard( { options: [ '', '' ] } ) );
		} );
		host.appendChild( addQ );

		return {
			getData: function () {
				var questions = [];
				list.querySelectorAll( '.jsl-quiz-q' ).forEach( function ( card ) {
					var options = [];
					var correct = 0;
					card.querySelectorAll( '.jsl-quiz-opt' ).forEach( function ( row, i ) {
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

	function closeDrawer() {
		document.querySelectorAll( '.jsl-drawer, .jsl-drawer-scrim' ).forEach( function ( n ) { n.remove(); } );
		document.querySelectorAll( '.jsl-lesson.is-editing' ).forEach( function ( n ) { n.classList.remove( 'is-editing' ); } );
		document.removeEventListener( 'keydown', escClose );
	}

	function escClose( e ) {
		if ( e.key === 'Escape' && ! document.querySelector( '.jsl-rte__urlbar:not([hidden])' ) ) {
			closeDrawer();
		}
	}

	/**
	 * Repeatable single-line list (used for "what you'll learn" and
	 * "requirements"). Returns a reader for the current values.
	 */
	function lineListEditor( host, values, placeholder ) {
		host.innerHTML = '';
		host.className = 'jsl-linelist';

		function addRow( value ) {
			var row = el( 'div', { class: 'jsl-linelist__row' } );
			var input = el( 'input', { class: 'jsl-input', type: 'text', placeholder: placeholder } );
			input.value = value || '';

			var remove = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Remove' } );
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

		var addBtn = el( 'button', { type: 'button', class: 'jsl-btn jsl-btn--ghost jsl-btn--sm' } );
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
	 * requirements and pricing. This is what the classic editor used to be
	 * for.
	 */
	function openCourseDrawer( courseId, course ) {
		closeDrawer();

		var scrim = el( 'div', { class: 'jsl-drawer-scrim' } );
		scrim.addEventListener( 'click', closeDrawer );
		document.body.appendChild( scrim );

		var drawer = el( 'aside', { class: 'jsl-drawer jsl-drawer--wide', role: 'dialog', 'aria-label': 'Course details' } );
		var meta   = course.meta || {};

		drawer.innerHTML =
			'<header class="jsl-drawer__head">' +
				'<div style="flex:1"><span class="jsl-drawer__eyebrow">Course details</span>' +
				'<input class="jsl-drawer__title" id="jsl-cd-title" type="text" placeholder="Course title"></div>' +
				'<button class="jsl-icon-btn" id="jsl-cd-close" title="Close" type="button">✕</button>' +
			'</header>' +

			'<div class="jsl-drawer__body">' +
				'<div class="jsl-field"><label for="jsl-cd-excerpt">Short description</label>' +
					'<textarea class="jsl-input" id="jsl-cd-excerpt" rows="3" placeholder="One or two sentences shown on cards and in search results…"></textarea></div>' +

				'<div class="jsl-field"><label>Course image</label>' +
					'<div class="jsl-media-pick" id="jsl-cd-image">' +
						'<div class="jsl-media-pick__preview" id="jsl-cd-image-preview"></div>' +
						'<div class="jsl-media-pick__actions">' +
							'<button class="jsl-btn jsl-btn--ghost jsl-btn--sm" id="jsl-cd-image-set" type="button">Choose image</button>' +
							'<button class="jsl-btn jsl-btn--danger jsl-btn--sm" id="jsl-cd-image-clear" type="button">Remove</button>' +
						'</div>' +
					'</div>' +
					'<span class="jsl-help">Leave empty to use the generated placeholder art.</span></div>' +

				'<div class="jsl-field"><label for="jsl-cd-level">Level</label>' +
					'<select class="jsl-input" id="jsl-cd-level" style="max-width:220px">' +
						'<option value="">Not specified</option>' +
						'<option value="beginner">Beginner</option>' +
						'<option value="intermediate">Intermediate</option>' +
						'<option value="advanced">Advanced</option>' +
					'</select></div>' +

				'<div class="jsl-field"><label>Full description</label>' +
					'<div id="jsl-cd-editor"></div>' +
					'<span class="jsl-help">Shown on the course page under the “About” tab.</span></div>' +

				'<div class="jsl-field"><label>What you’ll learn</label><div id="jsl-cd-outcomes"></div></div>' +

				'<div class="jsl-field"><label>Requirements</label><div id="jsl-cd-reqs"></div></div>' +

				'<div class="jsl-field"><label>Pricing</label>' +
					'<div class="jsl-segmented" id="jsl-cd-pricing">' +
						'<button type="button" data-price="free">Free</button>' +
						'<button type="button" data-price="paid">Paid</button>' +
					'</div></div>' +

				'<div id="jsl-cd-paid-fields">' +
					'<div class="jsl-field"><label for="jsl-cd-product">Dodo product ID</label>' +
						'<input class="jsl-input" id="jsl-cd-product" type="text" placeholder="pdt_…"></div>' +
					'<div class="jsl-field"><label for="jsl-cd-price">Price label</label>' +
						'<input class="jsl-input" id="jsl-cd-price" type="text" placeholder="$49" style="max-width:180px">' +
						'<span class="jsl-help">Display only — the amount charged is whatever the Dodo product says.</span></div>' +
				'</div>' +
			'</div>' +

			'<footer class="jsl-drawer__foot">' +
				'<span class="jsl-help" id="jsl-cd-state"></span>' +
				'<div style="margin-left:auto;display:flex;gap:8px">' +
					'<button class="jsl-btn jsl-btn--ghost" id="jsl-cd-cancel" type="button">Cancel</button>' +
					'<button class="jsl-btn jsl-btn--primary" id="jsl-cd-save" type="button">Save details</button>' +
				'</div>' +
			'</footer>';

		document.body.appendChild( drawer );
		document.addEventListener( 'keydown', escClose );

		document.getElementById( 'jsl-cd-title' ).value = ( course.title && course.title.raw ) || '';
		document.getElementById( 'jsl-cd-excerpt' ).value = ( course.excerpt && course.excerpt.raw ) || '';
		document.getElementById( 'jsl-cd-level' ).value = meta.jsl_course_level || '';
		document.getElementById( 'jsl-cd-product' ).value = meta.jsl_dodo_product_id || '';
		document.getElementById( 'jsl-cd-price' ).value = meta.jsl_price_label || '';

		var editor = richEditor(
			document.getElementById( 'jsl-cd-editor' ),
			( course.content && course.content.raw ) || ''
		);

		var outcomes = lineListEditor( document.getElementById( 'jsl-cd-outcomes' ), meta.jsl_course_outcomes || [], 'e.g. Write a résumé that survives an ATS' );
		var reqs = lineListEditor( document.getElementById( 'jsl-cd-reqs' ), meta.jsl_course_requirements || [], 'e.g. No prior experience needed' );

		/* ---- Featured image ---- */
		var imageId = course.featured_media || 0;
		var preview = document.getElementById( 'jsl-cd-image-preview' );

		function paintImage() {
			preview.innerHTML = '';
			if ( ! imageId ) {
				preview.appendChild( el( 'span', { class: 'jsl-help' }, 'No image set' ) );
				return;
			}
			req( 'wp/v2/media/' + imageId )
				.then( function ( m ) {
					var img = el( 'img', { src: m.source_url, alt: '' } );
					preview.innerHTML = '';
					preview.appendChild( img );
				} )
				.catch( function () {
					preview.appendChild( el( 'span', { class: 'jsl-help' }, 'Image #' + imageId ) );
				} );
		}
		paintImage();

		document.getElementById( 'jsl-cd-image-set' ).addEventListener( 'click', function () {
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

		document.getElementById( 'jsl-cd-image-clear' ).addEventListener( 'click', function () {
			imageId = 0;
			paintImage();
		} );

		/* ---- Pricing ---- */
		var pricing = meta.jsl_pricing_type === 'paid' ? 'paid' : 'free';
		var paidFields = document.getElementById( 'jsl-cd-paid-fields' );

		function paintPricing() {
			document.querySelectorAll( '#jsl-cd-pricing button' ).forEach( function ( b ) {
				b.setAttribute( 'aria-selected', b.dataset.price === pricing ? 'true' : 'false' );
			} );
			paidFields.hidden = pricing !== 'paid';
		}

		document.querySelectorAll( '#jsl-cd-pricing button' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				pricing = b.dataset.price;
				paintPricing();
			} );
		} );
		paintPricing();

		/* ---- Save ---- */
		document.getElementById( 'jsl-cd-close' ).addEventListener( 'click', closeDrawer );
		document.getElementById( 'jsl-cd-cancel' ).addEventListener( 'click', closeDrawer );

		document.getElementById( 'jsl-cd-save' ).addEventListener( 'click', function () {
			var btn = document.getElementById( 'jsl-cd-save' );
			btn.disabled = true;
			document.getElementById( 'jsl-cd-state' ).textContent = 'Saving…';

			var body = {
				title: document.getElementById( 'jsl-cd-title' ).value.trim(),
				excerpt: document.getElementById( 'jsl-cd-excerpt' ).value,
				content: editor.getHTML(),
				featured_media: imageId,
				meta: {
					jsl_course_level: document.getElementById( 'jsl-cd-level' ).value,
					jsl_course_outcomes: outcomes.getValues(),
					jsl_course_requirements: reqs.getValues(),
					jsl_pricing_type: pricing,
					jsl_dodo_product_id: document.getElementById( 'jsl-cd-product' ).value.trim(),
					jsl_price_label: document.getElementById( 'jsl-cd-price' ).value.trim(),
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
					document.getElementById( 'jsl-cd-state' ).textContent = '';
					fail( err );
				} );
		} );

		document.getElementById( 'jsl-cd-title' ).focus();
	}

	function openLessonDrawer( courseId, lessonId, row ) {
		closeDrawer();
		row.classList.add( 'is-editing' );

		var scrim = el( 'div', { class: 'jsl-drawer-scrim' } );
		scrim.addEventListener( 'click', closeDrawer );
		document.body.appendChild( scrim );

		var drawer = el( 'aside', { class: 'jsl-drawer', role: 'dialog', 'aria-label': 'Lesson editor' } );
		drawer.innerHTML = '<div class="jsl-skeleton-page"><span class="jsl-spinner"></span>Loading lesson…</div>';
		document.body.appendChild( drawer );
		document.addEventListener( 'keydown', escClose );

		Promise.all( [
			req( 'wp/v2/lessons/' + lessonId + '?context=edit' ),
			req( 'jsl/v1/lessons/' + lessonId + '/quiz-admin' ),
		] ).then( function ( results ) {
			var lesson = results[ 0 ];
			var quiz   = results[ 1 ];
			var meta   = lesson.meta || {};
			var type   = meta.jsl_lesson_type || ( meta.jsl_video_url ? 'video' : 'article' );

			drawer.innerHTML =
				'<header class="jsl-drawer__head">' +
					'<div style="flex:1;min-width:0"><span class="jsl-eyebrow">Lesson</span>' +
					'<input class="jsl-drawer__title" id="jsl-drawer-title" type="text" value="" placeholder="Lesson title"></div>' +
					'<select class="jsl-input" id="jsl-drawer-type" style="width:auto" title="Lesson type">' +
						'<option value="article">Article</option><option value="video">Video</option><option value="quiz">Quiz</option>' +
					'</select>' +
					'<a class="jsl-btn jsl-btn--ghost jsl-btn--sm" href="' + esc( lesson.link ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'<button class="jsl-icon-btn" id="jsl-drawer-close" title="Close (Esc)" style="width:32px;height:32px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>' +
				'</header>' +
				'<div class="jsl-drawer__body">' +
					'<div class="jsl-drawer__meta-grid" data-section="video">' +
						'<div class="jsl-field"><label for="jsl-drawer-video">Video URL</label><input class="jsl-input" id="jsl-drawer-video" type="url" placeholder="YouTube, Vimeo, or .mp4…"></div>' +
						'<div class="jsl-field"><label for="jsl-drawer-vstart">Start at</label><input class="jsl-input" id="jsl-drawer-vstart" type="text" placeholder="0:00"><span class="jsl-help">mm:ss — plays from here</span></div>' +
						'<div class="jsl-field"><label for="jsl-drawer-vend">End at</label><input class="jsl-input" id="jsl-drawer-vend" type="text" placeholder="full"><span class="jsl-help">mm:ss — stops here</span></div>' +
					'</div>' +
					'<div class="jsl-drawer__meta-grid">' +
						'<div class="jsl-field"><label for="jsl-drawer-duration">Duration (min)</label><input class="jsl-input" id="jsl-drawer-duration" type="number" min="0"></div>' +
						'<div class="jsl-field"><label>&nbsp;</label><label class="jsl-field--row" style="margin:0"><input type="checkbox" id="jsl-drawer-preview"> Free preview</label></div>' +
						'<div></div>' +
					'</div>' +
					'<div class="jsl-field" data-section="quiz"><label>Quiz</label><div id="jsl-drawer-quiz"></div></div>' +
					'<div class="jsl-field"><label>Lesson content</label><div id="jsl-drawer-rte"></div></div>' +
				'</div>' +
				'<footer class="jsl-drawer__foot">' +
					'<button class="jsl-btn jsl-btn--danger" id="jsl-drawer-delete">' + ICONS.trash + 'Delete lesson</button>' +
					'<div style="display:flex;gap:8px;align-items:center">' +
						'<button class="jsl-btn jsl-btn--ghost" id="jsl-drawer-cancel">Close</button>' +
						'<button class="jsl-btn jsl-btn--primary" id="jsl-drawer-save">Save lesson</button>' +
					'</div>' +
				'</footer>';

			document.getElementById( 'jsl-drawer-title' ).value = lesson.title.raw || '';
			document.getElementById( 'jsl-drawer-type' ).value = type;
			document.getElementById( 'jsl-drawer-video' ).value = meta.jsl_video_url || '';
			document.getElementById( 'jsl-drawer-vstart' ).value = fmtTime( meta.jsl_video_start );
			document.getElementById( 'jsl-drawer-vend' ).value = fmtTime( meta.jsl_video_end );
			document.getElementById( 'jsl-drawer-duration' ).value = meta.jsl_duration_minutes || '';
			document.getElementById( 'jsl-drawer-preview' ).checked = !! meta.jsl_is_preview;

			var editor = richEditor( document.getElementById( 'jsl-drawer-rte' ), lesson.content.raw || '' );
			var qb     = quizBuilder( document.getElementById( 'jsl-drawer-quiz' ), quiz );

			function syncSections() {
				var t = document.getElementById( 'jsl-drawer-type' ).value;
				drawer.querySelector( '[data-section="video"]' ).style.display = t === 'video' ? '' : 'none';
				drawer.querySelector( '[data-section="quiz"]' ).style.display = t === 'quiz' ? '' : 'none';
			}
			document.getElementById( 'jsl-drawer-type' ).addEventListener( 'change', syncSections );
			syncSections();

			document.getElementById( 'jsl-drawer-close' ).addEventListener( 'click', closeDrawer );
			document.getElementById( 'jsl-drawer-cancel' ).addEventListener( 'click', closeDrawer );

			document.getElementById( 'jsl-drawer-delete' ).addEventListener( 'click', function () {
				confirmInline( drawer.querySelector( '.jsl-drawer__foot' ), 'Move this lesson to trash?', function () {
					api( 'jsl/v1/lessons/' + lessonId, { method: 'DELETE' } ).then( function () {
						var list = row.closest( '.jsl-lessons' );
						row.remove();
						if ( list ) { refreshEmptyStates( list ); }
						refreshCounts();
						closeDrawer();
						toast( 'Lesson trashed' );
					} );
				} );
			} );

			document.getElementById( 'jsl-drawer-save' ).addEventListener( 'click', function () {
				var btn = document.getElementById( 'jsl-drawer-save' );
				btn.disabled = true;
				var lessonType = document.getElementById( 'jsl-drawer-type' ).value;
				var title = document.getElementById( 'jsl-drawer-title' ).value.trim() || '(untitled)';

				var saves = [
					api( 'wp/v2/lessons/' + lessonId, {
						method: 'POST',
						body: {
							title:   title,
							content: editor.getHTML(),
							meta: {
								jsl_lesson_type:      lessonType,
								jsl_video_url:        document.getElementById( 'jsl-drawer-video' ).value.trim(),
								jsl_video_start:      parseTime( document.getElementById( 'jsl-drawer-vstart' ).value ),
								jsl_video_end:        parseTime( document.getElementById( 'jsl-drawer-vend' ).value ),
								jsl_duration_minutes: parseInt( document.getElementById( 'jsl-drawer-duration' ).value, 10 ) || 0,
								jsl_is_preview:       document.getElementById( 'jsl-drawer-preview' ).checked,
							},
						},
					} ),
				];

				if ( lessonType === 'quiz' ) {
					saves.push( api( 'jsl/v1/lessons/' + lessonId + '/quiz-admin', { method: 'POST', body: qb.getData() } ) );
				}

				Promise.all( saves ).then( function () {
					row.querySelector( '.jsl-lesson__title' ).textContent = title;
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

		req( 'jsl/v1/paths' ).then( function ( data ) {
			var cards = data.paths.map( function ( p ) {
				return '<div class="jsl-course-card" data-href="#/paths/' + p.id + '" role="link" tabindex="0">' +
					'<div class="jsl-course-card__top"><span class="jsl-badge jsl-badge--' + esc( p.status ) + '">' + esc( p.status ) + '</span></div>' +
					'<h3>' + esc( p.title || '(untitled)' ) + '</h3>' +
					( p.excerpt ? '<p class="jsl-sub">' + esc( p.excerpt ) + '</p>' : '' ) +
					'<div class="jsl-course-card__meta"><span>' + ICONS.path + p.steps + ( p.steps === 1 ? ' step' : ' steps' ) + '</span></div>' +
				'</div>';
			} ).join( '' );

			view.innerHTML =
				'<header class="jsl-page-head"><div><h1>Learning paths</h1><p class="jsl-sub">Arrange courses, articles and videos into a route from A to B.</p></div>' +
				'<div class="jsl-page-head__actions"><form class="jsl-inline-form" id="jsl-new-path"><input class="jsl-input" type="text" placeholder="New path title…" required style="width:240px"><button class="jsl-btn jsl-btn--primary" type="submit">' + ICONS.plus + 'Create path</button></form></div></header>' +
				( cards
					? '<div class="jsl-course-grid">' + cards + '</div>'
					: '<div class="jsl-empty-state"><h3>No learning paths yet</h3><p>A path is the spine of the site — create one above, then drop courses and lessons into it.</p></div>' );

			bindRowLinks();

			var form  = document.getElementById( 'jsl-new-path' );
			var input = form.querySelector( 'input' );
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var title = input.value.trim();
				if ( ! title ) {
					return;
				}
				form.querySelector( 'button' ).disabled = true;
				req( 'jsl/v1/paths', { method: 'POST', body: { title: title } } )
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
		var ids = Array.prototype.map.call( document.querySelectorAll( '#jsl-path-steps .jsl-step' ), function ( row ) {
			return parseInt( row.dataset.stepId, 10 );
		} );
		api( 'jsl/v1/paths/' + pathId + '/steps/reorder', { method: 'POST', body: { step_ids: ids } } );
	}

	function renumberSteps() {
		Array.prototype.forEach.call( document.querySelectorAll( '#jsl-path-steps .jsl-step' ), function ( row, i ) {
			var num = row.querySelector( '.jsl-step__num' );
			if ( num ) {
				num.textContent = String( i + 1 ).padStart( 2, '0' );
			}
		} );
	}

	function stepRow( pathId, step ) {
		var row = el( 'li', { class: 'jsl-step', 'data-step-id': step.step_id } );

		var handle = el( 'span', { class: 'jsl-handle', title: 'Drag to reorder' } );
		handle.innerHTML = ICONS.grip;
		row.appendChild( handle );

		row.appendChild( el( 'span', { class: 'jsl-step__num' }, '00' ) );

		var kind = el( 'span', { class: 'jsl-step__kind' } );
		kind.innerHTML = stepIcon( step ) + '<span>' + esc( stepKindLabel( step ) ) + '</span>';
		row.appendChild( kind );

		var title = el( 'span', { class: 'jsl-step__title' }, step.title || '(untitled)' );
		row.appendChild( title );

		// Courses are renamed in the course editor; a standalone step is
		// renamed right here, since this is the only place it exists.
		if ( step.type === 'lesson' ) {
			editable( title, function ( value ) {
				api( 'wp/v2/lessons/' + step.id, { method: 'POST', body: { title: value } } );
			} );
		}

		if ( step.status !== 'publish' ) {
			row.appendChild( el( 'span', { class: 'jsl-badge jsl-badge--draft' }, 'draft' ) );
		}

		var actions = el( 'span', { class: 'jsl-step__actions' } );

		var open = el( 'a', {
			class: 'jsl-icon-btn',
			title: step.type === 'course' ? 'Open course builder' : 'Write this step',
			href: step.type === 'course' ? '#/courses/' + step.id : step.permalink,
		} );
		if ( step.type !== 'course' ) {
			open.setAttribute( 'target', '_blank' );
			open.setAttribute( 'rel', 'noopener' );
		}
		open.innerHTML = step.type === 'course' ? ICONS.external : ICONS.pencil;
		actions.appendChild( open );

		var del = el( 'button', { type: 'button', class: 'jsl-icon-btn jsl-icon-btn--danger', title: 'Remove from this path' } );
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
					api( 'jsl/v1/path-steps/' + step.step_id, { method: 'DELETE' } );
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
			try { e.dataTransfer.setData( 'text/plain', String( step.step_id ) ); } catch ( err ) {}
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

		req( 'jsl/v1/paths/' + pathId ).then( function ( path ) {
			view.innerHTML =
				'<header class="jsl-page-head">' +
					'<div>' +
						'<button class="jsl-btn jsl-btn--ghost jsl-btn--sm" id="jsl-path-back">' + ICONS.arrowL + 'All paths</button>' +
						'<h1 id="jsl-path-title" style="margin-top:10px">' + esc( path.title ) + '</h1>' +
						'<p class="jsl-sub" id="jsl-path-excerpt">' + esc( path.excerpt || 'Add a one-line description…' ) + '</p>' +
					'</div>' +
					'<div class="jsl-page-head__actions">' +
						'<span id="jsl-save-state" class="jsl-save-state"></span>' +
						'<label class="jsl-switch"><input type="checkbox" id="jsl-path-status"' + ( path.status === 'publish' ? ' checked' : '' ) + '><span>Published</span></label>' +
						'<a class="jsl-btn jsl-btn--ghost jsl-btn--sm" href="' + esc( path.permalink ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'</div>' +
				'</header>' +

				'<div class="jsl-card">' +
					'<div class="jsl-card__head"><h2>Path steps</h2><span class="jsl-sub" id="jsl-step-count"></span></div>' +
					'<div class="jsl-card__body">' +
						'<ol class="jsl-steps" id="jsl-path-steps"></ol>' +
						'<div class="jsl-step-add" id="jsl-step-add">' +
							'<form class="jsl-inline-form" id="jsl-add-course">' +
								'<select class="jsl-input" id="jsl-course-picker" style="min-width:220px"><option value="">Add an existing course…</option></select>' +
								'<button class="jsl-btn jsl-btn--ghost jsl-btn--sm" type="submit">' + ICONS.plus + 'Add course</button>' +
							'</form>' +
							'<form class="jsl-inline-form" id="jsl-add-inline">' +
								'<select class="jsl-input" id="jsl-inline-type"><option value="article">Article</option><option value="video">Video</option><option value="quiz">Quiz</option></select>' +
								'<input class="jsl-input" id="jsl-inline-title" type="text" placeholder="Step title…" required style="width:220px">' +
								'<button class="jsl-btn jsl-btn--primary jsl-btn--sm" type="submit">' + ICONS.plus + 'Add step</button>' +
							'</form>' +
						'</div>' +
					'</div>' +
				'</div>';

			var list = document.getElementById( 'jsl-path-steps' );

			function paint( steps ) {
				list.innerHTML = '';
				steps.forEach( function ( step ) {
					list.appendChild( stepRow( pathId, step ) );
				} );
				renumberSteps();
				document.getElementById( 'jsl-step-count' ).textContent =
					steps.length + ( steps.length === 1 ? ' step' : ' steps' );
				if ( ! steps.length ) {
					list.innerHTML = '<li class="jsl-empty-row">Nothing in this path yet — add a course or write a step below.</li>';
				}
			}

			paint( path.steps );

			document.getElementById( 'jsl-path-back' ).addEventListener( 'click', function () {
				window.location.hash = '/paths';
			} );

			editable( document.getElementById( 'jsl-path-title' ), function ( value ) {
				api( 'jsl/v1/paths/' + pathId, { method: 'PATCH', body: { title: value } } );
			} );

			editable( document.getElementById( 'jsl-path-excerpt' ), function ( value ) {
				api( 'jsl/v1/paths/' + pathId, { method: 'PATCH', body: { excerpt: value } } );
			} );

			document.getElementById( 'jsl-path-status' ).addEventListener( 'change', function ( e ) {
				api( 'jsl/v1/paths/' + pathId, {
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
				var rows = Array.prototype.filter.call( list.querySelectorAll( '.jsl-step' ), function ( r ) {
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
			var picker = document.getElementById( 'jsl-course-picker' );
			req( 'jsl/v1/paths/' + pathId + '/available-courses' ).then( function ( data ) {
				data.courses.forEach( function ( c ) {
					var opt = el( 'option', { value: String( c.id ) }, c.title + ( c.status !== 'publish' ? ' (draft)' : '' ) );
					picker.appendChild( opt );
				} );
			} ).catch( function () {} );

			document.getElementById( 'jsl-add-course' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var courseId = parseInt( picker.value, 10 );
				if ( ! courseId ) {
					return;
				}
				api( 'jsl/v1/paths/' + pathId + '/steps', {
					method: 'POST',
					body: { step_type: 'course', object_id: courseId },
				} ).then( function () {
					toast( 'Course added to path' );
					renderPathEditor( pathId );
				} ).catch( function () {} );
			} );

			document.getElementById( 'jsl-add-inline' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var titleInput = document.getElementById( 'jsl-inline-title' );
				var title = titleInput.value.trim();
				if ( ! title ) {
					return;
				}
				api( 'jsl/v1/paths/' + pathId + '/steps', {
					method: 'POST',
					body: { step_type: document.getElementById( 'jsl-inline-type' ).value, title: title },
				} ).then( function () {
					titleInput.value = '';
					toast( 'Step added' );
					renderPathEditor( pathId );
				} ).catch( function () {} );
			} );
		} ).catch( fail );
	}

	/* ================= learners ================= */

	function renderLearners() {
		setNav( 'learners' );
		loading();

		req( 'jsl/v1/analytics/learners' ).then( function ( data ) {
			var rows = data.learners.map( function ( u ) {
				return '<tr class="is-clickable" data-href="#/learners/' + u.id + '">' +
					'<td><span class="jsl-user-cell"><img class="jsl-avatar" src="' + esc( u.avatar ) + '" alt=""><span><strong>' + esc( u.name ) + '</strong><small>' + esc( u.email ) + '</small></span></span></td>' +
					'<td class="jsl-num">' + u.enrollments + '</td>' +
					'<td class="jsl-num">' + u.completed + '</td>' +
					'<td>' + ( u.last_active ? esc( u.last_active ) + ' ago' : '—' ) + '</td>' +
					'<td>' + esc( u.registered ) + '</td>' +
				'</tr>';
			} ).join( '' );

			view.innerHTML =
				'<header class="jsl-page-head"><div><h1>Learners</h1><p class="jsl-sub">Everyone enrolled in at least one course.</p></div></header>' +
				'<section class="jsl-card"><div class="jsl-table-wrap"><table class="jsl-table"><thead><tr><th>Learner</th><th class="jsl-num">Courses</th><th class="jsl-num">Lessons done</th><th>Last active</th><th>Joined</th></tr></thead><tbody>' +
				( rows || '<tr><td colspan="5">No learners yet — they appear here after their first enrollment.</td></tr>' ) +
				'</tbody></table></div></section>';

			bindRowLinks();
		} ).catch( fail );
	}

	function renderLearner( userId ) {
		setNav( 'learners' );
		loading();

		req( 'jsl/v1/analytics/learners/' + userId ).then( function ( u ) {
			var courses = u.courses.map( function ( c ) {
				return '<tr class="is-clickable" data-href="#/courses/' + c.id + '">' +
					'<td><strong>' + esc( c.title ) + '</strong><br><small style="color:var(--ink-500)">enrolled ' + esc( c.enrolled_at ) + ' · ' + esc( c.source ) + '</small></td>' +
					'<td class="jsl-num">' + c.completed + '/' + c.total + '</td>' +
					'<td>' + progressHTML( c.percent ) + '</td>' +
				'</tr>';
			} ).join( '' );

			var feed = u.activity.map( function ( a ) {
				return '<li><span class="jsl-feed__dot"></span><span>Completed <strong>' + esc( a.lesson ) + '</strong> <span class="jsl-feed__course">· ' + esc( a.course ) + '</span></span><span class="jsl-feed__when">' + esc( a.when ) + ' ago</span></li>';
			} ).join( '' ) || '<li><span>No completions yet.</span></li>';

			view.innerHTML =
				'<nav class="jsl-breadcrumb"><a href="#/learners">' + ICONS.arrowL + ' Learners</a></nav>' +
				'<header class="jsl-page-head">' +
					'<div class="jsl-user-cell"><img class="jsl-avatar jsl-avatar--lg" src="' + esc( u.avatar ) + '" alt="">' +
					'<span><h1>' + esc( u.name ) + '</h1><p class="jsl-sub">' + esc( u.email ) + ' · joined ' + esc( u.registered ) + '</p></span></div>' +
				'</header>' +
				'<div class="jsl-stat-grid">' +
					statHTML( 'layers', 'Enrolled courses', u.courses.length ) +
					statHTML( 'check', 'Lessons completed', u.total_done ) +
					statHTML( 'flame', 'Last active', u.last_active ? u.last_active + ' ago' : '—' ) +
				'</div>' +
				'<div class="jsl-grid-2">' +
					'<div style="display:flex;flex-direction:column;gap:16px;min-width:0">' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course progress</h2></div><div class="jsl-table-wrap"><table class="jsl-table"><thead><tr><th>Course</th><th class="jsl-num">Lessons</th><th>Progress</th></tr></thead><tbody>' + ( courses || '<tr><td colspan="3">No enrollments.</td></tr>' ) + '</tbody></table></div></section>' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Activity — last 14 days</h2></div><div class="jsl-card__body">' + sparkHTML( u.days_14 ) + '</div></section>' +
					'</div>' +
					'<section class="jsl-card"><div class="jsl-card__head"><h2>Recent completions</h2></div><ul class="jsl-feed">' + feed + '</ul></section>' +
				'</div>';

			bindRowLinks();
		} ).catch( fail );
	}

	/* ================= boot ================= */

	route();
} )();
