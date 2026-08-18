<script setup lang="ts">
/**
 * The story hero: a video loop under a gradient, with the two ways in.
 *
 * The video is decoration, so it is treated as decoration — poster first,
 * `preload="none"`, and it never plays at all under reduced motion or on a
 * connection that says it is metered. A hero video that autoplays on mobile data
 * is a cost decision as much as a design one, and the person this site is for is
 * exactly the person who notices.
 */
withDefaults(defineProps<{
  poster?: string
  /** Looping background clip. Nothing plays without one. */
  src?: string
  episodes?: number
  filmed?: number
}>(), {
  episodes: 0,
  filmed: 0
})

const video = useTemplateRef<HTMLVideoElement>('video')

const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')

onMounted(() => {
  if (reduced.value) {
    return
  }

  // `saveData` is set when the reader has asked their browser to use less data.
  const connection = (navigator as unknown as { connection?: { saveData?: boolean } }).connection
  if (connection?.saveData) {
    return
  }

  video.value?.play().catch(() => {
    // Autoplay blocked. The poster is already showing, so there is nothing to do.
  })
})
</script>

<template>
  <section class="story-hero">
    <div class="story-hero__media">
      <video
        v-if="src"
        ref="video"
        :poster="poster"
        class="story-hero__video"
        muted
        loop
        playsinline
        preload="none"
        aria-hidden="true"
        tabindex="-1"
      >
        <source
          :src="src"
          type="video/mp4"
        >
      </video>

      <!-- No clip yet: the contour grid stands in, and the gradient below makes
           the two cases look like the same design rather than a missing asset. -->
      <div
        v-else
        class="story-hero__fallback guide-contour"
      />

      <div class="story-hero__wash" />
    </div>

    <UContainer class="relative py-20 lg:py-28">
      <div class="max-w-2xl">
        <p class="flex items-center gap-2 text-sm text-[color:var(--guide-inverse-muted)]">
          <UIcon
            name="i-lucide-clapperboard"
            class="size-4"
          />
          Written and narrated by Swarnil
        </p>

        <h1 class="font-display text-4xl sm:text-5xl xl:text-6xl font-bold mt-4 text-[color:var(--guide-inverse-ink)] tracking-tight text-balance">
          My story
        </h1>

        <p class="mt-5 text-lg text-[color:var(--guide-inverse-muted)] text-balance">
          Average student, no plan, no guidance, and a family that could not fund
          one. Kota to Mahroni to Bangalore to Budapest — with every rejection and
          every number left in.
        </p>

        <!-- Two doors, equal weight. Some people will never watch a video and
             some will never read two thousand words; neither should have to
             work out that the other version exists. -->
        <div class="mt-9 flex flex-wrap gap-3">
          <UButton
            to="/series/read"
            label="Read the story"
            icon="i-lucide-book-open"
            size="xl"
          />
          <UButton
            to="/series/always-seventy-percent"
            label="Watch the story"
            icon="i-lucide-play"
            size="xl"
            color="neutral"
            variant="subtle"
          />
        </div>

        <p
          v-if="episodes"
          class="mt-6 text-sm text-[color:var(--guide-inverse-muted)]"
        >
          {{ episodes }} episodes written · {{ filmed }} filmed. The scripts go up
          before the videos do — they were written to be read.
        </p>
      </div>
    </UContainer>
  </section>
</template>

<style scoped>
.story-hero {
  position: relative;
  overflow: hidden;
  background: var(--guide-inverse-bg);
  color: var(--guide-inverse-ink);
}

.story-hero__media {
  position: absolute;
  inset: 0;
}

.story-hero__video,
.story-hero__fallback {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Blend mode over a dark plate: the footage reads as texture rather than as a
   picture competing with the headline for attention. */
.story-hero__video {
  mix-blend-mode: luminosity;
  opacity: 0.45;
}

.story-hero__fallback {
  opacity: 0.5;
}

.story-hero__wash {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      100deg,
      var(--guide-inverse-bg) 12%,
      color-mix(in oklab, var(--guide-inverse-bg) 70%, transparent) 48%,
      color-mix(in oklab, var(--color-guide-600) 45%, transparent) 100%
    ),
    radial-gradient(ellipse 60% 80% at 85% 20%, color-mix(in oklab, var(--color-spark-500) 30%, transparent), transparent);
}
</style>
