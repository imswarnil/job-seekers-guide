<script setup lang="ts">
/**
 * ::diagram — a hand-drawn or generated SVG, in the same frame as every other
 * diagram on the platform.
 *
 * `::flow`, `::memory`, `::timeline` and `::code-trace` cover the shapes that
 * recur. This is for the ones that do not: a hash collision, a packet's journey,
 * a graph traversal, the order a query is evaluated in. Without it those ship
 * unframed — no label, no caption, and no scroll container, so a wide drawing
 * makes the whole page scroll sideways on a phone.
 *
 * Write the SVG straight into the block:
 *
 * ```md
 * ::diagram{label="How the join matches rows" caption="One applicant, three applications."}
 *   <svg viewBox="0 0 480 200" role="img" aria-label="...">...</svg>
 * ::
 * ```
 *
 * The SVG is yours to draw, but the house rules still apply: one idea per
 * diagram, exactly one accent, every part labelled, and inherit colour from the
 * page via `currentColor` and the `--dgm-*` tokens rather than hard-coding hex,
 * so the drawing follows the reader's theme.
 *
 * Always give the `<svg>` a `viewBox` and an `aria-label` — without the first it
 * will not scale, and without the second it is invisible to a screen reader.
 */
withDefaults(defineProps<{
  label?: string
  caption?: string
  icon?: string
  /**
   * Wide drawings scroll inside their own container. On by default — a diagram
   * that overflows the page is worse than one the reader has to nudge.
   */
  scroll?: boolean
}>(), {
  scroll: true
})
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    :icon="icon"
    :scroll="scroll"
  >
    <div class="diagram__ink">
      <slot />
    </div>
  </DgmFigure>
</template>

<style scoped>
.diagram__ink {
  color: var(--dgm-ink);
}

/*
 * The defaults an author would otherwise repeat on every drawing. Anything the
 * SVG sets itself still wins.
 */
.diagram__ink :deep(svg) {
  max-width: 100%;
  height: auto;
  overflow: visible;
}

.diagram__ink :deep(text) {
  font-family: var(--ui-font-mono, ui-monospace, monospace);
  font-size: 11px;
  fill: var(--dgm-label);
}

.diagram__ink :deep([data-accent]) {
  color: var(--dgm-accent);
  stroke: var(--dgm-accent);
}

.diagram__ink :deep(text[data-accent]) {
  fill: var(--dgm-accent);
  stroke: none;
}
</style>
