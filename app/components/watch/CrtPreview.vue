<script setup lang="ts">
import { ambientVideoId } from '~/utils/story'

/**
 * A small television, already on.
 *
 * The card for the series used to be an icon in a box. An icon of a screen is
 * not a screen — so this is the set itself, running, with the same glass as the
 * one on the watch page: scanlines, phosphor mask, vignette, curved corners.
 *
 * The same three rules as every other embed on this site: it mounts a beat
 * after the page has painted, never on a narrow screen or under reduced motion,
 * and it is completely inert — muted, no controls, no tab stop. Before it
 * arrives, and whenever it will not, the screen shows a test card rather than a
 * hole.
 */
withDefaults(defineProps<{
  /** Shown on the test card while the picture is warming up. */
  channel?: string
}>(), {
  channel: 'CH 01'
})

const wide = useMediaQuery('(min-width: 640px)')
const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')
const live = ref(false)

onMounted(() => {
  if (!wide.value || reduced.value) {
    return
  }
  setTimeout(() => {
    live.value = true
  }, 1100)
})

const src = computed(() => {
  const params = new URLSearchParams({
    autoplay: '1',
    mute: '1',
    loop: '1',
    playlist: ambientVideoId,
    controls: '0',
    modestbranding: '1',
    playsinline: '1',
    disablekb: '1',
    rel: '0'
  })
  return `https://www.youtube-nocookie.com/embed/${ambientVideoId}?${params}`
})
</script>

<template>
  <div class="set">
    <div class="set__bezel">
      <div class="set__tube">
        <iframe
          v-if="live"
          :src="src"
          title=""
          tabindex="-1"
          loading="lazy"
          allow="autoplay; encrypted-media"
          class="set__video"
        />

        <div
          v-else
          class="set__card"
        >
          <div class="set__bars">
            <span
              v-for="n in 7"
              :key="n"
            />
          </div>
          <p class="set__channel">
            {{ channel }}
          </p>
        </div>

        <span class="set__scan" />
        <span class="set__mask" />
        <span class="set__vignette" />
        <span class="set__glare" />
      </div>
    </div>

    <div class="set__panel">
      <span class="set__led" />
      <span class="set__brand">JSG&nbsp;COLOUR&nbsp;14C</span>
      <span class="set__grille" />
    </div>
  </div>
</template>

<style scoped>
.set {
  /* Full width by default: this is a card, and in a flex column with
     `align-items: flex-start` an intrinsically-sized set collapses to the width
     of its own chrome and loses to whatever it is sitting beside. */
  width: 100%;
  border-radius: 1.1rem;
  padding: 0.85rem 0.85rem 0;
  background: linear-gradient(160deg, #4a4a55, #2b2b33 42%, #1b1b21);
  box-shadow:
    inset 0 1px 0 rgb(255 255 255 / 0.18),
    inset 0 -2px 6px rgb(0 0 0 / 0.5);
}

.set__bezel {
  border-radius: 0.9rem;
  padding: 0.45rem;
  background: linear-gradient(160deg, #16161c, #0b0b10);
  box-shadow: inset 0 2px 5px rgb(0 0 0 / 0.9);
}

/* Asymmetric radii give the bulged-glass corners a real tube has; a plain
   rounded rectangle reads as a flat panel. */
.set__tube {
  position: relative;
  aspect-ratio: 4 / 3;
  border-radius: 1.6rem / 2.2rem;
  overflow: hidden;
  background: #05060b;
  isolation: isolate;
}

.set__video {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 178%;
  height: 178%;
  transform: translate(-50%, -50%);
  border: 0;
  pointer-events: none;
}

.set__card {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
}

.set__bars {
  display: flex;
  width: 58%;
  height: 1.5rem;
  border-radius: 0.15rem;
  overflow: hidden;
}

.set__bars span {
  flex: 1;
}

.set__bars span:nth-child(1) { background: #c0c0c0 }
.set__bars span:nth-child(2) { background: #c0c000 }
.set__bars span:nth-child(3) { background: #00c0c0 }
.set__bars span:nth-child(4) { background: #00c000 }
.set__bars span:nth-child(5) { background: #c000c0 }
.set__bars span:nth-child(6) { background: #c00000 }
.set__bars span:nth-child(7) { background: #0000c0 }

.set__channel {
  font-family: var(--font-mono);
  font-size: 0.625rem;
  letter-spacing: 0.24em;
  color: var(--color-spark-400);
}

.set__scan,
.set__mask,
.set__vignette,
.set__glare {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 2;
}

.set__scan {
  background: repeating-linear-gradient(
    to bottom,
    rgb(0 0 0 / 0.24) 0 1px,
    transparent 1px 3px
  );
  opacity: 0.7;
  animation: set-roll 7s linear infinite;
}

@keyframes set-roll {
  to { background-position: 0 -120px }
}

.set__mask {
  background: repeating-linear-gradient(
    to right,
    rgb(255 0 0 / 0.05) 0 1px,
    rgb(0 255 0 / 0.05) 1px 2px,
    rgb(0 0 255 / 0.05) 2px 3px
  );
}

.set__vignette {
  background: radial-gradient(
    ellipse 88% 82% at 50% 48%,
    transparent 55%,
    rgb(0 0 0 / 0.55) 100%
  );
}

.set__glare {
  background: linear-gradient(
    118deg,
    rgb(255 255 255 / 0.12) 0%,
    rgb(255 255 255 / 0.03) 22%,
    transparent 42%
  );
}

.set__panel {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.25rem 0.6rem;
}

.set__led {
  width: 0.35rem;
  height: 0.35rem;
  border-radius: 999px;
  background: #ff4d4d;
  box-shadow: 0 0 6px rgb(255 77 77 / 0.9);
  flex-shrink: 0;
}

.set__brand {
  font-family: var(--font-mono);
  font-size: 0.5rem;
  letter-spacing: 0.2em;
  color: rgb(255 255 255 / 0.35);
}

.set__grille {
  margin-left: auto;
  width: 2.75rem;
  height: 0.7rem;
  border-radius: 0.15rem;
  background: repeating-linear-gradient(
    to bottom,
    rgb(0 0 0 / 0.55) 0 1px,
    transparent 1px 3px
  );
}

@media (prefers-reduced-motion: reduce) {
  .set__scan {
    animation: none;
  }
}
</style>
