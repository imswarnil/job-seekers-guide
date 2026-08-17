<script setup lang="ts">
import type { RunStage } from '~/utils/runners/visualise'

defineProps<{
  stages: RunStage[]
}>()

const tone = {
  pending: { ring: 'border-default', text: 'text-dimmed', icon: 'opacity-40' },
  running: { ring: 'border-primary', text: 'text-primary', icon: 'animate-pulse' },
  ok: { ring: 'border-[color:var(--dgm-good)]', text: 'text-highlighted', icon: '' },
  failed: { ring: 'border-[color:var(--dgm-bad)]', text: 'text-highlighted', icon: '' },
  skipped: { ring: 'border-dashed border-default', text: 'text-dimmed', icon: 'opacity-30' }
} as const
</script>

<template>
  <ol class="pipeline dgm-scroll">
    <li
      v-for="(item, index) in stages"
      :key="item.id"
      class="pipeline__stage"
      :data-status="item.status"
      :style="{ '--i': index }"
    >
      <div
        class="pipeline__box dgm-box"
        :class="tone[item.status].ring"
      >
        <div class="flex items-center gap-2">
          <UIcon
            :name="item.status === 'failed' ? 'i-lucide-circle-x' : item.status === 'skipped' ? 'i-lucide-circle-slash' : item.icon"
            class="size-4 shrink-0"
            :class="[
              tone[item.status].icon,
              item.status === 'failed' ? 'text-[color:var(--dgm-bad)]'
              : item.status === 'ok' ? 'text-[color:var(--dgm-good)]' : 'text-muted'
            ]"
          />
          <span
            class="text-sm font-medium truncate"
            :class="tone[item.status].text"
          >{{ item.label }}</span>

          <!-- Measured or parsed, said out loud. A visualiser that mixes numbers
               the engine produced with numbers it guessed is worse than none. -->
          <UIcon
            v-if="item.source === 'parsed'"
            name="i-lucide-scan-text"
            class="size-3 text-dimmed ms-auto shrink-0"
            title="Read from your source, not measured at runtime"
          />
        </div>

        <p class="dgm-label mt-1.5 line-clamp-2">
          {{ item.hint }}
        </p>

        <div
          v-if="item.metric || item.ms !== undefined"
          class="mt-2.5 flex items-baseline gap-1.5 flex-wrap"
        >
          <span
            v-if="item.metric"
            class="font-display text-lg font-bold text-highlighted tabular-nums"
          >{{ item.metric }}</span>
          <span
            v-if="item.metricLabel"
            class="text-xs text-muted"
          >{{ item.metricLabel }}</span>
          <span
            v-if="item.ms !== undefined"
            class="text-xs text-dimmed tabular-nums ms-auto"
          >{{ item.ms }} ms</span>
        </div>

        <p
          v-if="item.detail"
          class="mt-2 text-xs whitespace-pre-wrap dgm-mono"
          :class="item.status === 'failed' ? 'text-[color:var(--dgm-bad)]' : 'text-dimmed'"
        >
          {{ item.detail }}
        </p>
      </div>

      <UIcon
        name="i-lucide-chevron-right"
        class="pipeline__arrow size-4 text-dimmed"
      />
    </li>
  </ol>
</template>

<style scoped>
.pipeline {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .pipeline {
    flex-direction: row;
    align-items: stretch;
  }
}

.pipeline__stage {
  position: relative;
  flex: 1 1 0;
  min-width: 0;
  display: flex;
}

.pipeline__box {
  padding: 0.75rem 0.875rem;
  width: 100%;
  transition:
    border-color var(--dgm-t-base) var(--dgm-ease),
    opacity var(--dgm-t-base) var(--dgm-ease);
}

/* A stage that never ran is drawn dashed and faded rather than hidden, because
   "the JVM never started" is the most useful thing on the screen when a program
   fails to compile. */
.pipeline__stage[data-status='skipped'] .pipeline__box {
  opacity: 0.6;
}

.pipeline__arrow {
  position: absolute;
  left: 50%;
  bottom: calc(0.75rem * -1);
  transform: translate(-50%, 50%) rotate(90deg);
}

.pipeline__stage:last-child .pipeline__arrow {
  display: none;
}

@media (min-width: 768px) {
  .pipeline__arrow {
    left: auto;
    bottom: auto;
    top: 50%;
    right: calc(0.75rem * -1);
    transform: translate(50%, -50%);
  }
}
</style>
