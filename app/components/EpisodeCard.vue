<script setup lang="ts">
import type { SeriesCollectionItem } from '@nuxt/content'

const props = defineProps<{
  episode: SeriesCollectionItem
  /** The next unwatched one gets the big treatment. */
  featured?: boolean
}>()

const poster = computed(() =>
  props.episode.poster
  || (props.episode.muxPlaybackId
    ? `https://image.mux.com/${props.episode.muxPlaybackId}/thumbnail.webp?width=640&fit_mode=smartcrop`
    : undefined)
)

const number = computed(() => String(props.episode.episode).padStart(2, '0'))
</script>

<template>
  <NuxtLink
    :to="episode.path"
    class="episode-card group"
    :class="featured && 'episode-card--featured'"
  >
    <div class="episode-card__art">
      <img
        v-if="poster"
        :src="poster"
        :alt="episode.title"
        loading="lazy"
        class="size-full object-cover"
      >

      <!-- No still yet, so the card draws its own: the episode number, large,
           on the brand gradient. Ten identical grey rectangles would make the
           series look abandoned rather than upcoming. -->
      <div
        v-else
        class="episode-card__art-fallback"
      >
        <span class="episode-card__number">{{ number }}</span>
      </div>

      <span class="episode-card__play">
        <UIcon
          :name="episode.muxPlaybackId ? 'i-lucide-play' : 'i-lucide-book-open'"
          class="size-5"
        />
      </span>

      <span
        v-if="episode.runtime"
        class="episode-card__runtime"
      >{{ episode.runtime }}</span>
    </div>

    <div class="mt-3">
      <p class="flex items-center gap-2 text-xs text-dimmed">
        <span class="tabular-nums">Episode {{ number }}</span>
        <span v-if="episode.year">· {{ episode.year }}</span>
        <UBadge
          v-if="!episode.muxPlaybackId"
          label="Script only"
          color="neutral"
          variant="subtle"
          size="sm"
          class="ms-auto"
        />
      </p>

      <h3 class="font-display font-semibold text-highlighted mt-1 group-hover:text-primary transition-colors text-balance">
        {{ episode.title }}
      </h3>

      <p class="text-sm text-muted mt-1 line-clamp-2">
        {{ episode.description }}
      </p>

      <!-- The hook, on the card. This is a series; the reason to click the next
           one is the line the last one ended on. -->
      <p
        v-if="featured && episode.cliffhanger"
        class="mt-3 text-sm italic text-toned border-l-2 border-primary/40 pl-3"
      >
        {{ episode.cliffhanger }}
      </p>
    </div>
  </NuxtLink>
</template>

<style scoped>
.episode-card__art {
  position: relative;
  aspect-ratio: 16 / 9;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid var(--ui-border);
  background: var(--guide-inverse-bg);
}

.episode-card__art-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(ellipse 80% 80% at 30% 0%, color-mix(in oklab, var(--color-guide-600) 55%, transparent), transparent),
    var(--guide-inverse-bg);
}

.episode-card__number {
  font-family: var(--font-display);
  font-size: 3rem;
  font-weight: 800;
  line-height: 1;
  color: rgb(255 255 255 / 0.22);
  font-variant-numeric: tabular-nums;
}

.episode-card__play {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  background: rgb(0 0 0 / 0.15);
  opacity: 0;
  transition: opacity var(--dgm-t-fast) var(--dgm-ease);
}

.episode-card__play::before {
  content: '';
  position: absolute;
  width: 3rem;
  height: 3rem;
  border-radius: 999px;
  background: var(--color-guide-600);
}

.episode-card__play > * {
  position: relative;
}

.episode-card:hover .episode-card__play,
.episode-card:focus-visible .episode-card__play {
  opacity: 1;
}

.episode-card__runtime {
  position: absolute;
  right: 0.5rem;
  bottom: 0.5rem;
  padding: 0.125rem 0.45rem;
  border-radius: var(--radius-xs);
  background: rgb(0 0 0 / 0.68);
  color: white;
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
}

.episode-card--featured .episode-card__number {
  font-size: 5rem;
}
</style>
