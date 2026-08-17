<script setup lang="ts">
/**
 * ::compare — two panels side by side, for "the way most people do it" against
 * "the way that works".
 *
 * ```md
 * ::compare{caption="Both take six months. Only one is employable."}
 *   :::compare-side{label="What most people do" verdict="wrong"}
 *   Learn four languages badly.
 *   :::
 *   :::compare-side{label="What works" verdict="right"}
 *   One language, taken all the way.
 *   :::
 * ::
 * ```
 */
defineProps<{
  label?: string
  caption?: string
}>()
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-columns-2"
  >
    <div class="grid sm:grid-cols-2 gap-3 items-stretch">
      <slot />
    </div>
  </DgmFigure>
</template>
