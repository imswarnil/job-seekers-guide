<script setup lang="ts">
import { logoTrail } from '~/utils/logo'

defineProps<{
  /** Mark only, no wordmark — for tight spaces and the mobile header. */
  markOnly?: boolean
  /** Replay the draw on hover. On for the header, off everywhere else. */
  interactive?: boolean
}>()
</script>

<template>
  <span
    class="app-logo inline-flex items-center gap-2.5"
    :class="interactive && 'app-logo--interactive'"
  >
    <!-- A start node, a trail, a destination. No plate: the mark sits directly
         on the surface, so it works on the dark hero band and the light header
         without a second version of itself.

         The trail draws itself from the start node outward and the destination
         arrives last. Pure CSS on server-rendered SVG, so the prerendered HTML
         already holds the finished mark — nothing flashes, nothing waits for
         JavaScript, and under reduced motion it is simply a still logo. -->
    <svg
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="size-7 shrink-0 overflow-visible"
      aria-hidden="true"
    >
      <path
        :d="logoTrail.path"
        class="app-logo__trail stroke-primary"
        stroke-width="2.75"
        stroke-linecap="round"
        fill="none"
      />

      <circle
        v-for="(stop, index) in logoTrail.stops"
        :key="index"
        :cx="stop.cx"
        :cy="stop.cy"
        :r="stop.r"
        class="app-logo__stop fill-primary-300"
        :style="{ '--step': index + 1 }"
      />

      <circle
        :cx="logoTrail.start.cx"
        :cy="logoTrail.start.cy"
        :r="logoTrail.start.r"
        class="app-logo__start fill-primary"
      />

      <!-- The destination gets a halo, because it is the only part of the mark
           that is about somewhere you have not been yet. -->
      <circle
        :cx="logoTrail.end.cx"
        :cy="logoTrail.end.cy"
        :r="logoTrail.end.r + 2.5"
        class="app-logo__halo fill-secondary-400"
      />
      <circle
        :cx="logoTrail.end.cx"
        :cy="logoTrail.end.cy"
        :r="logoTrail.end.r"
        class="app-logo__end fill-secondary-400"
      />
    </svg>

    <span
      v-if="!markOnly"
      class="font-display font-semibold text-lg tracking-tight text-highlighted whitespace-nowrap"
    >
      Job Seekers <span class="text-primary">Guide</span>
    </span>
  </span>
</template>

<style scoped>
.app-logo__trail {
  stroke-dasharray: 34;
  animation: logo-draw var(--dgm-t-slow) var(--dgm-ease) backwards;
}

@keyframes logo-draw {
  from {
    stroke-dashoffset: 34;
  }
  to {
    stroke-dashoffset: 0;
  }
}

.app-logo__start {
  transform-box: fill-box;
  transform-origin: center;
  animation: logo-pop var(--dgm-t-base) var(--dgm-ease) backwards;
}

.app-logo__stop {
  transform-box: fill-box;
  transform-origin: center;
  animation: logo-pop var(--dgm-t-base) var(--dgm-ease) backwards;
  animation-delay: calc(var(--dgm-stagger) * var(--step));
}

.app-logo__end {
  transform-box: fill-box;
  transform-origin: center;
  animation: logo-pop var(--dgm-t-base) var(--dgm-ease) backwards;
  animation-delay: calc(var(--dgm-stagger) * 3);
}

@keyframes logo-pop {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: none;
    opacity: 1;
  }
}

.app-logo__halo {
  transform-box: fill-box;
  transform-origin: center;
  opacity: 0.18;
  animation: logo-halo 2.6s var(--dgm-ease) infinite;
  animation-delay: calc(var(--dgm-stagger) * 3);
  animation-play-state: var(--ill-play);
}

@keyframes logo-halo {
  0%, 100% { transform: scale(0.72); opacity: 0.22 }
  50% { transform: scale(1); opacity: 0.06 }
}

/* Hovering the header replays the draw. Restarting a CSS animation needs the
   element to leave and re-enter the animated state, which the name swap does
   without any JavaScript keeping a key around. */
.app-logo--interactive:hover .app-logo__trail {
  animation-name: logo-draw-again;
}

@keyframes logo-draw-again {
  from {
    stroke-dashoffset: 34;
  }
  to {
    stroke-dashoffset: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  .app-logo__trail,
  .app-logo__start,
  .app-logo__stop,
  .app-logo__end,
  .app-logo__halo,
  .app-logo--interactive:hover .app-logo__trail {
    animation: none;
  }

  .app-logo__halo {
    opacity: 0.16;
  }
}
</style>
