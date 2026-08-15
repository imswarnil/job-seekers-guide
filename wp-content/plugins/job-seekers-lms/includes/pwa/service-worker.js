/**
 * Service worker for the Job Seekers LMS.
 *
 * Served from PHP (JSL\Pwa\Pwa::serve_service_worker), which prepends a
 * `JSL` config object with the current version, the offline URL, and the
 * URLs to precache. Nothing here is generated — this file is the logic.
 *
 * The guiding constraint: this site has paid content and logged-in state,
 * so the cache must never be able to show one user something meant for
 * another, or show a stale "you must buy this" page to someone who just
 * bought it. That rules out cache-first for HTML.
 */

/* global JSL */

const HTML_CACHE = `jsl-html-${ JSL.version }`;
const ASSET_CACHE = `jsl-assets-${ JSL.version }`;

/** Requests that must always go to the network, uncached. */
function isNeverCacheable( url, request ) {
	if ( request.method !== 'GET' ) {
		return true;
	}

	// Anything authenticated, administrative, or an API call.
	return (
		url.pathname.startsWith( '/wp-admin' ) ||
		url.pathname.startsWith( '/wp-login' ) ||
		url.pathname.startsWith( '/wp-json' ) ||
		url.pathname.startsWith( '/auth/' ) ||
		url.searchParams.has( 'preview' )
	);
}

/**
 * A response we are allowed to keep. Anything that sets a cookie or varies
 * by cookie is per-user by definition and must not be shared from a cache.
 */
function isCacheable( response ) {
	if ( ! response || ! response.ok || response.type === 'opaque' ) {
		return false;
	}

	if ( response.headers.get( 'set-cookie' ) ) {
		return false;
	}

	const vary = response.headers.get( 'vary' ) || '';

	return ! vary.toLowerCase().includes( 'cookie' );
}

function isAsset( url ) {
	return /\.(?:css|js|mjs|woff2?|ttf|otf|png|jpe?g|gif|svg|webp|avif|ico)$/i.test( url.pathname );
}

self.addEventListener( 'install', ( event ) => {
	event.waitUntil(
		caches
			.open( HTML_CACHE )
			.then( ( cache ) => cache.addAll( JSL.precache ) )
			// A precache miss must not prevent the worker from installing.
			.catch( () => {} )
			.then( () => self.skipWaiting() )
	);
} );

self.addEventListener( 'activate', ( event ) => {
	event.waitUntil(
		caches
			.keys()
			.then( ( keys ) =>
				Promise.all(
					keys
						.filter( ( key ) => key !== HTML_CACHE && key !== ASSET_CACHE )
						.map( ( key ) => caches.delete( key ) )
				)
			)
			.then( () => self.clients.claim() )
	);
} );

self.addEventListener( 'fetch', ( event ) => {
	const request = event.request;
	const url = new URL( request.url );

	// Other origins (fonts, embeds) are left entirely alone.
	if ( url.origin !== self.location.origin ) {
		return;
	}

	if ( isNeverCacheable( url, request ) ) {
		return;
	}

	// Assets: serve from cache immediately, refresh in the background.
	if ( isAsset( url ) ) {
		event.respondWith(
			caches.open( ASSET_CACHE ).then( ( cache ) =>
				cache.match( request ).then( ( cached ) => {
					const network = fetch( request )
						.then( ( response ) => {
							if ( isCacheable( response ) ) {
								cache.put( request, response.clone() );
							}
							return response;
						} )
						.catch( () => cached );

					return cached || network;
				} )
			)
		);
		return;
	}

	// Pages: network first. The cache is a safety net for being offline,
	// never the primary source — a cached page can be out of date with the
	// viewer's login or purchase state.
	if ( request.mode === 'navigate' ) {
		event.respondWith(
			fetch( request )
				.then( ( response ) => {
					if ( isCacheable( response ) ) {
						const copy = response.clone();
						caches.open( HTML_CACHE ).then( ( cache ) => cache.put( request, copy ) );
					}
					return response;
				} )
				.catch( () =>
					caches
						.match( request )
						.then( ( cached ) => cached || caches.match( JSL.offlineUrl ) )
				)
		);
	}
} );
