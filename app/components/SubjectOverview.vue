<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'

const props = defineProps<{
  page?: PathCollectionItem
}>()

/**
 * The body, cut into the pieces the repeating ads go between. Short overviews
 * fall under the paragraph floor in `autoAds.ts` and come back in one piece,
 * which is most of them.
 */
const chunks = useAutoAds(() => props.page?.body as never)

const route = useRoute()

const { path } = usePath()
const { subject } = usePathPlayer(() => route.path)
const { moduleProgress } = useProgress()

/** Prerequisite slugs resolved into real subjects, so they can be linked. */
const prerequisites = computed(() =>
  (props.page?.prerequisites || [])
    .map(slug => path.value.subjects.find(candidate => candidate.slug === slug))
    .filter(Boolean)
)

/**
 * Modules start open, and stay however the reader leaves them.
 *
 * Open by default because a collapsed subject page tells somebody nothing about
 * what they are about to learn — the contents *are* the pitch. Collapsible
 * because a subject with six modules and forty lessons is otherwise a wall.
 */
const closed = ref(new Set<string>())

function toggle(modulePath: string) {
  const next = new Set(closed.value)
  if (next.has(modulePath)) {
    next.delete(modulePath)
  } else {
    next.add(modulePath)
  }
  closed.value = next
}

const allClosed = computed(() => closed.value.size === (subject.value?.modules.length || 0))

function toggleAll() {
  closed.value = allClosed.value
    ? new Set()
    : new Set(subject.value?.modules.map(module => module.path) || [])
}
</script>

<template>
  <div>
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
      <ul class="grid sm:grid-cols-2 gap-x-8 gap-y-2">
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
      <template
        v-for="(chunk, index) in chunks"
        :key="index"
      >
        <ContentRenderer :value="{ ...page!, body: chunk }" />
        <AdSlot
          v-if="index < chunks.length - 1"
          placement="in-feed"
          class="ad-auto"
        />
      </template>
    </div>

    <AdSlot placement="in-article" />

    <USeparator class="my-10" />

    <div class="flex items-center justify-between gap-4 mb-5">
      <h2 class="font-display text-lg font-semibold text-highlighted">
        Contents
      </h2>

      <UButton
        v-if="subject?.modules.length"
        :label="allClosed ? 'Expand all' : 'Collapse all'"
        :icon="allClosed ? 'i-lucide-chevrons-down' : 'i-lucide-chevrons-up'"
        color="neutral"
        variant="ghost"
        size="xs"
        @click="toggleAll"
      />
    </div>

    <div class="space-y-4">
      <section
        v-for="(module, index) in subject?.modules"
        :key="module.path"
        class="module"
      >
        <div class="module__head">
          <button
            type="button"
            class="module__toggle"
            :aria-expanded="!closed.has(module.path)"
            @click="toggle(module.path)"
          >
            <span class="module__number">{{ index + 1 }}</span>

            <span class="min-w-0 flex-1 text-left">
              <NuxtLink
                :to="module.path"
                class="module__title"
                @click.stop
              >
                {{ module.title }}
              </NuxtLink>
              <span class="module__meta">
                {{ module.lessons.length }} {{ module.lessons.length === 1 ? 'lesson' : 'lessons' }}
                <span v-if="module.minutes">· {{ formatMinutes(module.minutes) }}</span>
              </span>
            </span>

            <ClientOnly>
              <PlayerProgress
                :progress="moduleProgress(module)"
                variant="ring"
                :size="22"
              />
            </ClientOnly>

            <UIcon
              name="i-lucide-chevron-down"
              class="size-4 text-dimmed shrink-0 transition-transform"
              :class="closed.has(module.path) && '-rotate-90'"
            />
          </button>
        </div>

        <div v-if="!closed.has(module.path)">
          <ModuleContents :lessons="module.lessons" />
        </div>
      </section>

      <ModuleContents
        v-if="!subject?.modules.length && subject?.lessons.length"
        :lessons="subject.lessons"
        numbered
      />
    </div>
  </div>
</template>

<style scoped>
.module__head {
  margin-bottom: 0.5rem;
}

.module__toggle {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.5rem;
  margin-inline: -0.5rem;
  border-radius: var(--radius-md);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.module__toggle:hover {
  background: var(--ui-bg-elevated);
}

.module__number {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: var(--radius-sm);
  background: var(--ui-bg-elevated);
  border: 1px solid var(--ui-border);
  font-size: 0.6875rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-muted);
}

.module__title {
  display: block;
  font-family: var(--font-display);
  font-weight: 600;
  color: var(--ui-text-highlighted);
  transition: color var(--dgm-t-fast) var(--dgm-ease);
}

.module__title:hover {
  color: var(--ui-primary);
}

.module__meta {
  display: block;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
}
</style>
