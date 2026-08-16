/**
 * Success-story submission.
 *
 * The server decides the story's status (always pending) — this only
 * collects the fields and reports back.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'guide-story-form' );

		if ( ! form || ! window.guideStory ) {
			return;
		}

		var status = document.getElementById( 'guide-story-status' );
		var submit = form.querySelector( 'button[type="submit"]' );
		var body   = document.getElementById( 'guide-story-body' );
		var editor = buildEditor( document.getElementById( 'guide-story-rte' ), body );

		function t( key ) {
			return ( window.guideStory.i18n && window.guideStory.i18n[ key ] ) || '';
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();

			submit.disabled = true;
			status.textContent = t( 'sending' );

			var payload = {
				title: document.getElementById( 'guide-story-title' ).value.trim(),
				role: document.getElementById( 'guide-story-role' ).value.trim(),
				company: document.getElementById( 'guide-story-company' ).value.trim(),
				previous: document.getElementById( 'guide-story-previous' ).value.trim(),
				weeks: parseInt( document.getElementById( 'guide-story-weeks' ).value, 10 ) || 0,
				linkedin: document.getElementById( 'guide-story-linkedin' ).value.trim(),
				salary: document.getElementById( 'guide-story-salary' ).value,
				story: editor ? editor.getHTML() : body.value.trim(),
			};

			fetch( window.guideStory.restUrl + '/stories', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.guideStory.nonce },
				body: JSON.stringify( payload ),
			} )
				.then( function ( res ) {
					return res.json().then( function ( data ) {
						return { ok: res.ok, data: data };
					} );
				} )
				.then( function ( result ) {
					if ( ! result.ok ) {
						status.textContent = result.data.error || t( 'failed' );
						submit.disabled = false;
						return;
					}

					// Replace the form outright: there is nothing useful left
					// to do here, and leaving it filled in invites a re-submit.
					form.innerHTML =
						'<p class="notification is-primary is-light">' + t( 'thanks' ) + '</p>';

					if ( window.guideSnackbar ) {
						window.guideSnackbar( t( 'thanks' ) );
					}
				} )
				.catch( function () {
					status.textContent = t( 'failed' );
					submit.disabled = false;
				} );
		} );
	} );

	/**
	 * A small rich text editor over the story textarea.
	 *
	 * Deliberately fewer controls than the console's editor: headings, bold,
	 * italic, two kinds of list, a quote and a link. No images, no code blocks,
	 * no colours. Someone writing "here is how I got my first job" needs
	 * paragraphs and emphasis; every extra button is one more thing to get
	 * wrong on a phone.
	 *
	 * The textarea remains the source of truth for the no-JavaScript case and
	 * is kept in sync, so nothing is lost if the editor never initialises.
	 *
	 * Everything produced here is run through wp_kses_post on the server before
	 * storage, and a human approves the story before it is ever public — this
	 * is a writing aid, not a trust boundary.
	 */
	function buildEditor( host, textarea ) {
		if ( ! host || ! textarea || ! document.execCommand ) {
			return null;
		}

		var toolbar = document.createElement( 'div' );
		toolbar.className = 'guide-rte__toolbar';
		toolbar.setAttribute( 'role', 'toolbar' );
		toolbar.setAttribute( 'aria-label', 'Formatting' );

		var area = document.createElement( 'div' );
		area.className = 'guide-rte__area';
		area.setAttribute( 'contenteditable', 'true' );
		area.setAttribute( 'spellcheck', 'true' );
		area.setAttribute( 'role', 'textbox' );
		area.setAttribute( 'aria-multiline', 'true' );
		area.setAttribute( 'aria-labelledby', 'guide-story-body-label' );
		area.innerHTML = '<p></p>';

		var saved = null;

		function remember() {
			var sel = window.getSelection();
			if ( sel && sel.rangeCount && area.contains( sel.anchorNode ) ) {
				saved = sel.getRangeAt( 0 ).cloneRange();
			}
		}

		function exec( cmd, value ) {
			area.focus();
			if ( saved ) {
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange( saved );
			}
			document.execCommand( cmd, false, value || null );
			remember();
			sync();
		}

		function sync() {
			textarea.value = area.innerHTML.replace( /<p><\/p>/g, '' ).trim();
		}

		var BUTTONS = [
			{ label: 'H2', title: 'Heading', run: function () { exec( 'formatBlock', '<h2>' ); } },
			{ label: 'P', title: 'Paragraph', run: function () { exec( 'formatBlock', '<p>' ); } },
			{ sep: true },
			{ label: '<b>B</b>', title: 'Bold', run: function () { exec( 'bold' ); } },
			{ label: '<i>I</i>', title: 'Italic', run: function () { exec( 'italic' ); } },
			{ sep: true },
			{ label: '&bull;', title: 'Bullet list', run: function () { exec( 'insertUnorderedList' ); } },
			{ label: '1.', title: 'Numbered list', run: function () { exec( 'insertOrderedList' ); } },
			{ label: '&ldquo;&rdquo;', title: 'Quote', run: function () { exec( 'formatBlock', '<blockquote>' ); } },
			{ sep: true },
			{ label: 'Link', title: 'Add a link', run: function () {
				var url = window.prompt( 'Link address', 'https://' );
				if ( url && /^https?:\/\//i.test( url ) ) {
					exec( 'createLink', url );
				}
			} },
		];

		BUTTONS.forEach( function ( spec ) {
			if ( spec.sep ) {
				var sep = document.createElement( 'span' );
				sep.className = 'guide-rte__sep';
				toolbar.appendChild( sep );
				return;
			}

			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'guide-rte__btn';
			btn.title = spec.title;
			btn.setAttribute( 'aria-label', spec.title );
			btn.innerHTML = spec.label;

			// mousedown would move focus out of the editable area and lose the
			// selection before the command runs.
			btn.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); } );
			btn.addEventListener( 'click', spec.run );
			toolbar.appendChild( btn );
		} );

		[ 'keyup', 'mouseup', 'input', 'blur' ].forEach( function ( evt ) {
			area.addEventListener( evt, function () { remember(); sync(); } );
		} );

		// Paste as plain text: pasting from a word processor otherwise drags
		// in a page of inline styles that will be stripped server-side anyway,
		// so the writer would see one thing and publish another.
		area.addEventListener( 'paste', function ( e ) {
			e.preventDefault();
			var text = ( e.clipboardData || window.clipboardData ).getData( 'text/plain' );
			document.execCommand( 'insertText', false, text );
		} );

		host.className = 'guide-rte';
		host.appendChild( toolbar );
		host.appendChild( area );

		// The textarea stays in the DOM (and required) but is now driven by the
		// editor, so hide it from sight and from assistive technology.
		textarea.hidden = true;
		textarea.setAttribute( 'aria-hidden', 'true' );
		textarea.removeAttribute( 'required' );

		return { getHTML: function () { sync(); return textarea.value; } };
	}
} )();
