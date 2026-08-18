<script setup lang="ts">
import type { Lesson } from '~/utils/path'

defineProps<{
  lessons: Lesson[]
  /** Show a running number down the left. */
  numbered?: boolean
}>()

const { isComplete } = useProgress()
</script>

<template>
  <ul class="border border-default rounded-lg divide-y divide-default overflow-hidden">
    <li
      v-for="(lesson, index) in lessons"
      :key="lesson.path"
    >
      <NuxtLink
        :to="lesson.path"
        class="flex items-center gap-3.5 px-4 py-3 hover:bg-elevated/50 transition-colors"
      >
        <span
          v-if="numbered"
          class="text-xs text-dimmed tabular-nums w-5 shrink-0"
        >{{ index + 1 }}</span>

        <!-- Completion lives in localStorage, so the server draws the plain
             tile and the client swaps in the finished one. -->
        <ClientOnly>
          <LessonThumb
            :lesson="lesson"
            :complete="isComplete(lesson.path)"
          />

          <template #fallback>
            <LessonThumb :lesson="lesson" />
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
</template>
