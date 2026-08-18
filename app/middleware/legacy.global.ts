/**
 * Legacy URLs, redirected in the browser.
 *
 * There are three redirect mechanisms in this repo and they exist for different
 * hosts, which is worth stating plainly because it looks like duplication:
 *
 *   · `routeRules` in nuxt.config.ts — real 301s, on a host that runs Nitro.
 *   · `public/_redirects` — real 301s on Cloudflare, and the only one of the
 *     three that can express the `/courses/*` → `/*` splat server-side.
 *   · this file — GitHub Pages, which honours neither of the above.
 *
 * GitHub Pages serves `404.html` for any path it has no file for, and it leaves
 * the requested URL in the address bar. So the app boots, this middleware sees
 * where the reader actually asked to go, and sends them on. It is a client-side
 * redirect rather than a 301, which is the correct trade for a set of URLs that
 * were never published under this domain — they are insurance for old links, not
 * a live migration.
 */

/** Exact matches, checked first. */
const exact: Record<string, string> = {
  '/path': '/start',
  '/my-story': '/series/read',
  '/courses': '/start',
  '/docs': '/about',
  '/blog': '/changelog'
}

/**
 * Prefix rules. The first match wins, so the specific `/docs/...` sections are
 * listed before the catch-all `/docs/`.
 */
const prefixes: [string, string | ((rest: string) => string)][] = [
  // A course lesson kept its whole shape when courses became the path; only the
  // section prefix was dropped. `/courses/java/collections/generics` → `/java/…`
  ['/courses/', rest => `/${rest}`],
  ['/docs/getting-started/', '/about'],
  ['/docs/curriculum/', '/start'],
  ['/docs/help/', '/faq'],
  ['/docs/authoring/', '/about'],
  ['/docs/', '/about'],
  ['/blog/', '/changelog']
]

export default defineNuxtRouteMiddleware((to) => {
  const target = exact[to.path]
  if (target) {
    return navigateTo(target, { redirectCode: 301, replace: true })
  }

  for (const [prefix, destination] of prefixes) {
    if (to.path.startsWith(prefix)) {
      const rest = to.path.slice(prefix.length)
      const next = typeof destination === 'function' ? destination(rest) : destination

      // A rule that resolves to where we already are would loop.
      if (next !== to.path) {
        return navigateTo(next, { redirectCode: 301, replace: true })
      }
    }
  }
})
