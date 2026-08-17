<script setup lang="ts">
import type { Lesson } from '~/utils/path'

defineProps<{
  title: string
  description?: string
  lesson?: Lesson
  minutes?: number
  kind?: string
}>()
</script>

<template>
  <header class="mb-8">
    <div class="flex items-center gap-1.5 text-sm text-muted flex-wrap mb-3">
      <NuxtLink
        v-if="lesson?.subjectPath"
        :to="lesson.subjectPath"
        class="hover:text-primary transition-colors"
      >
        {{ lesson.subjectTitle }}
      </NuxtLink>

      <template v-if="lesson?.modulePath">
        <UIcon
          name="i-lucide-chevron-right"
          class="size-3.5 text-dimmed"
        />
        <NuxtLink
          :to="lesson.modulePath"
          class="hover:text-primary transition-colors"
        >
          {{ lesson.moduleTitle }}
        </NuxtLink>
      </template>
    </div>

    <h1 class="font-display text-3xl sm:text-4xl font-bold text-highlighted tracking-tight text-balance">
      {{ title }}
    </h1>

    <p
      v-if="description"
      class="mt-3 text-lg text-muted text-balance"
    >
      {{ description }}
    </p>

    <div
      v-if="minutes || kind"
      class="mt-4 flex items-center gap-2 flex-wrap"
    >
      <UBadge
        v-if="kind && kind !== 'lesson'"
        :label="kind"
        :icon="lessonIcon({ kind })"
        color="secondary"
        variant="subtle"
        size="sm"
        class="capitalize"
      />
      <span
        v-if="minutes"
        class="inline-flex items-center gap-1.5 text-sm text-dimmed"
      >
        <UIcon
          name="i-lucide-clock"
          class="size-3.5"
        />
        {{ minutes }} min read
      </span>
    </div>
  </header>
</template>
