<script setup lang="ts">
const colorMode = useColorMode()

const color = computed(() => colorMode.value === 'dark' ? '#0b0e1c' : 'white')

useHead({
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { key: 'theme-color', name: 'theme-color', content: color }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'en'
  }
})

useSeoMeta({
  titleTemplate: '%s — Job Seekers Guide',
  twitterCard: 'summary_large_image'
})

const { data: navigation } = await useAsyncData('navigation', () => queryCollectionNavigation('docs'), {
  transform: data => data.find(item => item.path === '/docs')?.children || []
})

// The courses tree is the curriculum. It is fetched once here and provided to
// the catalogue, the syllabus pages and the player, so all three read the same
// structure and none of them can disagree about what comes next.
const { data: coursesNavigation } = await useAsyncData('courses-navigation', () => queryCollectionNavigation('courses', ['description', 'icon', 'code', 'duration', 'level', 'minutes', 'kind']), {
  transform: data => data.find(item => item.path === '/courses')?.children || []
})

// The navigation tree carries structure but not front matter, so the pages are
// fetched flat alongside it and merged by path. Cheap — it is a few dozen rows
// of metadata with no bodies.
const { data: coursesPages } = await useAsyncData('courses-pages', () => queryCollection('courses')
  .select('path', 'title', 'description', 'icon', 'code', 'duration', 'level', 'minutes', 'kind')
  .all())

// Search spans the documentation and every lesson — somebody looking for
// "deadlock" does not care which of the two it lives in.
const { data: files } = useLazyAsyncData('search', async () => {
  const [docs, courses] = await Promise.all([
    queryCollectionSearchSections('docs'),
    queryCollectionSearchSections('courses')
  ])
  return [...docs, ...courses]
}, {
  server: false
})

provide('navigation', navigation)
provide('courses-navigation', coursesNavigation)
provide('courses-pages', coursesPages)
</script>

<template>
  <UApp>
    <NuxtLoadingIndicator />

    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>

    <ClientOnly>
      <LazyUContentSearch
        :files="files"
        :navigation="navigation"
        :links="navLinks"
        :fuse="{ resultLimit: 42 }"
      />
    </ClientOnly>
  </UApp>
</template>
