<script setup lang="ts">
const props = defineProps<{
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
 * Mux's player is a web component and about 200 KB, so it is imported
 * dynamically and only when there is actually something to play. An episode that
 * has not been filmed yet renders a poster and a state — the writing goes up
 * before the video does, which is deliberate: the scripts are worth reading on
 * their own and the series should not be a set of empty pages until it is
 * finished.
 *
 * The next-episode card is the thing that makes it feel like television rather
 * than a page with a video on it. It counts down, and any interaction cancels
 * it — an autoplay that fires while somebody is still thinking about the ending
 * is the reason people turn autoplay off everywhere else.
 */
const NEXT_SECONDS = 10

const ready = ref(false)
const failed = ref(false)
const ended = ref(false)
const remaining = ref(NEXT_SECONDS)

const hasVideo = computed(() => Boolean(props.playbackId))

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
  remaining.value = NEXT_SECONDS

  timer = setInterval(() => {
    remaining.value--
    if (remaining.value <= 0) {
      cancel()
      navigateTo(props.next!.path)
    }
  }, 1000)
}

onMounted(async () => {
  if (!hasVideo.value) {
    return
  }
  try {
    await import('@mux/mux-player')
    ready.value = true
  } catch {
    failed.value = true
  }
})

onBeforeUnmount(cancel)

// Mux generates a still from the video itself, so an unfilmed episode has no
// poster and falls through to the drawn placeholder below.
const posterUrl = computed(() => {
  if (props.poster) {
    return props.poster
  }
  if (props.playbackId) {
    return `https://image.mux.com/${props.playbackId}/thumbnail.webp?width=1280&fit_mode=smartcrop`
  }
  return undefined
})
</script>

<template>
  <div class="episode-player">
    <mux-player
      v-if="hasVideo && ready"
      :playback-id="playbackId"
      :metadata-video-title="title"
      :poster="posterUrl"
      stream-type="on-demand"
      accent-color="#4338ca"
      class="episode-player__mux"
      @ended="onEnded"
    />

    <!-- The unfilmed state. A real poster with the episode number and title, not
         a grey box: this page is worth landing on before the video exists. -->
    <div
      v-else
      class="episode-player__placeholder"
    >
      <div class="absolute inset-0 guide-contour opacity-40" />

      <div class="relative text-center px-6">
        <p class="text-xs uppercase tracking-[0.2em] text-[color:var(--guide-inverse-muted)]">
          Episode {{ String(episode).padStart(2, '0') }}
        </p>
        <p class="font-display text-2xl sm:text-3xl font-bold mt-2 text-[color:var(--guide-inverse-ink)] text-balance">
          {{ title }}
        </p>

        <p
          v-if="failed"
          class="mt-4 text-sm text-[color:var(--guide-inverse-muted)]"
        >
          The player could not load. The script is below and reads on its own.
        </p>

        <div
          v-else-if="hasVideo"
          class="mt-4 flex items-center justify-center gap-2 text-sm text-[color:var(--guide-inverse-muted)]"
        >
          <UIcon
            name="i-lucide-loader-circle"
            class="size-4 animate-spin"
          />
          Loading the player
        </div>

        <div
          v-else
          class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-1.5 text-sm text-[color:var(--guide-inverse-muted)]"
        >
          <UIcon
            name="i-lucide-clapperboard"
            class="size-4"
          />
          Not filmed yet<span v-if="runtime"> · about {{ runtime }}</span>
        </div>

        <p class="mt-4 text-sm text-[color:var(--guide-inverse-muted)] max-w-sm mx-auto">
          The full script is below. It was written to be read.
        </p>
      </div>
    </div>

    <!-- Up next, over the finished frame. -->
    <Transition name="up-next">
      <div
        v-if="ended && next"
        class="episode-player__next"
      >
        <div class="text-center px-6">
          <p class="text-xs uppercase tracking-[0.18em] text-[color:var(--guide-inverse-muted)]">
            Up next · Episode {{ String(next.episode).padStart(2, '0') }}
          </p>
          <p class="font-display text-xl sm:text-2xl font-bold mt-2 text-[color:var(--guide-inverse-ink)] text-balance">
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
.episode-player {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--ui-border);
  background: var(--guide-inverse-bg);
}

.episode-player__mux {
  display: block;
  width: 100%;
  aspect-ratio: 16 / 9;
  --controls-backdrop-color: rgb(0 0 0 / 0.4);
}

.episode-player__placeholder {
  position: relative;
  aspect-ratio: 16 / 9;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(ellipse 70% 70% at 50% 0%, color-mix(in oklab, var(--color-guide-600) 40%, transparent), transparent),
    var(--guide-inverse-bg);
}

.episode-player__next {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: color-mix(in oklab, var(--guide-inverse-bg) 88%, transparent);
  backdrop-filter: blur(6px);
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
