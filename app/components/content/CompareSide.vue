<script setup lang="ts">
/** One panel of a `::compare`. See `Compare.vue` for the authoring example. */
const props = withDefaults(defineProps<{
  label?: string
  /** Colours the panel and picks the icon. */
  verdict?: 'wrong' | 'right' | 'neutral'
  /** Override the verdict's icon. */
  icon?: string
}>(), {
  verdict: 'neutral'
})

// Colour never carries the meaning on its own: an icon and a word say it too,
// which is the difference between a diagram and a diagram somebody colour-blind
// can read.
const verdicts = {
  wrong: { icon: 'i-lucide-x', tone: 'error' },
  right: { icon: 'i-lucide-check', tone: 'success' },
  neutral: { icon: 'i-lucide-minus', tone: 'neutral' }
} as const

const verdict = computed(() => verdicts[props.verdict])
</script>

<template>
  <div
    class="dgm-box compare-side not-prose"
    :data-tone="verdict.tone"
  >
    <div class="flex items-center gap-2 mb-2">
      <span class="compare-side__mark">
        <UIcon
          :name="icon || verdict.icon"
          class="size-3.5"
        />
      </span>
      <span
        v-if="label"
        class="font-medium text-sm text-highlighted"
      >{{ label }}</span>
    </div>

    <div class="text-sm text-muted [&>*:last-child]:mb-0 [&>p]:mb-2 [&>ul]:mb-2 [&>ul]:list-disc [&>ul]:pl-5 [&>ul>li]:mb-1">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.compare-side {
  padding: 1rem;
  height: 100%;
  border-top-width: 3px;
}

.compare-side__mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 999px;
  flex-shrink: 0;
}

.compare-side[data-tone='error'] {
  border-top-color: var(--dgm-bad);
}

.compare-side[data-tone='error'] .compare-side__mark {
  background: color-mix(in oklab, var(--dgm-bad) 15%, transparent);
  color: var(--dgm-bad);
}

.compare-side[data-tone='success'] {
  border-top-color: var(--dgm-good);
}

.compare-side[data-tone='success'] .compare-side__mark {
  background: color-mix(in oklab, var(--dgm-good) 15%, transparent);
  color: var(--dgm-good);
}

.compare-side[data-tone='neutral'] .compare-side__mark {
  background: var(--ui-bg-accented);
  color: var(--dgm-label);
}
</style>
