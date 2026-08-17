<script setup lang="ts">
/**
 * Two people and a question crossing the table.
 *
 * The interview subject argues that "tell me about yourself" decides more than
 * anything technical that follows. So the thing that animates is the answer
 * travelling back — and it is longer than the question.
 */
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg interview"
    role="img"
    aria-label="Two people across a table, a question asked and a longer answer given"
  >
    <!-- Interviewer, left. -->
    <g class="interview__person">
      <circle
        cx="38"
        cy="52"
        r="13"
        class="interview__head"
      />
      <path
        d="M14 108c0-13 11-24 24-24s24 11 24 24"
        class="interview__body"
      />
    </g>

    <!-- Candidate, right. Drawn in the accent — this is whose side we are on. -->
    <g class="interview__person interview__person--you">
      <circle
        cx="162"
        cy="52"
        r="13"
        class="interview__head"
      />
      <path
        d="M138 108c0-13 11-24 24-24s24 11 24 24"
        class="interview__body"
      />
    </g>

    <line
      x1="10"
      y1="112"
      x2="190"
      y2="112"
      class="ill-stroke interview__table"
      stroke-width="2"
    />

    <!-- The question. Short. -->
    <g class="interview__bubble interview__bubble--q ill-loop">
      <rect
        x="66"
        y="34"
        width="36"
        height="16"
        rx="8"
        class="interview__bubble-box"
      />
      <rect
        x="74"
        y="41"
        width="20"
        height="2.6"
        rx="1.3"
        class="interview__bubble-line"
      />
    </g>

    <!-- The answer. Longer, two lines, and it is the one in the accent. -->
    <g class="interview__bubble interview__bubble--a ill-loop">
      <rect
        x="72"
        y="60"
        width="56"
        height="22"
        rx="9"
        class="interview__bubble-box interview__bubble-box--you"
      />
      <rect
        x="80"
        y="66"
        width="40"
        height="2.6"
        rx="1.3"
        class="interview__bubble-line interview__bubble-line--you"
      />
      <rect
        x="80"
        y="73"
        width="28"
        height="2.6"
        rx="1.3"
        class="interview__bubble-line interview__bubble-line--you"
      />
    </g>
  </svg>
</template>

<style scoped>
.interview__head,
.interview__body {
  fill: var(--ill-surface);
  stroke: var(--ill-line);
  stroke-width: 2;
}

.interview__person--you .interview__head,
.interview__person--you .interview__body {
  stroke: var(--ill-accent);
}

.interview__table {
  stroke: var(--ill-line);
}

.interview__bubble-box {
  fill: var(--ill-surface);
  stroke: var(--ill-line);
  stroke-width: 1;
}

.interview__bubble-box--you {
  fill: color-mix(in oklab, var(--ill-accent) 12%, var(--ill-surface));
  stroke: var(--ill-accent);
}

.interview__bubble-line {
  fill: var(--ill-muted);
}

.interview__bubble-line--you {
  fill: var(--ill-accent);
}

.interview__bubble--q {
  animation: iv-question var(--ill-loop) ease-in-out infinite;
}

@keyframes iv-question {
  0% { opacity: 0; transform: translateX(-16px) }
  12%, 84% { opacity: 1; transform: none }
  95%, 100% { opacity: 0; transform: translateX(-16px) }
}

.interview__bubble--a {
  animation: iv-answer var(--ill-loop) ease-in-out infinite;
}

/* The answer arrives a beat after the question, from the other side. That beat
   is the whole difference between prepared and improvised. */
@keyframes iv-answer {
  0%, 26% { opacity: 0; transform: translateX(16px) }
  40%, 84% { opacity: 1; transform: none }
  95%, 100% { opacity: 0; transform: translateX(16px) }
}
</style>
