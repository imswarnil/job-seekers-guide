<script setup lang="ts">
/**
 * The television.
 *
 * A CRT is the right frame for this story and not only because it looks good:
 * the episodes are about 2012 to 2019 in small-town India, and a set you have
 * to switch on and wait for puts the viewer in the room rather than in a feed.
 *
 * The power-on is the real one — the beam strikes as a horizontal line, widens,
 * then blooms vertically — because that single animation does more to sell the
 * object than any amount of bezel drawing.
 *
 * YouTube is loaded only after somebody switches the set on. Until then this is
 * a few kilobytes of CSS, which is the whole reason it is not an embed sitting
 * on the page from the first paint.
 */
const props = defineProps<{
  youtubeId?: string
  title: string
  episode: number
  poster?: string
  runtime?: string
  /** `youtubeId` is stand-in footage, not the real episode. */
  placeholder?: boolean
}>()

const on = defineModel<boolean>('on', { default: false })

const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')

/** `off` → `striking` (the beam) → `on`. */
const phase = ref<'off' | 'striking' | 'on'>('off')
const frame = useTemplateRef<HTMLIFrameElement>('frame')
const muted = ref(false)
const playing = ref(true)

let timer: ReturnType<typeof setTimeout> | undefined

watch(on, (value) => {
  clearTimeout(timer)

  if (!value) {
    phase.value = 'off'
    return
  }

  if (reduced.value) {
    phase.value = 'on'
    return
  }

  phase.value = 'striking'
  timer = setTimeout(() => {
    phase.value = 'on'
  }, 900)
})

/* Switching episode while the set is on should not replay the warm-up — a
   channel change on a CRT is a flicker, not a cold start. */
watch(() => props.youtubeId, () => {
  playing.value = true
})

onBeforeUnmount(() => clearTimeout(timer))

const src = computed(() => {
  if (!props.youtubeId) {
    return ''
  }
  const params = new URLSearchParams({
    autoplay: '1',
    rel: '0',
    modestbranding: '1',
    playsinline: '1',
    enablejsapi: '1'
  })
  return `https://www.youtube-nocookie.com/embed/${props.youtubeId}?${params}`
})

/**
 * Drive the player without loading the IFrame API.
 *
 * The API script is 60kB to do what a `postMessage` already does; the embed
 * listens for the same command envelope as long as `enablejsapi=1` is set.
 */
function command(func: string, args: unknown[] = []) {
  frame.value?.contentWindow?.postMessage(
    JSON.stringify({ event: 'command', func, args }),
    'https://www.youtube-nocookie.com'
  )
}

function toggleplay() {
  playing.value = !playing.value
  command(playing.value ? 'playVideo' : 'pauseVideo')
}

function togglemute() {
  muted.value = !muted.value
  command(muted.value ? 'mute' : 'unMute')
}

/** The remote's skip keys. YouTube has no relative seek, so ask and add. */
function skip(seconds: number) {
  const win = frame.value?.contentWindow
  if (!win) {
    return
  }
  // `getCurrentTime` answers on the message channel, which needs a listener and
  // a handshake. Seeking from a local running clock is close enough for a
  // ten-second nudge and costs nothing.
  elapsed.value = Math.max(0, elapsed.value + seconds)
  command('seekTo', [elapsed.value, true])
}

const elapsed = ref(0)
let tick: ReturnType<typeof setInterval> | undefined

watch([phase, playing], () => {
  clearInterval(tick)
  if (phase.value === 'on' && playing.value && props.youtubeId) {
    tick = setInterval(() => {
      elapsed.value += 1
    }, 1000)
  }
})

watch(() => props.youtubeId, () => {
  elapsed.value = 0
})

onBeforeUnmount(() => clearInterval(tick))

defineExpose({ toggleplay, togglemute, skip, command })
</script>

<template>
  <div
    class="crt"
    :data-phase="phase"
  >
    <div class="crt__cabinet">
      <div class="crt__bezel">
        <div class="crt__tube">
          <div class="crt__picture">
            <!-- Live -->
            <template v-if="phase !== 'off' && src">
              <iframe
                ref="frame"
                :src="src"
                :title="title"
                class="crt__video"
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              />
              <p
                v-if="placeholder"
                class="crt__placeholder"
              >
                <span class="crt__placeholder-dot" />
                Placeholder footage — this episode is written, not filmed
              </p>
            </template>

            <!-- Switched on, nothing to broadcast: the test card, which is the
                 honest state for an episode that is written but not filmed. -->
            <div
              v-else-if="phase !== 'off'"
              class="crt__card"
            >
              <div class="crt__bars">
                <span
                  v-for="n in 7"
                  :key="n"
                />
              </div>
              <p class="crt__card-ep">
                Episode {{ String(episode).padStart(2, '0') }}
              </p>
              <p class="crt__card-title">
                {{ title }}
              </p>
              <p class="crt__card-note">
                Not filmed yet — the script is below, and it was written to be read.
              </p>
            </div>

            <!-- Off -->
            <div
              v-else
              class="crt__standby"
            >
              <p class="crt__standby-ep">
                CH {{ String(episode).padStart(2, '0') }}
              </p>
              <p class="crt__standby-title">
                {{ title }}
              </p>
              <button
                type="button"
                class="crt__power-btn"
                @click="on = true"
              >
                <UIcon
                  name="i-lucide-power"
                  class="size-5"
                />
                Switch it on
              </button>
              <p
                v-if="runtime"
                class="crt__standby-note"
              >
                {{ runtime }}
              </p>
            </div>
          </div>

          <!-- Glass. Scanlines, the phosphor mask, the reflection and the
               corner vignette, in that order — each is unconvincing alone. -->
          <span class="crt__scan" />
          <span class="crt__mask" />
          <span class="crt__vignette" />
          <span class="crt__glare" />

          <!-- The beam striking, on switch-on. -->
          <span class="crt__beam" />
        </div>
      </div>

      <div class="crt__panel">
        <div class="crt__brand">
          <span class="crt__led" />
          <span class="crt__brandname">JSG&nbsp;COLOUR&nbsp;14C</span>
        </div>

        <div class="crt__knobs">
          <span class="crt__knob" />
          <span class="crt__knob crt__knob--small" />
          <span class="crt__grille" />
        </div>
      </div>
    </div>

    <div class="crt__feet">
      <span />
      <span />
    </div>
  </div>
</template>

<style scoped>
.crt {
  --tube-radius: 2.5rem / 3.5rem;
  width: 100%;
}

.crt__cabinet {
  position: relative;
  border-radius: 1.5rem;
  padding: 1.25rem 1.25rem 0;
  background:
    linear-gradient(160deg, #4a4a55, #2b2b33 42%, #1b1b21);
  box-shadow:
    inset 0 1px 0 rgb(255 255 255 / 0.18),
    inset 0 -2px 6px rgb(0 0 0 / 0.5),
    0 26px 60px rgb(6 8 24 / 0.42);
}

@media (min-width: 768px) {
  .crt__cabinet {
    padding: 2rem 2rem 0;
    border-radius: 2rem;
  }
}

.crt__bezel {
  border-radius: 1.35rem;
  padding: 0.6rem;
  background: linear-gradient(160deg, #16161c, #0b0b10);
  box-shadow:
    inset 0 2px 5px rgb(0 0 0 / 0.9),
    inset 0 -1px 0 rgb(255 255 255 / 0.06);
}

/* The tube. Asymmetric radii give the bulged-glass corners a real CRT has;
   a plain rounded rectangle reads as a modern panel. */
.crt__tube {
  position: relative;
  aspect-ratio: 4 / 3;
  border-radius: var(--tube-radius);
  overflow: hidden;
  background: #05060b;
  isolation: isolate;
}

@media (min-width: 768px) {
  .crt__tube {
    aspect-ratio: 16 / 10;
  }
}

.crt__picture {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  transition: opacity 420ms ease, filter 420ms ease;
}

.crt[data-phase='off'] .crt__picture {
  filter: none;
}

.crt[data-phase='striking'] .crt__picture {
  opacity: 0;
}

.crt__video {
  width: 100%;
  height: 100%;
  border: 0;
}

/* ── The beam ──────────────────────────────────────────────────────── */

.crt__beam {
  position: absolute;
  inset: 0;
  background: #fff;
  opacity: 0;
  pointer-events: none;
  z-index: 6;
}

.crt[data-phase='striking'] .crt__beam {
  animation: crt-strike 900ms cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes crt-strike {
  0% { transform: scale(0.001, 0.004); opacity: 1 }
  22% { transform: scale(1, 0.004); opacity: 1 }
  30% { transform: scale(1, 0.006); opacity: 1 }
  62% { transform: scale(1, 1); opacity: 0.85 }
  72% { transform: scale(1, 1); opacity: 0.32 }
  100% { transform: scale(1, 1); opacity: 0 }
}

/* ── The glass ─────────────────────────────────────────────────────── */

.crt__scan,
.crt__mask,
.crt__vignette,
.crt__glare {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 4;
}

.crt__scan {
  background: repeating-linear-gradient(
    to bottom,
    rgb(0 0 0 / 0.22) 0 1px,
    transparent 1px 3px
  );
  opacity: 0.7;
}

/* The aperture grille: vertical RGB stripes, barely there. Without it the
   scanlines alone read as a filter rather than as a tube. */
.crt__mask {
  background: repeating-linear-gradient(
    to right,
    rgb(255 0 0 / 0.05) 0 1px,
    rgb(0 255 0 / 0.05) 1px 2px,
    rgb(0 0 255 / 0.05) 2px 3px
  );
}

.crt__vignette {
  background: radial-gradient(
    ellipse 88% 82% at 50% 48%,
    transparent 55%,
    rgb(0 0 0 / 0.55) 100%
  );
}

.crt__glare {
  background: linear-gradient(
    118deg,
    rgb(255 255 255 / 0.13) 0%,
    rgb(255 255 255 / 0.04) 22%,
    transparent 42%
  );
}

.crt[data-phase='on'] .crt__scan {
  animation: crt-roll 7s linear infinite;
}

@keyframes crt-roll {
  to { background-position: 0 -120px }
}

/* The stand-in notice sits on the glass, under the scanlines, where a channel
   ident would be — so it reads as part of the broadcast rather than as chrome
   bolted onto the page. */
.crt__placeholder {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.625rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: rgb(244 245 255 / 0.72);
  background: linear-gradient(to top, rgb(5 6 15 / 0.85), transparent);
  text-align: center;
}

.crt__placeholder-dot {
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 999px;
  background: #ff4d4d;
  box-shadow: 0 0 6px rgb(255 77 77 / 0.8);
  flex-shrink: 0;
}

/* ── Standby and test card ─────────────────────────────────────────── */

.crt__standby,
.crt__card {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  height: 100%;
  width: 100%;
  padding: 1.5rem;
  text-align: center;
}

.crt__standby-ep,
.crt__card-ep {
  font-family: var(--font-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.24em;
  color: var(--color-spark-400);
}

.crt__standby-title,
.crt__card-title {
  font-family: var(--font-display);
  font-size: clamp(1.25rem, 3.2vw, 2rem);
  font-weight: 700;
  color: #f4f5ff;
  text-wrap: balance;
  text-shadow: 0 0 18px rgb(120 200 255 / 0.28);
}

.crt__standby-note,
.crt__card-note {
  font-size: 0.75rem;
  color: rgb(244 245 255 / 0.55);
  text-wrap: balance;
  max-width: 26rem;
}

.crt__power-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding: 0.55rem 1.1rem;
  border-radius: 999px;
  border: 1px solid rgb(255 255 255 / 0.25);
  background: rgb(255 255 255 / 0.06);
  color: #f4f5ff;
  font-size: 0.875rem;
  font-weight: 600;
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.crt__power-btn:hover {
  background: rgb(255 255 255 / 0.14);
}

/* The colour bars, in the order a test card actually uses. */
.crt__bars {
  display: flex;
  width: 62%;
  height: 1.6rem;
  border-radius: 0.15rem;
  overflow: hidden;
  margin-bottom: 0.6rem;
}

.crt__bars span {
  flex: 1;
}

.crt__bars span:nth-child(1) { background: #c0c0c0 }
.crt__bars span:nth-child(2) { background: #c0c000 }
.crt__bars span:nth-child(3) { background: #00c0c0 }
.crt__bars span:nth-child(4) { background: #00c000 }
.crt__bars span:nth-child(5) { background: #c000c0 }
.crt__bars span:nth-child(6) { background: #c00000 }
.crt__bars span:nth-child(7) { background: #0000c0 }

/* ── The cabinet ───────────────────────────────────────────────────── */

.crt__panel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.7rem 0.4rem 0.9rem;
}

.crt__brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.crt__led {
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 999px;
  background: #4a1414;
  box-shadow: inset 0 0 2px rgb(0 0 0 / 0.8);
  transition: background-color 300ms ease, box-shadow 300ms ease;
}

.crt[data-phase='on'] .crt__led,
.crt[data-phase='striking'] .crt__led {
  background: #ff4d4d;
  box-shadow: 0 0 7px rgb(255 77 77 / 0.9);
}

.crt__brandname {
  font-family: var(--font-mono);
  font-size: 0.5625rem;
  letter-spacing: 0.22em;
  color: rgb(255 255 255 / 0.35);
}

.crt__knobs {
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.crt__knob {
  width: 1.05rem;
  height: 1.05rem;
  border-radius: 999px;
  background: radial-gradient(circle at 32% 28%, #6b6b78, #24242c 70%);
  box-shadow: inset 0 -1px 2px rgb(0 0 0 / 0.6), 0 1px 0 rgb(255 255 255 / 0.08);
}

.crt__knob--small {
  width: 0.75rem;
  height: 0.75rem;
}

.crt__grille {
  width: 3.5rem;
  height: 0.85rem;
  border-radius: 0.15rem;
  background: repeating-linear-gradient(
    to bottom,
    rgb(0 0 0 / 0.55) 0 1px,
    transparent 1px 3px
  );
}

.crt__feet {
  display: flex;
  justify-content: space-between;
  padding: 0 3rem;
}

.crt__feet span {
  width: 3rem;
  height: 0.55rem;
  border-radius: 0 0 0.35rem 0.35rem;
  background: linear-gradient(#1b1b21, #101015);
  box-shadow: 0 5px 12px rgb(6 8 24 / 0.35);
}

@media (prefers-reduced-motion: reduce) {
  .crt__beam,
  .crt[data-phase='on'] .crt__scan {
    animation: none;
  }

  .crt__picture {
    transition: none;
  }
}
</style>
