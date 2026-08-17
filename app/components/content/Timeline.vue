<script setup lang="ts">
import { timelineKey } from '~/utils/diagram-keys'

/**
 * ::timeline — an ordered list of moments with a connector down the side. For
 * "what the first six months look like", "the stages of an interview loop".
 *
 * ```md
 * ::timeline{label="A hiring loop"}
 *   :::timeline-item{title="Application" state="done" date="Week 0"}
 *   A recruiter reads it for nine seconds.
 *   :::
 *   :::timeline-item{title="Technical screen" state="current" date="Week 1"}
 *   One problem, forty-five minutes, thinking out loud.
 *   :::
 * ::
 * ```
 */
defineProps<{
  label?: string
  caption?: string
}>()

const { classes } = useReveal()

let cursor = 0
provide(timelineKey, { claim: () => cursor++ })
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-milestone"
  >
    <ol
      ref="reveal"
      class="timeline"
      :class="classes"
    >
      <slot />
    </ol>
  </DgmFigure>
</template>

<style scoped>
.timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
}

/* The spine, drawn once behind every item rather than per item, so the gaps
   between markers never show through. */
.timeline::before {
  content: '';
  position: absolute;
  left: 0.6875rem;
  top: 0.5rem;
  bottom: 0.5rem;
  width: 1px;
  background: var(--dgm-box-border);
}
</style>
