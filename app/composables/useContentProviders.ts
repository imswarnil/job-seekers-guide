import type { PageMeta } from '~/utils/path'

/**
 * The one place the learning path is fetched.
 *
 * `app.vue` and `error.vue` both need the whole tree, and when this was written
 * out twice they drifted — the error page was searching a collection the rest of
 * the site had stopped using. One function, called from both.
 */
export async function useProvideContent() {
  // Both fetches are started before either is awaited. Awaiting the first would
  // lose the Nuxt instance for everything after it — and it fetches them in
  // parallel, which is what you wanted anyway.

  // The tree is the curriculum: structure, titles and icons, in path order.
  const navigationData = useAsyncData(
    'path-navigation',
    () => queryCollectionNavigation('path', ['description', 'icon', 'code', 'duration', 'stage', 'minutes', 'kind'])
  )

  // The tree carries structure but not front matter, so the pages are fetched
  // flat alongside it and merged by path. Cheap — a few hundred rows of
  // metadata with no bodies.
  const pagesData = useAsyncData(
    'path-pages',
    () => queryCollection('path')
      .select('path', 'title', 'description', 'icon', 'code', 'duration', 'stage', 'minutes', 'kind')
      .all() as Promise<PageMeta[]>
  )

  // Search spans every lesson and the standalone pages — somebody looking for
  // "deadlock" does not care which part of the site it lives in.
  const { data: files } = useLazyAsyncData('search', async () => {
    const [lessons, pages] = await Promise.all([
      queryCollectionSearchSections('path'),
      queryCollectionSearchSections('pages')
    ])
    return [...lessons, ...pages]
  }, { server: false })

  // Provide before awaiting. `provide` needs the component instance, and the
  // instance is gone on the far side of an `await` — the refs are what is being
  // shared, so their contents filling in later is exactly the intended
  // behaviour, not a race.
  const navigation = navigationData.data
  const pages = pagesData.data

  provide('path-navigation', navigation)
  provide('path-pages', pages)

  await Promise.all([navigationData, pagesData])

  return { navigation, pages, files }
}
