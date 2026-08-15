/**
 * Material 3 component behaviour.
 *
 * The CSS in src/md3.css draws the components; this gives them the parts of
 * M3 that can't be expressed in CSS alone:
 *
 * - ripple on press (M3's press feedback is a ripple *plus* the state layer)
 * - the top app bar's on-scroll colour/elevation change
 * - modal navigation drawer with scrim, focus trapping and Escape
 * - tabs
 * - menus
 * - snackbars (window.jslSnackbar)
 *
 * Everything is opt-in by markup, so a page only pays for what it uses.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ================= ripple ================= */

	/**
	 * Draw a ripple from the pointer, sized to reach the far corner of the
	 * element so it always covers it.
	 */
	function ripple( event ) {
		var host = event.target.closest(
			'.md-state, .md-icon-btn, .md-chip, .md-list-item, .md-tab, .md-fab, .md-menu__item, .md-drawer__item, .md-segmented__item, .jsl-btn'
		);

		if ( ! host || reduceMotion || host.hasAttribute( 'disabled' ) ) {
			return;
		}

		var rect = host.getBoundingClientRect();
		var x = ( event.clientX || rect.left + rect.width / 2 ) - rect.left;
		var y = ( event.clientY || rect.top + rect.height / 2 ) - rect.top;

		// Distance to the furthest corner.
		var size = Math.max(
			Math.hypot( x, y ),
			Math.hypot( rect.width - x, y ),
			Math.hypot( x, rect.height - y ),
			Math.hypot( rect.width - x, rect.height - y )
		) * 2;

		var span = document.createElement( 'span' );
		span.className = 'md-ripple';
		span.style.width = span.style.height = size + 'px';
		span.style.left = ( x - size / 2 ) + 'px';
		span.style.top = ( y - size / 2 ) + 'px';

		host.appendChild( span );
		span.addEventListener( 'animationend', function () { span.remove(); } );
	}

	document.addEventListener( 'pointerdown', ripple );

	/* ================= top app bar on scroll ================= */

	function initAppBar() {
		var bar = document.querySelector( '.md-top-app-bar' );
		if ( ! bar ) {
			return;
		}

		var ticking = false;

		function update() {
			bar.classList.toggle( 'is-scrolled', window.scrollY > 4 );
			ticking = false;
		}

		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );

		update();
	}

	/* ================= modal navigation drawer ================= */

	var FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

	function initDrawers() {
		document.querySelectorAll( '[data-drawer]' ).forEach( function ( drawer ) {
			var id = drawer.getAttribute( 'data-drawer' );
			var scrim = document.querySelector( '[data-drawer-scrim="' + id + '"]' );
			var openers = document.querySelectorAll( '[data-drawer-open="' + id + '"]' );
			var closers = drawer.querySelectorAll( '[data-drawer-close]' );
			var lastFocus = null;

			function open() {
				lastFocus = document.activeElement;
				drawer.classList.add( 'is-open' );
				if ( scrim ) {
					scrim.classList.add( 'is-open' );
				}
				document.body.style.overflow = 'hidden';
				openers.forEach( function ( o ) { o.setAttribute( 'aria-expanded', 'true' ); } );

				var first = drawer.querySelector( FOCUSABLE );
				if ( first ) {
					first.focus();
				}
			}

			function close() {
				drawer.classList.remove( 'is-open' );
				if ( scrim ) {
					scrim.classList.remove( 'is-open' );
				}
				document.body.style.overflow = '';
				openers.forEach( function ( o ) { o.setAttribute( 'aria-expanded', 'false' ); } );

				if ( lastFocus ) {
					lastFocus.focus();
				}
			}

			openers.forEach( function ( o ) { o.addEventListener( 'click', open ); } );
			closers.forEach( function ( c ) { c.addEventListener( 'click', close ); } );
			if ( scrim ) {
				scrim.addEventListener( 'click', close );
			}

			drawer.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' ) {
					close();
					return;
				}

				if ( e.key !== 'Tab' ) {
					return;
				}

				// Keep focus inside the drawer while it is modal.
				var items = Array.prototype.filter.call(
					drawer.querySelectorAll( FOCUSABLE ),
					function ( el ) { return el.offsetParent !== null; }
				);

				if ( ! items.length ) {
					return;
				}

				var first = items[ 0 ];
				var last = items[ items.length - 1 ];

				if ( e.shiftKey && document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				} else if ( ! e.shiftKey && document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
			} );
		} );
	}

	/* ================= tabs ================= */

	function initTabs() {
		document.querySelectorAll( '[data-tabs]' ).forEach( function ( group ) {
			var tabs = Array.prototype.slice.call( group.querySelectorAll( '[role="tab"]' ) );

			function select( tab ) {
				tabs.forEach( function ( t ) {
					var selected = t === tab;
					t.setAttribute( 'aria-selected', selected ? 'true' : 'false' );
					t.tabIndex = selected ? 0 : -1;

					var panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
					if ( panel ) {
						panel.hidden = ! selected;
					}
				} );
			}

			tabs.forEach( function ( tab, i ) {
				tab.addEventListener( 'click', function () { select( tab ); } );

				tab.addEventListener( 'keydown', function ( e ) {
					var next = null;
					if ( e.key === 'ArrowRight' ) {
						next = tabs[ ( i + 1 ) % tabs.length ];
					}
					if ( e.key === 'ArrowLeft' ) {
						next = tabs[ ( i - 1 + tabs.length ) % tabs.length ];
					}
					if ( next ) {
						e.preventDefault();
						select( next );
						next.focus();
					}
				} );
			} );
		} );
	}

	/* ================= menus ================= */

	function initMenus() {
		document.querySelectorAll( '[data-menu-trigger]' ).forEach( function ( trigger ) {
			var menu = document.getElementById( trigger.getAttribute( 'data-menu-trigger' ) );
			if ( ! menu ) {
				return;
			}

			trigger.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				var open = menu.classList.toggle( 'is-open' );
				trigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );

			document.addEventListener( 'click', function ( e ) {
				if ( ! menu.contains( e.target ) && e.target !== trigger ) {
					menu.classList.remove( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'false' );
				}
			} );

			menu.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' ) {
					menu.classList.remove( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'false' );
					trigger.focus();
				}
			} );
		} );
	}

	/* ================= snackbar ================= */

	/**
	 * @param {string} message
	 * @param {{actionLabel?: string, onAction?: Function, duration?: number}} [options]
	 */
	function snackbar( message, options ) {
		options = options || {};

		var host = document.querySelector( '.md-snackbar-host' );
		if ( ! host ) {
			host = document.createElement( 'div' );
			host.className = 'md-snackbar-host';
			// Announced politely: a snackbar is a confirmation, not an alert.
			host.setAttribute( 'aria-live', 'polite' );
			document.body.appendChild( host );
		}

		var bar = document.createElement( 'div' );
		bar.className = 'md-snackbar';

		var text = document.createElement( 'span' );
		text.style.flex = '1';
		text.textContent = message;
		bar.appendChild( text );

		if ( options.actionLabel ) {
			var action = document.createElement( 'button' );
			action.type = 'button';
			action.className = 'md-snackbar__action';
			action.textContent = options.actionLabel;
			action.addEventListener( 'click', function () {
				if ( options.onAction ) {
					options.onAction();
				}
				dismiss();
			} );
			bar.appendChild( action );
		}

		host.appendChild( bar );

		function dismiss() {
			bar.classList.add( 'is-leaving' );
			bar.addEventListener( 'animationend', function () { bar.remove(); } );
		}

		window.setTimeout( dismiss, options.duration || 4000 );

		return dismiss;
	}

	window.jslSnackbar = snackbar;

	/* ================= boot ================= */

	document.addEventListener( 'DOMContentLoaded', function () {
		initAppBar();
		initDrawers();
		initTabs();
		initMenus();
	} );
} )();
