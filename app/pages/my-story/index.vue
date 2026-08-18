<script setup lang="ts">
/**
 * The story, before you choose how to take it.
 *
 * Two doors used to be buried at the bottom of a Netflix-style shelf page, so
 * the choice was made for the visitor by whichever page they happened to land
 * on. This is the landing: who this is, why it is worth twenty minutes, and
 * then the fork — watch it or read it — given equal weight and equal size.
 */
const { data: episodes } = await useAsyncData('my-story:episodes', () =>
  queryCollection('series').order('episode', 'ASC').select('path', 'title', 'episode').all()
)

const { data: chapters } = await useAsyncData('my-story:chapters', () =>
  queryCollection('story').order('chapter', 'ASC').select('path', 'title', 'chapter').all()
)

const title = 'My story — from zero written rounds cleared to a Salesforce engineer in Europe'
const description = 'Average student from Mahroni. No plan, no guidance, no family money for one. Every rejection, the ₹13,000 first salary and every offer after it — as a ten-part series or as a book.'

usePageSeo({
  title: 'My story',
  description,
  headline: 'A true story'
})

useSeoMeta({ title: 'My story', ogTitle: title })

const site = useSiteConfig()

/* The story is a biography of a named person, and search engines treat that
   very differently from a page of marketing copy about one. */
useSchemaOrg([
  defineBreadcrumb({ itemListElement: [{ name: 'My story', item: '/my-story' }] }),
  {
    '@type': 'Person',
    'name': 'Swarnil Singhai',
    'jobTitle': 'Salesforce engineer',
    'description': 'Average student from Mahroni who cleared no written round in four years of college and is now a Salesforce engineer in Europe.',
    'url': `${site.url}/my-story`,
    'sameAs': ['https://github.com/imswarnil']
  }
])

const facts = [
  { value: '0', label: 'Written rounds cleared in four years of college' },
  { value: '₹13,000', label: 'First salary, per month, in 2019' },
  { value: '4', label: 'Offers cleared in one stretch, three years later' },
  { value: '₹32L', label: 'Six years after the first one' }
]

const route = [
  { place: 'Mahroni', note: 'A small town in Uttar Pradesh. No engineer in the family.' },
  { place: 'Kota', note: 'Sent to clear IIT. Did not clear IIT.' },
  { place: 'Bhopal', note: 'CSE at LNCT, chosen because somebody said the market was good.' },
  { place: 'Bangalore', note: 'Walk-ins, rejections, and thirteen thousand rupees a month.' },
  { place: 'Budapest', note: 'Salesforce engineering, and the reason this site exists.' }
]
</script>

<template>
  <div>
    <!-- ── Who this is ─────────────────────────────────────────────
         Full width, and the loop plays behind it under the same glass
         the television downstairs is behind — so the page announces
         what it is before a word of it is read. -->
    <section class="opener">
      <WatchAmbientVideo intensity="high" />

      <div class="opener__inner">
        <p class="opener__kicker">
          A true story
        </p>

        <h1 class="opener__title">
          I could not clear a single written round.
          <span class="opener__title-accent">I am now a Salesforce engineer in Europe.</span>
        </h1>

        <p class="opener__lede">
          Average student, no plan, no guidance, and a family that could not
          fund one. This is the whole route — the rejections, the ₹13,000 first
          salary, and every offer after it — written down exactly as it
          happened.
        </p>

        <dl class="opener__facts">
          <div
            v-for="fact in facts"
            :key="fact.label"
          >
            <dt>{{ fact.value }}</dt>
            <dd>{{ fact.label }}</dd>
          </div>
        </dl>
      </div>
    </section>

    <!-- ── The route, in five places ───────────────────────────────── -->
    <UContainer class="py-12 lg:py-16">
      <p class="text-xs font-semibold uppercase tracking-wider text-dimmed">
        Five places, thirteen years
      </p>

      <ol class="route">
        <li
          v-for="(stop, index) in route"
          :key="stop.place"
          class="route__stop"
          :style="{ '--i': index }"
        >
          <span class="route__dot" />
          <p class="route__place">
            {{ stop.place }}
          </p>
          <p class="route__note">
            {{ stop.note }}
          </p>
        </li>
      </ol>
    </UContainer>

    <!-- ── The fork ────────────────────────────────────────────────── -->
    <UContainer class="pb-16 lg:pb-24">
      <div class="text-center max-w-2xl mx-auto">
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-highlighted tracking-tight text-balance">
          Same story. Pick how you want it.
        </h2>
        <p class="mt-3 text-muted text-balance">
          Nothing is held back on either side. The series is the story told out
          loud; the book is the story with every number and every date left in.
        </p>
      </div>

      <div class="fork">
        <!-- The set, already on. An icon of a screen is not a screen — this is
             the television from the watch page, running, so the card shows the
             thing it is offering rather than describing it. -->
        <div class="fork__side">
          <WatchCrtPreview channel="CH 01" />

          <p class="fork__kicker">
            Watch it
          </p>
          <h3 class="fork__title">
            The web series
          </h3>
          <p class="fork__note">
            {{ episodes?.length || 10 }} episodes, in order, on an old television
            with a remote you can actually use.
          </p>
          <UButton
            to="/my-story/watch"
            label="Watch now"
            icon="i-lucide-play"
            size="lg"
            class="mt-5"
          />
        </div>

        <!-- The divider. Two cards side by side are two options; a rule with
             "or" on it is a choice. -->
        <div
          class="fork__divider"
          aria-hidden="true"
        >
          <span class="fork__line" />
          <span class="fork__or">or</span>
          <span class="fork__line" />
        </div>

        <div class="fork__side">
          <div class="fork__book">
            <span class="fork__book-spine" />
            <div class="fork__book-face">
              <p class="fork__book-kicker">
                A true story
              </p>
              <p class="fork__book-title">
                I could not clear a single written round
              </p>
              <span class="fork__book-rule" />
              <p class="fork__book-by">
                Swarnil
              </p>
            </div>
          </div>

          <p class="fork__kicker fork__kicker--read">
            Read it
          </p>
          <h3 class="fork__title">
            The book
          </h3>
          <p class="fork__note">
            {{ chapters?.length || 16 }} chapters as a real two-page book — pages
            you turn, a cover, and nothing else on the screen.
          </p>
          <UButton
            to="/my-story/book"
            label="Read now"
            icon="i-lucide-book-open"
            color="neutral"
            size="lg"
            class="mt-5"
          />
        </div>
      </div>
    </UContainer>
  </div>
</template>

<style scoped>
/* Full-bleed. This band is the one place on the site that gets to behave like
   a title sequence, so it is not held inside the reading container. */
.opener {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  color: var(--guide-inverse-ink);
  border-bottom: 1px solid var(--guide-inverse-line);
}

.opener__inner {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 76rem;
  margin: 0 auto;
  padding: 4.5rem 1.5rem;
}

@media (min-width: 1024px) {
  .opener__inner {
    padding: 7rem 2rem;
  }
}

.opener__kicker {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.24em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.opener__title {
  font-family: var(--font-display);
  font-size: clamp(2rem, 5.2vw, 3.5rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.06;
  margin-top: 1.25rem;
  max-width: 22ch;
  text-wrap: balance;
}

.opener__title-accent {
  color: var(--color-spark-300);
}

.opener__lede {
  margin-top: 1.5rem;
  font-size: 1.0625rem;
  line-height: 1.65;
  color: var(--guide-inverse-muted);
  max-width: 46rem;
  text-wrap: pretty;
}

.opener__facts {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 3rem;
}

@media (min-width: 1024px) {
  .opener__facts {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.opener__facts > div {
  padding: 0.9rem 1.1rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--guide-inverse-line);
  background: rgb(255 255 255 / 0.05);
  backdrop-filter: blur(6px);
}

.opener__facts dt {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.opener__facts dd {
  margin: 0.1rem 0 0;
  font-size: 0.6875rem;
  color: var(--guide-inverse-muted);
  line-height: 1.35;
  text-wrap: balance;
}

.route {
  display: grid;
  gap: 1.5rem;
  margin: 1.5rem 0 0;
  padding: 0;
  list-style: none;
}

@media (min-width: 768px) {
  .route {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 1rem;
  }
}

.route__stop {
  position: relative;
  padding-top: 1.5rem;
}

/* One rule across the whole row, with the dots sitting on it. Five separate
   bordered cards would read as five unrelated facts rather than one journey. */
.route__stop::before {
  content: '';
  position: absolute;
  top: 0.3rem;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--ui-border-accented);
}

.route__stop:last-child::before {
  right: 50%;
}

@media (max-width: 767px) {
  .route__stop::before {
    display: none;
  }
}

.route__dot {
  position: absolute;
  top: 0;
  left: 0;
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 999px;
  background: var(--ui-primary);
  box-shadow: 0 0 0 3px var(--ui-bg);
}

.route__stop:last-child .route__dot {
  background: var(--ui-secondary);
}

.route__place {
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--ui-text-highlighted);
}

.route__note {
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  margin-top: 0.2rem;
  text-wrap: balance;
}

/* The fork. Two sides of equal weight with a rule between them — the choice is
   genuinely even, and neither is the default. */
.fork {
  display: grid;
  gap: 2rem;
  margin-top: 2.5rem;
  align-items: start;
}

@media (min-width: 900px) {
  .fork {
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    gap: 2.5rem;
  }
}

.fork__side {
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

/* Everything is full width except the button, which would look like a banner. */
.fork__side :deep(a[href]) {
  align-self: flex-start;
}

.fork__divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

@media (min-width: 900px) {
  .fork__divider {
    flex-direction: column;
    align-self: stretch;
    padding-top: 1rem;
  }
}

.fork__line {
  background: var(--ui-border);
  height: 1px;
  flex: 1;
}

@media (min-width: 900px) {
  .fork__line {
    width: 1px;
    height: auto;
  }
}

.fork__or {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
  flex-shrink: 0;
}

.fork__kicker {
  margin-top: 1.5rem;
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.fork__kicker--read {
  color: var(--ui-secondary);
}

.fork__title {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--ui-text-highlighted);
  margin-top: 0.35rem;
}

.fork__note {
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  margin-top: 0.5rem;
  text-wrap: pretty;
}

/* The book, as an object rather than an icon — the same argument the set on the
   other side is making. */
.fork__book {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  display: grid;
  place-items: center;
  border-radius: var(--radius-lg);
  background:
    radial-gradient(ellipse 70% 60% at 50% 110%, color-mix(in oklab, var(--ui-secondary) 16%, transparent), transparent),
    var(--ui-bg-elevated);
  border: 1px solid var(--ui-border);
  overflow: hidden;
  perspective: 900px;
}

.fork__book-face {
  position: relative;
  width: 46%;
  aspect-ratio: 5 / 7;
  padding: 1rem 0.9rem 0.9rem 1.35rem;
  display: flex;
  flex-direction: column;
  border-radius: 0.2rem var(--radius-md) var(--radius-md) 0.2rem;
  background:
    radial-gradient(ellipse 95% 65% at 12% 0%, color-mix(in oklab, var(--color-guide-600) 68%, transparent), transparent),
    var(--color-guide-900);
  color: var(--guide-inverse-ink);
  box-shadow: 0 18px 34px rgb(16 19 64 / 0.3);
  transform: rotateY(-14deg) rotateX(4deg);
  transition: transform 460ms var(--dgm-ease);
}

.fork__side:hover .fork__book-face {
  transform: rotateY(-6deg) rotateX(2deg) translateZ(10px);
}

.fork__book-spine {
  position: absolute;
  inset: 0 auto 0 0;
  width: 0.55rem;
  background: linear-gradient(to right, rgb(0 0 0 / 0.45), transparent);
  z-index: 2;
}

.fork__book-kicker {
  font-size: 0.4375rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.fork__book-title {
  font-family: var(--font-display);
  font-size: 0.75rem;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.01em;
  margin-top: 0.4rem;
  text-wrap: balance;
}

.fork__book-rule {
  display: block;
  width: 1.1rem;
  height: 1.5px;
  background: var(--color-spark-400);
  margin-top: 0.5rem;
}

.fork__book-by {
  margin-top: auto;
  font-family: var(--font-display);
  font-size: 0.5625rem;
  font-weight: 600;
  color: var(--guide-inverse-muted);
}

@media (prefers-reduced-motion: reduce) {
  .fork__book-face {
    transition: none;
  }

  .fork__side:hover .fork__book-face {
    transform: rotateY(-14deg) rotateX(4deg);
  }
}
</style>
