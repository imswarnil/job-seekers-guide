<script setup lang="ts">
import { logoBarOpacity, logoBars } from '~/utils/logo'

defineProps<{
  /** Mark only, no wordmark — for tight spaces and the mobile header. */
  markOnly?: boolean
  /** Replay the rise on hover. On for the header, off everywhere else. */
  interactive?: boolean
}>()
</script>

<template>
  <span
    class="app-logo inline-flex items-center gap-2.5"
    :class="interactive && 'app-logo--interactive'"
  >
    <!-- The bars grow from the baseline in sequence: the mark performs the idea
         it stands for. The animation is pure CSS on server-rendered SVG, so the
         prerendered HTML already contains the finished geometry — nothing
         flashes, nothing waits for JavaScript, and under reduced motion it is
         simply the static mark it has always been. -->
    <svg
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="size-7 shrink-0"
      aria-hidden="true"
    >
      <rect
        width="32"
        height="32"
        rx="9"
        class="fill-primary-600"
      />

      <path
        v-for="(bar, index) in logoBars"
        :key="bar.d"
        :d="bar.d"
        class="app-logo__bar"
        :class="bar.accent ? 'fill-secondary-400' : 'fill-white'"
        :fill-opacity="bar.accent ? undefined : logoBarOpacity[index]"
        :style="{ '--step': bar.step }"
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
.app-logo__bar {
  transform-origin: 50% 22.5px;
  animation: app-logo-rise var(--dgm-t-slow) var(--dgm-ease) backwards;
  animation-delay: calc(var(--dgm-stagger) * var(--step));
}

@keyframes app-logo-rise {
  from {
    transform: scaleY(0.12);
    opacity: 0.35;
  }
  to {
    transform: none;
  }
}

/* Hovering the header replays it. Restarting a CSS animation needs the element
   to leave and re-enter the animated state, which the name swap below does
   without any JavaScript keeping a key around. */
.app-logo--interactive:hover .app-logo__bar {
  animation-name: app-logo-rise-again;
}

@keyframes app-logo-rise-again {
  from {
    transform: scaleY(0.12);
    opacity: 0.35;
  }
  to {
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .app-logo__bar,
  .app-logo--interactive:hover .app-logo__bar {
    animation: none;
  }
}
</style>
