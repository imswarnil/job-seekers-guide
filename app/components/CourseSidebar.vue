<script setup lang="ts">
import type { Course } from '~/utils/courses'

const props = defineProps<{
  course?: Course
  /** Path of the lesson currently open, so it can be highlighted. */
  current?: string
}>()

const { courseProgress, isComplete } = useCourseProgress()

const progress = computed(() => courseProgress(props.course))
</script>

<template>
  <nav v-if="course">
    <NuxtLink
      :to="course.path"
      class="flex items-center gap-2.5 group"
    >
      <UIcon
        :name="course.icon || 'i-lucide-graduation-cap'"
        class="size-5 text-primary shrink-0"
      />
      <span class="font-display font-semibold text-highlighted group-hover:text-primary transition-colors">
        {{ course.title }}
      </span>
    </NuxtLink>

    <ClientOnly>
      <div class="mt-4">
        <div class="flex items-center justify-between text-xs text-muted mb-1.5">
          <span>{{ progress.completed }} of {{ progress.total }} lessons</span>
          <span class="tabular-nums">{{ progress.percent }}%</span>
        </div>
        <UProgress
          :model-value="progress.percent"
          size="sm"
          :color="progress.finished ? 'success' : 'primary'"
        />
      </div>
    </ClientOnly>

    <USeparator class="my-5" />

    <div class="space-y-6">
      <div
        v-for="module in course.modules"
        :key="module.path"
      >
        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-dimmed mb-2">
          <UIcon
            v-if="module.icon"
            :name="module.icon"
            class="size-3.5"
          />
          {{ module.title }}
        </p>

        <ul class="space-y-px">
          <li
            v-for="lesson in module.lessons"
            :key="lesson.path"
          >
            <NuxtLink
              :to="lesson.path"
              class="flex items-start gap-2 rounded-md px-2 py-1.5 text-sm transition-colors"
              :class="lesson.path === current
                ? 'bg-primary/10 text-primary font-medium'
                : 'text-muted hover:text-highlighted hover:bg-elevated'"
            >
              <ClientOnly>
                <UIcon
                  :name="isComplete(lesson.path) ? 'i-lucide-circle-check' : lessonIcon(lesson)"
                  class="size-4 mt-0.5 shrink-0"
                  :class="isComplete(lesson.path) ? 'text-success' : 'opacity-60'"
                />

                <template #fallback>
                  <UIcon
                    :name="lessonIcon(lesson)"
                    class="size-4 mt-0.5 shrink-0 opacity-60"
                  />
                </template>
              </ClientOnly>

              <span>{{ lesson.title }}</span>
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
