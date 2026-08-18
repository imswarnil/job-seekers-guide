<script setup lang="ts">
const props = defineProps<{
  youtubeId?: string
  playbackId?: string
  title: string
  episode: number
  poster?: string
  runtime?: string
  next?: { path: string, title: string, episode: number }
}>()

/**
 * The episode player.
 *
 * YouTube first, because that is where these actually live and Mux bills per
 * minute delivered. It is a lite embed: a poster and a real link until somebody
 * presses play, so an episode page costs nothing in third-party JavaScript for
 * the people who came to read the script.
 *
 * An episode with no video at all is still a real page with a real poster. The
 * writing goes up before the filming, and ten empty rectangles would say the
 * series had been abandoned rather than that it is coming.
 */
const playing = ref(false)
const ended = ref(false)
const remaining = ref(10)

const hasYoutube = computed(() => Boolean(props.youtubeId))
const hasMux = computed(() => Boolean(props.playbackId))
const hasVideo = computed(() => hasYoutube.value || hasMux.value)

const muxReady = ref(false)

let timer: ReturnType<typeof setInterval> | undefined

function cancel() {
  ended.value = false
  if (timer) {
    clearInterval(timer)
    timer = undefined
  }
}

function onEnded() {
  if (!props.next) {
    return
  }
  ended.value = true
  remaining.value = 10

  timer = setInterval(() => {
    remaining.value--
    if (remaining.value <= 0) {
      cancel()
      navigateTo(props.next!.path)
    }
  }, 1000)
}

async function play() {
  playing.value = true
  if (hasMux.value && !hasYoutube.value) {
    try {
      await import('@mux/mux-player')
      muxReady.value = true
    } catch {
      playing.value = false
    }
  }
}

onBeforeUnmount(cancel)

const poster = computed(() => {
  if (props.poster) {
    return props.poster
  }
  if (props.youtubeId) {
    return `https://i.ytimg.com/vi/${props.youtubeId}/maxresdefault.jpg`
  }
  if (props.playbackId) {
    return `https://image.mux.com/${props.playbackId}/thumbnail.webp?width=1280&fit_mode=smartcrop`
  }
  return undefined
})

/** `enablejsapi` is what lets the page hear the video end and offer the next. */
const embedUrl = computed(() => {
  if (!props.youtubeId) {
    return ''
  }
  const url = new URL(`https://www.youtube-nocookie.com/embed/${props.youtubeId}`)
  url.searchParams.set('autoplay', '1')
  url.searchParams.set('rel', '0')
  url.searchParams.set('modestbranding', '1')
  url.searchParams.set('enablejsapi', '1')
  return url.toString()
})

const watchUrl = computed(() =>
  props.youtubeId ? `https://www.youtube.com/watch?v=${props.youtubeId}` : undefined
)

// YouTube posts player-state messages when `enablejsapi` is on. State 0 is
// "ended", which is the only one this page needs.
useEventListener('message', (event: MessageEvent) => {
  if (!playing.value || !hasYoutube.value) {
    return
  }
  if (typeof event.origin !== 'string' || !event.origin.includes('youtube')) {
    return
  }
  try {
    const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data
    if (data?.event === 'infoDelivery' && data?.info?.playerState === 0) {
      onEnded()
    }
  } catch {
    // Not a message meant for us.
  }
})
</script>

<template>
  <div class="player">
    <iframe
      v-if="playing && hasYoutube"
      :src="embedUrl"
      :title="title"
      class="player__frame"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      referrerpolicy="strict-origin-when-cross-origin"
      allowfullscreen
    />

    <mux-player
      v-else-if="playing && muxReady"
      :playback-id="playbackId"
      :metadata-video-title="title"
      :poster="poster"
      stream-type="on-demand"
      accent-color="#4338ca"
      class="player__frame"
      @ended="onEnded"
    />

    <!-- Poster. A real anchor to YouTube underneath, so it works with no
         JavaScript and can be middle-clicked. -->
    <component
      :is="watchUrl ? 'a' : 'div'"
      v-else
      :href="watchUrl"
      target="_blank"
      rel="noopener"
      class="player__poster"
      :class="!hasVideo && 'player__poster--empty'"
      @click="hasVideo ? ($event.preventDefault(), play()) : undefined"
    >
      <img
        v-if="poster"
        :src="poster"
        :alt="title"
        class="player__still"
      >
      <div
        v-else
        class="absolute inset-0 guide-contour opacity-40"
      />

      <div class="player__scrim" />

      <div class="player__overlay">
        <template v-if="hasVideo">
          <span class="player__play">
            <UIcon
              name="i-lucide-play"
              class="size-7"
            />
          </span>
          <p class="player__hint">
            Nothing loads from YouTube until you press play
          </p>
        </template>

        <template v-else>
          <p class="player__kicker">
            Episode {{ String(episode).padStart(2, '0') }}
          </p>
          <p class="player__title">
            {{ title }}
          </p>
          <span class="player__badge">
            <UIcon
              name="i-lucide-clapperboard"
              class="size-4"
            />
            Not filmed yet<span v-if="runtime"> · about {{ runtime }}</span>
          </span>
          <p class="player__hint">
            The full script is below. It was written to be read.
          </p>
        </template>
      </div>
    </component>

    <Transition name="up-next">
      <div
        v-if="ended && next"
        class="player__next"
      >
        <div class="text-center px-6">
          <p class="player__kicker">
            Up next · Episode {{ String(next.episode).padStart(2, '0') }}
          </p>
          <p class="player__title">
            {{ next.title }}
          </p>

          <div class="mt-5 flex items-center justify-center gap-3">
            <UButton
              :to="next.path"
              :label="`Play now · ${remaining}s`"
              icon="i-lucide-play"
              @click="cancel"
            />
            <UButton
              label="Stay here"
              color="neutral"
              variant="subtle"
              @click="cancel"
            />
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.player {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--ui-border);
  background: var(--guide-inverse-bg);
  aspect-ratio: 16 / 9;
}

.player__frame {
  display: block;
  width: 100%;
  height: 100%;
  border: 0;
}

.player__poster {
  position: relative;
  display: block;
  width: 100%;
  height: 100%;
}

.player__still {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.player__scrim {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(to top, rgb(0 0 0 / 0.7), transparent 60%),
    radial-gradient(ellipse 70% 70% at 50% 0%, color-mix(in oklab, var(--color-guide-600) 35%, transparent), transparent);
}

.player__poster--empty .player__scrim {
  background: radial-gradient(ellipse 70% 70% at 50% 0%, color-mix(in oklab, var(--color-guide-600) 45%, transparent), transparent);
}

.player__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  text-align: center;
  padding: 1.5rem;
  color: var(--guide-inverse-ink);
}

.player__play {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 4.25rem;
  height: 4.25rem;
  border-radius: 999px;
  background: var(--color-guide-600);
  color: white;
  box-shadow: 0 10px 30px rgb(0 0 0 / 0.45);
  transition: transform var(--dgm-t-fast) var(--dgm-ease);
  padding-left: 0.2rem;
}

.player__poster:hover .player__play {
  transform: scale(1.08);
}

.player__kicker {
  font-size: 0.6875rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--guide-inverse-muted);
}

.player__title {
  font-family: var(--font-display);
  font-size: clamp(1.25rem, 3vw, 1.875rem);
  font-weight: 700;
  text-wrap: balance;
}

.player__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgb(255 255 255 / 0.2);
  font-size: 0.8125rem;
  color: var(--guide-inverse-muted);
}

.player__hint {
  font-size: 0.75rem;
  color: var(--guide-inverse-muted);
  max-width: 22rem;
}

.player__next {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: color-mix(in oklab, var(--guide-inverse-bg) 90%, transparent);
  backdrop-filter: blur(6px);
  color: var(--guide-inverse-ink);
}

.up-next-enter-active,
.up-next-leave-active {
  transition: opacity var(--dgm-t-base) var(--dgm-ease);
}

.up-next-enter-from,
.up-next-leave-to {
  opacity: 0;
}
</style>
