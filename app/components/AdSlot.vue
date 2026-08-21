<script setup lang="ts">
import type { AdSlotId } from '~/utils/ads'
import { adSlots } from '~/utils/ads'

/**
 * The one ad implementation on the site.
 *
 * Everything else — the MDC wrapper, the parallax band, the navbar leaderboard —
 * renders this. The rules it enforces are why it is one component: the box is
 * reserved before anything loads so nothing shifts, nothing loads until the slot
 * is near the viewport, and the whole thing disappears when ads are off.
 *
 * Three states, not two:
 *
 * · **live** — ads on, a creative renders.
 * · **placeholder** — ads off but `ads.showPlaceholders` on. Draws the reserved
 *   box, labelled, at the real dimensions. This is what makes the layout honest
 *   while ads are switched off: you can see where they will land and prove the
 *   reservation works before a single real ad exists.
 * · **nothing** — both off.
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

const enabledHere = computed(() => !(ads?.slots && ads.slots[props.placement] === false))

const live = computed(() => Boolean(ads?.enabled) && ads.provider !== 'none' && enabledHere.value)
const placeholder = computed(() => !live.value && Boolean(ads?.showPlaceholders) && enabledHere.value)

// The viewport gate is a render decision, not a CSS one — a hidden slot still
// costs a request, and on a phone the 970px leaderboard has nowhere to go.
// Treated as fitting during SSR (width 0) so the reserved box is in the HTML;
// the client removes it if the window is genuinely too narrow.
const fits = computed(() => {
  if (!definition.value.minViewport) {
    return true
  }
  return width.value === 0 || width.value >= definition.value.minViewport
})

const shown = computed(() => (live.value || placeholder.value) && fits.value)

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
    v-if="shown"
    ref="root"
    class="ad"
    :class="[`ad--${variant}`, placeholder && 'ad--placeholder']"
    :style="{ '--ad-aspect': aspect, '--ad-max': `${definition.width}px` }"
    :aria-label="definition.label"
    :aria-hidden="placeholder || undefined"
  >
    <p class="ad__label">
      {{ definition.label }}
    </p>

    <div class="ad__box">
      <!-- The house creative: the platform advertising itself rather than
           renting the space out. It is the default because it is the only
           option that costs the reader nothing. -->
      <NuxtLink
        v-if="live && loaded && ads.provider === 'house'"
        to="/start"
        class="ad__house"
      >
        <UIcon
          name="i-lucide-compass"
          class="size-5 text-primary shrink-0"
        />
        <span>
          <span class="block font-medium text-sm text-highlighted">The whole path is free</span>
          <span class="block text-xs text-muted">Orientation to offer. No fees, no bonds, no placement claims.</span>
        </span>
      </NuxtLink>

      <slot v-else-if="live && loaded" />

      <!-- Off, but drawn. Says what it is and what size it would be, so nobody
           has to guess where an ad lands once they are switched on. -->
      <p
        v-else-if="placeholder"
        class="ad__placeholder-text"
      >
        {{ definition.width }} × {{ definition.height }}
        <span class="ad__placeholder-id">{{ definition.id }}</span>
      </p>
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

.ad--banner {
  margin-block: 0;
  padding-block: 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-bottom: 1px solid var(--ui-border);
}

.ad__label {
  font-size: 0.625rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
  margin-bottom: 0.375rem;
}

/* The box exists at full size from the first paint, whether or not anything
   ever fills it. That is the entire anti-CLS mechanism. */
.ad__box {
  aspect-ratio: var(--ad-aspect);
  max-width: var(--ad-max);
  width: 100%;
  /* Centred in whatever it is dropped into. A 728px box left-aligned in a
     1280px container reads as a layout mistake rather than as an ad slot. */
  margin-inline: auto;
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

.ad--placeholder .ad__box {
  background:
    repeating-linear-gradient(
      -45deg,
      transparent,
      transparent 9px,
      color-mix(in oklab, var(--ui-border) 45%, transparent) 9px,
      color-mix(in oklab, var(--ui-border) 45%, transparent) 10px
    );
}

.ad__placeholder-text {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-dimmed);
  background: var(--ui-bg);
  padding: 0.2rem 0.6rem;
  border-radius: var(--radius-xs);
}

.ad__placeholder-id {
  font-family: var(--font-mono);
  opacity: 0.65;
}
</style>
