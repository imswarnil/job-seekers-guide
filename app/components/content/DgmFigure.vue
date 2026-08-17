<script setup lang="ts">
/**
 * The frame every teaching diagram sits in: an optional label above, the
 * diagram, an optional caption below, and the horizontal scroll container that
 * keeps a wide diagram from making the whole page scroll sideways on a phone.
 *
 * Not authored directly from markdown — the diagram components wrap themselves
 * in it, which is what makes them all look like one family.
 */
defineProps<{
  label?: string
  caption?: string
  icon?: string
  /** Let the content scroll inside itself rather than wrap. */
  scroll?: boolean
}>()
</script>

<template>
  <figure class="dgm-figure not-prose">
    <figcaption
      v-if="label"
      class="dgm-label flex items-center gap-1.5 mb-3 font-medium uppercase tracking-wider"
    >
      <UIcon
        v-if="icon"
        :name="icon"
        class="size-3.5"
      />
      {{ label }}
    </figcaption>

    <div :class="scroll && 'dgm-scroll'">
      <slot />
    </div>

    <figcaption
      v-if="caption"
      class="dgm-caption"
    >
      {{ caption }}
    </figcaption>
  </figure>
</template>
