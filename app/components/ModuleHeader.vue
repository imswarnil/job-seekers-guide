<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'

defineProps<{
  page?: PathCollectionItem
}>()

const route = useRoute()

const { subject, module } = usePathPlayer(() => route.path)
const { moduleProgress, isComplete } = useProgress()

const progress = computed(() => moduleProgress(module.value))

const start = computed(() =>
  module.value?.lessons.find(lesson => !isComplete(lesson.path)) || module.value?.lessons[0]
)
</script>

<template>
  <div class="lg:flex lg:items-start lg:justify-between lg:gap-12">
    <div class="min-w-0">
      <NuxtLink
        v-if="subject"
        :to="subject.path"
        class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors mb-3"
      >
        <UIcon
          name="i-lucide-arrow-left"
          class="size-3.5"
        />
        {{ subject.title }}
      </NuxtLink>

      <div class="flex items-center gap-2.5">
        <UIcon
          v-if="module?.icon"
          :name="module.icon"
          class="size-6 text-primary shrink-0"
        />
        <h1 class="font-display text-3xl xl:text-4xl font-bold text-highlighted tracking-tight text-balance">
          {{ page?.title || module?.title }}
        </h1>
      </div>

      <p
        v-if="page?.description || module?.description"
        class="mt-3 text-lg text-muted text-balance max-w-3xl"
      >
        {{ page?.description || module?.description }}
      </p>

      <div class="mt-4 flex items-center gap-3 text-sm text-dimmed">
        <span>{{ module?.lessons.length }} {{ module?.lessons.length === 1 ? 'lesson' : 'lessons' }}</span>
        <span v-if="module?.minutes">· {{ formatMinutes(module.minutes) }}</span>
      </div>
    </div>

    <div class="mt-6 lg:mt-0 shrink-0">
      <UButton
        v-if="start"
        :to="start.path"
        label="Start this module"
        trailing-icon="i-lucide-arrow-right"
        size="lg"
      />

      <ClientOnly>
        <PlayerProgress
          v-if="progress.started"
          :progress="progress"
          class="mt-4 lg:w-56"
        />
      </ClientOnly>
    </div>
  </div>
</template>
