<script setup lang="ts">
/**
 * Every page of the learning path.
 *
 * Subjects, modules and lessons all live at the root of the site, so one
 * catch-all serves all three and depth decides which. vue-router ranks static
 * segments above this, which is why `/about` reaches `about.vue` and never gets
 * here — and why `modules/reserved-slugs.ts` fails the build if somebody names a
 * subject after one of those pages.
 *
 * The three-band layout (full-width hero, content + sidebar, full-width
 * pagination) lives in `player/PlayerShell.vue`; this file decides what goes in
 * each band.
 */
const route = useRoute()

const depth = computed(() => route.path.split('/').filter(Boolean).length)

const { data: page } = await useAsyncData(
  () => `path:${route.path}`,
  () => queryCollection('path').path(route.path).first(),
  { watch: [() => route.path] }
)

const { path, subject, module, lesson, previous, next, position, crossesSubject } = usePathPlayer(() => route.path)

// A module folder need not carry an `index.md`. When it does not, the page is
// still real — it is built from the navigation tree. Only 404 when the content
// query and the tree both come up empty.
const known = computed(() => {
  if (page.value) {
    return true
  }
  if (depth.value === 2) {
    return Boolean(module.value && module.value.path === route.path)
  }
  return false
})

if (!page.value && !known.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

const view = computed(() => depth.value >= 3 ? 'lesson' : depth.value === 2 ? 'module' : 'subject')

const title = computed(() =>
  page.value?.seo?.title || page.value?.title || module.value?.title || subject.value?.title || 'Start here'
)
const description = computed(() =>
  page.value?.seo?.description || page.value?.description || module.value?.description || subject.value?.description
)

const toc = computed(() => page.value?.body?.toc?.links || [])

/** The markdown file behind this page, for "edit this page". */
const file = computed(() => {
  const stem = page.value?.stem
  return stem ? `content/1.path/${stem}${page.value?.extension ? `.${page.value.extension}` : '.md'}` : undefined
})

usePageSeo({
  title,
  description,
  headline: computed(() => view.value === 'subject' ? 'Subject' : subject.value?.title),
  schema: computed(() => ({
    kind: view.value,
    page: page.value,
    path: path.value,
    subject: subject.value,
    module: module.value
  }))
})
</script>

<template>
  <PlayerShell :current="route.path">
    <template #hero>
      <LessonHeader
        v-if="view === 'lesson' && page"
        :title="page.title"
        :description="page.description"
        :lesson="lesson"
        :minutes="page.minutes"
        :kind="page.kind"
      />
      <ModuleHeader
        v-else-if="view === 'module'"
        :page="page || undefined"
      />
      <SubjectHeader
        v-else
        :page="page || undefined"
      />
    </template>

    <LessonPlayer
      v-if="view === 'lesson' && page"
      :page="page"
    />
    <ModuleOverview
      v-else-if="view === 'module'"
      :page="page || undefined"
    />
    <SubjectOverview
      v-else
      :page="page || undefined"
    />

    <template #aside>
      <!-- `shell-toc` is the hook PlayerShell uses to let the contents scroll
           on its own rather than pushing everything under it off screen. -->
      <div
        v-if="toc.length"
        class="shell-toc"
      >
        <UContentToc
          :links="toc"
          highlight
          class="!bg-transparent !border-0 !p-0 !static"
        />
      </div>

      <AdSlot
        placement="sidebar"
        variant="card"
      />

      <PageActions :file="file" />
    </template>

    <template
      v-if="view === 'lesson'"
      #pagination
    >
      <PlayerPagination
        :previous="previous"
        :next="next"
        :crosses-subject="crossesSubject"
        :position="position"
      />
    </template>
  </PlayerShell>
</template>
