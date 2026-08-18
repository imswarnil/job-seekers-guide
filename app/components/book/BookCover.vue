<script setup lang="ts">
/**
 * The cover, as an object.
 *
 * A book opens on a thing before it opens on words, and this one has a job: the
 * reader arriving here has been told the story is worth twenty minutes and has
 * no reason to believe it. So the board carries the claim and the three numbers
 * that make it checkable, and the only action on it is to open the book.
 *
 * It is a real board on a real block of pages — tilted in 3D, with edges and a
 * spine — because the whole reading experience after this is a book, and a flat
 * rectangle here would promise a web page.
 */
defineProps<{
  chapters: number
  minutes: number
  /** Set the moment the reader opens it; the board swings away on this. */
  opening?: boolean
  /** Somewhere to come back to, if they have read before. */
  resumeLabel?: string
}>()

defineEmits<{ open: [], resume: [] }>()

const stats = [
  { value: '0', label: 'Written rounds cleared in college' },
  { value: '₹13,000', label: 'First salary, per month' },
  { value: '₹32L', label: 'Six years later' }
]
</script>

<template>
  <div
    class="cover"
    :data-opening="opening ? '' : undefined"
  >
    <div class="cover__scene">
      <div class="cover__book">
        <!-- The block: the pages the board is sitting on. Drawn as a stack of
             edges so the closed book has thickness from the side. -->
        <div class="cover__block">
          <span
            v-for="n in 7"
            :key="n"
            class="cover__edge"
            :style="{ '--n': n }"
          />
        </div>

        <!-- The half-title, revealed as the board swings off it. -->
        <div class="cover__flyleaf">
          <p class="cover__flyleaf-title">
            I could not clear a single written round
          </p>
          <p class="cover__flyleaf-by">
            Swarnil
          </p>
        </div>

        <div class="cover__board">
          <div class="cover__front">
            <BookCoverArt face="front" />
            <span class="cover__wash" />
            <span class="cover__spine" />

            <div class="cover__art">
              <p class="cover__kicker">
                A true story
              </p>

              <h1 class="cover__title">
                I could not<br>clear a single<br>written round
              </h1>

              <p class="cover__route">
                Mahroni → Kota → Bangalore → Budapest
              </p>

              <span class="cover__rule" />

              <dl class="cover__stats">
                <div
                  v-for="stat in stats"
                  :key="stat.label"
                >
                  <dt>{{ stat.value }}</dt>
                  <dd>{{ stat.label }}</dd>
                </div>
              </dl>

              <div class="cover__foot">
                <p class="cover__author">
                  Swarnil
                </p>
                <p class="cover__meta">
                  {{ chapters }} chapters · about {{ minutes }} minutes
                </p>
              </div>
            </div>
          </div>

          <!-- The inside of the board. Only ever seen mid-swing, which is
               exactly when its absence would be noticed. -->
          <div class="cover__inside" />
        </div>
      </div>
    </div>

    <div class="cover__actions">
      <UButton
        label="Open the book"
        icon="i-lucide-book-open"
        size="xl"
        @click="$emit('open')"
      />

      <UButton
        v-if="resumeLabel"
        :label="resumeLabel"
        icon="i-lucide-bookmark"
        color="neutral"
        variant="ghost"
        size="lg"
        @click="$emit('resume')"
      />
    </div>
  </div>
</template>

<style scoped>
.cover {
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
  transition: opacity 320ms ease 380ms, visibility 0s linear 700ms;
}

/* The board leaves first, then the whole cover fades out from under it — so
   the reader sees the book open rather than a panel disappear. */
.cover[data-opening] {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
}

.cover__scene {
  perspective: 2000px;
  perspective-origin: 60% 50%;
}

.cover__book {
  position: relative;
  width: min(21rem, 74vw);
  aspect-ratio: 5 / 7;
  /* The stage is a fixed rectangle now — the board fits inside it rather than
     making the page taller, because nothing here is allowed to scroll. */
  max-height: 58vh;
  transform-style: preserve-3d;
  transform: rotateX(6deg) rotateY(-16deg) rotateZ(-1deg);
  transition: transform 520ms var(--dgm-ease);
  animation: cover-arrive 900ms var(--dgm-ease) backwards;
}

.cover__scene:hover .cover__book {
  transform: rotateX(3deg) rotateY(-7deg) rotateZ(0deg) translateZ(14px);
}

@keyframes cover-arrive {
  from {
    opacity: 0;
    transform: rotateX(14deg) rotateY(-30deg) translateY(24px) scale(0.94);
  }
}

.cover[data-opening] .cover__book {
  transform: rotateX(2deg) rotateY(-2deg) translateX(14%) scale(1.04);
}

/* The page block, seen edge-on down the right side. */
.cover__block {
  position: absolute;
  inset: 0.35rem -0.15rem 0.35rem 0.5rem;
  border-radius: 0.2rem var(--radius-md) var(--radius-md) 0.2rem;
  background: linear-gradient(to right, #d9d7ea, #fbfbff 22%, #eceaf6);
  box-shadow: var(--shadow-lg);
}

.cover__edge {
  position: absolute;
  top: 0;
  bottom: 0;
  right: calc(var(--n) * 0.09rem);
  width: 1px;
  background: rgb(30 27 75 / 0.09);
}

.cover__flyleaf {
  position: absolute;
  inset: 0.35rem 0 0.35rem 0.5rem;
  border-radius: 0.2rem var(--radius-md) var(--radius-md) 0.2rem;
  background: #fbfbff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 2rem;
  text-align: center;
}

.cover__flyleaf-title {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 600;
  color: #1e1b4b;
  text-wrap: balance;
}

.cover__flyleaf-by {
  font-size: 0.6875rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: rgb(30 27 75 / 0.45);
}

.cover__board {
  position: absolute;
  inset: 0;
  transform-style: preserve-3d;
  transform-origin: left center;
  transition: transform 780ms cubic-bezier(0.4, 0.05, 0.2, 1);
}

.cover[data-opening] .cover__board {
  transform: rotateY(-168deg);
}

.cover__front,
.cover__inside {
  position: absolute;
  inset: 0;
  border-radius: 0.25rem var(--radius-lg) var(--radius-lg) 0.25rem;
  backface-visibility: hidden;
  overflow: hidden;
}

.cover__front {
  background: var(--color-guide-900);
  color: var(--guide-inverse-ink);
  box-shadow:
    var(--shadow-lg),
    inset -1px 0 0 rgb(255 255 255 / 0.06);
}

.cover__inside {
  transform: rotateY(180deg);
  background: linear-gradient(to left, #f6f5fd, #e8e6f6);
  box-shadow: inset 2px 0 8px rgb(30 27 75 / 0.16);
}

/* The art is a picture, not a backdrop for text — so the type sits on a wash
   heavy enough to read against and light enough to still see through. */
.cover__wash {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(to bottom, rgb(20 18 47 / 0.86) 0%, rgb(20 18 47 / 0.5) 42%, rgb(20 18 47 / 0.9) 100%);
}

/* The bound edge. A cover without one reads as a poster. */
.cover__spine {
  z-index: 3;
  position: absolute;
  inset: 0 auto 0 0;
  width: 1.15rem;
  background: linear-gradient(
    to right,
    rgb(0 0 0 / 0.5),
    rgb(0 0 0 / 0.14) 55%,
    rgb(255 255 255 / 0.1) 88%,
    transparent
  );
}

.cover__art {
  position: relative;
  z-index: 2;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 1.85rem 1.5rem 1.5rem 2.35rem;
}

.cover__kicker {
  font-size: 0.5625rem;
  letter-spacing: 0.24em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.cover__title {
  font-family: var(--font-display);
  font-size: clamp(1.3rem, 4.6vw, 1.8rem);
  font-weight: 800;
  line-height: 1.08;
  letter-spacing: -0.03em;
  margin-top: 0.9rem;
}

.cover__route {
  margin-top: 0.7rem;
  font-size: 0.6875rem;
  color: var(--guide-inverse-muted);
}

.cover__rule {
  display: block;
  width: 2.25rem;
  height: 2px;
  background: var(--color-spark-400);
  margin-block: 1.1rem;
}

.cover__stats {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.cover__stats dt {
  font-family: var(--font-display);
  font-size: 0.95rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.cover__stats dd {
  margin: 0;
  font-size: 0.5625rem;
  color: var(--guide-inverse-muted);
  line-height: 1.3;
}

.cover__foot {
  margin-top: auto;
}

.cover__author {
  font-family: var(--font-display);
  font-size: 0.95rem;
  font-weight: 600;
}

.cover__meta {
  font-size: 0.625rem;
  color: var(--guide-inverse-muted);
  margin-top: 0.1rem;
}

.cover__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

@media (prefers-reduced-motion: reduce) {
  .cover,
  .cover__book,
  .cover__board {
    transition-duration: 0ms;
    animation: none;
  }

  .cover__scene:hover .cover__book {
    transform: rotateX(6deg) rotateY(-16deg) rotateZ(-1deg);
  }
}
</style>
