<script setup lang="ts">
/**
 * The one sentence the whole site rests on.
 *
 * It used to sit inside a card between two other cards, which made it look like
 * a third feature. It is not a feature — it is the evidence for every claim
 * above it, and the only reason to trust a stranger telling you what order to
 * learn things in.
 *
 * So it gets its own band and its own typography, and the sentence is set as
 * what it actually is: two clauses six years apart, with the distance between
 * them drawn rather than described.
 */
const proof = [
  { value: '0', label: 'Written rounds cleared in four years of college' },
  { value: '₹13,000', label: 'First salary, per month, in 2019' },
  { value: '4', label: 'Offers cleared in one stretch, three years later' },
  { value: '₹32L', label: 'Six years after the first one' }
]
</script>

<template>
  <section class="story">
    <div class="story__inner">
      <div class="story__claim">
        <p class="story__kicker">
          Why this exists
        </p>

        <!-- The two clauses, with the six years between them drawn. A single
             run-on sentence hides the only thing that matters about it: how far
             apart the two halves are. -->
        <blockquote class="story__quote">
          <p class="story__before">
            I could not clear a single written round.
          </p>

          <span
            class="story__gap"
            aria-hidden="true"
          >
            <span class="story__gap-line" />
            <span class="story__gap-label">six years</span>
            <span class="story__gap-line" />
          </span>

          <p class="story__after">
            I am now a <span class="story__role">Salesforce engineer in Europe</span>.
          </p>
        </blockquote>

        <p class="story__lede">
          Average student, no plan, no guidance, and a family that could not fund
          one. This is the whole route — the rejections, the ₹13,000 first
          salary, and every offer after it — written down exactly as it happened.
        </p>

        <div class="story__actions">
          <UButton
            to="/my-story/book"
            label="Read it as a book"
            icon="i-lucide-book-open"
            size="lg"
          />
          <UButton
            to="/my-story/watch"
            label="Watch the series"
            icon="i-lucide-play"
            color="neutral"
            variant="subtle"
            size="lg"
            class="story__ghost"
          />
        </div>
      </div>

      <dl class="story__proof">
        <div
          v-for="item in proof"
          :key="item.label"
        >
          <dt>{{ item.value }}</dt>
          <dd>{{ item.label }}</dd>
        </div>
      </dl>
    </div>
  </section>
</template>

<style scoped>
/* A deliberately dark band, like the hero on the story pages. It is fixed
   rather than themed: this section is dark by design, and pointing it at an
   inverted surface would render dark-on-dark once the page itself is dark. */
.story {
  background:
    radial-gradient(ellipse 70% 60% at 12% 0%, color-mix(in oklab, var(--color-guide-600) 45%, transparent), transparent),
    radial-gradient(ellipse 60% 60% at 100% 100%, color-mix(in oklab, var(--color-spark-600) 30%, transparent), transparent),
    var(--guide-inverse-bg);
  color: var(--guide-inverse-ink);
  border-block: 1px solid var(--guide-inverse-line);
}

.story__inner {
  width: 100%;
  max-width: 80rem;
  margin: 0 auto;
  padding: 3.5rem 1.5rem;
  display: grid;
  gap: 3rem;
}

@media (min-width: 1024px) {
  .story__inner {
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 5rem;
    padding: 5rem 2rem;
    align-items: center;
  }
}

.story__kicker {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.story__quote {
  margin: 1.5rem 0 0;
  padding: 0;
  border: 0;
}

.story__before,
.story__after {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 3.6vw, 2.4rem);
  font-weight: 700;
  line-height: 1.15;
  letter-spacing: -0.025em;
  text-wrap: balance;
}

/* The first clause is where he was — present, but not the point. */
.story__before {
  color: rgb(244 245 255 / 0.55);
}

.story__after {
  color: var(--guide-inverse-ink);
}

.story__role {
  color: var(--color-spark-300);
}

/* The distance, drawn. */
.story__gap {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin: 1.1rem 0;
}

.story__gap-line {
  height: 1px;
  flex: 1;
  max-width: 5rem;
  background: linear-gradient(to right, transparent, var(--color-spark-400));
}

.story__gap-line:last-child {
  background: linear-gradient(to left, transparent, var(--color-spark-400));
  max-width: none;
}

.story__gap-label {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-spark-400);
  flex-shrink: 0;
}

.story__lede {
  margin-top: 1.75rem;
  font-size: 1.0625rem;
  line-height: 1.65;
  color: var(--guide-inverse-muted);
  max-width: 38rem;
  text-wrap: pretty;
}

.story__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 2rem;
}

/* The neutral button is drawn for a light surface; on this band it needs its
   own edges or it disappears. */
.story__ghost {
  --ui-bg-elevated: rgb(255 255 255 / 0.08);
  --ui-bg-accented: rgb(255 255 255 / 0.14);
  --ui-text: var(--guide-inverse-ink);
  --ui-border-accented: var(--guide-inverse-line);
}

.story__proof {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  margin: 0;
}

.story__proof > div {
  padding: 1.1rem 1.25rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--guide-inverse-line);
  background: rgb(255 255 255 / 0.05);
}

.story__proof dt {
  font-family: var(--font-display);
  font-size: clamp(1.35rem, 2.6vw, 1.75rem);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.story__proof dd {
  margin: 0.15rem 0 0;
  font-size: 0.6875rem;
  line-height: 1.35;
  color: var(--guide-inverse-muted);
  text-wrap: balance;
}
</style>
