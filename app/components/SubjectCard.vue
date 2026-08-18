<script setup lang="ts">
import type { Subject } from '~/utils/path'
import { findTech } from '~/utils/tech'

const props = defineProps<{
  subject: Subject
  /** Position along the whole path. Drawn large, behind the card. */
  index?: number
}>()

const { subjectProgress } = useProgress()

const progress = computed(() => subjectProgress(props.subject))

/**
 * The technologies a subject is actually about, for the thumbnail strip.
 *
 * Matched by slug first — `/java` is Java — then by any tech name appearing in
 * the title, so a subject called "Databases in Practice" picks up SQL without
 * anybody maintaining a second mapping.
 */
const thumbs = computed(() => {
  const direct = findTech(props.subject.slug)
  if (direct) {
    return [props.subject.slug]
  }

  const haystack = `${props.subject.slug} ${props.subject.title}`.toLowerCase()
  const guesses: Record<string, string[]> = {
    'operating-systems': ['os', 'linux'],
    'databases': ['sql', 'mysql'],
    'how-the-industry-works': ['git', 'html'],
    'networks': ['networks'],
    'data-structures': ['dsa'],
    'web': ['html', 'css', 'javascript']
  }

  for (const [key, names] of Object.entries(guesses)) {
    if (haystack.includes(key.replace(/-/g, ' ')) || haystack.includes(key)) {
      return names
    }
  }

  return []
})

const meta = computed(() => [
  props.subject.duration,
  props.subject.lessons.length === 1 ? '1 lesson' : `${props.subject.lessons.length} lessons`
].filter(Boolean))

const number = computed(() => props.index === undefined ? '' : String(props.index + 1))
</script>

<template>
  <div class="subject">
    <!-- The number lives outside the card and is half hidden behind it. The
         order of the path is the entire product; as a small badge inside the
         card it was something nobody read. -->
    <span
      v-if="number"
      class="subject__number"
      aria-hidden="true"
    >{{ number }}</span>

    <NuxtLink
      :to="subject.path"
      class="subject__card"
    >
      <div class="flex items-start justify-between gap-3">
        <UIcon
          :name="subject.icon || 'i-lucide-book-open'"
          class="size-6 text-primary shrink-0"
        />

        <div
          v-if="thumbs.length"
          class="flex items-center gap-1.5"
        >
          <TechThumb
            v-for="name in thumbs"
            :key="name"
            :name="name"
            size="xs"
          />
        </div>
      </div>

      <h3 class="font-display font-semibold text-highlighted mt-3 text-balance">
        {{ subject.title }}
      </h3>

      <p class="text-sm text-muted mt-1.5 line-clamp-3">
        {{ subject.description }}
      </p>

      <div class="mt-auto pt-4">
        <div class="flex items-center gap-2 flex-wrap text-xs text-dimmed">
          <UBadge
            v-if="subject.code"
            :label="subject.code"
            color="neutral"
            variant="subtle"
            size="sm"
          />
          <span>{{ meta.join(' · ') }}</span>
        </div>

        <ClientOnly>
          <div
            v-if="progress.started"
            class="mt-3"
          >
            <UProgress
              :model-value="progress.percent"
              size="xs"
              :color="progress.finished ? 'success' : 'primary'"
            />
            <p class="text-xs text-muted mt-1.5">
              {{ progress.finished ? 'Finished' : `${progress.percent}% complete` }}
            </p>
          </div>
        </ClientOnly>
      </div>
    </NuxtLink>
  </div>
</template>

<style scoped>
.subject {
  position: relative;
  padding-left: 1.75rem;
}

@media (min-width: 640px) {
  .subject {
    padding-left: 2.25rem;
  }
}

.subject__number {
  position: absolute;
  left: 0;
  bottom: -0.35rem;
  z-index: 0;
  font-family: var(--font-display);
  font-size: 5.5rem;
  font-weight: 800;
  line-height: 0.8;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.06em;
  color: transparent;
  -webkit-text-stroke: 2px var(--ui-border-accented);
  transition: -webkit-text-stroke-color var(--dgm-t-base) var(--dgm-ease);
  pointer-events: none;
  user-select: none;
}

@media (min-width: 640px) {
  .subject__number {
    font-size: 7rem;
    -webkit-text-stroke-width: 2.5px;
  }
}

.subject:hover .subject__number {
  -webkit-text-stroke-color: var(--ui-primary);
}

.subject__card {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1.25rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease),
    box-shadow var(--dgm-t-fast) var(--dgm-ease);
}

.subject__card:hover {
  border-color: var(--ui-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}
</style>
