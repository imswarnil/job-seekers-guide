<script setup lang="ts">
import type { Course, CourseLevel } from '~/utils/courses'

const { courses } = useCourses()
const { courseProgress, resumeLesson } = useCourseProgress()

const title = 'Courses'
const description = 'The whole path, in order. Orientation first, foundations second, the job hunt taught with the same seriousness as everything before it.'

useSeoMeta({
  title,
  ogTitle: title,
  description,
  ogDescription: description
})

defineOgImage('Guide', { title, description, headline: 'Curriculum' })

const order: CourseLevel[] = ['orientation', 'foundation', 'applied', 'job-search']

const groups = computed(() => {
  const grouped = new Map<CourseLevel, Course[]>()

  for (const course of courses.value) {
    const level = course.level || 'foundation'
    grouped.set(level, [...(grouped.get(level) || []), course])
  }

  return order
    .filter(level => grouped.has(level))
    .map(level => ({ level, label: levelLabels[level], courses: grouped.get(level)! }))
})

// The one course everybody starts with, surfaced separately: orientation before
// choice is the whole reason the path is ordered rather than browsed.
const first = computed(() => courses.value.find(course => course.level === 'orientation'))
</script>

<template>
  <UContainer>
    <UPageHeader
      :title="title"
      :description="description"
      class="py-[50px]"
    />

    <UPageBody>
      <UPageCard
        v-if="first"
        variant="subtle"
        class="mb-10"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-6 justify-between">
          <div>
            <UBadge
              color="secondary"
              variant="subtle"
              label="Start here"
            />
            <h2 class="font-display text-xl font-semibold text-highlighted mt-3">
              {{ first.title }}
            </h2>
            <p class="text-muted mt-1 max-w-xl">
              {{ first.description }}
            </p>
          </div>

          <ClientOnly>
            <UButton
              :to="resumeLesson(first)?.path || first.path"
              :label="courseProgress(first).started ? 'Continue' : 'Begin'"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
              class="shrink-0"
            />

            <template #fallback>
              <UButton
                :to="first.path"
                label="Begin"
                trailing-icon="i-lucide-arrow-right"
                size="lg"
                class="shrink-0"
              />
            </template>
          </ClientOnly>
        </div>
      </UPageCard>

      <div
        v-for="group in groups"
        :key="group.level"
        class="mb-12"
      >
        <h2 class="font-display text-lg font-semibold text-highlighted mb-4">
          {{ group.label }}
        </h2>

        <UPageGrid>
          <CourseCard
            v-for="course in group.courses"
            :key="course.path"
            :course="course"
          />
        </UPageGrid>
      </div>

      <UPageCard
        variant="naked"
        title="The rest of the path"
        description="Your programming language, web development, four escalating projects, and the job hunt itself are outlined in the curriculum while the courses are written."
      >
        <template #footer>
          <UButton
            to="/docs/curriculum"
            label="Read the curriculum"
            variant="subtle"
            trailing-icon="i-lucide-arrow-right"
          />
        </template>
      </UPageCard>
    </UPageBody>
  </UContainer>
</template>
