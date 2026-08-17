import type { ContentNavigationItem } from '@nuxt/content'
import type { PageMeta } from '~/utils/path'
import { findModule, findSubject, toPath } from '~/utils/path'

/**
 * The learning path, fetched once in `app.vue` and provided to everything below
 * it. Every component that needs the curriculum reads the same tree, so the
 * player rail, the overview pages and the progress figures can never disagree.
 */
export function usePath() {
  const navigation = inject<Ref<ContentNavigationItem[] | null>>('path-navigation', ref(null))
  const pages = inject<Ref<PageMeta[] | null>>('path-pages', ref(null))

  const path = computed(() => toPath(navigation.value, pages.value))

  return { navigation, pages, path }
}

/**
 * Everything the player needs about where it currently is.
 *
 * The one line that matters is `lessons` — it indexes into the whole path, not
 * into the current subject. That is what makes "next" at the end of Operating
 * Systems land on the first lesson of Databases instead of a dead end.
 */
export function usePathPlayer(url: MaybeRefOrGetter<string>) {
  const { path } = usePath()

  const lessons = computed(() => path.value.lessons)
  const index = computed(() => lessons.value.findIndex(lesson => lesson.path === toValue(url)))

  const previous = computed(() => index.value > 0 ? lessons.value[index.value - 1] : undefined)
  const next = computed(() => index.value >= 0 ? lessons.value[index.value + 1] : undefined)

  return {
    path,
    lessons,
    index,
    subject: computed(() => findSubject(path.value, toValue(url))),
    module: computed(() => findModule(path.value, toValue(url))),
    lesson: computed(() => index.value >= 0 ? lessons.value[index.value] : undefined),
    previous,
    next,
    /** "Lesson 42 of 310" — position along the whole path, not the subject. */
    position: computed(() => ({ n: index.value + 1, total: lessons.value.length })),
    /** True when the next lesson starts a different subject, so the footer bar
     *  can say "Start Databases" rather than a bare lesson title. */
    crossesSubject: computed(() => {
      const here = lessons.value[index.value]
      return Boolean(here && next.value && here.subjectPath !== next.value.subjectPath)
    })
  }
}
