<script setup lang="ts">
import type { AdSlotId } from '~/utils/ads'
import { adSlots } from '~/utils/ads'

/**
 * The one ad implementation on the site.
 *
 * Everything else — the MDC wrapper, the parallax band — renders this. The rules
 * it enforces are the reason it is one component: the box is reserved before
 * anything loads so nothing shifts, nothing loads until the slot is near the
 * viewport, the provider script is injected once per page rather than once per
 * slot, and the whole thing disappears when `ads.enabled` is off.
 */
const props = withDefaults(defineProps<{
  placement: AdSlotId
  variant?: 'inline' | 'banner' | 'card'
}>(), {
  variant: 'inline'
})

const { ads } = useAppConfig()

const definition = computed(() => adSlots[props.placement])

const root = useTemplateRef<HTMLElement>('root')
const near = useElementVisibility(root, { rootMargin: '400px' })

const width = useWindowSize().width

const allowed = computed(() => {
  if (!ads?.enabled || ads.provider === 'none') {
    return false
  }
  if (ads.slots && ads.slots[props.placement] === false) {
    return false
  }
  return true
})

// Viewport gate is a render decision, not a CSS one — a hidden slot still costs
// a request, and on a phone the 240px rail slot has nowhere to go anyway.
const fits = computed(() => !definition.value.minViewport || width.value >= definition.value.minViewport)

const live = computed(() => allowed.value && fits.value)
const loaded = ref(false)

watch([live, near], ([isLive, isNear]) => {
  if (isLive && isNear) {
    loaded.value = true
  }
})

const aspect = computed(() => `${definition.value.width} / ${definition.value.height}`)
</script>

<template>
  <aside
    v-if="live"
    ref="root"
    class="ad"
    :class="`ad--${variant}`"
    :style="{ '--ad-aspect': aspect, '--ad-max': `${definition.width}px` }"
    :aria-label="definition.label"
  >
    <p class="ad__label">
      {{ definition.label }}
    </p>

    <div class="ad__box">
      <!-- The house creative: the platform advertising itself rather than
           renting the space out. It is the default because it is the only
           option that costs the reader nothing. -->
      <NuxtLink
        v-if="loaded && ads.provider === 'house'"
        to="/path"
        class="ad__house"
      >
        <UIcon
          name="i-lucide-route"
          class="size-5 text-primary shrink-0"
        />
        <span>
          <span class="block font-medium text-sm text-highlighted">The whole path is free</span>
          <span class="block text-xs text-muted">Orientation to offer. No fees, no bonds, no placement claims.</span>
        </span>
      </NuxtLink>

      <slot v-else-if="loaded" />
    </div>
  </aside>
</template>

<style scoped>
.ad {
  margin-block: 1.75rem;
}

.ad--card {
  margin-block: 1rem;
}

.ad__label {
  font-size: 0.625rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--dgm-dim);
  margin-bottom: 0.375rem;
}

/* The box exists at full size from the first paint, whether or not anything
   ever fills it. That is the entire anti-CLS mechanism. */
.ad__box {
  aspect-ratio: var(--ad-aspect);
  max-width: var(--ad-max);
  width: 100%;
  border: 1px dashed var(--dgm-box-border);
  border-radius: var(--dgm-box-radius);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.ad__house {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  width: 100%;
  height: 100%;
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.ad__house:hover {
  background: var(--ui-bg-elevated);
}
</style>
