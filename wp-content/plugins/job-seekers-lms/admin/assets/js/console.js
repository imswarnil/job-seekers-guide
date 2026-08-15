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
		] ).then( function ( results ) {
			var course    = results[ 0 ];
			var structure = results[ 1 ];
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
						'<section class="jsl-card"><div class="jsl-card__head"><h2>Course card text</h2></div><div class="jsl-card__body">' +
							'<div class="jsl-field"><label for="jsl-course-excerpt">Short description</label>' +
							'<textarea class="jsl-input" id="jsl-course-excerpt" rows="4" placeholder="One or two sentences shown on course cards…"></textarea>' +
							'<span class="jsl-help">Saved when you click away.</span></div>' +
						'</div></section>' +
						'<section class="jsl-card"><div class="jsl-card__head"><h2>More</h2></div><div class="jsl-card__body" style="display:flex;flex-direction:column;gap:8px">' +
							'<a class="jsl-btn jsl-btn--ghost" href="' + esc( cfg.adminUrl ) + 'post.php?post=' + courseId + '&action=edit">Full WordPress editor</a>' +
							'<a class="jsl-btn jsl-btn--ghost" href="' + esc( cfg.adminUrl ) + 'post.php?post=' + courseId + '&action=edit#jsl-pricing">Pricing (free / paid)</a>' +
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

	/* ================= lesson drawer (writing) ================= */

	var EDITOR_ID = 'jsl-lesson-content';

	function closeDrawer() {
		if ( window.wp && wp.editor && document.getElementById( EDITOR_ID ) ) {
			wp.editor.remove( EDITOR_ID );
		}
		document.querySelectorAll( '.jsl-drawer, .jsl-drawer-scrim' ).forEach( function ( n ) { n.remove(); } );
		document.querySelectorAll( '.jsl-lesson.is-editing' ).forEach( function ( n ) { n.classList.remove( 'is-editing' ); } );
		document.removeEventListener( 'keydown', escClose );
	}

	function escClose( e ) {
		if ( e.key === 'Escape' && ! ( window.tinymce && tinymce.activeEditor && tinymce.activeEditor.hasFocus() ) ) {
			closeDrawer();
		}
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

		req( 'wp/v2/lessons/' + lessonId + '?context=edit' ).then( function ( lesson ) {
			var meta = lesson.meta || {};

			drawer.innerHTML =
				'<header class="jsl-drawer__head">' +
					'<div style="flex:1;min-width:0"><span class="jsl-eyebrow">Lesson</span>' +
					'<input class="jsl-drawer__title" id="jsl-drawer-title" type="text" value="" placeholder="Lesson title"></div>' +
					'<a class="jsl-btn jsl-btn--ghost jsl-btn--sm" href="' + esc( lesson.link ) + '" target="_blank" rel="noopener">' + ICONS.external + 'View</a>' +
					'<button class="jsl-icon-btn" id="jsl-drawer-close" title="Close (Esc)" style="width:32px;height:32px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>' +
				'</header>' +
				'<div class="jsl-drawer__body">' +
					'<div class="jsl-drawer__meta-grid">' +
						'<div class="jsl-field"><label for="jsl-drawer-video">Video URL</label><input class="jsl-input" id="jsl-drawer-video" type="url" placeholder="YouTube, Vimeo, or .mp4…"></div>' +
						'<div class="jsl-field"><label for="jsl-drawer-duration">Duration (min)</label><input class="jsl-input" id="jsl-drawer-duration" type="number" min="0"></div>' +
						'<div class="jsl-field"><label>&nbsp;</label><label class="jsl-field--row" style="margin:0"><input type="checkbox" id="jsl-drawer-preview"> Free preview</label></div>' +
					'</div>' +
					'<div class="jsl-field"><label>Lesson content</label><textarea id="' + EDITOR_ID + '" rows="14" style="width:100%"></textarea></div>' +
				'</div>' +
				'<footer class="jsl-drawer__foot">' +
					'<button class="jsl-btn jsl-btn--danger" id="jsl-drawer-delete">' + ICONS.trash + 'Delete lesson</button>' +
					'<div style="display:flex;gap:8px;align-items:center">' +
						'<button class="jsl-btn jsl-btn--ghost" id="jsl-drawer-cancel">Close</button>' +
						'<button class="jsl-btn jsl-btn--primary" id="jsl-drawer-save">Save lesson</button>' +
					'</div>' +
				'</footer>';

			document.getElementById( 'jsl-drawer-title' ).value = lesson.title.raw || '';
			document.getElementById( 'jsl-drawer-video' ).value = meta.jsl_video_url || '';
			document.getElementById( 'jsl-drawer-duration' ).value = meta.jsl_duration_minutes || '';
			document.getElementById( 'jsl-drawer-preview' ).checked = !! meta.jsl_is_preview;
			document.getElementById( EDITOR_ID ).value = lesson.content.raw || '';

			if ( window.wp && wp.editor ) {
				wp.editor.initialize( EDITOR_ID, {
					tinymce: {
						wpautop: true,
						height: 340,
						toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,hr,undo,redo',
					},
					quicktags: true,
					mediaButtons: true,
				} );
			}

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
				var content = ( window.wp && wp.editor && wp.editor.getContent( EDITOR_ID ) ) || document.getElementById( EDITOR_ID ).value;
				var title   = document.getElementById( 'jsl-drawer-title' ).value.trim() || '(untitled)';

				api( 'wp/v2/lessons/' + lessonId, {
					method: 'POST',
					body: {
						title:   title,
						content: content,
						meta: {
							jsl_video_url:        document.getElementById( 'jsl-drawer-video' ).value.trim(),
							jsl_duration_minutes: parseInt( document.getElementById( 'jsl-drawer-duration' ).value, 10 ) || 0,
							jsl_is_preview:       document.getElementById( 'jsl-drawer-preview' ).checked,
						},
					},
				} ).then( function () {
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
