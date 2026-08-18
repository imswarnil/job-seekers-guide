<script setup lang="ts">
/**
 * How long this actually takes.
 *
 * "How long will it take me to get a job in software" is one of the most
 * searched questions in this whole category and it is almost never answered
 * with a number, because a number can be checked and a vague answer cannot.
 * So: two numbers, both real, with what each one assumes stated next to it.
 *
 * The bars are the same five stages at two speeds, which is the honest shape of
 * it — the work does not get smaller when you have less time for it, it just
 * takes longer.
 */
const stages = [
  { label: 'Orientation', share: 5, tone: 'a' },
  { label: 'Foundations', share: 27, tone: 'b' },
  { label: 'One language', share: 27, tone: 'a' },
  { label: 'Projects', share: 27, tone: 'b' },
  { label: 'The job hunt', share: 14, tone: 'c' }
]

const tracks = [
  {
    hours: '25 hours a week',
    weeks: '16 weeks',
    who: 'Full-time on it. Graduated, between jobs, or on a break and treating this as the job.',
    note: 'About four months. This is the fastest honest number, not the fastest number.'
  },
  {
    hours: '6–8 hours a week',
    weeks: '44 weeks',
    who: 'Evenings and weekends, around a job or a final year.',
    note: 'About ten months. Slower, and finished by more people than the sixteen-week version.'
  }
]
</script>

<template>
  <section class="pace">
    <div class="pace__intro">
      <p class="pace__kicker">
        The honest answer
      </p>
      <h2 class="pace__heading">
        How long does it take?
      </h2>
      <p class="pace__lede">
        Sixteen weeks if you can give it twenty-five hours a week. Forty-four if
        you have six to eight. Both are real, neither includes the job hunt
        dragging on, and anybody quoting you a single number without asking how
        many hours you have is guessing.
      </p>
    </div>

    <div class="pace__tracks">
      <article
        v-for="track in tracks"
        :key="track.weeks"
        class="track"
      >
        <div class="track__head">
          <p class="track__weeks">
            {{ track.weeks }}
          </p>
          <p class="track__hours">
            at {{ track.hours }}
          </p>
        </div>

        <!-- The same five stages at both speeds: the work does not shrink, the
             calendar stretches. -->
        <div class="track__bar">
          <span
            v-for="stage in stages"
            :key="stage.label"
            class="track__seg"
            :data-tone="stage.tone"
            :style="{ '--share': stage.share }"
            :title="stage.label"
          />
        </div>

        <p class="track__who">
          {{ track.who }}
        </p>
        <p class="track__note">
          {{ track.note }}
        </p>
      </article>
    </div>

    <ul class="pace__key">
      <li
        v-for="stage in stages"
        :key="stage.label"
      >
        <span
          class="pace__dot"
          :data-tone="stage.tone"
        />
        {{ stage.label }}
      </li>
    </ul>
  </section>
</template>

<style scoped>
.pace {
  padding-block: 1rem;
}

.pace__intro {
  max-width: 44rem;
}

.pace__kicker {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-secondary);
}

.pace__heading {
  font-family: var(--font-display);
  font-size: clamp(1.6rem, 3.6vw, 2.35rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--ui-text-highlighted);
  margin-top: 0.9rem;
}

.pace__lede {
  margin-top: 1rem;
  font-size: 1.0625rem;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}

.pace__tracks {
  display: grid;
  gap: 1.5rem;
  margin-top: 2.5rem;
}

@media (min-width: 768px) {
  .pace__tracks {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem;
  }
}

.track {
  padding: 1.5rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--ui-border);
  background: var(--ui-bg-elevated);
}

.track__head {
  display: flex;
  align-items: baseline;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.track__weeks {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  font-variant-numeric: tabular-nums;
}

.track__hours {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
}

.track__bar {
  display: flex;
  gap: 2px;
  height: 0.6rem;
  margin-top: 1.1rem;
}

.track__seg {
  flex: var(--share);
  border-radius: 2px;
}

.track__seg:first-child {
  border-radius: 999px 2px 2px 999px;
}

.track__seg:last-child {
  border-radius: 2px 999px 999px 2px;
}

.track__seg[data-tone='a'],
.pace__dot[data-tone='a'] {
  background: var(--ui-primary);
}

.track__seg[data-tone='b'],
.pace__dot[data-tone='b'] {
  background: color-mix(in oklab, var(--ui-primary) 55%, var(--ui-bg));
}

.track__seg[data-tone='c'],
.pace__dot[data-tone='c'] {
  background: var(--ui-secondary);
}

.track__who {
  margin-top: 1.1rem;
  font-size: 0.9375rem;
  color: var(--ui-text-toned, var(--ui-text-muted));
  text-wrap: pretty;
}

.track__note {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: var(--ui-text-dimmed);
  text-wrap: pretty;
}

.pace__key {
  list-style: none;
  margin: 1.5rem 0 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1.25rem;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
}

.pace__key li {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.pace__dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 2px;
}
</style>
