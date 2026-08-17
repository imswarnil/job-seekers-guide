import type { RouterConfig } from '@nuxt/schema'

/**
 * Moving between lessons should feel like turning a page, not like landing
 * halfway down the next one. Finishing a lesson happens at the *bottom* of the
 * page, so without this the reader arrives at the next lesson already scrolled
 * past its opening paragraph.
 */
export default <RouterConfig>{
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    if (to.hash) {
      return { el: to.hash, top: 96, behavior: 'smooth' }
    }

    return { top: 0 }
  }
}
