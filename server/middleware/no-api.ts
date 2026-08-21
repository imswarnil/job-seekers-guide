/**
 * `/api/**` is not this site's, and never renders a page.
 *
 * There is no API here — the whole thing prerenders to static files. But the
 * catch-all page route matches any path with three segments, so a stray request
 * to something like `/api/workspaces` reaches `app/pages/[...slug].vue`, finds
 * no lesson, and throws a *fatal* error. That renders the full error page and
 * prints a Vue stack trace, which is a lot of machinery for a URL that was
 * never ours.
 *
 * It happens more than you would think. Anything else listening on 3000 or 3001
 * — AnythingLLM, a Rails app, a stray Vite server — has a front end that will
 * happily call `/api/...` on whichever process answered, and Node binding to
 * `[::1]` while the other app holds `127.0.0.1` is enough for the two to be
 * confused for each other on the same port number.
 *
 * So the request is answered here, before Vue is involved: a plain 404, no
 * render, no trace. Reserved prefixes only — `modules/reserved-slugs.ts` is the
 * matching guard that stops a subject folder ever being named one of these.
 */
export default defineEventHandler((event) => {
  const path = getRequestURL(event).pathname

  // One exception: Nuxt Icon serves icon data to the browser from
  // `/api/_nuxt_icon/<collection>`. Swallowing it here made every icon on the
  // site warn "failed to load icon" in dev.
  if (path.startsWith('/api/_nuxt_icon/')) {
    return
  }

  if (path === '/api' || path.startsWith('/api/')) {
    setResponseStatus(event, 404)
    return { statusCode: 404, message: 'Not found. This site has no API.' }
  }
})
