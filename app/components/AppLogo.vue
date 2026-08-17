<script setup lang="ts">
import { logoCard, logoLens, logoLineHeight, logoLines } from '~/utils/logo'

defineProps<{
  /** Mark only, no wordmark — for tight spaces and the mobile header. */
  markOnly?: boolean
  /** Replay the sweep on hover. On for the header, off everywhere else. */
  interactive?: boolean
}>()
</script>

<template>
  <span
    class="app-logo inline-flex items-center gap-2.5"
    :class="interactive && 'app-logo--interactive'"
  >
    <!-- A magnifier over a job listing. The glass sweeps in from the left and
         settles on the third line, which is the one in the accent colour — the
         listing you were looking for, found.

         Pure CSS on server-rendered SVG, so the prerendered HTML already holds
         the finished mark: nothing flashes, nothing waits for JavaScript, and
         under reduced motion it is simply a still logo. -->
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

      <rect
        :x="logoCard.x"
        :y="logoCard.y"
        :width="logoCard.width"
        :height="logoCard.height"
        :rx="logoCard.radius"
        class="fill-white"
        fill-opacity="0.16"
      />

      <rect
        v-for="(line, index) in logoLines"
        :key="index"
        :x="line.x"
        :y="line.y"
        :width="line.width"
        :height="logoLineHeight"
        :rx="logoLineHeight / 2"
        class="app-logo__line"
        :class="line.accent ? 'fill-secondary-400' : 'fill-white'"
        :fill-opacity="line.accent ? undefined : 0.62"
        :style="{ '--step': index }"
      />

      <g class="app-logo__glass">
        <!-- The lens is knocked out of the plate rather than tinted, so the
             listing reads as magnified rather than as covered up. -->
        <circle
          :cx="logoLens.cx"
          :cy="logoLens.cy"
          :r="logoLens.r"
          class="fill-primary-600"
          fill-opacity="0.55"
        />
        <circle
          :cx="logoLens.cx"
          :cy="logoLens.cy"
          :r="logoLens.r"
          fill="none"
          stroke="white"
          stroke-width="2"
        />
        <line
          :x1="logoLens.handle.x1"
          :y1="logoLens.handle.y1"
          :x2="logoLens.handle.x2"
          :y2="logoLens.handle.y2"
          stroke="white"
          stroke-width="2.4"
          stroke-linecap="round"
        />
      </g>
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
.app-logo__line {
  transform-origin: 6px center;
  animation: app-logo-line var(--dgm-t-base) var(--dgm-ease) backwards;
  animation-delay: calc(var(--dgm-stagger) * var(--step));
}

@keyframes app-logo-line {
  from {
    transform: scaleX(0);
    opacity: 0;
  }
  to {
    transform: none;
  }
}

.app-logo__glass {
  animation: app-logo-sweep var(--dgm-t-slow) var(--dgm-ease) backwards;
  animation-delay: calc(var(--dgm-stagger) * 2);
}

@keyframes app-logo-sweep {
  from {
    transform: translate(-9px, -5px) scale(0.82);
    opacity: 0;
  }
  to {
    transform: none;
    opacity: 1;
  }
}

/* Hovering the header replays it. Restarting a CSS animation needs the element
   to leave and re-enter the animated state, which the name swap does without
   any JavaScript keeping a key around. */
.app-logo--interactive:hover .app-logo__glass {
  animation-name: app-logo-sweep-again;
}

@keyframes app-logo-sweep-again {
  from {
    transform: translate(-9px, -5px) scale(0.82);
    opacity: 0;
  }
  to {
    transform: none;
    opacity: 1;
  }
}

@media (prefers-reduced-motion: reduce) {
  .app-logo__line,
  .app-logo__glass,
  .app-logo--interactive:hover .app-logo__glass {
    animation: none;
  }
}
</style>
