<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'
import type { Stage } from '~/utils/path'

const props = defineProps<{
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
  return progress.value.started ? 'Continue' : 'Start here'
})

/** Prerequisite slugs resolved into real subjects, so they can be linked. */
const prerequisites = computed(() =>
  (props.page?.prerequisites || [])
    .map(slug => path.value.subjects.find(candidate => candidate.slug === slug))
    .filter(Boolean)
)
</script>

<template>
  <div>
    <header class="mb-8">
      <div class="flex items-center gap-2 flex-wrap mb-4">
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
      </div>

      <h1 class="font-display text-3xl sm:text-4xl font-bold text-highlighted tracking-tight text-balance">
        {{ page?.title || subject?.title }}
      </h1>

      <p
        v-if="page?.description || subject?.description"
        class="mt-3 text-lg text-muted text-balance"
      >
        {{ page?.description || subject?.description }}
      </p>

      <div class="mt-6 flex items-center gap-3 flex-wrap">
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
              label="Start here"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
            />
          </template>
        </ClientOnly>

        <UButton
          to="/path"
          label="See the whole path"
          color="neutral"
          variant="ghost"
          icon="i-lucide-route"
        />
      </div>
    </header>

    <ClientOnly>
      <PlayerProgress
        v-if="progress.started"
        :progress="progress"
        :label="`${progress.completed} of ${progress.total} lessons finished`"
        class="mb-8"
      />
    </ClientOnly>

    <UAlert
      v-if="prerequisites.length"
      icon="i-lucide-signpost"
      color="neutral"
      variant="subtle"
      title="Comes after"
      class="mb-8"
    >
      <template #description>
        <span class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
          <template
            v-for="(prerequisite, index) in prerequisites"
            :key="prerequisite!.path"
          >
            <span v-if="index">·</span>
            <NuxtLink
              :to="prerequisite!.path"
              class="text-primary hover:underline"
            >{{ prerequisite!.title }}</NuxtLink>
          </template>
          <span class="text-muted">— nothing is locked, but this is the order it was written in.</span>
        </span>
      </template>
    </UAlert>

    <div
      v-if="page?.outcomes?.length"
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

    <div
      v-if="page?.body"
      class="guide-prose"
    >
      <ContentRenderer :value="page" />
    </div>

    <USeparator class="my-10" />

    <h2 class="font-display text-lg font-semibold text-highlighted mb-4">
      Contents
    </h2>

    <div class="space-y-8">
      <section
        v-for="(module, index) in subject?.modules"
        :key="module.path"
      >
        <div class="flex items-center gap-2.5 mb-3">
          <span class="flex items-center justify-center size-6 rounded-md bg-elevated text-xs font-semibold text-muted tabular-nums shrink-0">
            {{ index + 1 }}
          </span>
          <NuxtLink
            :to="module.path"
            class="font-display font-semibold text-highlighted hover:text-primary transition-colors"
          >
            {{ module.title }}
          </NuxtLink>
          <span
            v-if="module.minutes"
            class="text-xs text-dimmed"
          >{{ formatMinutes(module.minutes) }}</span>
        </div>

        <ModuleContents :lessons="module.lessons" />
      </section>

      <ModuleContents
        v-if="!subject?.modules.length && subject?.lessons.length"
        :lessons="subject.lessons"
        numbered
      />
    </div>
  </div>
</template>
