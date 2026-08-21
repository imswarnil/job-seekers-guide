<script setup lang="ts">
import type { ProgressSummary } from '~/composables/useProgress'

const props = withDefaults(defineProps<{
  progress: ProgressSummary
  /** `ring` for the compact rail summary, `bar` everywhere else. */
  variant?: 'bar' | 'ring'
  label?: string
  size?: number
  /**
   * Draw the unfilled part of the ring.
   *
   * Off where the ring is laid over something that already has a circular
   * border — the timeline node — because the track and the border are then two
   * concentric circles a pixel apart, which reads as a target rather than as
   * progress. With it off, the border *is* the track.
   */
  track?: boolean
}>(), {
  variant: 'bar',
  size: 22,
  track: true
})

const STROKE = 2.5

/** Radius that puts the stroke's outer edge exactly on the edge of the box, so
 *  a ring drawn over a bordered element lands on the border rather than beside
 *  it. */
const radius = computed(() => (props.size - STROKE) / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const offset = computed(() => circumference.value * (1 - props.progress.percent / 100))
</script>

<template>
  <div v-if="variant === 'ring'">
    <svg
      :width="size"
      :height="size"
      :viewBox="`0 0 ${size} ${size}`"
      class="-rotate-90 shrink-0"
      role="img"
      :aria-label="`${progress.percent}% complete`"
    >
      <circle
        v-if="track"
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        fill="none"
        stroke="currentColor"
        :stroke-width="STROKE"
        class="text-accented"
      />
      <circle
        v-if="progress.started"
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        fill="none"
        stroke="currentColor"
        :stroke-width="STROKE"
        stroke-linecap="round"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="offset"
        :class="progress.finished ? 'text-success' : 'text-primary'"
        :style="{ transition: `stroke-dashoffset var(--dgm-t-base) var(--dgm-ease)` }"
      />
    </svg>
  </div>

  <div v-else>
    <div class="flex items-center justify-between text-xs text-muted mb-1.5">
      <span>{{ label || `${progress.completed} of ${progress.total} lessons` }}</span>
      <span class="tabular-nums">{{ progress.percent }}%</span>
    </div>
    <UProgress
      :model-value="progress.percent"
      size="sm"
      :color="progress.finished ? 'success' : 'primary'"
    />
  </div>
</template>
