<script setup lang="ts">
/**
 * An episode still, before there is a still.
 *
 * Nine of the ten episodes are written but not filmed, and nine identical grey
 * rectangles would say the series had been abandoned. So each one generates its
 * own artwork from the episode number: a different hue, a different pattern and
 * a different glyph, deterministic so the same episode always looks like itself.
 *
 * The numeral behind it is the Netflix trick, and it earns its place here for a
 * reason beyond looking good — the running order *is* the story, and a number
 * you cannot miss is the cheapest way to say "start at one".
 */
const props = withDefaults(defineProps<{
  episode: number
  title?: string
  /** A real still, once one exists. */
  poster?: string
  filmed?: boolean
  size?: 'sm' | 'lg'
}>(), {
  size: 'sm'
})

/* Deterministic, not random: an SSR render and the hydrated one have to agree,
   and an episode that changes colour on reload reads as a bug. */
const seed = computed(() => props.episode * 47)
const hue = computed(() => (seed.value * 37) % 360)
const pattern = computed(() => ['grid', 'dots', 'rays', 'wave'][props.episode % 4])
const glyph = computed(() => [
  'i-lucide-map-pin',
  'i-lucide-book-open',
  'i-lucide-users',
  'i-lucide-monitor',
  'i-lucide-file-x',
  'i-lucide-utensils',
  'i-lucide-heart',
  'i-lucide-code',
  'i-lucide-wallet',
  'i-lucide-plane'
][props.episode % 10])
</script>

<template>
  <div
    class="thumb"
    :data-size="size"
  >
    <!-- The numeral sits behind and half outside the frame, the way it does on
         a shelf of ranked things. -->
    <span
      class="thumb__rank"
      aria-hidden="true"
    >{{ episode }}</span>

    <div
      class="thumb__frame"
      :style="{ '--hue': hue }"
      :data-pattern="pattern"
    >
      <img
        v-if="poster"
        :src="poster"
        :alt="title || `Episode ${episode}`"
        loading="lazy"
        class="thumb__img"
      >

      <template v-else>
        <span class="thumb__wash" />
        <span class="thumb__pattern" />

        <!-- Sprocket holes, so an empty frame still reads as film. -->
        <span class="thumb__perf thumb__perf--top" />
        <span class="thumb__perf thumb__perf--bottom" />

        <UIcon
          :name="glyph"
          class="thumb__glyph"
        />
      </template>

      <span
        v-if="!filmed"
        class="thumb__flag"
      >Script</span>
      <UIcon
        v-else
        name="i-lucide-play"
        class="thumb__play"
      />
    </div>
  </div>
</template>

<style scoped>
.thumb {
  display: flex;
  align-items: center;
  gap: 0.15rem;
}

.thumb__rank {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 2.4rem;
  line-height: 0.8;
  letter-spacing: -0.06em;
  color: transparent;
  -webkit-text-stroke: 2px var(--ui-border-accented);
  flex-shrink: 0;
  /* Pulled under the frame so the two overlap rather than sit side by side. */
  margin-right: -0.5rem;
  user-select: none;
}

.thumb[data-size='lg'] .thumb__rank {
  font-size: 4.5rem;
  -webkit-text-stroke-width: 3px;
  margin-right: -0.9rem;
}

.thumb__frame {
  position: relative;
  aspect-ratio: 16 / 9;
  width: 5.25rem;
  flex-shrink: 0;
  border-radius: var(--radius-xs);
  overflow: hidden;
  background: hsl(var(--hue) 45% 18%);
  border: 1px solid var(--ui-border);
  isolation: isolate;
}

.thumb[data-size='lg'] .thumb__frame {
  width: 11rem;
  border-radius: var(--radius-sm);
}

.thumb__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Brand colours first, the generated hue only as a tint on top — ten unrelated
   rainbow tiles would stop looking like one series. */
.thumb__wash {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(135deg, hsl(var(--hue) 55% 30% / 0.75), transparent 60%),
    linear-gradient(to top right, var(--color-guide-900), var(--color-guide-700));
}

.thumb__pattern {
  position: absolute;
  inset: 0;
  opacity: 0.28;
}

.thumb__frame[data-pattern='grid'] .thumb__pattern {
  background-image:
    linear-gradient(to right, rgb(255 255 255 / 0.5) 1px, transparent 1px),
    linear-gradient(to bottom, rgb(255 255 255 / 0.5) 1px, transparent 1px);
  background-size: 12px 12px;
}

.thumb__frame[data-pattern='dots'] .thumb__pattern {
  background-image: radial-gradient(rgb(255 255 255 / 0.6) 1px, transparent 1px);
  background-size: 9px 9px;
}

.thumb__frame[data-pattern='rays'] .thumb__pattern {
  background-image: repeating-linear-gradient(
    -45deg,
    rgb(255 255 255 / 0.35) 0 1px,
    transparent 1px 8px
  );
}

.thumb__frame[data-pattern='wave'] .thumb__pattern {
  background-image: repeating-radial-gradient(
    circle at 20% 120%,
    rgb(255 255 255 / 0.35) 0 1px,
    transparent 1px 10px
  );
}

.thumb__perf {
  position: absolute;
  left: 0;
  right: 0;
  height: 15%;
  background-image: repeating-linear-gradient(
    to right,
    rgb(0 0 0 / 0.55) 0 4px,
    transparent 4px 10px
  );
  opacity: 0.7;
}

.thumb__perf--top {
  top: 0;
}

.thumb__perf--bottom {
  bottom: 0;
}

.thumb__glyph {
  position: absolute;
  top: 50%;
  left: 50%;
  translate: -50% -50%;
  width: 1.15rem;
  height: 1.15rem;
  color: var(--color-spark-300);
  opacity: 0.9;
}

.thumb[data-size='lg'] .thumb__glyph {
  width: 2.25rem;
  height: 2.25rem;
}

.thumb__flag {
  position: absolute;
  right: 0.15rem;
  bottom: 0.15rem;
  font-size: 0.5rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 0.05rem 0.25rem;
  border-radius: 0.2rem;
  background: rgb(0 0 0 / 0.55);
  color: rgb(255 255 255 / 0.75);
}

.thumb[data-size='lg'] .thumb__flag {
  font-size: 0.625rem;
  right: 0.4rem;
  bottom: 0.4rem;
  padding: 0.1rem 0.4rem;
}

.thumb__play {
  position: absolute;
  right: 0.25rem;
  bottom: 0.25rem;
  width: 0.85rem;
  height: 0.85rem;
  color: #fff;
  filter: drop-shadow(0 1px 2px rgb(0 0 0 / 0.6));
}
</style>
