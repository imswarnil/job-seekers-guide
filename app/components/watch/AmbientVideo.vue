<script setup lang="ts">
import { ambientVideoId } from '~/utils/story'

/**
 * A looping video behind something else.
 *
 * Three rules, all of them about not making the page worse:
 *
 *   · It mounts late. A third-party iframe whose only job is atmosphere must
 *     never be part of what the page costs to open, so it arrives a beat after
 *     everything that matters has painted.
 *   · It never mounts at all on a narrow screen or under reduced motion. On a
 *     phone it would be a hidden video eating a data plan, and moving wallpaper
 *     is exactly what reduced motion is asking not to have.
 *   · It is inert. No pointer events, no tab stop, muted, no controls.
 *
 * The scanlines and the vignette are the reason it works with the rest of the
 * section: the television downstairs has the same glass on it, so the band
 * reads as the same world rather than as a stock video loop.
 */
withDefaults(defineProps<{
  /** How much of the video shows through the wash. */
  intensity?: 'low' | 'high'
  videoId?: string
}>(), {
  intensity: 'low'
})

const wide = useMediaQuery('(min-width: 768px)')
const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')
const live = ref(false)

onMounted(() => {
  if (!wide.value || reduced.value) {
    return
  }
  setTimeout(() => {
    live.value = true
  }, 900)
})

const src = computed(() => {
  const id = ambientVideoId
  const params = new URLSearchParams({
    autoplay: '1',
    mute: '1',
    loop: '1',
    playlist: id,
    controls: '0',
    modestbranding: '1',
    playsinline: '1',
    disablekb: '1',
    rel: '0'
  })
  return `https://www.youtube-nocookie.com/embed/${id}?${params}`
})
</script>

<template>
  <div
    class="ambient"
    :data-intensity="intensity"
    aria-hidden="true"
  >
    <div
      v-if="live"
      class="ambient__frame"
    >
      <iframe
        :src="src"
        title=""
        tabindex="-1"
        loading="lazy"
        allow="autoplay; encrypted-media"
      />
    </div>

    <span class="ambient__wash" />
    <span class="ambient__scan" />
    <span class="ambient__vignette" />
  </div>
</template>

<style scoped>
.ambient {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  background: #05060f;
}

/* Oversized and centred: a 16:9 video inside a short wide band has to be
   cropped from the middle, not letterboxed. */
.ambient__frame {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100vw;
  height: 56.25vw;
  min-height: 100%;
  min-width: 177.78vh;
  transform: translate(-50%, -50%);
}

.ambient__frame iframe {
  width: 100%;
  height: 100%;
  border: 0;
}

.ambient__wash,
.ambient__scan,
.ambient__vignette {
  position: absolute;
  inset: 0;
}

.ambient__wash {
  background: linear-gradient(
    to bottom,
    rgb(8 9 26 / 0.82) 0%,
    rgb(8 9 26 / 0.7) 45%,
    rgb(5 6 15 / 0.92) 100%
  );
}

.ambient[data-intensity='high'] .ambient__wash {
  background: linear-gradient(
    to bottom,
    rgb(8 9 26 / 0.62) 0%,
    rgb(8 9 26 / 0.5) 45%,
    rgb(5 6 15 / 0.82) 100%
  );
}

/* The same glass as the television downstairs. */
.ambient__scan {
  background: repeating-linear-gradient(
    to bottom,
    rgb(0 0 0 / 0.3) 0 1px,
    transparent 1px 3px
  );
  opacity: 0.55;
  animation: ambient-roll 9s linear infinite;
}

@keyframes ambient-roll {
  to { background-position: 0 -150px }
}

.ambient__vignette {
  background: radial-gradient(
    ellipse 85% 90% at 50% 45%,
    transparent 45%,
    rgb(5 6 15 / 0.7) 100%
  );
}

@media (prefers-reduced-motion: reduce) {
  .ambient__scan {
    animation: none;
  }
}
</style>
