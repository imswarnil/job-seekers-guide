<script setup lang="ts">
/**
 * An editor typing a line, running it, and printing a result.
 *
 * Deliberately a small program that works, not a wall of impressive-looking
 * code. The audience for this platform has been shown enough walls.
 */
const lines = [
  { indent: 0, tokens: [12, 22, 8] },
  { indent: 1, tokens: [16, 30] },
  { indent: 1, tokens: [10, 18, 24] },
  { indent: 0, tokens: [6] }
]
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg code"
    role="img"
    aria-label="A code editor with a line being typed and a result appearing below"
  >
    <rect
      x="16"
      y="14"
      width="168"
      height="122"
      rx="8"
      class="code__window"
    />

    <!-- Window chrome, because everybody reads three dots as "an editor". -->
    <line
      x1="16"
      y1="32"
      x2="184"
      y2="32"
      class="ill-stroke code__rule"
      stroke-width="1"
    />
    <circle
      cx="28"
      cy="23"
      r="2.6"
      class="code__dot"
      style="--d: 0"
    />
    <circle
      cx="37"
      cy="23"
      r="2.6"
      class="code__dot"
      style="--d: 1"
    />
    <circle
      cx="46"
      cy="23"
      r="2.6"
      class="code__dot"
      style="--d: 2"
    />

    <g
      v-for="(line, index) in lines"
      :key="index"
      :transform="`translate(${28 + line.indent * 12} ${46 + index * 13})`"
    >
      <rect
        v-for="(token, t) in line.tokens"
        :key="t"
        :x="line.tokens.slice(0, t).reduce((sum, w) => sum + w + 5, 0)"
        y="0"
        :width="token"
        height="4"
        rx="2"
        class="code__token"
        :class="t === 0 && 'code__token--key'"
      />
    </g>

    <!-- The caret, typing the last line in. -->
    <rect
      x="28"
      y="98"
      width="1.8"
      height="8"
      class="code__caret ill-loop"
    />
    <rect
      x="28"
      y="99.5"
      width="0"
      height="4"
      rx="2"
      class="code__typing ill-loop"
    />

    <!-- The output. Arrives after the typing, in the accent. -->
    <g class="code__output ill-loop">
      <rect
        x="28"
        y="114"
        width="144"
        height="14"
        rx="4"
        class="code__output-box"
      />
      <rect
        x="36"
        y="119"
        width="8"
        height="4"
        rx="2"
        class="code__output-arrow"
      />
      <rect
        x="50"
        y="119"
        width="46"
        height="4"
        rx="2"
        class="code__output-text"
      />
    </g>
  </svg>
</template>

<style scoped>
.code__window {
  fill: var(--ill-surface);
  stroke: var(--ill-line);
  stroke-width: 1;
}

.code__rule {
  stroke: var(--ill-line);
}

.code__dot {
  fill: var(--ill-muted);
  opacity: 0.4;
}

.code__token {
  fill: var(--ill-muted);
  opacity: 0.42;
}

.code__token--key {
  fill: var(--ill-accent);
  opacity: 0.7;
}

.code__caret {
  fill: var(--ill-accent);
  animation: code-caret 1.1s steps(1) infinite;
}

@keyframes code-caret {
  0%, 50% { opacity: 1 }
  51%, 100% { opacity: 0 }
}

/* The line writes itself, then holds, then clears — the same three seconds of
   work over and over, which is what learning to code actually looks like. */
.code__typing {
  fill: var(--ill-muted);
  opacity: 0.42;
  animation: code-type var(--ill-loop) ease-in-out infinite;
}

@keyframes code-type {
  0% { width: 0 }
  30%, 78% { width: 54px }
  92%, 100% { width: 0 }
}

.code__output-box {
  fill: var(--ill-spark);
  opacity: 0.12;
}

.code__output-arrow {
  fill: var(--ill-spark);
}

.code__output-text {
  fill: var(--ill-spark);
  opacity: 0.7;
}

.code__output {
  animation: code-output var(--ill-loop) ease-in-out infinite;
}

@keyframes code-output {
  0%, 32% { opacity: 0; transform: translateY(4px) }
  44%, 78% { opacity: 1; transform: none }
  90%, 100% { opacity: 0; transform: translateY(4px) }
}
</style>
