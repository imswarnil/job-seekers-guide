<script setup lang="ts">
import type { Course } from '~/utils/courses'

defineProps<{
  course?: Course
}>()

const { isComplete } = useCourseProgress()
</script>

<template>
  <div
    v-if="course"
    class="space-y-8"
  >
    <section
      v-for="(module, index) in course.modules"
      :key="module.path"
    >
      <div class="flex items-center gap-2.5 mb-3">
        <span class="flex items-center justify-center size-6 rounded-md bg-elevated text-xs font-semibold text-muted tabular-nums shrink-0">
          {{ index + 1 }}
        </span>
        <h3 class="font-display font-semibold text-highlighted">
          {{ module.title }}
        </h3>
        <span
          v-if="module.minutes"
          class="text-xs text-dimmed"
        >{{ module.minutes }} min</span>
      </div>

      <ul class="border border-default rounded-lg divide-y divide-default overflow-hidden">
        <li
          v-for="lesson in module.lessons"
          :key="lesson.path"
        >
          <NuxtLink
            :to="lesson.path"
            class="flex items-center gap-3 px-4 py-3 hover:bg-elevated/50 transition-colors"
          >
            <ClientOnly>
              <UIcon
                :name="isComplete(lesson.path) ? 'i-lucide-circle-check' : lessonIcon(lesson)"
                class="size-4 shrink-0"
                :class="isComplete(lesson.path) ? 'text-success' : 'text-dimmed'"
              />

              <template #fallback>
                <UIcon
                  :name="lessonIcon(lesson)"
                  class="size-4 shrink-0 text-dimmed"
                />
              </template>
            </ClientOnly>

            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-highlighted truncate">
                {{ lesson.title }}
              </p>
              <p
                v-if="lesson.description"
                class="text-sm text-muted truncate"
              >
                {{ lesson.description }}
              </p>
            </div>

            <span
              v-if="lesson.minutes"
              class="text-xs text-dimmed shrink-0 tabular-nums"
            >{{ lesson.minutes }} min</span>
          </NuxtLink>
        </li>
      </ul>
    </section>
  </div>
</template>
