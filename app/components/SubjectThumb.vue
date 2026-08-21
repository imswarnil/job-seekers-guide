<script setup lang="ts">
import type { Subject } from '~/utils/path'
import { findTech } from '~/utils/tech'

/**
 * A subject's picture: its own mark, on a wash of its own colour.
 *
 * This used to draw a different SVG motif per section behind a small badge in
 * the corner, which meant every tile carried two marks — a hand-drawn one
 * saying which section, and an icon saying which subject — and neither was
 * large enough to read at a glance. One mark now, centred and big, with the
 * colour doing the categorising.
 *
 * The colour comes from the tech registry, so Java is Java's red and Supabase
 * is Supabase's green rather than everything being indigo. Subjects that are
 * ideas rather than products have no mark of their own and fall back to the
 * house pair, deterministically — the server render and the hydrated one have
 * to agree, and a tile that changes on reload reads as a bug.
 */
const props = withDefaults(defineProps<{
  subject: Subject
  /**
   * `sm` and `md` are the fixed-width tiles that sit beside a title. `card` is
   * the full-width 16:9 header of a subject card.
   */
  size?: 'sm' | 'md' | 'card'
  /** Drawn as finished, in the success colour with a tick over it. */
  complete?: boolean
}>(), {
  size: 'md'
})

/** Small, stable string hash. Only needs to be well-spread, not secure. */
function hash(input: string) {
  let h = 2166136261
  for (let i = 0; i < input.length; i++) {
    h ^= input.charCodeAt(i)
    h = Math.imul(h, 16777619)
  }
  return Math.abs(h)
}

const tech = computed(() => findTech(props.subject.slug))

/**
 * Two house colours rather than a free hue for the subjects with no mark of
 * their own: twenty-one subjects in twenty-one unrelated colours stops looking
 * like one curriculum.
 */
const accent = computed(() => {
  if (props.complete) {
    return 'var(--ui-success)'
  }
  if (tech.value) {
    return tech.value.color
  }
  return hash(props.subject.path) % 2 === 0
    ? 'var(--color-guide-500)'
    : 'var(--color-spark-500)'
})

const glyph = computed(() =>
  props.complete ? 'i-lucide-check' : (props.subject.icon || 'i-lucide-book-open')
)
</script>

<template>
  <span
    class="sthumb"
    :data-size="size"
    :data-complete="complete ? '' : undefined"
    :style="{ '--accent': accent }"
    aria-hidden="true"
  >
    <UIcon
      :name="glyph"
      class="sthumb__mark"
    />
  </span>
</template>

<style scoped>
.sthumb {
  position: relative;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 5.5rem;
  aspect-ratio: 16 / 10;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid color-mix(in oklab, var(--accent) 30%, transparent);
  background:
    linear-gradient(
      135deg,
      color-mix(in oklab, var(--accent) 88%, #000),
      color-mix(in oklab, var(--accent) 52%, #000)
    );
}

.sthumb[data-size='sm'] {
  width: 3.5rem;
  border-radius: var(--radius-sm);
}

/* The full-width header of a subject card: sixteen by nine, square across the
   bottom because the card continues underneath it. */
.sthumb[data-size='card'] {
  width: 100%;
  aspect-ratio: 16 / 9;
  border: 0;
  border-bottom: 1px solid color-mix(in oklab, var(--accent) 30%, transparent);
  border-radius: 0;
}

/* The brand colours are picked to sit on a light surface; on the dark theme
   several of them (MySQL, Oracle, SQLite) go to mud, so the wash lifts toward
   the page instead of down into black.
   `.dark` rather than `:global(.dark)` — scoped CSS puts the scope attribute on
   the last compound only, so this compiles to `.dark .sthumb[data-v-…]` and
   matches the class on `<html>`. Wrapping the ancestor in `:global()` does not
   survive the build. */
.dark .sthumb {
  background:
    linear-gradient(
      135deg,
      color-mix(in oklab, var(--accent) 72%, #000),
      color-mix(in oklab, var(--accent) 34%, #000)
    );
}

.sthumb__mark {
  width: 1.5rem;
  height: 1.5rem;
  color: #fff;
  filter: drop-shadow(0 2px 6px rgb(0 0 0 / 0.4));
  transition: transform var(--dgm-t-base) var(--dgm-ease);
}

.sthumb[data-size='sm'] .sthumb__mark {
  width: 1.1rem;
  height: 1.1rem;
}

.sthumb[data-size='card'] .sthumb__mark {
  width: 2.75rem;
  height: 2.75rem;
  filter: drop-shadow(0 2px 12px rgb(0 0 0 / 0.45));
}

@media (min-width: 640px) {
  .sthumb[data-size='card'] .sthumb__mark {
    width: 3.25rem;
    height: 3.25rem;
  }
}

/* On a phone the tile is competing with the title for the same row. It stays —
   a card with no picture is the wall of text this component exists to break —
   but it gives the words back most of the width. */
@media (max-width: 639px) {
  .sthumb {
    width: 3.75rem;
  }

  .sthumb[data-size='sm'] {
    width: 2.75rem;
  }

  .sthumb[data-size='card'] {
    width: 100%;
  }
}

@media (prefers-reduced-motion: reduce) {
  .sthumb__mark {
    transition: none;
  }
}
</style>
