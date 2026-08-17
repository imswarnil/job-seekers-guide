<script setup lang="ts">
/**
 * A full-width ad band whose contents drift against the scroll.
 *
 * Built because it was asked for, shipped off by default, and honest about why:
 * parallax is the effect most likely to make a teaching site read as a content
 * farm, and the transform runs on every scroll frame. It is disabled outright
 * under reduced motion and on touch, where it reliably janks.
 *
 * If it is ever enabled, prove it on `/path` with a Lighthouse run before it
 * goes anywhere near a lesson.
 */
const root = useTemplateRef<HTMLElement>('root')

const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')
const touch = useMediaQuery('(hover: none)')
const visible = useElementVisibility(root)

const offset = ref(0)

const animate = computed(() => !reduced.value && !touch.value)

function update() {
  if (!animate.value || !visible.value || !root.value) {
    offset.value = 0
    return
  }

  const rect = root.value.getBoundingClientRect()
  const progress = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight

  // Deliberately shallow. A large offset is what makes parallax look cheap.
  offset.value = Math.max(-24, Math.min(24, progress * -24))
}

useEventListener('scroll', update, { passive: true })
useEventListener('resize', update, { passive: true })
onMounted(update)
</script>

<template>
  <div
    ref="root"
    class="ad-parallax"
  >
    <div
      class="ad-parallax__inner"
      :style="{ transform: `translate3d(0, ${offset}px, 0)` }"
    >
      <UContainer>
        <AdSlot
          placement="path-parallax"
          variant="banner"
        />
      </UContainer>
    </div>
  </div>
</template>

<style scoped>
.ad-parallax {
  overflow: hidden;
  /* The band is taller than its contents so the drift never exposes an edge. */
  padding-block: 1.5rem;
}

.ad-parallax__inner {
  will-change: transform;
}

@media (prefers-reduced-motion: reduce) {
  .ad-parallax__inner {
    transform: none !important;
    will-change: auto;
  }
}
</style>
