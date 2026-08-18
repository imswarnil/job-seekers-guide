<script setup lang="ts">
import type { Lesson } from '~/utils/path'

/**
 * The end of a lesson, given the width it deserves.
 *
 * Previously two small cards inside the reading column. The move to the next
 * lesson is the single most important action on the page — it is the mechanism
 * by which somebody finishes a curriculum — so it gets a full-width band and
 * looks like a decision rather than a footnote.
 */
defineProps<{
  previous?: Lesson
  next?: Lesson
  crossesSubject?: boolean
  position: { n: number, total: number }
}>()
</script>

<template>
  <nav
    class="pagination"
    aria-label="Lessons"
  >
    <NuxtLink
      v-if="previous"
      :to="previous.path"
      class="pagination__card pagination__card--prev"
    >
      <span class="pagination__label">
        <UIcon
          name="i-lucide-arrow-left"
          class="size-3.5"
        />
        Previous
      </span>
      <span class="pagination__title">{{ previous.title }}</span>
      <span class="pagination__meta">{{ previous.subjectTitle }}</span>
    </NuxtLink>
    <span v-else />

    <div class="pagination__centre">
      <p class="text-xs text-dimmed tabular-nums">
        Lesson {{ position.n }} of {{ position.total }}
      </p>
      <div class="pagination__bar">
        <div
          class="pagination__fill"
          :style="{ width: `${position.total ? (position.n / position.total) * 100 : 0}%` }"
        />
      </div>
      <UButton
        to="/start"
        label="All subjects"
        icon="i-lucide-route"
        color="neutral"
        variant="ghost"
        size="xs"
      />
    </div>

    <NuxtLink
      v-if="next"
      :to="next.path"
      class="pagination__card pagination__card--next"
    >
      <span class="pagination__label">
        <!-- Crossing into a new subject is the moment the path stops feeling
             like a course, so it says so rather than showing a bare title. -->
        {{ crossesSubject ? `Next subject · ${next.subjectTitle}` : 'Next' }}
        <UIcon
          name="i-lucide-arrow-right"
          class="size-3.5"
        />
      </span>
      <span class="pagination__title">{{ next.title }}</span>
      <span class="pagination__meta">{{ crossesSubject ? 'A new subject starts here' : next.moduleTitle }}</span>
    </NuxtLink>

    <NuxtLink
      v-else
      to="/start"
      class="pagination__card pagination__card--next"
    >
      <span class="pagination__label">
        Finished
        <UIcon
          name="i-lucide-flag"
          class="size-3.5"
        />
      </span>
      <span class="pagination__title">That is the end of the path so far</span>
      <span class="pagination__meta">More subjects are being written</span>
    </NuxtLink>
  </nav>
</template>

<style scoped>
.pagination {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .pagination {
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 2rem;
  }
}

.pagination__card {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 1rem 1.25rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.pagination__card:hover {
  border-color: var(--ui-primary);
  transform: translateY(-1px);
}

.pagination__card--next {
  text-align: right;
  align-items: flex-end;
}

.pagination__label {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--ui-text-dimmed);
}

.pagination__card:hover .pagination__label {
  color: var(--ui-primary);
}

.pagination__title {
  font-family: var(--font-display);
  font-weight: 600;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.pagination__meta {
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
}

.pagination__centre {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 10rem;
}

.pagination__bar {
  width: 8rem;
  height: 0.25rem;
  border-radius: 999px;
  background: var(--ui-bg-accented);
  overflow: hidden;
}

.pagination__fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(to right, var(--ui-primary), var(--ui-secondary));
  transition: width var(--dgm-t-base) var(--dgm-ease);
}
</style>
