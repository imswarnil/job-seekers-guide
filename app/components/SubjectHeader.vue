<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'
import type { Stage } from '~/utils/path'

defineProps<{
  page?: PathCollectionItem
}>()

const route = useRoute()

const { path } = usePath()
const { subject } = usePathPlayer(() => route.path)
const { subjectProgress, resume } = useProgress()

const progress = computed(() => subjectProgress(subject.value))

const resumeTo = computed(() => resume(path.value, subject.value)?.path || subject.value?.lessons[0]?.path)
const resumeLabel = computed(() => {
  if (progress.value.finished) {
    return 'Review from the start'
  }
  return progress.value.started ? 'Continue' : 'Start this subject'
})
</script>

<template>
  <div class="lg:flex lg:items-start lg:justify-between lg:gap-12">
    <div class="min-w-0">
      <div class="flex items-center gap-2 flex-wrap mb-4">
        <TechThumb
          v-if="subject?.slug"
          :name="subject.slug"
          size="xs"
          class="hidden"
        />
        <UBadge
          v-if="page?.code"
          :label="page.code"
          color="neutral"
          variant="subtle"
        />
        <UBadge
          v-if="page?.stage"
          :label="stageLabels[page.stage as Stage]"
          color="secondary"
          variant="subtle"
        />
        <span
          v-if="page?.duration"
          class="text-sm text-muted"
        >{{ page.duration }}</span>
        <span
          v-if="subject?.minutes"
          class="text-sm text-dimmed"
        >· {{ formatMinutes(subject.minutes) }} of reading</span>
        <span
          v-if="subject?.lessons.length"
          class="text-sm text-dimmed"
        >· {{ subject.lessons.length }} lessons</span>
      </div>

      <h1 class="font-display text-3xl sm:text-4xl xl:text-5xl font-bold text-highlighted tracking-tight text-balance">
        {{ page?.title || subject?.title }}
      </h1>

      <p
        v-if="page?.description || subject?.description"
        class="mt-3 text-lg text-muted text-balance max-w-3xl"
      >
        {{ page?.description || subject?.description }}
      </p>
    </div>

    <div class="mt-6 lg:mt-0 shrink-0">
      <ClientOnly>
        <UButton
          :to="resumeTo"
          :label="resumeLabel"
          trailing-icon="i-lucide-arrow-right"
          size="lg"
        />

        <template #fallback>
          <UButton
            :to="subject?.lessons[0]?.path"
            label="Start this subject"
            trailing-icon="i-lucide-arrow-right"
            size="lg"
          />
        </template>
      </ClientOnly>

      <ClientOnly>
        <PlayerProgress
          v-if="progress.started"
          :progress="progress"
          :label="`${progress.completed} of ${progress.total} finished`"
          class="mt-4 lg:w-56"
        />
      </ClientOnly>
    </div>
  </div>
</template>
