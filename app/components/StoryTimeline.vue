<script setup lang="ts">
export interface StoryChapter {
  id: string
  label: string
  year?: string | number
}

const props = defineProps<{
  chapters: StoryChapter[]
}>()

defineEmits<{ navigate: [] }>()

/**
 * The story's spine, tracking where the reader is.
 *
 * It is a progress bar as much as a table of contents. The story is long and it
 * gets worse before it gets better, so the useful thing to show somebody in the
 * middle of the rejections is how much of the climb is already behind them.
 */
const active = ref(props.chapters[0]?.id)

let observer: IntersectionObserver | undefined

onMounted(() => {
  const headings = props.chapters
    .map(chapter => document.getElementById(chapter.id))
    .filter((el): el is HTMLElement => Boolean(el))

  if (!headings.length) {
    return
  }

  // The band is the top third of the viewport: a chapter counts as "the one you
  // are reading" once its heading has passed the top and before the next one
  // arrives, which is closer to how reading feels than a midpoint test.
  observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        active.value = entry.target.id
      }
    }
  }, { rootMargin: '-88px 0px -66% 0px', threshold: 0 })

  headings.forEach(heading => observer!.observe(heading))
})

onBeforeUnmount(() => observer?.disconnect())

const activeIndex = computed(() => props.chapters.findIndex(chapter => chapter.id === active.value))
const percent = computed(() =>
  props.chapters.length > 1
    ? Math.round((Math.max(0, activeIndex.value) / (props.chapters.length - 1)) * 100)
    : 0
)
</script>

<template>
  <nav
    class="story-timeline"
    aria-label="Chapters"
  >
    <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-4">
      The route
    </p>

    <ol class="story-timeline__list">
      <li
        v-for="(chapter, index) in chapters"
        :key="chapter.id"
        class="story-timeline__item"
        :data-state="index < activeIndex ? 'past' : index === activeIndex ? 'current' : 'ahead'"
      >
        <a
          :href="`#${chapter.id}`"
          class="story-timeline__link"
          @click="$emit('navigate')"
        >
          <span class="story-timeline__marker" />
          <span class="min-w-0">
            <span class="story-timeline__label">{{ chapter.label }}</span>
            <span
              v-if="chapter.year"
              class="story-timeline__year"
            >{{ chapter.year }}</span>
          </span>
        </a>
      </li>
    </ol>

    <p class="mt-5 text-xs text-dimmed tabular-nums">
      {{ percent }}% through the story
    </p>
  </nav>
</template>

<style scoped>
.story-timeline__list {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
}

/* One spine behind every marker, so the gaps between them never show through. */
.story-timeline__list::before {
  content: '';
  position: absolute;
  left: 4.5px;
  top: 10px;
  bottom: 10px;
  width: 1px;
  background: var(--ui-border);
}

.story-timeline__link {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.3rem 0;
  transition: color var(--dgm-t-fast) var(--dgm-ease);
}

.story-timeline__marker {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  width: 10px;
  height: 10px;
  margin-top: 0.3rem;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-border-accented);
  transition:
    background-color var(--dgm-t-fast) var(--dgm-ease),
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.story-timeline__label {
  display: block;
  font-size: 0.8125rem;
  line-height: 1.35;
  color: var(--ui-text-dimmed);
  transition: color var(--dgm-t-fast) var(--dgm-ease);
}

.story-timeline__year {
  display: block;
  font-size: 0.6875rem;
  color: var(--ui-text-dimmed);
  opacity: 0.7;
  font-variant-numeric: tabular-nums;
}

/* Read: filled in. The line behind you is solid. */
.story-timeline__item[data-state='past'] .story-timeline__marker {
  background: var(--ui-primary);
  border-color: var(--ui-primary);
}

.story-timeline__item[data-state='past'] .story-timeline__label {
  color: var(--ui-text-muted);
}

.story-timeline__item[data-state='current'] .story-timeline__marker {
  background: var(--ui-bg);
  border-color: var(--ui-primary);
  transform: scale(1.35);
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--ui-primary) 16%, transparent);
}

.story-timeline__item[data-state='current'] .story-timeline__label {
  color: var(--ui-text-highlighted);
  font-weight: 600;
}

.story-timeline__link:hover .story-timeline__label {
  color: var(--ui-text-highlighted);
}
</style>
