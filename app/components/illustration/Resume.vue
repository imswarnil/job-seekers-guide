<script setup lang="ts">
/**
 * A resume being read. Lines of text with a highlight sweeping down them, and a
 * recruiter's eye-line that stops well before the bottom.
 *
 * The nine-second figure in the job-search subject is the point: the second half
 * of the page dims because in practice it is never reached.
 */
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg resume"
    role="img"
    aria-label="A resume with the top half being read and the bottom half fading out"
  >
    <rect
      x="46"
      y="8"
      width="108"
      height="134"
      rx="7"
      class="resume__page"
    />

    <!-- The header block: name and contact, always read. -->
    <circle
      cx="66"
      cy="28"
      r="8"
      class="resume__avatar"
    />
    <rect
      x="80"
      y="22"
      width="42"
      height="4.5"
      rx="2.25"
      class="resume__strong"
    />
    <rect
      x="80"
      y="31"
      width="30"
      height="3"
      rx="1.5"
      class="resume__faint"
    />

    <line
      x1="58"
      y1="46"
      x2="142"
      y2="46"
      class="ill-stroke resume__rule"
      stroke-width="1"
    />

    <!-- Body lines. Opacity falls off down the page. -->
    <g>
      <rect
        v-for="(line, index) in [72, 60, 66, 54, 68, 48, 62, 40]"
        :key="index"
        x="58"
        :y="56 + index * 10"
        :width="line"
        height="3.2"
        rx="1.6"
        class="resume__line"
        :style="{ '--i': index, 'opacity': Math.max(0.12, 0.5 - index * 0.05) }"
      />
    </g>

    <!-- What the reader actually got through in nine seconds. -->
    <rect
      x="52"
      y="16"
      width="96"
      height="52"
      rx="5"
      class="resume__read ill-loop"
    />

    <g class="resume__marker ill-loop">
      <line
        x1="40"
        y1="0"
        x2="160"
        y2="0"
        class="ill-stroke resume__marker-line"
        stroke-width="1.5"
      />
      <circle
        cx="40"
        cy="0"
        r="3"
        class="resume__marker-dot"
      />
    </g>
  </svg>
</template>

<style scoped>
.resume__page {
  fill: var(--ill-surface);
  stroke: var(--ill-line);
  stroke-width: 1;
}

.resume__avatar {
  fill: var(--ill-accent);
  opacity: 0.3;
}

.resume__strong {
  fill: var(--ill-ink);
  opacity: 0.55;
}

.resume__faint,
.resume__line {
  fill: var(--ill-muted);
}

.resume__rule {
  stroke: var(--ill-line);
}

.resume__read {
  fill: var(--ill-accent);
  opacity: 0.1;
  animation: resume-read var(--ill-loop) ease-in-out infinite;
}

@keyframes resume-read {
  0%, 8% { opacity: 0; height: 0 }
  38%, 72% { opacity: 0.12; height: 52px }
  95%, 100% { opacity: 0; height: 0 }
}

.resume__marker-line {
  stroke: var(--ill-spark);
  stroke-dasharray: 3 4;
}

.resume__marker-dot {
  fill: var(--ill-spark);
}

.resume__marker {
  animation: resume-marker var(--ill-loop) ease-in-out infinite;
}

/* It stops at 68 — a third of the way down — and goes back up. */
@keyframes resume-marker {
  0%, 8% { transform: translateY(20px); opacity: 0 }
  20% { opacity: 1 }
  38%, 72% { transform: translateY(68px); opacity: 1 }
  95%, 100% { transform: translateY(20px); opacity: 0 }
}
</style>
