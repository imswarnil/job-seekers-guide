<script setup lang="ts">
/**
 * The journey, as a trail that draws itself.
 *
 * The same shape as the logo, at full size, with the stages named. It is the
 * hero's whole job: somebody arriving here has been told a hundred times to
 * "learn to code" and has never once been shown the route as a single object
 * with a beginning and an end.
 *
 * The stages are the real ones from the curriculum, in the real order. Nothing
 * here is decorative — a reader who only looks at this picture and leaves has
 * still been given the answer to "what do I do first".
 */
const stops = [
  { label: 'Where you are', note: 'No plan, no guidance', icon: 'i-lucide-map-pin' },
  { label: 'Orientation', note: 'What the industry actually is', icon: 'i-lucide-compass' },
  { label: 'Foundations', note: 'OS, databases, networks', icon: 'i-lucide-layers' },
  { label: 'One language', note: 'Java, properly', icon: 'i-lucide-code' },
  { label: 'Projects', note: 'Proof you can build', icon: 'i-lucide-hammer' },
  { label: 'The job hunt', note: 'Taught as a subject', icon: 'i-lucide-briefcase' },
  { label: 'Employed', note: 'The first offer', icon: 'i-lucide-flag' }
]

const root = useTemplateRef<HTMLElement>('root')
const armed = ref(false)
const seen = useElementVisibility(root)
const arrived = ref(false)

onMounted(() => {
  armed.value = true
})

watch(seen, (value) => {
  if (value) {
    arrived.value = true
  }
})
</script>

<template>
  <div
    ref="root"
    class="hero-trail"
    :class="[armed && 'hero-trail--armed', arrived && 'hero-trail--in']"
  >
    <!-- The spine, drawn behind every stop so the gaps never show through. It
         is one continuous line on purpose: the argument is that this is a single
         route, not seven unrelated things to learn. -->
    <span
      class="hero-trail__spine"
      aria-hidden="true"
    />
    <span
      class="hero-trail__progress"
      aria-hidden="true"
    />

    <ol class="hero-trail__list">
      <li
        v-for="(stop, index) in stops"
        :key="stop.label"
        class="hero-trail__stop"
        :data-first="index === 0 || undefined"
        :data-last="index === stops.length - 1 || undefined"
        :style="{ '--i': index }"
      >
        <span class="hero-trail__node">
          <UIcon
            :name="stop.icon"
            class="size-3.5"
          />
        </span>

        <span class="min-w-0">
          <span class="hero-trail__label">{{ stop.label }}</span>
          <span class="hero-trail__note">{{ stop.note }}</span>
        </span>
      </li>
    </ol>
  </div>
</template>

<style scoped>
.hero-trail {
  position: relative;
  padding-block: 0.25rem;
}

.hero-trail__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.hero-trail__spine,
.hero-trail__progress {
  position: absolute;
  left: 13px;
  top: 20px;
  bottom: 20px;
  width: 2px;
  border-radius: 2px;
}

.hero-trail__spine {
  background: var(--ui-border-accented);
}

/* The travelled part of the line grows downward over the same window the nodes
   appear in, so the trail reads as being walked rather than assembled. */
.hero-trail__progress {
  background: linear-gradient(to bottom, var(--ui-primary), var(--ui-secondary));
  transform-origin: top;
  transform: scaleY(1);
}

.hero-trail--armed .hero-trail__progress {
  transform: scaleY(0);
  transition: transform 1.6s var(--dgm-ease);
}

.hero-trail--armed.hero-trail--in .hero-trail__progress {
  transform: scaleY(1);
}

.hero-trail__stop {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  position: relative;
}

.hero-trail--armed .hero-trail__stop {
  opacity: 0;
  transform: translateX(-6px);
  transition:
    opacity var(--dgm-t-base) var(--dgm-ease),
    transform var(--dgm-t-base) var(--dgm-ease);
  transition-delay: calc(var(--i) * 0.16s);
}

.hero-trail--armed.hero-trail--in .hero-trail__stop {
  opacity: 1;
  transform: none;
}

.hero-trail__node {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-border-accented);
  color: var(--ui-text-dimmed);
}

/* First and last are the two that matter: where you are, and where this ends. */
.hero-trail__stop[data-first] .hero-trail__node {
  border-color: var(--ui-primary);
  background: color-mix(in oklab, var(--ui-primary) 12%, var(--ui-bg));
  color: var(--ui-primary);
}

.hero-trail__stop[data-last] .hero-trail__node {
  border-color: var(--ui-secondary);
  background: color-mix(in oklab, var(--ui-secondary) 16%, var(--ui-bg));
  color: var(--ui-secondary);
}

.hero-trail__stop[data-last] .hero-trail__node::after {
  content: '';
  position: absolute;
  inset: -6px;
  border-radius: 999px;
  background: var(--ui-secondary);
  opacity: 0.16;
  animation: hero-trail-halo 2.6s var(--dgm-ease) infinite;
  animation-play-state: var(--ill-play);
}

@keyframes hero-trail-halo {
  0%, 100% { transform: scale(0.72); opacity: 0.22 }
  50% { transform: scale(1); opacity: 0.05 }
}

.hero-trail__label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
  line-height: 1.3;
}

.hero-trail__note {
  display: block;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  line-height: 1.35;
}
</style>
