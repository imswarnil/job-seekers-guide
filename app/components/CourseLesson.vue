<script setup lang="ts">
import type { CoursesCollectionItem } from '@nuxt/content'

defineProps<{
  page: CoursesCollectionItem
}>()

const route = useRoute()

const { course, lesson, previous, next } = useCoursePlayer(() => route.path)
const { isComplete, setComplete, toggleComplete, markVisited } = useCourseProgress()

// Resume-where-you-left-off only works if somebody writes down where you were.
onMounted(() => {
  if (course.value) {
    markVisited(course.value.path, route.path)
  }
})

watch(() => route.path, (path) => {
  if (course.value) {
    markVisited(course.value.path, path)
  }
})

/** Finish the lesson and move on — the only button most readers will press. */
function completeAndContinue() {
  setComplete(route.path, true)

  if (next.value) {
    return navigateTo(next.value.path)
  }
  return navigateTo(course.value?.path || '/courses')
}
</script>

<template>
  <div>
    <UPageHeader
      :title="page.title"
      :description="page.description"
    >
      <template #headline>
        <div class="flex items-center gap-2 text-sm text-muted flex-wrap">
          <NuxtLink
            :to="course?.path"
            class="hover:text-primary transition-colors"
          >
            {{ course?.title }}
          </NuxtLink>
          <span v-if="lesson?.moduleTitle">/</span>
          <span v-if="lesson?.moduleTitle">{{ lesson.moduleTitle }}</span>
          <span v-if="page.minutes">· {{ page.minutes }} min</span>
        </div>
      </template>
    </UPageHeader>

    <UPageBody>
      <ContentRenderer
        v-if="page.body"
        :value="page"
      />

      <USeparator class="my-10" />

      <ClientOnly>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
          <UButton
            :label="isComplete(route.path) ? 'Finished' : 'Mark as finished'"
            :icon="isComplete(route.path) ? 'i-lucide-circle-check' : 'i-lucide-circle'"
            :color="isComplete(route.path) ? 'success' : 'neutral'"
            variant="subtle"
            @click="toggleComplete(route.path)"
          />

          <UButton
            :label="next ? 'Finish and continue' : 'Finish the course'"
            trailing-icon="i-lucide-arrow-right"
            @click="completeAndContinue"
          />
        </div>
      </ClientOnly>

      <div class="grid sm:grid-cols-2 gap-4 mt-8">
        <UPageCard
          v-if="previous"
          :to="previous.path"
          :title="previous.title"
          :description="previous.description"
          icon="i-lucide-arrow-left"
          variant="subtle"
          :ui="{ description: 'line-clamp-2' }"
        />
        <span v-else />

        <UPageCard
          v-if="next"
          :to="next.path"
          :title="next.title"
          :description="next.description"
          icon="i-lucide-arrow-right"
          variant="subtle"
          :ui="{ description: 'line-clamp-2' }"
        />
      </div>
    </UPageBody>
  </div>
</template>
