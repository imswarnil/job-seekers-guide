<script setup lang="ts">
/**
 * Six years of salary, drawn.
 *
 * The whole argument of this story is in one shape: it starts at almost nothing
 * and the steep part comes *after* learning how to interview, not after learning
 * more Java. Written as a sentence it is a boast. Drawn, it is a claim somebody
 * can check against their own position on the same axis.
 *
 * Log scale, and labelled as such — on a linear axis the first three jobs are
 * indistinguishable from zero, which hides exactly the years that matter to
 * somebody standing at the start.
 */
interface Point {
  year: string
  company: string
  lpa: number
  note?: string
  /** `start` and `jump` are the two moments worth colouring differently. */
  tone?: 'start' | 'jump' | 'end'
}

const points: Point[] = [
  { year: '2019', company: 'First job', lpa: 1.8, note: '₹13,000 a month', tone: 'start' },
  { year: '2019', company: 'Accenture', lpa: 3.6, note: 'Three months later' },
  { year: '2021', company: 'Accenture', lpa: 5.5, note: 'Two years, same desk' },
  { year: '2022', company: 'Cognizant', lpa: 15.5, note: 'Best of four offers', tone: 'jump' },
  { year: '2022', company: 'PwC', lpa: 21, note: 'Seven months later' },
  { year: '2023', company: 'Twilio', lpa: 32, note: 'With stocks', tone: 'end' }
]

/**
 * The year the whole shape turns on.
 *
 * Cognizant is the bar on the chart, but it was one of four offers cleared in
 * the same stretch — and the spread is the actual evidence. One good number can
 * be luck. Four in a row is a skill somebody acquired, which is the only claim
 * this page is making.
 */
const offers = [
  { company: 'ABSYZ', lpa: 9 },
  { company: 'Genpact', lpa: 12 },
  { company: 'Dazeworks', lpa: 14 },
  { company: 'Cognizant', lpa: 15.5, taken: true }
]

const max = Math.max(...points.map(p => p.lpa))
const min = Math.min(...points.map(p => p.lpa))

/** Log scale so the ₹1.8L → ₹3.6L step is as visible as ₹21L → ₹32L. */
function height(lpa: number) {
  const t = (Math.log(lpa) - Math.log(min)) / (Math.log(max) - Math.log(min))
  return 12 + t * 88
}

const root = useTemplateRef<HTMLElement>('root')
const seen = useElementVisibility(root)
const armed = ref(false)
const shown = ref(false)

onMounted(() => {
  armed.value = true
})
watch(seen, (value) => {
  if (value) {
    shown.value = true
  }
})
</script>

<template>
  <figure
    ref="root"
    class="salary not-prose"
    :class="[armed && 'salary--armed', shown && 'salary--in']"
  >
    <figcaption class="salary__head">
      <p class="text-xs font-semibold uppercase tracking-wider text-dimmed">
        What it actually paid
      </p>
      <p class="text-sm text-muted mt-1 text-balance">
        Six years, in lakhs per annum. The steep part is not where I learned more
        Java — it is where I learned how to be interviewed.
      </p>
    </figcaption>

    <div class="salary__chart dgm-scroll">
      <div
        v-for="(point, index) in points"
        :key="index"
        class="salary__col"
        :data-tone="point.tone"
        :style="{ '--i': index, '--h': `${height(point.lpa)}%` }"
      >
        <span class="salary__stack">
          <span class="salary__value">₹{{ point.lpa }}L</span>
          <span class="salary__bar" />
        </span>

        <span class="salary__labels">
          <span class="salary__company">{{ point.company }}</span>
          <span class="salary__year">{{ point.year }}</span>
          <span
            v-if="point.note"
            class="salary__note"
          >{{ point.note }}</span>
        </span>
      </div>
    </div>

    <!-- The four offers behind the fourth bar. Same person, same Java, same
         year — the only thing that changed was knowing how to be interviewed. -->
    <div class="salary__offers">
      <p class="salary__offers-head">
        The jump was four offers, cleared in one stretch
      </p>
      <ul class="salary__offers-list">
        <li
          v-for="offer in offers"
          :key="offer.company"
          class="salary__offer"
          :data-taken="offer.taken || undefined"
        >
          <span class="salary__offer-lpa">₹{{ offer.lpa }}L</span>
          <span class="salary__offer-co">{{ offer.company }}</span>
          <span
            v-if="offer.taken"
            class="salary__offer-tag"
          >took it</span>
        </li>
      </ul>
    </div>

    <p class="salary__axis">
      Logarithmic — on a straight axis the first three jobs sit on the floor, and
      those are the years this is written for.
    </p>
  </figure>
</template>

<style scoped>
.salary {
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  background: var(--ui-bg-elevated);
}

.salary__head {
  margin-bottom: 2rem;
  max-width: 34rem;
}

.salary__chart {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(5.5rem, 1fr);
  gap: 0.75rem;
  /* Stretch, not `end`. With `align-items: end` the columns shrink to their
     content and the bars' percentage heights then resolve against nothing. */
  align-items: stretch;
  height: 17rem;
}

.salary__col {
  display: grid;
  grid-template-rows: 1fr auto;
  align-items: end;
  justify-items: center;
  height: 100%;
  text-align: center;
}

/* The bar and its figure share the growing half of the column, so the labels
   stay on one baseline whatever the bar does. */
.salary__stack {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
  height: 100%;
}

.salary__bar {
  display: block;
  width: 100%;
  max-width: 3.5rem;
  border-radius: var(--radius-sm) var(--radius-sm) 0 0;
  background: linear-gradient(to top, color-mix(in oklab, var(--ui-primary) 55%, transparent), var(--ui-primary));
  height: var(--h);
  margin-top: 0.375rem;
}

.salary--armed .salary__bar {
  height: 0;
  transition: height var(--dgm-t-slow) var(--dgm-ease);
  transition-delay: calc(var(--i) * 0.09s);
}

.salary--armed.salary--in .salary__bar {
  height: var(--h);
}

/* Two moments get their own colour: where it started, and the jump that came
   from a skill rather than from time served. */
.salary__col[data-tone='start'] .salary__bar {
  background: linear-gradient(to top, var(--ui-bg-accented), var(--ui-border-accented));
}

.salary__col[data-tone='jump'] .salary__bar,
.salary__col[data-tone='end'] .salary__bar {
  background: linear-gradient(to top, color-mix(in oklab, var(--ui-secondary) 55%, transparent), var(--ui-secondary));
}

.salary__value {
  font-family: var(--font-display);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  font-variant-numeric: tabular-nums;
}

/* A fixed label block so every bar sits on the same baseline. Without it a
   column with no note gets a shorter label row, the growing row absorbs the
   difference, and that one bar hangs lower than the rest. */
.salary__labels {
  display: block;
  padding-top: 0.5rem;
  min-height: 3.75rem;
}

.salary__company {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--ui-text-muted);
  line-height: 1.2;
}

.salary__year {
  display: block;
  font-size: 0.6875rem;
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
}

.salary__note {
  display: block;
  font-size: 0.625rem;
  color: var(--ui-text-dimmed);
  line-height: 1.3;
  margin-top: 0.25rem;
  text-wrap: balance;
}

.salary__offers {
  margin-top: 1.75rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--ui-border);
}

.salary__offers-head {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--ui-text-dimmed);
}

.salary__offers-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin: 0.75rem 0 0;
  padding: 0;
  list-style: none;
}

.salary__offer {
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  border: 1px solid var(--ui-border);
  background: var(--ui-bg);
}

.salary__offer[data-taken] {
  border-color: color-mix(in oklab, var(--ui-secondary) 55%, transparent);
  background: color-mix(in oklab, var(--ui-secondary) 10%, transparent);
}

.salary__offer-lpa {
  font-family: var(--font-display);
  font-size: 0.8125rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-highlighted);
}

.salary__offer-co {
  font-size: 0.6875rem;
  color: var(--ui-text-muted);
}

.salary__offer-tag {
  font-size: 0.5625rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--ui-secondary);
  font-weight: 600;
}

.salary__axis {
  font-size: 0.6875rem;
  color: var(--ui-text-dimmed);
  margin-top: 1.25rem;
  text-wrap: balance;
}
</style>
