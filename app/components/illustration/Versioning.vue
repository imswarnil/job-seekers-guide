<script setup lang="ts">
/**
 * A commit history with a branch coming off it and merging back.
 *
 * Version control is the first thing a new job assumes you already know and the
 * last thing a curriculum teaches. The shape is the explanation: a line of
 * commits, a branch, a merge back.
 */
const trunk = [24, 56, 88, 152, 184]
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg versioning"
    role="img"
    aria-label="A line of commits with a branch splitting off and merging back in"
  >
    <!-- The trunk. -->
    <line
      x1="16"
      y1="96"
      x2="192"
      y2="96"
      class="ill-stroke versioning__trunk"
      stroke-width="2"
    />

    <!-- The branch: out at the third commit, back in at the fourth. -->
    <path
      d="M88 96c14 0 14-40 28-40h20c14 0 14 40 16 40"
      class="ill-stroke versioning__branch ill-loop"
      stroke-width="2"
    />

    <circle
      v-for="(x, index) in trunk"
      :key="`t${index}`"
      :cx="x"
      cy="96"
      r="6"
      class="versioning__commit ill-loop"
      :style="{ '--i': index }"
    />

    <circle
      v-for="(x, index) in [116, 140]"
      :key="`b${index}`"
      :cx="x"
      cy="56"
      r="5"
      class="versioning__commit versioning__commit--branch ill-loop"
      :style="{ '--i': index + 3 }"
    />
  </svg>
</template>

<style scoped>
.versioning__trunk {
  stroke: var(--ill-line);
}

.versioning__branch {
  stroke: var(--ill-spark);
  stroke-dasharray: 140;
  animation: ver-branch var(--ill-loop) ease-in-out infinite;
}

@keyframes ver-branch {
  0%, 10% { stroke-dashoffset: 140 }
  55%, 88% { stroke-dashoffset: 0 }
  98%, 100% { stroke-dashoffset: 140 }
}

.versioning__commit {
  fill: var(--ui-bg);
  stroke: var(--ill-accent);
  stroke-width: 2.5;
  animation: ver-commit var(--ill-loop) ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.3s);
}

.versioning__commit--branch {
  stroke: var(--ill-spark);
}

@keyframes ver-commit {
  0%, 6% { opacity: 0; r: 2 }
  22%, 90% { opacity: 1 }
  99%, 100% { opacity: 0 }
}
</style>
