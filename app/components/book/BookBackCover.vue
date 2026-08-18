<script setup lang="ts">
/**
 * The back board.
 *
 * Reached by turning past the last page, because that is what happens on a real
 * book — the last page is not a dead end with a greyed-out button on it. It
 * carries what a back cover carries: the blurb, the numbers, and the one thing
 * to do next.
 */
defineProps<{
  chapters: number
  minutes: number
  exitTo: string
}>()

defineEmits<{ reopen: [], restart: [] }>()
</script>

<template>
  <div class="back">
    <div class="back__scene">
      <div class="back__board">
        <BookCoverArt face="back" />
        <span class="back__wash" />
        <span class="back__spine" />

        <div class="back__body">
          <p class="back__kicker">
            The end
          </p>

          <p class="back__blurb">
            He was told to learn to code and nobody told him what that meant. He
            cleared no written round in four years, took thirteen thousand rupees
            a month because it was the only offer, and spent three years standing
            still before he worked out that being interviewed is a subject you
            can study.
          </p>

          <p class="back__blurb back__blurb--tight">
            This is that route, with the numbers left in.
          </p>

          <div class="back__meta">
            <span>{{ chapters }} chapters</span>
            <span>·</span>
            <span>about {{ minutes }} minutes</span>
          </div>

          <!-- The barcode. Nothing on the back of a book says "book" faster,
               and it is the only place a joke belongs on this page. -->
          <div class="back__barcode">
            <span
              v-for="n in 34"
              :key="n"
              :style="{ '--w': `${(n * 7) % 4 + 1}px` }"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="back__actions">
      <UButton
        label="Back to the last page"
        icon="i-lucide-arrow-left"
        color="neutral"
        variant="ghost"
        @click="$emit('reopen')"
      />
      <UButton
        label="Read it again"
        icon="i-lucide-rotate-ccw"
        color="neutral"
        variant="subtle"
        @click="$emit('restart')"
      />
      <UButton
        to="/start"
        label="Start the path"
        trailing-icon="i-lucide-compass"
        color="success"
      />
      <UButton
        :to="exitTo"
        label="Leave the book"
        color="neutral"
        variant="ghost"
      />
    </div>
  </div>
</template>

<style scoped>
.back {
  position: absolute;
  inset: 0;
  z-index: 20;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding: 1rem;
  overflow-y: auto;
  background: var(--ui-bg);
  animation: back-in 420ms var(--dgm-ease);
}

@keyframes back-in {
  from { opacity: 0 }
}

.back__scene {
  perspective: 2000px;
}

/* The mirror image of the front board: tilted the other way, because this is
   the same object seen from the other side. */
.back__board {
  position: relative;
  width: min(20rem, 74vw);
  aspect-ratio: 5 / 7;
  max-height: 56vh;
  border-radius: var(--radius-lg) 0.25rem 0.25rem var(--radius-lg);
  overflow: hidden;
  transform: rotateX(6deg) rotateY(14deg);
  transition: transform 520ms var(--dgm-ease);
  box-shadow: var(--shadow-lg);
  background: var(--color-guide-900);
  color: var(--guide-inverse-ink);
}

.back__scene:hover .back__board {
  transform: rotateX(3deg) rotateY(6deg) translateZ(12px);
}

.back__wash {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgb(20 18 47 / 0.55), rgb(20 18 47 / 0.94) 55%);
}

.back__spine {
  position: absolute;
  inset: 0 0 0 auto;
  width: 1.15rem;
  background: linear-gradient(
    to left,
    rgb(0 0 0 / 0.5),
    rgb(0 0 0 / 0.14) 55%,
    rgb(255 255 255 / 0.08) 88%,
    transparent
  );
}

.back__body {
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 1.75rem 2.35rem 1.5rem 1.5rem;
}

.back__kicker {
  font-size: 0.5625rem;
  letter-spacing: 0.24em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.back__blurb {
  margin-top: 1rem;
  font-size: 0.8125rem;
  line-height: 1.65;
  color: var(--guide-inverse-muted);
  text-wrap: pretty;
}

.back__blurb--tight {
  margin-top: 0.75rem;
  color: var(--guide-inverse-ink);
  font-style: italic;
}

.back__meta {
  display: flex;
  gap: 0.4rem;
  margin-top: auto;
  font-size: 0.625rem;
  color: var(--guide-inverse-muted);
}

.back__barcode {
  display: flex;
  align-items: flex-end;
  gap: 1px;
  height: 1.75rem;
  margin-top: 0.75rem;
  padding: 0.25rem 0.4rem;
  border-radius: 0.15rem;
  background: #f4f5ff;
  width: fit-content;
}

.back__barcode span {
  width: var(--w);
  height: 100%;
  background: #14122f;
}

.back__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
}

@media (prefers-reduced-motion: reduce) {
  .back,
  .back__board {
    animation: none;
    transition: none;
  }

  .back__scene:hover .back__board {
    transform: rotateX(6deg) rotateY(14deg);
  }
}
</style>
