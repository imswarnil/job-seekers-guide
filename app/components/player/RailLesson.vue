<script setup lang="ts">
import type { Lesson } from '~/utils/path'

defineProps<{
  lesson: Lesson
  active?: boolean
}>()

defineEmits<{ navigate: [] }>()

const { isComplete } = useProgress()
</script>

<template>
  <NuxtLink
    :to="lesson.path"
    :data-active="active || undefined"
    class="group flex items-start gap-2.5 rounded-md px-2 py-1.5 text-sm transition-colors"
    :class="active
      ? 'bg-primary/10 text-primary font-medium'
      : 'text-muted hover:text-highlighted hover:bg-elevated'"
    @click="$emit('navigate')"
  >
    <!-- Completion lives in localStorage, so the server renders the kind icon
         and the client swaps in the tick. Keeping the link itself outside
         ClientOnly is what lets the prerender crawler find every lesson. -->
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

    <span class="flex-1 min-w-0">{{ lesson.title }}</span>

    <span
      v-if="lesson.minutes"
      class="text-xs text-dimmed tabular-nums shrink-0 mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
      :class="active && 'opacity-100'"
    >{{ lesson.minutes }}m</span>
  </NuxtLink>
</template>
