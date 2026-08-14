( function () {
	'use strict';

	if ( ! window.jslBuilder || ! window.jslBuilder.courseId ) {
		return;
	}

	var cfg  = window.jslBuilder;
	var root = document.getElementById( 'jsl-builder-root' );

	function api( path, options ) {
		options = options || {};
		options.headers = Object.assign( { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce }, options.headers || {} );
		return fetch( cfg.restUrl + path, options ).then( function ( res ) {
			if ( ! res.ok ) {
				throw new Error( 'Request failed: ' + res.status );
			}
			return res.json();
		} );
	}

	function el( tag, attrs, children ) {
		var node = document.createElement( tag );
		Object.keys( attrs || {} ).forEach( function ( key ) {
			if ( key === 'text' ) {
				node.textContent = attrs[ key ];
			} else {
				node.setAttribute( key, attrs[ key ] );
			}
		} );
		( children || [] ).forEach( function ( child ) {
			node.appendChild( child );
		} );
		return node;
	}

	function render( structure ) {
		root.innerHTML = '';

		structure.modules.forEach( function ( module ) {
			var moduleEl = el( 'div', { class: 'jsl-module', 'data-module-id': module.id } );
			moduleEl.appendChild( el( 'h3', { class: 'jsl-module__title', text: module.title } ) );

			var list = el( 'ul', { class: 'jsl-lesson-list', 'data-module-id': module.id } );

			module.lessons.forEach( function ( lesson ) {
				var item = el( 'li', { class: 'jsl-lesson-list__item', draggable: 'true', 'data-lesson-id': lesson.id } );
				item.appendChild( el( 'span', { class: 'jsl-drag-handle', text: '≡' } ) );
				var link = el( 'a', { href: lesson.edit_url, text: lesson.title } );
				item.appendChild( link );
				attachDragHandlers( item, list, module.id );
				list.appendChild( item );
			} );

			moduleEl.appendChild( list );

			var addForm = el( 'form', { class: 'jsl-add-lesson' } );
			var input   = el( 'input', { type: 'text', placeholder: 'New lesson title', required: 'required' } );
			addForm.appendChild( input );
			addForm.appendChild( el( 'button', { type: 'submit', class: 'button', text: 'Add lesson' } ) );
			addForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				if ( ! input.value.trim() ) {
					return;
				}
				api( '/lessons', {
					method: 'POST',
					body: JSON.stringify( { course_id: cfg.courseId, module_id: module.id, title: input.value.trim() } ),
				} ).then( load );
			} );
			moduleEl.appendChild( addForm );

			root.appendChild( moduleEl );
		} );

		var addModuleForm = el( 'form', { class: 'jsl-add-module' } );
		var moduleInput   = el( 'input', { type: 'text', placeholder: 'New module title', required: 'required' } );
		addModuleForm.appendChild( moduleInput );
		addModuleForm.appendChild( el( 'button', { type: 'submit', class: 'button button-primary', text: 'Add module' } ) );
		addModuleForm.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			if ( ! moduleInput.value.trim() ) {
				return;
			}
			api( '/modules', {
				method: 'POST',
				body: JSON.stringify( { course_id: cfg.courseId, title: moduleInput.value.trim() } ),
			} ).then( load );
		} );
		root.appendChild( addModuleForm );
	}

	var dragged = null;

	function attachDragHandlers( item, list ) {
		item.addEventListener( 'dragstart', function () {
			dragged = item;
			item.classList.add( 'is-dragging' );
		} );
		item.addEventListener( 'dragend', function () {
			item.classList.remove( 'is-dragging' );
			dragged = null;
		} );
		item.addEventListener( 'dragover', function ( e ) {
			e.preventDefault();
			var target = e.target.closest( '.jsl-lesson-list__item' );
			if ( ! target || target === dragged ) {
				return;
			}
			var parentList = target.parentElement;
			var rect       = target.getBoundingClientRect();
			var after      = ( e.clientY - rect.top ) > rect.height / 2;
			parentList.insertBefore( dragged, after ? target.nextSibling : target );
		} );
		list.addEventListener( 'drop', function ( e ) {
			e.preventDefault();
			persistOrder( list );
		} );
	}

	function persistOrder( list ) {
		var moduleId  = list.getAttribute( 'data-module-id' );
		var lessonIds = Array.prototype.map.call( list.querySelectorAll( '.jsl-lesson-list__item' ), function ( item ) {
			return parseInt( item.getAttribute( 'data-lesson-id' ), 10 );
		} );

		api( '/lessons/reorder', {
			method: 'POST',
			body: JSON.stringify( { course_id: cfg.courseId, module_id: parseInt( moduleId, 10 ), lesson_ids: lessonIds } ),
		} );
	}

	function load() {
		api( '/courses/' + cfg.courseId + '/structure' ).then( render );
	}

	load();
} )();
