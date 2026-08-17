<script setup lang="ts">
/**
 * A head with connections lighting up inside it.
 *
 * Stands for the foundations argument: OS, DBMS, networks and OOP are not
 * separate facts to memorise, they are a small number of ideas that keep
 * connecting to each other. The nodes are always there; what animates is the
 * links between them, because that is the part that takes four months.
 */
const nodes = [
  { x: 78, y: 52, r: 4.5 },
  { x: 104, y: 44, r: 3.5 },
  { x: 118, y: 66, r: 4 },
  { x: 88, y: 76, r: 3.5 },
  { x: 108, y: 92, r: 4.5 },
  { x: 74, y: 96, r: 3 }
]

const links: [number, number][] = [
  [0, 1], [1, 2], [2, 4], [0, 3], [3, 4], [3, 5], [4, 5], [1, 3]
]
</script>

<template>
  <svg
    viewBox="0 0 200 150"
    class="ill-svg foundations"
    role="img"
    aria-label="A head in profile with connected ideas lighting up inside it"
  >
    <!-- The profile. One path, deliberately simple: this is a diagram of an
         idea, not a portrait. -->
    <path
      d="M132 130V112c10-6 16-17 16-30 0-27-22-46-50-46S48 55 48 82c0 11 4 19 10 25l-5 12c-1 3 1 5 4 5h7v6c0 5 4 9 9 9h10v-9"
      class="ill-stroke foundations__head"
      stroke-width="2.5"
    />

    <line
      v-for="(link, index) in links"
      :key="`l${index}`"
      :x1="nodes[link[0]]!.x"
      :y1="nodes[link[0]]!.y"
      :x2="nodes[link[1]]!.x"
      :y2="nodes[link[1]]!.y"
      class="ill-stroke foundations__link ill-loop"
      stroke-width="1.4"
      :style="{ '--i': index }"
    />

    <circle
      v-for="(node, index) in nodes"
      :key="`n${index}`"
      :cx="node.x"
      :cy="node.y"
      :r="node.r"
      class="foundations__node ill-loop"
      :style="{ '--i': index }"
    />
  </svg>
</template>

<style scoped>
.foundations__head {
  stroke: var(--ill-line);
}

/* Links draw themselves in sequence and hold — connections made, not made and
   unmade. The reset at the end of the loop is fast enough to read as a redraw
   rather than as forgetting. */
.foundations__link {
  stroke: var(--ill-accent);
  stroke-dasharray: 60;
  animation: found-link var(--ill-loop-slow) ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.28s);
}

@keyframes found-link {
  0% { stroke-dashoffset: 60; opacity: 0 }
  12% { opacity: 0.5 }
  40%, 88% { stroke-dashoffset: 0; opacity: 0.5 }
  97%, 100% { stroke-dashoffset: 60; opacity: 0 }
}

.foundations__node {
  fill: var(--ill-spark);
  animation: found-node var(--ill-loop-slow) ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.28s);
}

@keyframes found-node {
  0% { opacity: 0.2; transform: scale(0.7) }
  16%, 88% { opacity: 1; transform: scale(1) }
  97%, 100% { opacity: 0.2; transform: scale(0.7) }
}

.foundations__node {
  transform-box: fill-box;
  transform-origin: center;
}
</style>
