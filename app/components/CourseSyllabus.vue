<script setup lang="ts">
import type { CoursesCollectionItem } from '@nuxt/content'

defineProps<{
  page: CoursesCollectionItem
}>()

const route = useRoute()

const { course } = useCoursePlayer(() => route.path)
const { courseProgress, resumeLesson } = useCourseProgress()

const progress = computed(() => courseProgress(course.value))
</script>

<template>
  <div>
    <UPageHeader
      :title="page.title"
      :description="page.description"
    >
      <template #headline>
        <div class="flex items-center gap-2 flex-wrap">
          <UBadge
            v-if="page.code"
            :label="page.code"
            color="neutral"
            variant="subtle"
          />
          <UBadge
            v-if="page.level"
            :label="levelLabels[page.level as CourseLevel]"
            color="secondary"
            variant="subtle"
          />
          <span
            v-if="page.duration"
            class="text-sm text-muted"
          >{{ page.duration }}</span>
        </div>
      </template>

      <template #links>
        <ClientOnly>
          <UButton
            :to="resumeLesson(course)?.path"
            :label="progress.finished ? 'Review from the start' : progress.started ? 'Continue' : 'Start the course'"
            trailing-icon="i-lucide-arrow-right"
            size="lg"
          />

          <template #fallback>
            <UButton
              :to="course?.lessons[0]?.path"
              label="Start the course"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
            />
          </template>
        </ClientOnly>
      </template>
    </UPageHeader>

    <UPageBody>
      <ClientOnly>
        <div
          v-if="progress.started"
          class="mb-8"
        >
          <div class="flex items-center justify-between text-sm text-muted mb-2">
            <span>{{ progress.completed }} of {{ progress.total }} lessons finished</span>
            <span class="tabular-nums">{{ progress.percent }}%</span>
          </div>
          <UProgress
            :model-value="progress.percent"
            :color="progress.finished ? 'success' : 'primary'"
          />
        </div>
      </ClientOnly>

      <div
        v-if="page.outcomes?.length"
        class="mb-10"
      >
        <h2 class="font-display text-lg font-semibold text-highlighted mb-3">
          By the end you can
        </h2>
        <ul class="space-y-2">
          <li
            v-for="outcome in page.outcomes"
            :key="outcome"
            class="flex items-start gap-2.5 text-muted"
          >
            <UIcon
              name="i-lucide-check"
              class="size-4 mt-1 text-secondary shrink-0"
            />
            <span>{{ outcome }}</span>
          </li>
        </ul>
      </div>

      <ContentRenderer
        v-if="page.body"
        :value="page"
      />

      <USeparator class="my-10" />

      <h2 class="font-display text-lg font-semibold text-highlighted mb-4">
        Contents
      </h2>

      <CourseContents :course="course" />
    </UPageBody>
  </div>
</template>
