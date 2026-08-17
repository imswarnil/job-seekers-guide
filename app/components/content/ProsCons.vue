<script setup lang="ts">
/**
 * ::pros-cons — the honest trade-off. Every technology choice on this platform
 * is argued for rather than asserted, and this is the shape that argument takes.
 *
 * ```md
 * ::pros-cons
 * ---
 * title: Java as your first language
 * pros:
 *   - The most entry-level roles in this market
 *   - Verbose enough that the machine model stays visible
 * cons:
 *   - Slower to a first running program than Python
 *   - The build tooling is genuinely unpleasant at first
 * ---
 * ::
 * ```
 *
 * For anything longer than a line each, use the `pros` and `cons` slots instead
 * and write prose.
 */
withDefaults(defineProps<{
  title?: string
  pros?: string[]
  cons?: string[]
  prosLabel?: string
  consLabel?: string
}>(), {
  prosLabel: 'For',
  consLabel: 'Against'
})

const slots = useSlots()
</script>

<template>
  <DgmFigure
    :label="title"
    icon="i-lucide-scale"
  >
    <div class="grid sm:grid-cols-2 gap-3">
      <div
        class="dgm-box pros-cons__panel"
        data-tone="good"
      >
        <p class="pros-cons__head">
          <UIcon
            name="i-lucide-thumbs-up"
            class="size-3.5"
          />
          {{ prosLabel }}
        </p>

        <ul
          v-if="pros?.length"
          class="space-y-1.5"
        >
          <li
            v-for="item in pros"
            :key="item"
            class="flex items-start gap-2 text-sm text-muted"
          >
            <UIcon
              name="i-lucide-check"
              class="size-3.5 mt-1 shrink-0"
              :style="{ color: 'var(--dgm-good)' }"
            />
            <span>{{ item }}</span>
          </li>
        </ul>

        <div
          v-if="slots.pros"
          class="text-sm text-muted [&>*:last-child]:mb-0 [&>p]:mb-2"
        >
          <slot name="pros" />
        </div>
      </div>

      <div
        class="dgm-box pros-cons__panel"
        data-tone="bad"
      >
        <p class="pros-cons__head">
          <UIcon
            name="i-lucide-thumbs-down"
            class="size-3.5"
          />
          {{ consLabel }}
        </p>

        <ul
          v-if="cons?.length"
          class="space-y-1.5"
        >
          <li
            v-for="item in cons"
            :key="item"
            class="flex items-start gap-2 text-sm text-muted"
          >
            <UIcon
              name="i-lucide-x"
              class="size-3.5 mt-1 shrink-0"
              :style="{ color: 'var(--dgm-bad)' }"
            />
            <span>{{ item }}</span>
          </li>
        </ul>

        <div
          v-if="slots.cons"
          class="text-sm text-muted [&>*:last-child]:mb-0 [&>p]:mb-2"
        >
          <slot name="cons" />
        </div>
      </div>
    </div>
  </DgmFigure>
</template>

<style scoped>
.pros-cons__panel {
  padding: 1rem;
}

.pros-cons__head {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 0.75rem;
}

.pros-cons__panel[data-tone='good'] .pros-cons__head {
  color: var(--dgm-good);
}

.pros-cons__panel[data-tone='bad'] .pros-cons__head {
  color: var(--dgm-bad);
}
</style>
