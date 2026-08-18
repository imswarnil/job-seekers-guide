<script setup lang="ts">
import type { LearningPath } from '~/utils/path'

/**
 * The path, as one connected line.
 *
 * `/start` used to be a grid of cards grouped under stage headings, and a grid
 * says "here are eleven things, pick one" — which is exactly the decision the
 * visitor came here unable to make. A line says "here is the order". That is
 * the entire product claim, so it is worth drawing literally.
 *
 * The spine is continuous through the stage headings on purpose: the stages are
 * a way of reading the route, not breaks in it. The filled part of the spine is
 * how far the reader has actually come.
 */
const props = defineProps<{
  path: LearningPath
}>()

const { subjectProgress, pathProgress, isComplete } = useProgress()

const groups = computed(() => byStage(props.path))

/** Running index across the whole path, so numbering does not restart per stage. */
const positions = computed(() => {
  const map = new Map<string, number>()
  props.path.subjects.forEach((subject, index) => map.set(subject.path, index))
  return map
})

const overall = computed(() => pathProgress(props.path))

/** How far down the line the reader has got, as a percentage of its height. */
const filled = computed(() => overall.value.total
  ? Math.round((overall.value.completed / overall.value.total) * 100)
  : 0)

/** The first subject with anything left in it — where "you are here" belongs. */
const currentSubject = computed(() => props.path.subjects.find(subject =>
  subject.lessons.some(lesson => !isComplete(lesson.path))
)?.path)
</script>

<template>
  <div class="tl">
    <!-- One spine for the whole route, drawn behind everything, with the
         travelled part of it filled in. -->
    <span
      class="tl__spine"
      aria-hidden="true"
    />
    <ClientOnly>
      <span
        class="tl__spine tl__spine--done"
        :style="{ height: `${filled}%` }"
        aria-hidden="true"
      />
    </ClientOnly>

    <div
      v-for="group in groups"
      :key="group.label"
      class="tl__group"
    >
      <p class="tl__stage">
        <span
          class="tl__stage-marker"
          aria-hidden="true"
        />
        {{ group.label }}
      </p>

      <ol class="tl__list">
        <li
          v-for="subject in group.subjects"
          :key="subject.path"
          class="tl__item"
        >
          <span
            class="tl__node"
            :data-state="currentSubject === subject.path ? 'current' : undefined"
            aria-hidden="true"
          >
            <ClientOnly>
              <!-- Drawn over the node's own border, which is the ring's
                   track — two concentric circles would read as a target. -->
              <PlayerProgress
                :progress="subjectProgress(subject)"
                variant="ring"
                :size="42"
                class="absolute -inset-0.5"
              />
            </ClientOnly>
            <span class="tl__n">{{ (positions.get(subject.path) ?? 0) + 1 }}</span>
          </span>

          <NuxtLink
            :to="subject.path"
            class="tl__card"
          >
            <div class="tl__head">
              <UIcon
                :name="subject.icon || 'i-lucide-book-open'"
                class="size-4 text-primary shrink-0"
              />
              <h3 class="tl__title">
                {{ subject.title }}
              </h3>
              <UBadge
                v-if="subject.code"
                :label="subject.code"
                color="neutral"
                variant="subtle"
                size="sm"
                class="shrink-0"
              />
            </div>

            <p
              v-if="subject.description"
              class="tl__desc"
            >
              {{ subject.description }}
            </p>

            <p class="tl__meta">
              <span>{{ subject.lessons.length }} {{ subject.lessons.length === 1 ? 'lesson' : 'lessons' }}</span>
              <span v-if="subject.minutes">· {{ formatMinutes(subject.minutes) }}</span>
              <span v-if="subject.duration">· {{ subject.duration }}</span>
            </p>

            <!-- The first lesson, one click away. The commonest thing somebody
                 wants from a subject card is not the subject page. -->
            <span
              v-if="subject.lessons[0]"
              class="tl__first"
            >
              Start with “{{ subject.lessons[0].title }}”
              <UIcon
                name="i-lucide-arrow-right"
                class="size-3.5"
              />
            </span>
          </NuxtLink>
        </li>
      </ol>
    </div>

    <!-- The end of the line, so the route reads as having one. -->
    <div class="tl__end">
      <span
        class="tl__node tl__node--end"
        aria-hidden="true"
      >
        <UIcon
          name="i-lucide-flag"
          class="size-4"
        />
      </span>
      <div>
        <p class="tl__title">
          Employed
        </p>
        <p class="tl__desc">
          The job hunt is the last subject on the path, taught with the same
          seriousness as everything before it — not an afterthought once you
          "know enough".
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tl {
  position: relative;
  padding-left: 3.5rem;
}

.tl__spine {
  position: absolute;
  left: 1.1875rem;
  top: 0.5rem;
  bottom: 3rem;
  width: 2px;
  border-radius: 999px;
  background: var(--ui-border-accented);
}

.tl__spine--done {
  bottom: auto;
  background: linear-gradient(to bottom, var(--ui-primary), var(--ui-secondary));
  transition: height var(--dgm-t-slow) var(--dgm-ease);
}

.tl__group + .tl__group {
  margin-top: 2.5rem;
}

.tl__stage {
  position: relative;
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
  margin-bottom: 1rem;
}

/* The stage marker sits on the spine rather than beside it, so the headings
   read as points along the route instead of as section breaks in a page. */
.tl__stage-marker {
  position: absolute;
  left: -2.5rem;
  top: 0.05rem;
  width: 0.6rem;
  height: 0.6rem;
  border-radius: 2px;
  transform: rotate(45deg);
  background: var(--ui-bg);
  border: 2px solid var(--ui-border-accented);
}

.tl__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tl__item {
  position: relative;
}

.tl__node {
  position: absolute;
  left: -3.5rem;
  top: 0.9rem;
  width: 2.375rem;
  height: 2.375rem;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-border-accented);
}

.tl__node[data-state='current'] {
  border-color: var(--ui-primary);
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--ui-primary) 14%, transparent);
}

.tl__n {
  font-family: var(--font-display);
  font-size: 0.8125rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-muted);
}

.tl__node[data-state='current'] .tl__n {
  color: var(--ui-primary);
}

.tl__card {
  display: block;
  padding: 1rem 1.15rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    background-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.tl__card:hover {
  border-color: var(--ui-border-accented);
  background: var(--ui-bg-elevated);
  transform: translateX(2px);
}

.tl__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tl__title {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
}

.tl__desc {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  text-wrap: pretty;
}

.tl__meta {
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  margin-top: 0.5rem;
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
  font-variant-numeric: tabular-nums;
}

.tl__first {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 0.7rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--ui-primary);
}

.tl__end {
  position: relative;
  margin-top: 2.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.tl__node--end {
  top: -0.15rem;
  border-color: var(--ui-secondary);
  color: var(--ui-secondary);
  background: color-mix(in oklab, var(--ui-secondary) 10%, var(--ui-bg));
}

@media (max-width: 639px) {
  .tl {
    padding-left: 2.75rem;
  }

  .tl__spine {
    left: 0.9375rem;
  }

  .tl__node {
    left: -2.75rem;
    width: 1.875rem;
    height: 1.875rem;
  }

  .tl__stage-marker {
    left: -2.15rem;
  }

  .tl__n {
    font-size: 0.6875rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .tl__card,
  .tl__spine--done {
    transition: none;
  }

  .tl__card:hover {
    transform: none;
  }
}
</style>
