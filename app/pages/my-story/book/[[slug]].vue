<script setup lang="ts">
import { chapterDescription, chapterTitle } from '~/utils/story'

/**
 * The book.
 *
 * One route serves the whole thing: `/my-story/book` opens on the cover, and
 * `/my-story/book/<chapter>` opens on that chapter. Every chapter is therefore
 * a real URL that can be linked, shared and indexed — the reader is a surface
 * over the chapters, not a replacement for them — and turning a page rewrites
 * the address without reloading anything.
 */
definePageMeta({ layout: 'reader' })

const route = useRoute()

const { data: chapters } = await useAsyncData('book:chapters', () =>
  queryCollection('story').order('chapter', 'ASC').all()
)

if (!chapters.value?.length) {
  throw createError({ statusCode: 404, statusMessage: 'Story not found', fatal: true })
}

const slug = computed(() => {
  const value = route.params.slug
  return Array.isArray(value) ? value[0] : value
})

const opened = computed(() => chapters.value?.find(chapter => chapter.path.endsWith(`/${slug.value}`)))

if (slug.value && !opened.value) {
  throw createError({ statusCode: 404, statusMessage: 'Chapter not found', fatal: true })
}

const total = computed(() => chapters.value?.length ?? 0)

/**
 * Reading time, from the actual words.
 *
 * Nuxt Content stores the body as a tree of arrays where the text nodes are
 * bare strings, so counting has to walk it — stringifying the tree counts tag
 * names and prop keys and gives an answer that is confidently wrong.
 */
function textOf(node: unknown): string {
  if (typeof node === 'string') {
    return node
  }
  if (Array.isArray(node)) {
    return node.map(textOf).join(' ')
  }
  if (node && typeof node === 'object' && 'value' in node) {
    return textOf((node as { value: unknown }).value)
  }
  return ''
}

const minutes = computed(() => {
  const words = (chapters.value || [])
    .map(chapter => textOf(chapter.body).trim().split(/\s+/).filter(Boolean).length)
    .reduce((sum, count) => sum + count, 0)
  // 220 a minute, which is roughly how fast this reads out loud.
  return Math.max(5, Math.round(words / 220))
})

const bookTitle = 'I could not clear a single written round'

/* Titled like a book, so the chapter pages never compete with the episodes that
   cover the same events. */
const seoTitle = computed(() => opened.value
  ? chapterTitle(opened.value.chapter, opened.value.title, {
      place: opened.value.place,
      year: opened.value.year
    })
  : bookTitle)

const seoDescription = computed(() => opened.value
  ? chapterDescription({
      chapter: opened.value.chapter,
      total: total.value,
      description: opened.value.description,
      subtitle: opened.value.subtitle,
      place: opened.value.place,
      year: opened.value.year
    })
  : `Mahroni to Kota to Bangalore to Budapest, in ${total.value} chapters. Average student, no plan, no guidance and a family that could not fund one — with every rejection and every number left in.`)

usePageSeo({
  title: seoTitle,
  description: seoDescription,
  headline: opened.value ? bookTitle : 'The book'
})

const site = useSiteConfig()

useSchemaOrg([
  defineBreadcrumb({
    itemListElement: [
      { name: 'My story', item: '/my-story' },
      { name: 'The book', item: '/my-story/book' },
      ...(opened.value ? [{ name: opened.value.title, item: route.path }] : [])
    ]
  }),
  {
    '@type': 'Book',
    'name': bookTitle,
    'author': { '@type': 'Person', 'name': 'Swarnil Singhai' },
    'numberOfPages': total.value,
    'url': `${site.url}/my-story/book`,
    'inLanguage': 'en'
  }
])

/** Turning a page rewrites the address; it never pushes a history entry. */
function onChapter(chapter: { path: string } | undefined) {
  const to = chapter?.path ?? '/my-story/book'
  if (to !== route.path) {
    history.replaceState(history.state, '', to)
  }
}
</script>

<template>
  <BookReader
    :chapters="chapters ?? []"
    :minutes="minutes"
    :book="bookTitle"
    :start-at="opened?.path"
    exit-to="/my-story"
    @chapter="onChapter"
  />
</template>
