<script setup lang="ts">
import { findTech } from '~/utils/tech'

/**
 * A technology as a tile: its glyph on a wash of its own brand colour.
 *
 * Used on subject cards, in the story, and anywhere a lesson needs to say
 * "this bit is Java" without a sentence. The colour comes from the registry
 * rather than the site palette on purpose — these are other people's marks and
 * recolouring them to indigo would make them unrecognisable, which defeats the
 * point of using a logo at all.
 */
const props = withDefaults(defineProps<{
  name: string
  size?: 'xs' | 'sm' | 'md' | 'lg'
  /** Show the name beside the tile. */
  labelled?: boolean
  /** Square tile, or a pill with the label inside it. */
  variant?: 'tile' | 'pill'
}>(), {
  size: 'md',
  variant: 'tile'
})

const entry = computed(() => findTech(props.name))

const tileSizes = {
  xs: 'size-7 rounded-md',
  sm: 'size-9 rounded-lg',
  md: 'size-12 rounded-xl',
  lg: 'size-16 rounded-2xl'
}

const glyphSizes = {
  xs: 'size-3.5',
  sm: 'size-4.5',
  md: 'size-6',
  lg: 'size-8'
}
</script>

<template>
  <span
    v-if="entry"
    class="tech"
    :class="variant === 'pill' ? 'tech--pill' : 'inline-flex items-center gap-2.5'"
    :style="{ '--tech': entry.color }"
    :title="entry.note || entry.label"
  >
    <span
      class="tech__tile"
      :class="variant === 'pill' ? 'size-5 rounded-md' : tileSizes[size]"
    >
      <UIcon
        :name="entry.icon"
        :class="variant === 'pill' ? 'size-3' : glyphSizes[size]"
        class="tech__glyph"
      />
    </span>

    <span
      v-if="labelled || variant === 'pill'"
      class="tech__label"
    >{{ entry.label }}</span>
  </span>

  <!-- An unknown key is a typo in markdown, and silently rendering nothing is
       how a lesson ends up with a hole in it. -->
  <UBadge
    v-else
    :label="`Unknown tech: ${name}`"
    color="warning"
    variant="subtle"
    size="sm"
  />
</template>

<style scoped>
.tech__tile {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: color-mix(in oklab, var(--tech) 14%, transparent);
  border: 1px solid color-mix(in oklab, var(--tech) 26%, transparent);
  transition:
    background-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.tech__glyph {
  color: var(--tech);
}

/* The brand colours are picked to pass on a light surface; against a dark one
   several of them (MySQL, Oracle, SQLite) go muddy, so the wash carries more of
   the work and the glyph is lightened toward the page. */
/* `.dark` rather than `:global(.dark)`. Scoped CSS already puts the scope
   attribute on the last compound only, so `.dark .x` compiles to
   `.dark .x[data-v-…]` and matches the class on `<html>`. Wrapping the ancestor
   in `:global()` here does not survive the build — the descendant is dropped
   and the declarations land on `<html>` itself. */
.dark .tech__tile {
  background: color-mix(in oklab, var(--tech) 22%, transparent);
  border-color: color-mix(in oklab, var(--tech) 34%, transparent);
}

.dark .tech__glyph {
  color: color-mix(in oklab, var(--tech) 72%, white);
}

.tech:hover .tech__tile {
  background: color-mix(in oklab, var(--tech) 22%, transparent);
  transform: translateY(-1px);
}

.tech__label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--ui-text-highlighted);
}

.tech--pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.625rem 0.25rem 0.3rem;
  border-radius: 999px;
  border: 1px solid var(--ui-border);
  background: var(--ui-bg-elevated);
}

.tech--pill .tech__label {
  font-size: 0.8125rem;
}
</style>
