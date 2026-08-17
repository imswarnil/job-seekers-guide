<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps<{
  error: NuxtError
}>()

useHead({
  htmlAttrs: {
    lang: 'en'
  }
})

useSeoMeta({
  title: 'Page not found',
  description: 'We are sorry but this page could not be found.'
})

const { data: navigation } = await useAsyncData('navigation', () => queryCollectionNavigation('docs'), {
  transform: data => data.find(item => item.path === '/docs')?.children || []
})
const { data: coursesNavigation } = await useAsyncData('courses-navigation', () => queryCollectionNavigation('courses', ['description', 'icon', 'code', 'duration', 'level', 'minutes', 'kind']), {
  transform: data => data.find(item => item.path === '/courses')?.children || []
})

const { data: coursesPages } = await useAsyncData('courses-pages', () => queryCollection('courses')
  .select('path', 'title', 'description', 'icon', 'code', 'duration', 'level', 'minutes', 'kind')
  .all())

const { data: files } = useLazyAsyncData('search', () => queryCollectionSearchSections('docs'), {
  server: false
})

provide('navigation', navigation)
provide('courses-navigation', coursesNavigation)
provide('courses-pages', coursesPages)
</script>

<template>
  <UApp>
    <AppHeader />

    <UMain>
      <UContainer>
        <UPage>
          <UError :error="error" />
        </UPage>
      </UContainer>
    </UMain>

    <AppFooter />

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
