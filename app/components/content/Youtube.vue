<script setup lang="ts">
/**
 * ::youtube — a video, with a start and an end, costing nothing until clicked.
 *
 * A normal YouTube embed loads roughly a megabyte of third-party JavaScript and
 * sets cookies before anybody presses play, on every lesson that has one. This
 * renders a poster image and a real link; the iframe appears on click.
 *
 * ```md
 * ::youtube{id="dQw4w9WgXcQ" start="42" end="940" title="What a process is"}
 * The three minutes that explain scheduling better than the textbook.
 * ::
 * ```
 *
 * `start` is honoured by YouTube directly. `end` is advisory — the player is
 * asked to stop there, and the caption says so rather than pretending.
 */
const props = defineProps<{
  id: string
  /** Seconds. */
  start?: number | string
  /** Seconds. */
  end?: number | string
  title?: string
}>()

const playing = ref(false)

const startSeconds = computed(() => Number(props.start) || 0)
const endSeconds = computed(() => Number(props.end) || 0)

const poster = computed(() => `https://i.ytimg.com/vi/${props.id}/hqdefault.jpg`)

const watchUrl = computed(() => {
  const url = new URL(`https://www.youtube.com/watch`)
  url.searchParams.set('v', props.id)
  if (startSeconds.value) {
    url.searchParams.set('t', `${startSeconds.value}s`)
  }
  return url.toString()
})

const embedUrl = computed(() => {
  const url = new URL(`https://www.youtube-nocookie.com/embed/${props.id}`)
  url.searchParams.set('autoplay', '1')
  url.searchParams.set('rel', '0')
  url.searchParams.set('modestbranding', '1')
  if (startSeconds.value) {
    url.searchParams.set('start', String(startSeconds.value))
  }
  if (endSeconds.value) {
    url.searchParams.set('end', String(endSeconds.value))
  }
  return url.toString()
})

function clock(seconds: number) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

const range = computed(() => {
  if (!startSeconds.value && !endSeconds.value) {
    return ''
  }
  if (startSeconds.value && endSeconds.value) {
    return `${clock(startSeconds.value)} – ${clock(endSeconds.value)}`
  }
  return startSeconds.value ? `from ${clock(startSeconds.value)}` : `to ${clock(endSeconds.value)}`
})
</script>

<template>
  <figure class="dgm-figure not-prose">
    <div class="youtube">
      <iframe
        v-if="playing"
        :src="embedUrl"
        :title="title || 'YouTube video'"
        class="youtube__frame"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen
      />

      <!-- A real anchor, so it works with no JavaScript and can be
           middle-clicked. The click handler intercepts it when it can. -->
      <a
        v-else
        :href="watchUrl"
        target="_blank"
        rel="noopener"
        class="youtube__poster"
        @click.prevent="playing = true"
      >
        <img
          :src="poster"
          :alt="title ? `Play: ${title}` : 'Play video'"
          loading="lazy"
          width="480"
          height="360"
        >

        <span class="youtube__play">
          <UIcon
            name="i-lucide-play"
            class="size-6"
          />
        </span>

        <span
          v-if="range"
          class="youtube__range"
        >{{ range }}</span>
      </a>
    </div>

    <figcaption class="dgm-caption">
      <span
        v-if="title"
        class="text-highlighted"
      >{{ title }}</span>
      <slot />
    </figcaption>
  </figure>
</template>

<style scoped>
.youtube {
  position: relative;
  aspect-ratio: 16 / 9;
  border-radius: var(--dgm-box-radius);
  overflow: hidden;
  background: var(--color-ink-950);
  border: 1px solid var(--dgm-box-border);
}

.youtube__frame,
.youtube__poster {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
}

.youtube__poster {
  display: block;
}

/* The 4:3 thumbnail YouTube serves is cropped to the 16:9 frame rather than
   letterboxed, which is what the real player does too. */
.youtube__poster img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.youtube__play {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  background: rgb(0 0 0 / 0.25);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.youtube__play::before {
  content: '';
  position: absolute;
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 999px;
  background: var(--color-guide-600);
  box-shadow: 0 6px 20px rgb(0 0 0 / 0.35);
  transition: transform var(--dgm-t-fast) var(--dgm-ease);
}

.youtube__poster:hover .youtube__play {
  background: rgb(0 0 0 / 0.1);
}

.youtube__poster:hover .youtube__play::before {
  transform: scale(1.08);
}

.youtube__play > * {
  position: relative;
  margin-left: 0.125rem;
}

.youtube__range {
  position: absolute;
  right: 0.625rem;
  bottom: 0.625rem;
  padding: 0.125rem 0.5rem;
  border-radius: var(--radius-xs);
  background: rgb(0 0 0 / 0.7);
  color: white;
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
}
</style>
