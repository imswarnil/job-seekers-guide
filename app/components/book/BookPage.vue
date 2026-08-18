<script setup lang="ts">
import type { StoryCollectionItem } from '@nuxt/content'

/**
 * One page. Paper, a running head, a folio, and one page's worth of chapter.
 *
 * This is also the component the reader measures with: the hidden probe that
 * counts how many pages a chapter comes to is a `BookPage` like any other, so
 * the measurement is taken through exactly the same padding, type size and
 * leading the reader will actually see. Measuring in a bespoke box and
 * rendering in this one is how a paginator ends up half a line out.
 */
const props = defineProps<{
  /** Absent means a blank leaf — a real book has them, at chapter breaks. */
  chapter?: StoryCollectionItem
  column?: number
  folio?: number
  side?: 'left' | 'right'
  /** Shown as the running head on left-hand pages. */
  book?: string
}>()

const flow = useTemplateRef<{ measure: () => { pages: number, overflowed: boolean } }>('flow')

function measure() {
  return flow.value?.measure() ?? { pages: 1, overflowed: false }
}

defineExpose({ measure })

const side = computed(() => props.side ?? 'right')
</script>

<template>
  <div
    class="paper"
    :data-side="side"
    :data-blank="chapter ? undefined : ''"
  >
    <template v-if="chapter">
      <p class="paper__head">
        {{ side === 'left' ? (book || '') : chapter.title }}
      </p>

      <div class="paper__clip">
        <BookFlow
          ref="flow"
          :chapter="chapter"
          :column="column ?? 0"
        />
      </div>

      <p class="paper__folio">
        {{ folio }}
      </p>
    </template>
  </div>
</template>

<style scoped>
.paper {
  position: relative;
  display: flex;
  flex-direction: column;
  width: var(--page-w);
  height: var(--page-h);
  padding: var(--page-pad-y) var(--page-pad-x);
  background: var(--book-paper);
  overflow: hidden;
}

/* Grain.
   Ruled gradients gave a screen-door pattern rather than paper — the eye finds
   the repeat immediately. Fractal noise does not repeat, and one inline filter
   costs nothing to fetch. Kept far below the threshold where it is a texture
   you notice and just above the one where the surface reads as flat. */
.paper::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)'/%3E%3C/svg%3E");
}

.dark .paper::before {
  opacity: 0.05;
  mix-blend-mode: screen;
}

/* The gutter. Paper curves into the binding, so the inner edge of each page
   darkens toward the spine — this is most of what makes a two-page spread read
   as one sheet of a book rather than as two cards side by side. */
.paper[data-side='left']::after,
.paper[data-side='right']::after {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  width: 3.25rem;
  pointer-events: none;
}

.paper[data-side='left']::after {
  right: 0;
  background: linear-gradient(to right, transparent, var(--book-gutter));
}

.paper[data-side='right']::after {
  left: 0;
  background: linear-gradient(to left, transparent, var(--book-gutter));
}

.paper__head {
  flex-shrink: 0;
  height: 1.5rem;
  font-size: 0.625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.paper[data-side='right'] .paper__head {
  text-align: right;
  /* Room for the ribbon, which hangs down the outer edge. */
  padding-right: 1.5rem;
}

.paper__clip {
  position: relative;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.paper__folio {
  flex-shrink: 0;
  height: 1.5rem;
  padding-top: 0.5rem;
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-dimmed);
  text-align: center;
}
</style>
