<script setup lang="ts">
import type { Lesson } from '~/utils/path'

/**
 * A lesson's picture, generated rather than drawn.
 *
 * Three hundred lessons cannot each have a commissioned thumbnail, and three
 * hundred identical grey icons tell the reader nothing about where they are. So
 * the tile is derived from the lesson's own position in the path: the subject
 * decides the colour family, the module decides the pattern, and the kind
 * decides the glyph. Lessons in a module look like siblings; lessons in a
 * different subject look like a different subject.
 *
 * Deterministic, and no randomness anywhere — the server render and the
 * hydrated one have to agree, and a tile that changes on reload reads as a bug.
 * Pure inline SVG, so there is nothing to download and nothing to lazy-load.
 */
const props = withDefaults(defineProps<{
  lesson: Lesson
  size?: 'sm' | 'md'
  /** Drawn as finished. */
  complete?: boolean
}>(), {
  size: 'sm'
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

/* Two brand families rather than a free hue: eleven subjects in eleven
   unrelated colours stops looking like one curriculum. */
const family = computed(() =>
  hash(props.lesson.subjectPath || props.lesson.path) % 2 === 0 ? 'indigo' : 'teal'
)

const pattern = computed(() => {
  const key = props.lesson.modulePath || props.lesson.subjectPath || props.lesson.path
  return ['grid', 'dots', 'rays', 'rings'][hash(key) % 4]
})

/** A quiet shift within the family, so sibling modules are not identical. */
const shade = computed(() => hash(props.lesson.modulePath || props.lesson.path) % 3)

const glyph = computed(() => lessonIcon(props.lesson))
</script>

<template>
  <span
    class="thumb"
    :data-size="size"
    :data-family="family"
    :data-shade="shade"
    :data-pattern="pattern"
    :data-complete="complete ? '' : undefined"
    aria-hidden="true"
  >
    <span class="thumb__pattern" />

    <UIcon
      :name="complete ? 'i-lucide-check' : glyph"
      class="thumb__glyph"
    />
  </span>
</template>

<style scoped>
.thumb {
  position: relative;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 2.75rem;
  aspect-ratio: 16 / 11;
  border-radius: var(--radius-xs);
  overflow: hidden;
  border: 1px solid var(--ui-border);
}

.thumb[data-size='md'] {
  width: 4.5rem;
  border-radius: var(--radius-sm);
}

.thumb[data-family='indigo'] {
  --a: var(--color-guide-600);
  --b: var(--color-guide-800);
  --ink: var(--color-guide-100);
}

.thumb[data-family='teal'] {
  --a: var(--color-spark-600);
  --b: var(--color-spark-800);
  --ink: var(--color-spark-100);
}

.thumb[data-shade='1'] {
  --a: color-mix(in oklab, var(--a) 78%, #000);
}

.thumb[data-shade='2'] {
  --a: color-mix(in oklab, var(--a) 82%, var(--color-guide-900));
}

.thumb[data-complete] {
  --a: var(--ui-success);
  --b: color-mix(in oklab, var(--ui-success) 55%, #000);
}

.thumb::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--a), var(--b));
}

.thumb__pattern {
  position: absolute;
  inset: 0;
  opacity: 0.3;
}

.thumb[data-pattern='grid'] .thumb__pattern {
  background-image:
    linear-gradient(to right, rgb(255 255 255 / 0.6) 1px, transparent 1px),
    linear-gradient(to bottom, rgb(255 255 255 / 0.6) 1px, transparent 1px);
  background-size: 7px 7px;
}

.thumb[data-pattern='dots'] .thumb__pattern {
  background-image: radial-gradient(rgb(255 255 255 / 0.75) 1px, transparent 1px);
  background-size: 6px 6px;
}

.thumb[data-pattern='rays'] .thumb__pattern {
  background-image: repeating-linear-gradient(
    -45deg,
    rgb(255 255 255 / 0.5) 0 1px,
    transparent 1px 6px
  );
}

.thumb[data-pattern='rings'] .thumb__pattern {
  background-image: repeating-radial-gradient(
    circle at 15% 110%,
    rgb(255 255 255 / 0.5) 0 1px,
    transparent 1px 8px
  );
}

.thumb__glyph {
  position: relative;
  width: 0.95rem;
  height: 0.95rem;
  color: var(--ink);
  filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.35));
}

.thumb[data-size='md'] .thumb__glyph {
  width: 1.35rem;
  height: 1.35rem;
}

.thumb[data-complete] .thumb__glyph {
  color: #fff;
}
</style>
