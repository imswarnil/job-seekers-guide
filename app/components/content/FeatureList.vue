<script setup lang="ts">
/**
 * ::feature-list — a grid of small labelled points. For "the four things a
 * process owns", "what a code review is actually looking for".
 *
 * ```md
 * ::feature-list{columns="2"}
 *   :::feature{icon="i-lucide-cpu" title="Its own memory"}
 *   One process cannot read another's address space.
 *   :::
 *   :::feature{icon="i-lucide-file" title="Its own file handles"}
 *   Closing a file in one does not close it in another.
 *   :::
 * ::
 * ```
 */
withDefaults(defineProps<{
  columns?: '1' | '2' | '3' | 1 | 2 | 3
  label?: string
}>(), {
  columns: 2
})
</script>

<template>
  <DgmFigure
    :label="label"
    icon="i-lucide-layout-grid"
  >
    <div
      class="grid gap-3"
      :class="{
        'sm:grid-cols-2': String(columns) === '2',
        'sm:grid-cols-2 lg:grid-cols-3': String(columns) === '3'
      }"
    >
      <slot />
    </div>
  </DgmFigure>
</template>
