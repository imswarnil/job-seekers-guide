<script setup lang="ts">
import { timelineKey } from '~/utils/diagram-keys'

/** One moment in a `::timeline`. See `Timeline.vue` for the example. */
const props = withDefaults(defineProps<{
  title?: string
  date?: string
  icon?: string
  state?: 'done' | 'current' | 'todo'
}>(), {
  state: 'todo'
})

const timeline = inject(timelineKey, null)
const index = timeline?.claim() ?? 0

const marker = computed(() => {
  if (props.icon) {
    return props.icon
  }
  return props.state === 'done'
    ? 'i-lucide-check'
    : props.state === 'current' ? 'i-lucide-dot' : 'i-lucide-circle'
})
</script>

<template>
  <li
    class="dgm-item timeline-item"
    :data-state="state"
    :style="{ '--dgm-i': index }"
  >
    <span class="timeline-item__marker">
      <UIcon
        :name="marker"
        class="size-3"
      />
    </span>

    <div class="min-w-0 pb-6">
      <div class="flex items-baseline gap-2 flex-wrap">
        <p
          v-if="title"
          class="font-medium text-sm text-highlighted"
        >
          {{ title }}
        </p>
        <span
          v-if="date"
          class="dgm-label tabular-nums"
        >{{ date }}</span>
      </div>

      <div class="mt-1 text-sm text-muted [&>*:last-child]:mb-0 [&>p]:mb-2">
        <slot />
      </div>
    </div>
  </li>
</template>

<style scoped>
.timeline-item {
  display: flex;
  gap: 0.875rem;
  position: relative;
}

.timeline-item:last-child > div {
  padding-bottom: 0;
}

.timeline-item__marker {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.375rem;
  height: 1.375rem;
  border-radius: 999px;
  border: 1px solid var(--dgm-box-border);
  background: var(--dgm-bg);
  color: var(--dgm-dim);
  margin-top: 0.0625rem;
}

.timeline-item[data-state='done'] .timeline-item__marker {
  border-color: var(--dgm-good);
  background: color-mix(in oklab, var(--dgm-good) 14%, var(--dgm-bg));
  color: var(--dgm-good);
}

.timeline-item[data-state='current'] .timeline-item__marker {
  border-color: var(--dgm-accent);
  background: var(--dgm-accent-soft);
  color: var(--dgm-accent);
}
</style>
