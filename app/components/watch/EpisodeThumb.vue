<script setup lang="ts">
/**
 * An episode still, before there is a still.
 *
 * This used to generate its own artwork per episode — a hue from the number, a
 * pattern, sprocket holes — and ten of them side by side in a running order was
 * a rainbow that fought the thing it was a list of. One neutral frame and one
 * icon now: the icon says which episode this is, and the panel stays a list.
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

    <div class="thumb__frame">
      <img
        v-if="poster"
        :src="poster"
        :alt="title || `Episode ${episode}`"
        loading="lazy"
        class="thumb__img"
      >

      <UIcon
        v-else
        :name="glyph"
        class="thumb__glyph"
      />

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
  background: var(--ui-bg-elevated);
  border: 1px solid var(--ui-border);
  display: grid;
  place-items: center;
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

.thumb__glyph {
  width: 1.15rem;
  height: 1.15rem;
  color: var(--ui-text-dimmed);
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

/* Themed, not white: the frame is a light surface now, and a white glyph with
   a drop shadow on it was invisible in daylight. */
.thumb__play {
  position: absolute;
  right: 0.25rem;
  bottom: 0.25rem;
  width: 0.85rem;
  height: 0.85rem;
  color: var(--ui-primary);
}
</style>
