<script setup lang="ts">
/**
 * The questions people are too embarrassed to ask, on the front page.
 *
 * Read from the questions page rather than written again here, so there is one
 * copy of every answer and the two pages can never drift. One question is taken
 * from each group, which keeps the spread honest — a preview that showed four
 * variations of "is it free" would be a sales page.
 *
 * On the front page because these are the questions that decide whether
 * somebody starts at all. "Do I need to be good at maths" stops more capable
 * people than any technical subject on the path does.
 */
const { data: page } = await useAsyncData('home:questions', () =>
  queryCollection('pages').path('/faq').select('groups').first()
)

interface Pick { q: string, a: string }

/**
 * The first question of each group — one per theme rather than four of one.
 *
 * The predicate is spelled out because `.filter(Boolean)` does not narrow the
 * type: a group with no questions in it would type-check and then blow up in
 * the template.
 */
const picks = computed<Pick[]>(() =>
  (page.value?.groups || [])
    .map(group => group.questions[0])
    .filter((item): item is Pick => Boolean(item))
    .slice(0, 4)
)

/** Answers are markdown; the front page wants one plain sentence of each. */
function opener(answer: string) {
  const text = answer
    .replace(/\|.*\|/g, '')
    .replace(/\[([^\]]+)\]\([^)]*\)/g, '$1')
    .replace(/[*_`>#]/g, '')
    .replace(/\s+/g, ' ')
    .trim()

  const end = text.indexOf('. ')
  return end > 40 ? text.slice(0, end + 1) : text.slice(0, 190)
}
</script>

<template>
  <section
    v-if="picks.length"
    class="hq"
  >
    <div class="hq__intro">
      <p class="hq__kicker">
        Before you start
      </p>
      <h2 class="hq__heading">
        The questions people are embarrassed to ask
      </h2>
      <p class="hq__lede">
        Asked by real people, answered by somebody who is not trying to sell you
        anything. If one of these is the thing quietly stopping you, it is worth
        ninety seconds.
      </p>
    </div>

    <ul class="hq__list">
      <li
        v-for="(item, index) in picks"
        :key="item.q"
        class="hq__item"
      >
        <span class="hq__n">{{ String(index + 1).padStart(2, '0') }}</span>
        <div class="min-w-0">
          <h3 class="hq__q">
            {{ item.q }}
          </h3>
          <p class="hq__a">
            {{ opener(item.a) }}
          </p>
        </div>
      </li>
    </ul>

    <UButton
      to="/faq"
      label="All the questions"
      trailing-icon="i-lucide-arrow-right"
      color="neutral"
      variant="subtle"
      size="lg"
      class="mt-8"
    />
  </section>
</template>

<style scoped>
.hq {
  padding-block: 1rem;
}

.hq__intro {
  max-width: 42rem;
}

.hq__kicker {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.hq__heading {
  font-family: var(--font-display);
  font-size: clamp(1.6rem, 3.6vw, 2.35rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--ui-text-highlighted);
  margin-top: 0.9rem;
  text-wrap: balance;
}

.hq__lede {
  margin-top: 1rem;
  font-size: 1.0625rem;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}

/* Two columns of two rather than four cards: these are questions, and a
   question set in a card reads as a feature. */
.hq__list {
  list-style: none;
  margin: 2.5rem 0 0;
  padding: 0;
  display: grid;
  gap: 2rem 3.5rem;
}

@media (min-width: 768px) {
  .hq__list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.hq__item {
  display: flex;
  gap: 1rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--ui-border);
}

.hq__n {
  font-family: var(--font-display);
  font-size: 0.75rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--ui-primary);
  padding-top: 0.2rem;
  flex-shrink: 0;
}

.hq__q {
  font-family: var(--font-display);
  font-size: 1.0625rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.hq__a {
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  margin-top: 0.4rem;
  text-wrap: pretty;
}
</style>
