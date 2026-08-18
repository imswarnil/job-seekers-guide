<script setup lang="ts">
import { logoMark } from '~/utils/logo'

/**
 * A release cover, generated rather than designed.
 *
 * Nobody is going to draw artwork for every changelog entry, and a changelog
 * with no artwork at all is a wall of dates. So each release gets a cover made
 * from its own version number: the number decides the hue within the brand
 * range and the pattern behind it, so 1.2 and 1.3 look like siblings and 2.0
 * looks like a different season.
 *
 * Deterministic — no randomness anywhere, because the server render and the
 * hydrated one have to agree, and a cover that changes on reload is a bug the
 * reader cannot report.
 */
const props = defineProps<{
  version?: string
  codename?: string
  date?: string | Date
}>()

/** Small, stable string hash. Only needs to be well spread, not secure. */
function hash(input: string) {
  let h = 2166136261
  for (let i = 0; i < input.length; i++) {
    h ^= input.charCodeAt(i)
    h = Math.imul(h, 16777619)
  }
  return Math.abs(h)
}

const seed = computed(() => hash(props.version || props.codename || 'release'))

/** Constrained to the brand's own arc — indigo through to teal, nothing else. */
const hue = computed(() => 244 + (seed.value % 5) * 14)
const pattern = computed(() => ['grid', 'dots', 'rays', 'rings'][seed.value % 4])

/** `1.2.0` → major `1`, so a major release reads as one at a glance. */
const major = computed(() => (props.version || '').split('.')[0] || '')

const formatter = new Intl.DateTimeFormat('en-GB', { month: 'short', year: 'numeric' })
const stamp = computed(() => props.date ? formatter.format(new Date(props.date)) : '')
</script>

<template>
  <div
    class="rt"
    :style="{ '--hue': hue }"
    :data-pattern="pattern"
  >
    <span class="rt__pattern" />

    <!-- The major version, oversized and half off the plate. It is wallpaper
         rather than information — the readable number is in the middle. -->
    <span
      v-if="major"
      class="rt__ghost"
      aria-hidden="true"
    >{{ major }}</span>

    <div class="rt__body">
      <p
        v-if="version"
        class="rt__version"
      >
        v{{ version }}
      </p>
      <p
        v-if="codename"
        class="rt__codename"
      >
        {{ codename }}
      </p>
      <span class="rt__rule" />
    </div>

    <svg
      class="rt__mark"
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <rect
        v-for="(row, index) in logoMark.rows"
        :key="index"
        :x="row.x"
        :y="row.y"
        :width="row.w"
        :height="row.h"
        :rx="row.h / 2"
        :fill="row.match ? '#5eead4' : 'rgba(244,245,255,0.55)'"
      />
      <circle
        :cx="logoMark.lens.cx"
        :cy="logoMark.lens.cy"
        :r="logoMark.lens.r"
        stroke="rgba(244,245,255,0.8)"
        :stroke-width="logoMark.lensWidth"
        fill="none"
      />
      <path
        :d="logoMark.handle"
        stroke="rgba(244,245,255,0.8)"
        :stroke-width="logoMark.handleWidth"
        stroke-linecap="round"
        fill="none"
      />
    </svg>

    <p
      v-if="stamp"
      class="rt__date"
    >
      {{ stamp }}
    </p>
  </div>
</template>

<style scoped>
.rt {
  /* The type sizes below are in `cqw`, so the cover has to declare itself a
     container or they resolve against the viewport and the number is enormous
     on a phone. */
  container-type: inline-size;
  position: relative;
  aspect-ratio: 16 / 10;
  border-radius: var(--radius-md);
  overflow: hidden;
  display: grid;
  place-items: center;
  isolation: isolate;
  background:
    linear-gradient(140deg, hsl(var(--hue) 62% 34%), hsl(var(--hue) 70% 16%) 62%, #14122f);
  border: 1px solid rgb(255 255 255 / 0.1);
}

.rt__pattern {
  position: absolute;
  inset: 0;
  opacity: 0.22;
}

.rt[data-pattern='grid'] .rt__pattern {
  background-image:
    linear-gradient(to right, rgb(255 255 255 / 0.55) 1px, transparent 1px),
    linear-gradient(to bottom, rgb(255 255 255 / 0.55) 1px, transparent 1px);
  background-size: 16px 16px;
}

.rt[data-pattern='dots'] .rt__pattern {
  background-image: radial-gradient(rgb(255 255 255 / 0.7) 1px, transparent 1px);
  background-size: 12px 12px;
}

.rt[data-pattern='rays'] .rt__pattern {
  background-image: repeating-linear-gradient(
    -45deg,
    rgb(255 255 255 / 0.45) 0 1px,
    transparent 1px 12px
  );
}

.rt[data-pattern='rings'] .rt__pattern {
  background-image: repeating-radial-gradient(
    circle at 18% 110%,
    rgb(255 255 255 / 0.45) 0 1px,
    transparent 1px 16px
  );
}

.rt__ghost {
  position: absolute;
  right: -0.12em;
  bottom: -0.34em;
  font-family: var(--font-display);
  font-size: clamp(4rem, 34cqw, 9rem);
  font-weight: 800;
  line-height: 1;
  color: rgb(255 255 255 / 0.09);
  user-select: none;
}

/* The readable half: number, name, rule — centred, the way a title page is. */
.rt__body {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1rem;
}

.rt__version {
  font-family: var(--font-display);
  font-size: clamp(1.15rem, 9cqw, 1.75rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #f4f5ff;
  font-variant-numeric: tabular-nums;
}

.rt__codename {
  margin-top: 0.25rem;
  font-size: clamp(0.5rem, 3.2cqw, 0.6875rem);
  font-weight: 600;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #5eead4;
  text-wrap: balance;
}

.rt__rule {
  display: block;
  width: 1.75rem;
  height: 2px;
  margin-top: 0.6rem;
  border-radius: 999px;
  background: #2dd4bf;
  opacity: 0.8;
}

.rt__mark {
  position: absolute;
  top: 0.6rem;
  left: 0.6rem;
  width: 1.1rem;
  height: 1.1rem;
  z-index: 2;
}

.rt__date {
  position: absolute;
  top: 0.65rem;
  right: 0.7rem;
  z-index: 2;
  font-size: 0.5625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgb(244 245 255 / 0.5);
}
</style>
