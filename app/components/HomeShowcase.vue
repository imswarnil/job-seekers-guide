<script setup lang="ts">
/**
 * The path as pictures instead of paragraphs.
 *
 * This was an auto-rotating panel, which meant five of the six claims were
 * hidden at any moment and the one on screen changed while you were reading it.
 * A grid says all of it at once, in the order the path runs, and nothing moves.
 * The illustrations already ship for the lessons, so it costs nothing new to
 * download.
 */
type Art = 'job-search' | 'resume' | 'code' | 'database' | 'foundations' | 'versioning' | 'interview'

const steps: { art: Art, kicker: string, title: string, line: string }[] = [
  {
    art: 'foundations',
    kicker: 'Weeks 1–4',
    title: 'The machine stops being magic',
    line: 'What a computer is really doing, and a terminal you are no longer frightened of.'
  },
  {
    art: 'code',
    kicker: 'The long middle',
    title: 'One language, learned properly',
    line: 'Not four badly. Everything after it is a dialect you can read in an afternoon.'
  },
  {
    art: 'database',
    kicker: 'The part that pays',
    title: 'Data you can ask questions of',
    line: 'Schemas, joins and queries you can defend line by line in an interview.'
  },
  {
    art: 'resume',
    kicker: 'The evidence',
    title: 'One system, built all the way through',
    line: 'A live university management app on your own domain, not a folder of tutorials.'
  },
  {
    art: 'job-search',
    kicker: 'Taught, not assumed',
    title: 'The hunt is a subject too',
    line: 'Applications, referrals and the nine seconds somebody spends on your CV.'
  },
  {
    art: 'interview',
    kicker: 'The last mile',
    title: 'Saying all of it out loud',
    line: 'The rounds, the questions, and answers attached to code you actually wrote.'
  }
]
</script>

<template>
  <section aria-label="What the path does, in six pictures">
    <h2 class="show__heading">
      What a year of this actually gives you
    </h2>

    <ul class="show">
      <li
        v-for="step in steps"
        :key="step.title"
        class="show__item"
      >
        <Illustration
          :name="step.art"
          size="sm"
          class="show__art"
        />
        <p class="show__kicker">
          {{ step.kicker }}
        </p>
        <h3 class="show__title">
          {{ step.title }}
        </h3>
        <p class="show__line">
          {{ step.line }}
        </p>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.show__heading {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
  margin-bottom: 2rem;
}

.show {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 1.75rem 2.5rem;
}

@media (min-width: 640px) {
  .show {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .show {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 2.5rem 3rem;
  }
}

.show__art {
  margin: 0 0 1rem;
}

.show__kicker {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.show__title {
  font-family: var(--font-display);
  font-size: 1.0625rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
  margin-top: 0.4rem;
  text-wrap: balance;
}

.show__line {
  margin-top: 0.4rem;
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}
</style>
