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

/**
 * AdSense needs two ids, and they are easy to confuse: `client` is the account
 * (`ca-pub-…`, one per site) and `unit` is the individual ad unit (a number, one
 * per placement). Both have to be present or there is nothing to fill.
 */
const adsense = computed(() => {
  if (ads?.provider !== 'adsense') {
    return null
  }
  const client = ads.client?.trim()
  const unit = ads.slotIds?.[props.placement]?.trim()
  return client && unit ? { client, unit } : null
})

// An adsense slot with no unit id is not live, which drops it back to the
// dashed placeholder rather than an empty box. That is deliberate: a forgotten
// unit id then looks like a missing ad instead of like nothing at all.
const live = computed(() => {
  if (!ads?.enabled || ads.provider === 'none' || !enabledHere.value) {
    return false
  }
  return ads.provider === 'adsense' ? Boolean(adsense.value) : true
})

const placeholder = computed(() => !live.value && Boolean(ads?.showPlaceholders) && enabledHere.value)

const shown = computed(() => (live.value || placeholder.value) && fits.value)

const loaded = ref(false)

watch([live, near], ([isLive, isNear]) => {
  if (isLive && isNear) {
    loaded.value = true
  }
})

// The AdSense library, requested once for the whole document however many slots
// are on the page. `useHead` dedupes on `key`, so this is safe to call from
// every instance. It is only ever asked for when there is a real unit to fill,
// which is what keeps a reader who sees no ads from paying for the script.
watchEffect(() => {
  if (!adsense.value || !loaded.value) {
    return
  }
  useHead({
    script: [{
      key: 'adsbygoogle',
      src: `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adsense.value.client}`,
      async: true,
      crossorigin: 'anonymous'
    }]
  })
})

const ins = useTemplateRef<HTMLElement>('ins')

/**
 * Handing one `<ins>` to AdSense fills it. Pushing twice for the same element
 * throws `adsbygoogle.push() error: All ins elements ... already have ads`, and
 * client-side navigation makes that easy to do by accident, so each element is
 * pushed exactly once and never again.
 *
 * The queue is an array before the library arrives and replaced by the library
 * afterwards, so pushing early is fine — it is picked up on load.
 */
const filled = new WeakSet<HTMLElement>()

watchEffect(async () => {
  if (!import.meta.client || !adsense.value || !loaded.value) {
    return
  }
  await nextTick()
  const el = ins.value
  if (!el || filled.has(el)) {
    return
  }
  filled.add(el)
  try {
    const w = window as unknown as { adsbygoogle?: unknown[] }
    ;(w.adsbygoogle = w.adsbygoogle || []).push({})
  } catch (error) {
    // A blocked or failed ad is not worth breaking a lesson over.
    console.warn('[AdSlot] adsbygoogle push failed', error)
  }
})
</script>

<template>
  <aside
    v-if="shown"
    ref="root"
    class="ad"
    :class="[`ad--${variant}`, placeholder && 'ad--placeholder']"
    :style="{ '--ad-height': `${definition.height}px`, '--ad-max': `${definition.width}px` }"
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

      <!-- AdSense, sized by its container and nothing else.
           `data-ad-format="auto"` is deliberately absent: with it set, the
           library picks its own height and ignores the reservation — a 300×250
           slot came back as 298×600 in testing, clipped by the box, which
           serves the advertiser a half-visible impression.
           `full-width-responsive` is off for the same reason, since it expands
           a unit to the viewport width on a phone. -->
      <ins
        v-else-if="live && loaded && adsense"
        ref="ins"
        class="adsbygoogle ad__adsense"
        :data-ad-client="adsense.client"
        :data-ad-slot="adsense.unit"
        data-full-width-responsive="false"
      />

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
   ever fills it. That is the entire anti-CLS mechanism.

   The height is fixed rather than an aspect ratio, because ads do not scale.
   A 728×90 box at an aspect ratio is 43px tall on a phone, and no creative is
   43px tall, so the unit would either overflow or go unfilled. Holding the
   height and letting only the width shrink means a narrow screen gets a real
   ad of the same height instead. */
.ad__box {
  height: var(--ad-height);
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

/* `display: block` is what AdSense's own snippet sets, and the unit measures
   its container, so it has to fill the reserved box rather than sit inside it. */
.ad__adsense {
  display: block;
  width: 100%;
  height: 100%;
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
