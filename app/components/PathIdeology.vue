<script setup lang="ts">
/**
 * What this is, before the list of what is in it.
 *
 * `/start` opened straight onto sixteen subject cards, which answers "what is
 * here" and never answers "why is it in this order and what happens to me if I
 * follow it". Somebody deciding whether to spend a year on a free course is
 * asking the second question, so it goes above the route rather than on a
 * separate About page nobody clicks.
 */
const principles = [
  {
    icon: 'i-lucide-target',
    title: 'One idea a lesson',
    body: 'Two new ideas arriving together is where beginners drown. A lesson on Flexbox teaches Flexbox — not Grid, not JavaScript "while we are here".'
  },
  {
    icon: 'i-lucide-link',
    title: 'Every lesson ends on a question',
    body: 'The next one answers it in its first line. Nothing is a standalone article, so there is never a moment where you have to decide what to read next.'
  },
  {
    icon: 'i-lucide-building-2',
    title: 'One system, built all the way through',
    body: 'Every example, table and exercise belongs to one university management system. No animal classes, no to-do apps, no throwaway demo that teaches nothing you can show anybody.'
  },
  {
    icon: 'i-lucide-terminal',
    title: 'You run everything yourself',
    body: 'Real commands with their real output, from the first week. When a command fails, the failure is taught — permission denied, port already in use, push rejected. Those are the moments people quit.'
  },
  {
    icon: 'i-lucide-check-check',
    title: 'Correct, not merely running',
    body: 'Code that runs is not code that is right. The silently-wrong version is shown next to the right one, with the reason the first is wrong, because that is the difference an interviewer is testing for.'
  },
  {
    icon: 'i-lucide-triangle-alert',
    title: 'The traps are named first',
    body: 'Before objects, generics, joins, async, server versus client. You get warned that the hard part is hard, out loud, so you never think the problem is you.'
  }
]

const outcomes = [
  { icon: 'i-lucide-globe', text: 'A live system on your own domain that a stranger can open' },
  { icon: 'i-lucide-git-branch', text: 'A commit history that shows a year of real work' },
  { icon: 'i-lucide-messages-square', text: 'The interview answers, attached to code you actually wrote' },
  { icon: 'i-lucide-compass', text: 'A way of learning the next thing without a course' }
]
</script>

<template>
  <section class="ideology">
    <p class="ideology__eyebrow">
      <UIcon
        name="i-lucide-quote"
        class="size-3.5"
      />
      Why this exists
    </p>

    <p class="ideology__thesis">
      None of this is hard. It is a lot of small ideas, stacked in the right
      order.
    </p>

    <p class="ideology__lede">
      I am not a professor and this is not a syllabus. I am one step ahead of
      you on the same path, writing down the parts that confused me and what
      finally made them click. Everything on this page is free, in order, and
      written for somebody who has never opened a terminal.
    </p>

    <!-- ── How it is taught ──────────────────────────────────────────── -->
    <h2 class="ideology__heading">
      How it is taught
    </h2>

    <ul class="ideology__grid">
      <li
        v-for="item in principles"
        :key="item.title"
        class="ideology__item"
      >
        <span
          class="ideology__glyph"
          aria-hidden="true"
        >
          <UIcon
            :name="item.icon"
            class="size-4"
          />
        </span>
        <div>
          <h3 class="ideology__item-title">
            {{ item.title }}
          </h3>
          <p class="ideology__item-body">
            {{ item.body }}
          </p>
        </div>
      </li>
    </ul>

    <!-- ── Where it ends ─────────────────────────────────────────────── -->
    <div class="ideology__goal">
      <h2 class="ideology__heading ideology__heading--flush">
        Where it ends
      </h2>

      <p class="ideology__item-body">
        The last section of the path is the job hunt, taught with the same
        seriousness as everything before it. By the end of it you have four
        things, and none of them is a certificate.
      </p>

      <ul class="ideology__outcomes">
        <li
          v-for="outcome in outcomes"
          :key="outcome.text"
        >
          <UIcon
            :name="outcome.icon"
            class="size-4 text-primary shrink-0 mt-0.5"
          />
          <span>{{ outcome.text }}</span>
        </li>
      </ul>

      <p class="ideology__honest">
        <UIcon
          name="i-lucide-scale"
          class="size-3.5 shrink-0 mt-0.5"
        />
        <span>
          What I will not do is promise you an outcome. No placement figures, no
          guaranteed packages, no fees and no bonds. The path is long — read the
          weeks on each subject below and decide with the real number in front
          of you.
        </span>
      </p>
    </div>
  </section>
</template>

<style scoped>
.ideology {
  padding: 1.5rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background:
    linear-gradient(
      180deg,
      color-mix(in oklab, var(--ui-primary) 4%, var(--ui-bg)),
      var(--ui-bg) 55%
    );
}

@media (min-width: 640px) {
  .ideology {
    padding: 2rem;
  }
}

.ideology__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
}

.ideology__thesis {
  font-family: var(--font-display);
  font-size: 1.375rem;
  font-weight: 600;
  line-height: 1.35;
  color: var(--ui-text-highlighted);
  margin-top: 0.75rem;
  text-wrap: balance;
  max-width: 34ch;
}

@media (min-width: 640px) {
  .ideology__thesis {
    font-size: 1.625rem;
  }
}

.ideology__lede {
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  margin-top: 0.9rem;
  max-width: 62ch;
  text-wrap: pretty;
}

.ideology__heading {
  font-family: var(--font-display);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-text-highlighted);
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--ui-border);
}

.ideology__heading--flush {
  margin-top: 0;
  padding-top: 0;
  border-top: 0;
}

.ideology__grid {
  list-style: none;
  margin: 1.25rem 0 0;
  padding: 0;
  display: grid;
  gap: 1.1rem;
}

@media (min-width: 768px) {
  .ideology__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem 2rem;
  }
}

.ideology__item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.ideology__glyph {
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 1.875rem;
  height: 1.875rem;
  border-radius: var(--radius-sm);
  color: var(--ui-primary);
  background: color-mix(in oklab, var(--ui-primary) 10%, transparent);
  border: 1px solid color-mix(in oklab, var(--ui-primary) 22%, transparent);
}

.ideology__item-title {
  font-family: var(--font-display);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
}

.ideology__item-body {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.25rem;
  text-wrap: pretty;
}

.ideology__goal {
  margin-top: 2rem;
  padding: 1.25rem;
  border-radius: var(--radius-md);
  border: 1px solid color-mix(in oklab, var(--ui-secondary) 25%, transparent);
  background: color-mix(in oklab, var(--ui-secondary) 6%, transparent);
}

.ideology__outcomes {
  list-style: none;
  margin: 1rem 0 0;
  padding: 0;
  display: grid;
  gap: 0.6rem;
  font-size: 0.875rem;
  color: var(--ui-text-toned);
}

@media (min-width: 768px) {
  .ideology__outcomes {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.6rem 1.5rem;
  }
}

.ideology__outcomes li {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.ideology__honest {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-top: 1.1rem;
  padding-top: 0.9rem;
  border-top: 1px dashed var(--ui-border-accented);
  font-size: 0.8125rem;
  color: var(--ui-text-dimmed);
  text-wrap: pretty;
}
</style>
