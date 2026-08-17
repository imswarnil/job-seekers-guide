<script setup lang="ts">
/**
 * The hero illustration: a magnifier moving across a column of job listings,
 * one of which lights up as it passes.
 *
 * This is the argument of the whole platform in one loop. The listings are all
 * there, all the time — nothing is hidden and nothing is scarce. What moves is
 * the glass. Finding the right one is a search problem, not a supply problem,
 * and the platform is the thing holding the glass.
 */
withDefaults(defineProps<{
  /** Rows of listing to draw. */
  rows?: number
}>(), {
  rows: 5
})

const listings = [
  { title: 40, meta: 22, match: false },
  { title: 30, meta: 26, match: false },
  { title: 44, meta: 20, match: true },
  { title: 34, meta: 24, match: false },
  { title: 38, meta: 18, match: false }
]
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg job-search"
    role="img"
    aria-label="A magnifying glass moving down a list of job openings, stopping on one"
  >
    <!-- The stack of listings. Present from the first frame: the openings are
         not the scarce thing. -->
    <g class="job-search__list">
      <g
        v-for="(listing, index) in listings.slice(0, rows)"
        :key="index"
        :transform="`translate(14 ${12 + index * 26})`"
        class="job-search__row ill-loop"
        :class="listing.match && 'job-search__row--match'"
        :style="{ '--i': index }"
      >
        <rect
          width="130"
          height="20"
          rx="5"
          class="job-search__card"
        />
        <rect
          x="9"
          y="5"
          :width="listing.title"
          height="3.5"
          rx="1.75"
          class="job-search__title"
        />
        <rect
          x="9"
          y="12"
          :width="listing.meta"
          height="2.5"
          rx="1.25"
          class="job-search__meta"
        />
        <!-- The tick only ever appears on the row the glass settles on. -->
        <g
          v-if="listing.match"
          class="job-search__tick"
        >
          <circle
            cx="120"
            cy="10"
            r="5.5"
            class="job-search__tick-disc"
          />
          <path
            d="M117.6 10.2 119.4 12 122.5 8.4"
            class="ill-stroke job-search__tick-mark"
            stroke-width="1.6"
          />
        </g>
      </g>
    </g>

    <!-- The glass. It travels down the column and pauses on the third row. -->
    <g class="job-search__glass ill-loop">
      <circle
        cx="0"
        cy="0"
        r="17"
        class="job-search__lens"
      />
      <circle
        cx="0"
        cy="0"
        r="17"
        class="ill-stroke job-search__ring"
        stroke-width="3"
      />
      <line
        x1="12.2"
        y1="12.2"
        x2="22"
        y2="22"
        class="ill-stroke job-search__handle"
        stroke-width="3.5"
      />
    </g>
  </svg>
</template>

<style scoped>
.job-search__card {
  fill: var(--ill-surface);
  stroke: var(--ill-line);
  stroke-width: 1;
}

.job-search__title {
  fill: var(--ill-muted);
  opacity: 0.55;
}

.job-search__meta {
  fill: var(--ill-muted);
  opacity: 0.32;
}

/* Rows breathe very slightly out of phase so the stack does not read as a
   single flat block, but not enough that the eye tracks any individual one. */
.job-search__row {
  animation: job-row var(--ill-loop-slow) ease-in-out infinite;
  animation-delay: calc(var(--i) * -1.4s);
}

@keyframes job-row {
  0%, 100% { opacity: 0.75 }
  50% { opacity: 1 }
}

/* The matched row lights up exactly when the glass is over it. Timed against
   the same loop rather than triggered, because nothing here is interactive. */
.job-search__row--match .job-search__card {
  animation: job-match var(--ill-loop) ease-in-out infinite;
}

@keyframes job-match {
  0%, 32% {
    fill: var(--ill-surface);
    stroke: var(--ill-line);
  }
  46%, 74% {
    fill: color-mix(in oklab, var(--ill-spark) 16%, var(--ill-surface));
    stroke: var(--ill-spark);
  }
  88%, 100% {
    fill: var(--ill-surface);
    stroke: var(--ill-line);
  }
}

.job-search__row--match .job-search__title {
  fill: var(--ill-accent);
  opacity: 0.9;
}

.job-search__tick-disc {
  fill: var(--ill-spark);
}

.job-search__tick-mark {
  stroke: var(--ui-bg);
}

.job-search__tick {
  transform-origin: 120px 10px;
  animation: job-tick var(--ill-loop) ease-in-out infinite;
}

@keyframes job-tick {
  0%, 40% { opacity: 0; transform: scale(0.4) }
  52%, 74% { opacity: 1; transform: scale(1) }
  86%, 100% { opacity: 0; transform: scale(0.4) }
}

.job-search__lens {
  fill: var(--ui-bg);
  opacity: 0.55;
}

.job-search__ring,
.job-search__handle {
  stroke: var(--ill-accent);
}

/* The travel. Starts high and left, sweeps down the column, rests on the third
   listing, then drifts off — a search that finds something rather than a
   decoration that orbits. */
.job-search__glass {
  animation: job-glass var(--ill-loop) ease-in-out infinite;
}

@keyframes job-glass {
  0% { transform: translate(52px, 20px) scale(0.92) }
  20% { transform: translate(96px, 38px) scale(0.96) }
  46% { transform: translate(78px, 74px) scale(1.04) }
  74% { transform: translate(78px, 74px) scale(1.04) }
  100% { transform: translate(52px, 20px) scale(0.92) }
}
</style>
