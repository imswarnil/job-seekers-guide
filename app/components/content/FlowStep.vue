<script setup lang="ts">
import { flowKey } from '~/utils/diagram-keys'

/** One box in a `::flow`. See `Flow.vue` for the authoring example. */
defineProps<{
  label?: string
  icon?: string
  /** A short aside under the box — "this is where most people get stuck". */
  note?: string
  /** Marks the step the lesson is actually about. */
  highlight?: boolean
}>()

const flow = inject(flowKey, null)
const index = flow?.claim() ?? 0
const isVertical = computed(() => flow?.direction.value === 'vertical')
</script>

<template>
  <div
    class="dgm-item flow-step"
    :style="{ '--dgm-i': index }"
  >
    <div
      class="dgm-box flow-step__box"
      :data-state="highlight ? 'active' : undefined"
    >
      <div class="flex items-center gap-2 mb-1.5">
        <span
          v-if="flow?.numbered.value"
          class="flex items-center justify-center size-5 rounded-full bg-primary/12 text-primary text-[11px] font-semibold tabular-nums shrink-0"
        >{{ index + 1 }}</span>

        <UIcon
          v-else-if="icon"
          :name="icon"
          class="size-4 text-primary shrink-0"
        />

        <span
          v-if="label"
          class="font-medium text-sm text-highlighted"
        >{{ label }}</span>
      </div>

      <div class="text-sm text-muted [&>p:last-child]:mb-0 [&>p]:mb-2">
        <slot />
      </div>
    </div>

    <p
      v-if="note"
      class="dgm-label mt-2"
    >
      {{ note }}
    </p>

    <!-- The connector. It belongs to the step rather than sitting between them
         so the flex row does not need an explicit separator element per gap. -->
    <span
      class="flow-step__arrow"
      aria-hidden="true"
    >
      <!-- A horizontal flow stacks below `sm`, and an arrow pointing right at a
           box that is now underneath is worse than no arrow. Both are rendered
           and CSS picks — the direction is a layout fact, not a data one. -->
      <UIcon
        name="i-lucide-arrow-down"
        class="size-4 flow-step__down"
      />
      <UIcon
        v-if="!isVertical"
        name="i-lucide-arrow-right"
        class="size-4 flow-step__right"
      />
    </span>
  </div>
</template>

<style scoped>
.flow-step {
  position: relative;
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.flow-step__box {
  padding: 0.875rem 1rem;
  height: 100%;
}

.flow-step__arrow {
  position: absolute;
  color: var(--dgm-dim);
  display: flex;
  align-items: center;
  justify-content: center;
  /* Sits in the gap the flex layout leaves; hidden on the last step, which has
     no following sibling to point at. */
  left: 50%;
  bottom: calc(var(--dgm-gap) * -1);
  transform: translate(-50%, 50%);
}

.flow-step:last-child .flow-step__arrow {
  display: none;
}

.flow-step__right {
  display: none;
}

@media (min-width: 640px) {
  .flow:not(.flow--v) .flow-step__arrow {
    left: auto;
    bottom: auto;
    top: 50%;
    right: calc(var(--dgm-gap) * -1);
    transform: translate(50%, -50%);
  }

  .flow:not(.flow--v) .flow-step__down {
    display: none;
  }

  .flow:not(.flow--v) .flow-step__right {
    display: block;
  }
}
</style>
