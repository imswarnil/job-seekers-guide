<script setup lang="ts">
/**
 * A query going into a database and rows coming back.
 *
 * The classic stacked cylinder, but with the part that matters animated: a
 * query travels in, and three rows — not all of them — travel out. Selectivity
 * is the whole idea of a WHERE clause and it is what beginners miss.
 */
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg database"
    role="img"
    aria-label="A query entering a database and matching rows returning"
  >
    <!-- The cylinder: three discs and the body between them. -->
    <g class="database__drum">
      <path
        d="M62 44v62c0 7 17 12 38 12s38-5 38-12V44"
        class="database__body"
      />
      <ellipse
        cx="100"
        cy="44"
        rx="38"
        ry="12"
        class="database__disc"
      />
      <ellipse
        cx="100"
        cy="44"
        rx="38"
        ry="12"
        class="ill-stroke database__edge"
        stroke-width="1.5"
      />
      <path
        d="M62 65c0 7 17 12 38 12s38-5 38-12"
        class="ill-stroke database__edge"
        stroke-width="1.5"
      />
      <path
        d="M62 86c0 7 17 12 38 12s38-5 38-12"
        class="ill-stroke database__edge"
        stroke-width="1.5"
      />
      <path
        d="M62 44v62c0 7 17 12 38 12s38-5 38-12V44"
        class="ill-stroke database__edge"
        stroke-width="1.5"
      />
    </g>

    <!-- The query going in. -->
    <g class="database__query ill-loop">
      <rect
        x="4"
        y="34"
        width="34"
        height="12"
        rx="4"
        class="database__query-box"
      />
      <rect
        x="10"
        y="39"
        width="22"
        height="2.5"
        rx="1.25"
        class="database__query-text"
      />
    </g>

    <!-- Rows coming back. Staggered, and only three of however many were
         scanned — which is the point of the picture. -->
    <g
      v-for="(row, index) in [0, 1, 2]"
      :key="index"
      class="database__row ill-loop"
      :style="{ '--i': index }"
    >
      <rect
        x="148"
        y="60"
        width="46"
        height="11"
        rx="3.5"
        class="database__row-box"
      />
      <rect
        x="154"
        y="64.5"
        width="18"
        height="2.5"
        rx="1.25"
        class="database__row-text"
      />
      <rect
        x="176"
        y="64.5"
        width="10"
        height="2.5"
        rx="1.25"
        class="database__row-text database__row-text--dim"
      />
    </g>
  </svg>
</template>

<style scoped>
.database__body,
.database__disc {
  fill: var(--ill-surface);
}

.database__disc {
  fill: color-mix(in oklab, var(--ill-accent) 12%, var(--ill-surface));
}

.database__edge {
  stroke: var(--ill-line);
}

.database__query-box {
  fill: var(--ill-accent);
  opacity: 0.16;
}

.database__query-text {
  fill: var(--ill-accent);
}

.database__query {
  animation: db-query var(--ill-loop) ease-in-out infinite;
}

@keyframes db-query {
  0% { transform: translateX(-14px); opacity: 0 }
  14% { opacity: 1 }
  34% { transform: translateX(48px); opacity: 0 }
  100% { transform: translateX(48px); opacity: 0 }
}

.database__row-box {
  fill: var(--ill-surface);
  stroke: var(--ill-spark);
  stroke-width: 1;
}

.database__row-text {
  fill: var(--ill-spark);
}

.database__row-text--dim {
  opacity: 0.4;
}

.database__row {
  animation: db-row var(--ill-loop) ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.22s);
}

@keyframes db-row {
  0%, 36% { transform: translate(-24px, 0); opacity: 0 }
  50% { opacity: 1 }
  /* Each row settles a little lower than the last, so they stack. */
  62%, 84% { transform: translate(0, calc(var(--i) * 15px)); opacity: 1 }
  96%, 100% { transform: translate(0, calc(var(--i) * 15px)); opacity: 0 }
}
</style>
