/**
 * Shared UI behaviour: snackbar, dropdown menus, and the slide-over drawers.
 *
 * Replaces the old Material 3 component script. Everything here is plain DOM —
 * no framework, no build step for JS — because the whole point of committing
 * compiled assets is that production needs no Node toolchain.
 */
( function () {
	'use strict';

	/* ---------------------------------------------------------------------
	 * Snackbar
	 * ------------------------------------------------------------------ */

	var snackTimer = null;

	/**
	 * @param {string} message
	 * @param {{duration?: number, actionLabel?: string, onAction?: Function}} [options]
	 */
	window.guideSnackbar = function ( message, options ) {
		var el = document.getElementById( 'guide-snackbar' );

		if ( ! el || ! message ) {
			return;
		}

		var opts     = options || {};
		var duration = opts.duration || 3000;

		el.textContent = '';

		var text = document.createElement( 'span' );
		text.textContent = message;
		el.appendChild( text );

		// An action turns the snackbar from a notification into an undo, which
		// is the difference between "you toggled that by accident, too bad" and
		// "one tap to put it back".
		if ( opts.actionLabel && typeof opts.onAction === 'function' ) {
			var action = document.createElement( 'button' );
			action.type = 'button';
			action.className = 'guide-snackbar__action';
			action.textContent = opts.actionLabel;
			action.addEventListener( 'click', function () {
				el.classList.remove( 'is-open' );
				opts.onAction();
			} );
			el.appendChild( action );
		}

		el.classList.add( 'is-open' );

		window.clearTimeout( snackTimer );
		snackTimer = window.setTimeout( function () {
			el.classList.remove( 'is-open' );
		}, duration );
	};

	/* ---------------------------------------------------------------------
	 * Dropdown menus
	 * ------------------------------------------------------------------ */

	function closeAllMenus( except ) {
		document.querySelectorAll( '.dropdown.is-active' ).forEach( function ( menu ) {
			if ( menu === except ) {
				return;
			}
			menu.classList.remove( 'is-active' );
			var trigger = menu.querySelector( '[data-menu-trigger]' );
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var trigger = event.target.closest( '[data-menu-trigger]' );

		if ( trigger ) {
			var menu = document.getElementById( trigger.getAttribute( 'data-menu-trigger' ) );

			if ( menu ) {
				event.preventDefault();
				var open = menu.classList.toggle( 'is-active' );
				trigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				closeAllMenus( menu );
				return;
			}
		}

		// A click anywhere outside an open menu closes it. Clicks *inside* the
		// menu are left alone so links still work.
		if ( ! event.target.closest( '.dropdown.is-active' ) ) {
			closeAllMenus( null );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}

		closeAllMenus( null );
		closeDrawers();
	} );

	/* ---------------------------------------------------------------------
	 * Drawers — the lesson player sidebar and the mobile filter rail
	 * ------------------------------------------------------------------ */

	function closeDrawers() {
		document.querySelectorAll( '[data-drawer].is-open' ).forEach( function ( drawer ) {
			drawer.classList.remove( 'is-open' );
		} );

		document.querySelectorAll( '[data-drawer-scrim].is-open' ).forEach( function ( scrim ) {
			scrim.classList.remove( 'is-open' );
		} );

		document.querySelectorAll( '[data-drawer-toggle][aria-expanded="true"]' ).forEach( function ( button ) {
			button.setAttribute( 'aria-expanded', 'false' );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '[data-drawer-toggle]' );

		if ( toggle ) {
			event.preventDefault();

			var target = document.getElementById( toggle.getAttribute( 'data-drawer-toggle' ) );
			if ( ! target ) {
				return;
			}

			var open = target.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			var scrim = target.parentElement
				? target.parentElement.querySelector( '[data-drawer-scrim]' )
				: null;
			if ( scrim ) {
				scrim.classList.toggle( 'is-open', open );
			}
			return;
		}

		if ( event.target.closest( '[data-drawer-scrim]' ) ) {
			closeDrawers();
		}
	} );

	/* ---------------------------------------------------------------------
	 * Tabs
	 *
	 * The panels are real elements with real ids, so the tab links double as
	 * in-page anchors: with JavaScript off, clicking "About" still jumps to
	 * the About section rather than doing nothing.
	 * ------------------------------------------------------------------ */

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-tabs]' ).forEach( function ( tabs ) {
			var links = Array.prototype.slice.call( tabs.querySelectorAll( '[role="tab"]' ) );

			if ( links.length < 2 ) {
				return;
			}

			function select( link ) {
				links.forEach( function ( other ) {
					var panel    = document.getElementById( other.getAttribute( 'aria-controls' ) );
					var isTarget = other === link;

					other.setAttribute( 'aria-selected', isTarget ? 'true' : 'false' );
					other.tabIndex = isTarget ? 0 : -1;

					if ( other.parentElement ) {
						other.parentElement.classList.toggle( 'is-active', isTarget );
					}

					if ( panel ) {
						panel.hidden = ! isTarget;
					}
				} );
			}

			links.forEach( function ( link, index ) {
				link.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					select( link );
				} );

				link.addEventListener( 'keydown', function ( event ) {
					var next = null;

					if ( event.key === 'ArrowRight' ) {
						next = links[ ( index + 1 ) % links.length ];
					} else if ( event.key === 'ArrowLeft' ) {
						next = links[ ( index - 1 + links.length ) % links.length ];
					}

					if ( next ) {
						event.preventDefault();
						select( next );
						next.focus();
					}
				} );
			} );
		} );
	} );


	/* ---------------------------------------------------------------------
	 * Table of contents
	 *
	 * Built from the headings already in the content rather than authored
	 * separately, so it can never drift out of sync with the article. Headings
	 * without an id get one, because an anchor link needs a target.
	 * ------------------------------------------------------------------ */

	document.addEventListener( 'DOMContentLoaded', function () {
		var source = document.querySelector( '[data-toc-source]' );
		var host   = document.querySelector( '[data-toc]' );
		var list   = document.querySelector( '[data-toc-list]' );

		if ( ! source || ! host || ! list ) {
			return;
		}

		var headings = Array.prototype.slice.call( source.querySelectorAll( 'h2, h3' ) );

		// One or two headings is not a document worth a contents list.
		if ( headings.length < 3 ) {
			host.hidden = true;
			return;
		}

		var used = {};

		headings.forEach( function ( heading ) {
			if ( ! heading.id ) {
				var slug = heading.textContent
					.toLowerCase()
					.replace( /[^a-z0-9\s-]/g, '' )
					.trim()
					.replace( /\s+/g, '-' )
					.slice( 0, 60 ) || 'section';

				// Two headings with the same words must not collide.
				used[ slug ] = ( used[ slug ] || 0 ) + 1;
				heading.id = used[ slug ] > 1 ? slug + '-' + used[ slug ] : slug;
			}

			var link = document.createElement( 'a' );
			link.href = '#' + heading.id;
			link.textContent = heading.textContent;
			if ( heading.tagName === 'H3' ) {
				link.classList.add( 'is-sub' );
			}
			list.appendChild( link );
		} );

		// Highlight whichever heading is currently on screen.
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var links = {};
		Array.prototype.forEach.call( list.children, function ( link ) {
			links[ link.getAttribute( 'href' ).slice( 1 ) ] = link;
		} );

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				var link = links[ entry.target.id ];
				if ( ! link ) {
					return;
				}
				if ( entry.isIntersecting ) {
					Object.keys( links ).forEach( function ( key ) {
						links[ key ].classList.remove( 'is-current' );
					} );
					link.classList.add( 'is-current' );
				}
			} );
		}, { rootMargin: '-80px 0px -70% 0px' } );

		headings.forEach( function ( heading ) {
			observer.observe( heading );
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Filter rail disclosure (small screens)
	 * ------------------------------------------------------------------ */

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '[data-filters-toggle]' );

		if ( ! toggle ) {
			return;
		}

		event.preventDefault();

		var filters = document.getElementById( toggle.getAttribute( 'data-filters-toggle' ) );
		if ( ! filters ) {
			return;
		}

		var open = filters.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );
} )();
