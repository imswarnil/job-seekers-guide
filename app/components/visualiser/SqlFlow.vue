<script setup lang="ts">
import type { FunnelStep } from '~/utils/runners/sql-analyse'

/**
 * The rows, moving.
 *
 * The funnel bars said *how many* survived each clause. This says *which* — a
 * grid of dots, one per row, where the ones a clause rejected visibly drop out
 * and the ones that survive carry on to the next stage.
 *
 * Everything here is still measured. The counts come from executing the query
 * with its later clauses stripped back; all this component decides is which
 * arbitrary dots to drop, because SQLite reports how many rows survived and not
 * which. That distinction is stated on the page rather than glossed over: the
 * quantity is a fact, the choice of dots is not.
 */
const props = defineProps<{
  funnel: FunnelStep[]
}>()

/** More than this and individual dots stop being countable anyway. */
const MAX_DOTS = 120

const stages = computed(() => props.funnel.map((step, index) => ({
  ...step,
  index,
  dots: Math.min(step.rows, MAX_DOTS),
  truncated: step.rows > MAX_DOTS
})))

const active = ref(0)
const playing = ref(false)

const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')

let timer: ReturnType<typeof setInterval> | undefined

function stop() {
  playing.value = false
  if (timer) {
    clearInterval(timer)
    timer = undefined
  }
}

function play() {
  stop()
  active.value = 0
  playing.value = true

  timer = setInterval(() => {
    if (active.value >= stages.value.length - 1) {
      stop()
      return
    }
    active.value++
  }, reduced.value ? 400 : 1100)
}

onBeforeUnmount(stop)

// Play once when the result first arrives, so the reader sees the mechanism
// without having to know there is a button.
watch(() => props.funnel, (next) => {
  if (next?.length) {
    active.value = 0
    nextTick(play)
  }
}, { immediate: true })

const current = computed(() => stages.value[active.value])
const previous = computed(() => active.value > 0 ? stages.value[active.value - 1] : undefined)
</script>

<template>
  <section
    v-if="stages.length"
    class="flow"
  >
    <header class="flex items-start justify-between gap-4 flex-wrap mb-4">
      <div>
        <h3 class="text-sm font-semibold text-highlighted">
          Watch the rows go through
        </h3>
        <p class="dgm-label mt-1 max-w-lg">
          One dot per row. The counts are measured; which particular dots fall
          away is arbitrary, because SQLite reports how many survived each
          clause and not which.
        </p>
      </div>

      <UButton
        :label="playing ? 'Playing' : 'Replay'"
        :icon="playing ? 'i-lucide-loader-circle' : 'i-lucide-rotate-ccw'"
        :class="playing && '[&_span:first-child]:animate-spin'"
        size="xs"
        color="neutral"
        variant="subtle"
        @click="play"
      />
    </header>

    <!-- The stage strip. Clicking a stage jumps to it, so it is a control as
         well as a progress indicator. -->
    <ol class="flow__stages">
      <li
        v-for="stage in stages"
        :key="stage.index"
      >
        <button
          type="button"
          class="flow__stage"
          :data-state="stage.index === active ? 'current' : stage.index < active ? 'past' : 'ahead'"
          @click="stop(); active = stage.index"
        >
          <span class="flow__stage-label">{{ stage.label }}</span>
          <span class="flow__stage-rows">{{ stage.rows }}</span>
        </button>
      </li>
    </ol>

    <div class="flow__field">
      <div class="flow__dots">
        <span
          v-for="n in (stages[0]?.dots || 0)"
          :key="n"
          class="flow__dot"
          :data-state="n <= (current?.dots || 0) ? 'kept' : 'dropped'"
          :style="{ '--i': n }"
        />
      </div>

      <p class="flow__caption">
        <template v-if="current">
          <code class="dgm-mono text-primary">{{ current.clause }}</code>
          <span v-if="previous && current.delta < 0">
            — dropped <strong class="text-[color:var(--dgm-bad)]">{{ Math.abs(current.delta) }}</strong>
            of {{ previous.rows }}, leaving <strong class="text-highlighted">{{ current.rows }}</strong>
          </span>
          <span v-else-if="previous && current.delta > 0">
            — grew to <strong class="text-highlighted">{{ current.rows }}</strong>
          </span>
          <span v-else>
            — <strong class="text-highlighted">{{ current.rows }}</strong> rows to begin with
          </span>
        </template>
      </p>

      <p
        v-if="stages[0]?.truncated"
        class="dgm-label mt-2"
      >
        Showing the first {{ MAX_DOTS }} of {{ stages[0]?.rows }} rows — past that
        the dots stop being countable.
      </p>
    </div>
  </section>
</template>

<style scoped>
.flow__stages {
  list-style: none;
  margin: 0 0 1rem;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.flow__stage {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  border: 1px solid var(--ui-border);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    background-color var(--dgm-t-fast) var(--dgm-ease);
}

.flow__stage[data-state='past'] {
  border-color: color-mix(in oklab, var(--ui-primary) 40%, transparent);
}

.flow__stage[data-state='current'] {
  border-color: var(--ui-primary);
  background: color-mix(in oklab, var(--ui-primary) 10%, var(--ui-bg));
}

.flow__stage[data-state='ahead'] {
  opacity: 0.55;
}

.flow__stage-label {
  font-size: 0.75rem;
  color: var(--ui-text-muted);
}

.flow__stage[data-state='current'] .flow__stage-label {
  color: var(--ui-text-highlighted);
  font-weight: 600;
}

.flow__stage-rows {
  font-size: 0.75rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-highlighted);
}

.flow__field {
  border: 1px solid var(--dgm-box-border);
  border-radius: var(--dgm-box-radius);
  background: var(--dgm-box-bg);
  padding: 1rem;
}

.flow__dots {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
  min-height: 4rem;
  align-content: flex-start;
}

.flow__dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 999px;
  transition:
    background-color var(--dgm-t-base) var(--dgm-ease),
    transform var(--dgm-t-base) var(--dgm-ease),
    opacity var(--dgm-t-base) var(--dgm-ease);
  /* Staggered so the drop reads as a wave rather than a switch being flipped. */
  transition-delay: calc(var(--i) * 4ms);
}

.flow__dot[data-state='kept'] {
  background: var(--ui-primary);
  opacity: 1;
  transform: none;
}

.flow__dot[data-state='dropped'] {
  background: var(--ui-border-accented);
  opacity: 0.35;
  transform: scale(0.55) translateY(3px);
}

.flow__caption {
  margin-top: 0.875rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--dgm-box-border);
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  line-height: 1.6;
}
</style>
