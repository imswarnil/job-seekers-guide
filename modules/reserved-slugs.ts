import { readdirSync } from 'node:fs'
import { join } from 'node:path'
import { defineNuxtModule } from 'nuxt/kit'

/**
 * Subjects live at the root of the site, so `content/1.path/3.java/` becomes
 * `/java`. That is the whole point of the URL scheme, and it has one sharp edge:
 * a subject named after an existing page is shadowed by it. vue-router ranks
 * static routes above the catch-all, so `content/1.path/9.about/` would build
 * cleanly, deploy cleanly, and simply never be reachable.
 *
 * Fail the build instead. A loud error at authoring time costs a minute; a page
 * that silently does not exist costs however long it takes somebody to notice.
 */
const RESERVED = [
  'start',
  'path',
  // No page of its own any more, but still reserved: `/about` is a published
  // URL that redirects to the front page, so a subject there would never load.
  'about',
  'my-story',
  'series',
  'run',
  'faq',
  'changelog',
  'login',
  'signup',
  'search',
  'api',
  '_nuxt',
  '_ipx',
  '__nuxt_content',
  '__og-image__'
]

export default defineNuxtModule({
  meta: { name: 'reserved-slugs' },

  setup(_options, nuxt) {
    nuxt.hook('build:before', () => {
      const root = join(nuxt.options.rootDir, 'content/1.path')

      let entries: string[]
      try {
        entries = readdirSync(root, { withFileTypes: true })
          .filter(entry => entry.isDirectory())
          .map(entry => entry.name)
      } catch {
        // No path content yet — nothing to collide with.
        return
      }

      const clashes = entries
        .map(name => ({ name, slug: name.replace(/^\d+\./, '') }))
        .filter(entry => RESERVED.includes(entry.slug))

      if (clashes.length) {
        throw new Error(
          `[reserved-slugs] These subject folders would be shadowed by an existing page and never render:\n`
          + clashes.map(c => `  content/1.path/${c.name}  →  /${c.slug}`).join('\n')
          + `\n\nReserved slugs: ${RESERVED.join(', ')}\nRename the folder.`
        )
      }
    })
  }
})
