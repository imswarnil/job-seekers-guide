<script setup lang="ts">
/**
 * The same advice, twice: as it arrives, and as it should have.
 *
 * This is the argument the hero animates, written down — and it is here because
 * the hero is a picture and a picture cannot be indexed. Somebody searching
 * "what should I learn first for a software job" should land on a page that
 * answers it in words, in order, with the honest reason each step comes where
 * it does.
 *
 * The left panel is deliberately uncomfortable to read. It is a pile, and the
 * point is that a pile is what everybody is handed.
 */
const noise = [
  'Learn DSA first',
  'No — build projects first',
  '500 LeetCode problems',
  'Get AWS certified',
  'Learn React, it pays more',
  'Python is easier, switch',
  'You need maths',
  'Do an internship first',
  'System design from day one',
  'Just start applying',
  'Learn 3 languages',
  'College is useless'
]

const order = [
  {
    n: 'Weeks 1–2',
    title: 'Find out what the job actually is',
    body: 'What software is, what the roles are, who you would report to, and what each team does all day. Everyone starts here — computer science graduates included, because it is the part nobody is taught.'
  },
  {
    n: 'Weeks 3–14',
    title: 'The foundations that do not expire',
    body: 'Operating systems, databases, networks, OOP, data structures. Not because they are on a syllabus, but because every technical round is built on them and every tool on top of them changes anyway.'
  },
  {
    n: 'Weeks 15–26',
    title: 'One language, learned properly',
    body: 'Java by default, and we defend the choice. C++, Python or TypeScript if you have a reason. What we refuse to endorse is four languages learned badly.'
  },
  {
    n: 'Weeks 27–38',
    title: 'Four projects, each bigger than the last',
    body: 'Proof of work, at a fraction of what a certificate costs and worth considerably more in the room. Each one ends up on your resume with something true to say about it.'
  },
  {
    n: 'Weeks 39–44',
    title: 'The job hunt, taught as a subject',
    body: 'Resume, search strategy, HR rounds, technical rounds, negotiation, the first switch. This is the module that converts all of the above into a salary, and it is the one every roadmap leaves out.'
  }
]
</script>

<template>
  <section class="contrast">
    <div class="contrast__intro">
      <p class="contrast__kicker">
        The problem is not the content
      </p>
      <h2 class="contrast__heading">
        Everything you need is already free. Nobody will tell you what order to
        do it in.
      </h2>
      <p class="contrast__lede">
        That is the whole of it. The lectures exist, the documentation exists,
        the tutorials exist — and an institute charges you a lakh and a half for
        the one thing that is missing. Here is what you are handed now, and here
        is the sequence instead.
      </p>
    </div>

    <div class="contrast__grid">
      <!-- The pile. -->
      <div class="contrast__pile">
        <p class="contrast__label contrast__label--bad">
          <UIcon
            name="i-lucide-shuffle"
            class="size-4"
          />
          What you get told
        </p>

        <ul class="pile">
          <li
            v-for="(item, index) in noise"
            :key="item"
            class="pile__chip"
            :style="{ '--tilt': `${((index * 47) % 13) - 6}deg` }"
          >
            {{ item }}
          </li>
        </ul>

        <p class="contrast__note">
          All of it true somewhere. None of it in an order. Every one of those
          sentences is somebody's day one, and they contradict each other.
        </p>
      </div>

      <!-- The order. -->
      <div class="contrast__order">
        <p class="contrast__label contrast__label--good">
          <UIcon
            name="i-lucide-list-ordered"
            class="size-4"
          />
          What this gives you
        </p>

        <ol class="order">
          <li
            v-for="step in order"
            :key="step.title"
            class="order__step"
          >
            <span class="order__n">{{ step.n }}</span>
            <h3 class="order__title">
              {{ step.title }}
            </h3>
            <p class="order__body">
              {{ step.body }}
            </p>
          </li>
        </ol>

        <UButton
          to="/start"
          label="See the whole sequence"
          trailing-icon="i-lucide-arrow-right"
          size="lg"
          class="mt-7"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.contrast {
  padding-block: 1rem;
}

.contrast__intro {
  max-width: 44rem;
}

.contrast__kicker {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.contrast__heading {
  font-family: var(--font-display);
  font-size: clamp(1.6rem, 3.6vw, 2.35rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  line-height: 1.15;
  color: var(--ui-text-highlighted);
  margin-top: 0.9rem;
  text-wrap: balance;
}

.contrast__lede {
  margin-top: 1rem;
  font-size: 1.0625rem;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}

.contrast__grid {
  display: grid;
  gap: 2rem;
  margin-top: 3rem;
}

/* The pile gets less room than the order on purpose — the point is not to dwell
   on it. */
@media (min-width: 1024px) {
  .contrast__grid {
    grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
    gap: 3.5rem;
  }
}

.contrast__label {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 1.25rem;
}

.contrast__label--bad {
  color: var(--ui-text-dimmed);
}

.contrast__label--good {
  color: var(--ui-secondary);
}

.contrast__pile {
  padding: 1.5rem;
  border-radius: var(--radius-lg);
  border: 1px dashed var(--ui-border-accented);
  background: var(--ui-bg-elevated);
  align-self: start;
}

.pile {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* Tilted, overlapping, no alignment to speak of. It should look like something
   nobody arranged, because nobody did. */
.pile__chip {
  font-size: 0.8125rem;
  padding: 0.4rem 0.75rem;
  border-radius: var(--radius-sm);
  background: var(--ui-bg);
  border: 1px solid var(--ui-border);
  color: var(--ui-text-muted);
  rotate: var(--tilt);
  transition: rotate var(--dgm-t-base) var(--dgm-ease);
}

/* Hovering the pile straightens it. The one interaction on this section, and
   it makes the argument better than the copy does. */
.contrast__pile:hover .pile__chip {
  rotate: 0deg;
}

.contrast__note {
  margin-top: 1.5rem;
  font-size: 0.875rem;
  color: var(--ui-text-dimmed);
  text-wrap: pretty;
}

.order {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
  padding-left: 1.75rem;
}

/* One line down the left, so five steps read as one route rather than five
   cards. */
.order::before {
  content: '';
  position: absolute;
  left: 0.28rem;
  top: 0.5rem;
  bottom: 0.5rem;
  width: 2px;
  border-radius: 999px;
  background: linear-gradient(to bottom, var(--ui-primary), var(--ui-secondary));
  opacity: 0.35;
}

.order__step {
  position: relative;
}

.order__step + .order__step {
  margin-top: 1.75rem;
}

.order__step::before {
  content: '';
  position: absolute;
  left: -1.75rem;
  top: 0.35rem;
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-primary);
}

.order__n {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--ui-primary);
  font-variant-numeric: tabular-nums;
}

.order__title {
  font-family: var(--font-display);
  font-size: 1.0625rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  margin-top: 0.2rem;
  text-wrap: balance;
}

.order__body {
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  text-wrap: pretty;
}

@media (prefers-reduced-motion: reduce) {
  .pile__chip {
    transition: none;
  }
}
</style>
