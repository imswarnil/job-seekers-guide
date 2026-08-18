<script setup lang="ts">
import type { StoryCollectionItem } from '@nuxt/content'

/**
 * A chapter, typeset.
 *
 * This is the part that makes the thing a book rather than a slideshow. The
 * whole chapter goes into one CSS multi-column box that is exactly one page
 * wide and exactly one page tall; the browser then does what a typesetter does
 * and breaks the prose into page-sized columns, laid out to the right of each
 * other and overflowing the box. Showing page *n* is translating the box left
 * by *n* pages and clipping.
 *
 * The consequence worth knowing: page count is not a property of the content,
 * it is a property of the content *at this size*. Resize the window and the
 * book repaginates, exactly like changing the type size on an e-reader.
 */
const props = defineProps<{
  chapter: StoryCollectionItem
  /** Which page of this chapter to show. */
  column?: number
}>()

const flow = useTemplateRef<HTMLElement>('flow')

/**
 * How many pages this chapter came to.
 *
 * `scrollWidth` spans the overflow columns, so the count falls out of the
 * arithmetic. `overflowed` is the safety net: if a browser ever clips
 * vertically instead of spilling sideways, the text past page one would vanish
 * silently — so we report it and the reader falls back to scrolling rather than
 * quietly eating half the story.
 */
function measure(): { pages: number, overflowed: boolean } {
  const el = flow.value
  if (!el) {
    return { pages: 1, overflowed: false }
  }

  const width = el.clientWidth
  if (!width) {
    return { pages: 1, overflowed: false }
  }

  const gap = Number.parseFloat(getComputedStyle(el).columnGap) || 0
  const pages = Math.max(1, Math.round((el.scrollWidth + gap) / (width + gap)))
  const overflowed = pages === 1 && el.scrollHeight > el.clientHeight + 4

  return { pages, overflowed }
}

defineExpose({ measure })

/* Percentages in `translateX` resolve against the element's own width, which is
   one page — so the gutter between columns has to be added back by hand. */
const shift = computed(() => `translateX(calc(${-(props.column ?? 0)} * (100% + var(--book-col-gap))))`)
</script>

<template>
  <div
    ref="flow"
    class="flow"
    :style="{ transform: shift }"
  >
    <!-- Rendered on every page of the chapter, not just the first. The opener
         is part of the flow, so pagination lands it in column zero by itself;
         hiding it on later pages would move every page break after it. -->
    <header class="flow__open">
      <p class="flow__eyebrow">
        <span>{{ chapter.chapter === 0 ? 'Prologue' : `Chapter ${chapter.chapter}` }}</span>
        <span
          v-if="chapter.year"
          class="flow__year"
        >{{ chapter.year }}</span>
      </p>

      <h2 class="flow__title">
        {{ chapter.title }}
      </h2>

      <p
        v-if="chapter.subtitle"
        class="flow__sub"
      >
        {{ chapter.subtitle }}
      </p>

      <p
        v-if="chapter.place"
        class="flow__place"
      >
        {{ chapter.place }}
      </p>

      <span class="flow__rule" />
    </header>

    <div class="flow__body">
      <ContentRenderer :value="chapter" />
    </div>
  </div>
</template>

<style scoped>
/* `column-count: 1` plus a definite height plus `column-fill: auto` is the
   whole trick: content that does not fit spills into overflow columns beside
   the box instead of being clipped off the bottom. */
.flow {
  /* Absolute rather than `height: 100%`: the multi-column box needs a
     definite height for `column-fill: auto` to break pages at all, and
     `inset: 0` against a positioned parent is the one way to get that
     without depending on how the parent's own height was resolved. */
  position: absolute;
  inset: 0;
  column-count: 1;
  column-gap: var(--book-col-gap);
  column-fill: auto;
  /* Hyphenation matters more here than on a web page: a justified column one
     page wide has nowhere to hide a long word. */
  hyphens: auto;
  text-align: justify;
  text-justify: inter-word;
  font-family: var(--book-serif);
  font-size: var(--book-type-size);
  line-height: var(--book-leading);
  color: var(--ui-text-toned, var(--ui-text-muted));
  transition: transform 0ms;
}

.flow__open {
  /* A chapter opener should not be split across a page break. */
  break-inside: avoid;
  margin-bottom: 1.1em;
  text-align: left;
}

.flow__eyebrow {
  display: flex;
  align-items: baseline;
  gap: 0.75rem;
  font-size: 0.625rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.flow__year {
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.12em;
}

.flow__title {
  font-family: var(--font-display);
  font-size: clamp(1.35rem, 2.1vw, 1.75rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  line-height: 1.1;
  margin-top: 0.45rem;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
  hyphens: none;
}

.flow__sub {
  margin-top: 0.5rem;
  font-size: 0.9375rem;
  font-style: italic;
  color: var(--ui-text-muted);
  text-wrap: balance;
  hyphens: none;
}

.flow__place {
  margin-top: 0.4rem;
  font-size: 0.6875rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
}

.flow__rule {
  display: block;
  width: 2.25rem;
  height: 2px;
  margin-top: 0.85rem;
  background: var(--ui-primary);
  opacity: 0.7;
}

/* Book measure, not web measure. Widows and orphans are set because a page
   break is now a real thing that happens rather than a print-only fiction. */
.flow__body :deep(p) {
  margin: 0;
  orphans: 2;
  widows: 2;
}

/* Indent continuation paragraphs and set them tight to each other — the way a
   novel does it. The first paragraph after a heading stays flush. */
.flow__body :deep(p + p) {
  text-indent: 1.35em;
}

.flow__body :deep(h2),
.flow__body :deep(h3) {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.0625em;
  letter-spacing: -0.01em;
  color: var(--ui-text-highlighted);
  margin: 1.4em 0 0.5em;
  text-align: left;
  hyphens: none;
  break-after: avoid;
}

.flow__body :deep(blockquote) {
  margin: 1.2em 0;
  padding-left: 1.1em;
  border-left: 2px solid var(--ui-primary);
  font-style: italic;
  text-align: left;
  color: var(--ui-text-muted);
  break-inside: avoid;
}

.flow__body :deep(ul),
.flow__body :deep(ol) {
  margin: 1em 0;
  padding-left: 1.3em;
  text-align: left;
}

.flow__body :deep(li) {
  margin: 0.35em 0;
}

.flow__body :deep(img) {
  max-width: 100%;
  border-radius: var(--radius-sm);
  break-inside: avoid;
}

/* Anything embedded — a figure, a diagram, one of the illustration components —
   is scaled to the page it is on. A block that is taller than the text box can
   never be placed, so it lands on the next page and leaves a hole in this one. */
.flow__body :deep(figure) {
  margin: 1em 0;
  max-width: 100%;
  zoom: 0.82;
}

.flow__body :deep(hr) {
  /* A scene break, set as a book sets it. */
  border: 0;
  height: 1.6em;
  margin: 0.6em 0;
  break-inside: avoid;
}

.flow__body :deep(hr)::before {
  content: '❦';
  display: block;
  text-align: center;
  font-size: 0.8em;
  color: var(--ui-text-dimmed);
}

/* The dropped capital. It only ever lands on the chapter's first page because
   that is where the first paragraph is — pagination puts it there for free.
   The pseudo-element has to sit inside `:deep()`, or the scope attribute is
   inserted between the selector and `::first-letter` and the rule never
   matches at all. */
.flow__body :deep(> p:first-of-type::first-letter) {
  float: left;
  font-family: var(--font-display);
  font-size: 3.1em;
  line-height: 0.8;
  font-weight: 700;
  padding: 0.06em 0.09em 0 0;
  color: var(--ui-primary);
}
</style>
