<script setup lang="ts">
import { codeTraceKey } from '~/utils/diagram-keys'

/**
 * One step of a `::code-trace`. See `CodeTrace.vue` for the authoring example.
 *
 * Renders nothing itself — it registers its line range and caption with the
 * parent, which owns the display. That way the author writes the steps next to
 * the code they describe, and the reader sees them one at a time.
 */
const props = defineProps<{
  /** 1-based, e.g. "3" or "1-2" or "3,7-9". */
  lines: string
  caption?: string
}>()

const trace = inject(codeTraceKey, null)

const slots = useSlots()

trace?.register({ lines: props.lines, caption: props.caption })
</script>

<template>
  <!-- The default slot is the long-form version of the caption, kept in the
       document for readers and search engines even though the stepper shows the
       short one. -->
  <div
    v-if="slots.default"
    class="sr-only"
  >
    <slot />
  </div>
</template>
