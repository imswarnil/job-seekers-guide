<script setup lang="ts">
definePageMeta({
  layout: 'course'
})

const route = useRoute()

const { data: page } = await useAsyncData(route.path, () => queryCollection('courses').path(route.path).first())
if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

const { course } = useCoursePlayer(() => route.path)

/** A course folder's `index.md` is the syllabus; everything deeper is a lesson. */
const isSyllabus = computed(() => route.path === course.value?.path)

const title = page.value.seo?.title || page.value.title
const description = page.value.seo?.description || page.value.description

useSeoMeta({
  title,
  ogTitle: title,
  description,
  ogDescription: description
})

defineOgImage('Guide', {
  title,
  description,
  headline: isSyllabus.value ? 'Course' : course.value?.title
})
</script>

<template>
  <UPage v-if="page">
    <CourseSyllabus
      v-if="isSyllabus"
      :page="page"
    />
    <CourseLesson
      v-else
      :page="page"
    />

    <template
      v-if="!isSyllabus && page?.body?.toc?.links?.length"
      #right
    >
      <UContentToc :links="page.body.toc.links" />
    </template>
  </UPage>
</template>
