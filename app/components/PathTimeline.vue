<script setup lang="ts">
import type { LearningPath } from '~/utils/path'

/**
 * The path, as one connected line.
 *
 * `/start` used to be a grid of cards grouped under stage headings, and a grid
 * says "here are sixteen things, pick one" — which is exactly the decision the
 * visitor came here unable to make. A line says "here is the order". That is
 * the entire product claim, so it is worth drawing literally.
 *
 * The spine is continuous through the section headings on purpose: the sections
 * are a way of reading the route, not breaks in it. The filled part of the
 * spine is how far the reader has actually come.
 */
const props = defineProps<{
  path: LearningPath
}>()

const { subjectProgress, pathProgress, isComplete } = useProgress()

const groups = computed(() => byStage(props.path))

/** Running index across the whole path, so numbering does not restart per section. */
const positions = computed(() => {
  const map = new Map<string, number>()
  props.path.subjects.forEach((subject, index) => map.set(subject.path, index))
  return map
})

const overall = computed(() => pathProgress(props.path))

/** How far down the line the reader has got, as a percentage of its height. */
const filled = computed(() => overall.value.total
  ? Math.round((overall.value.completed / overall.value.total) * 100)
  : 0)

/** The first subject with anything left in it — where "you are here" belongs. */
const currentSubject = computed(() => props.path.subjects.find(subject =>
  subject.lessons.some(lesson => !isComplete(lesson.path))
)?.path)
</script>

<template>
  <div class="tl">
    <!-- One spine for the whole route, drawn behind everything, with the
         travelled part of it filled in. -->
    <span
      class="tl__spine"
      aria-hidden="true"
    />
    <ClientOnly>
      <span
        class="tl__spine tl__spine--done"
        :style="{ height: `${filled}%` }"
        aria-hidden="true"
      />
    </ClientOnly>

    <section
      v-for="group in groups"
      :id="group.id"
      :key="group.stage"
      class="tl__group"
    >
      <header class="tl__stage">
        <span
          class="tl__stage-marker"
          aria-hidden="true"
        >
          <UIcon
            :name="group.icon"
            class="size-3.5"
          />
        </span>

        <h2 class="tl__stage-title">
          {{ group.label }}
        </h2>

        <p class="tl__stage-blurb">
          {{ group.blurb }}
        </p>

        <p class="tl__stage-meta">
          <span class="tl__fact">
            <UIcon
              name="i-lucide-layers"
              class="size-3.5"
            />
            {{ group.subjects.length }} {{ group.subjects.length === 1 ? 'subject' : 'subjects' }}
          </span>
          <span
            v-if="group.lessons"
            class="tl__fact"
          >
            <UIcon
              name="i-lucide-book-open"
              class="size-3.5"
            />
            {{ group.lessons }} {{ group.lessons === 1 ? 'lesson' : 'lessons' }}
          </span>
          <span
            v-if="group.minutes"
            class="tl__fact"
          >
            <UIcon
              name="i-lucide-clock"
              class="size-3.5"
            />
            {{ formatMinutes(group.minutes) }}
          </span>
        </p>
      </header>

      <ol class="tl__list">
        <li
          v-for="subject in group.subjects"
          :key="subject.path"
          class="tl__item"
        >
          <span
            class="tl__node"
            :data-state="currentSubject === subject.path ? 'current' : undefined"
            aria-hidden="true"
          >
            <ClientOnly>
              <!-- One circle, not two. The node's border is the ring's track, so
                   the ring draws only the filled arc, at the size of the node's
                   border box (`-inset-0.5` backs an absolute box out over the
                   2px border) and at a radius that lands the stroke on it. -->
              <PlayerProgress
                :progress="subjectProgress(subject)"
                variant="ring"
                :size="42"
                :track="false"
                class="absolute -inset-0.5"
              />
            </ClientOnly>

            <!-- The node no longer carries the number — the card does, at a size
                 you can read from across the page. What is left is the one thing
                 a dot on a route should say: done, here, or neither. -->
            <ClientOnly>
              <UIcon
                v-if="subjectProgress(subject).finished"
                name="i-lucide-check"
                class="tl__tick"
              />
              <span
                v-else
                class="tl__dot"
              />

              <template #fallback>
                <span class="tl__dot" />
              </template>
            </ClientOnly>
          </span>

          <NuxtLink
            :to="subject.path"
            class="tl__card"
          >
            <!-- The position, outlined and set into the bottom-right corner of
                 the card, running off the edge so the corner clips it. Behind
                 everything: it is the order of the path, which matters, drawn
                 at a size that never competes with the title. -->
            <span
              class="tl__num"
              aria-hidden="true"
            >{{ String((positions.get(subject.path) ?? 0) + 1).padStart(2, '0') }}</span>

            <ClientOnly>
              <SubjectThumb
                :subject="subject"
                :complete="subjectProgress(subject).finished"
                class="tl__thumb"
              />
              <template #fallback>
                <SubjectThumb
                  :subject="subject"
                  class="tl__thumb"
                />
              </template>
            </ClientOnly>

            <div class="tl__body">
              <div class="tl__head">
                <UIcon
                  :name="subject.icon || 'i-lucide-book-open'"
                  class="size-4 text-primary shrink-0"
                />
                <h3 class="tl__title">
                  {{ subject.title }}
                </h3>
                <UBadge
                  v-if="subject.code"
                  :label="subject.code"
                  color="neutral"
                  variant="subtle"
                  size="sm"
                  class="shrink-0"
                />
              </div>

              <p
                v-if="subject.description"
                class="tl__desc"
              >
                {{ subject.description }}
              </p>

              <!-- Every number gets its own glyph rather than being separated by
                   middots. A row of "24 · 3h 10m · 10 weeks" is three numbers
                   the reader has to work out the meaning of. -->
              <p class="tl__meta">
                <span
                  v-if="subject.lessons.length"
                  class="tl__fact"
                >
                  <UIcon
                    name="i-lucide-book-open"
                    class="size-3.5"
                  />
                  {{ subject.lessons.length }} {{ subject.lessons.length === 1 ? 'lesson' : 'lessons' }}
                </span>

                <!-- "0 lessons" is worse than saying nothing. Most of the path
                     is still being written and the card should admit it rather
                     than render a zero and let the reader work it out. -->
                <span
                  v-else
                  class="tl__fact tl__fact--soon"
                >
                  <UIcon
                    name="i-lucide-pencil-line"
                    class="size-3.5"
                  />
                  Being written
                </span>
                <span
                  v-if="subject.modules.length"
                  class="tl__fact"
                >
                  <UIcon
                    name="i-lucide-layers"
                    class="size-3.5"
                  />
                  {{ subject.modules.length }} {{ subject.modules.length === 1 ? 'chapter' : 'chapters' }}
                </span>
                <span
                  v-if="subject.minutes"
                  class="tl__fact"
                >
                  <UIcon
                    name="i-lucide-clock"
                    class="size-3.5"
                  />
                  {{ formatMinutes(subject.minutes) }}
                </span>
                <span
                  v-if="subject.duration"
                  class="tl__fact"
                >
                  <UIcon
                    name="i-lucide-calendar-days"
                    class="size-3.5"
                  />
                  {{ subject.duration }}
                </span>
              </p>

              <ClientOnly>
                <div
                  v-if="subjectProgress(subject).started"
                  class="tl__progress"
                >
                  <UProgress
                    :model-value="subjectProgress(subject).percent"
                    size="xs"
                    :color="subjectProgress(subject).finished ? 'success' : 'primary'"
                  />
                  <p class="tl__progress-label">
                    {{ subjectProgress(subject).finished
                      ? 'Finished'
                      : `${subjectProgress(subject).completed} of ${subjectProgress(subject).total} lessons` }}
                  </p>
                </div>
              </ClientOnly>

              <!-- ── What is actually in it ─────────────────────────────
                   A lesson count is a number; four lesson titles are the
                   subject. These are plain text rather than links on purpose —
                   the whole card is already one link, and a link inside a link
                   is invalid markup and unusable with a keyboard. The titles
                   are here to be read, and the card is here to be clicked. -->
              <ul
                v-if="subject.lessons.length"
                class="tl__lessons"
              >
                <li
                  v-for="lesson in subject.lessons.slice(0, 4)"
                  :key="lesson.path"
                >
                  <UIcon
                    :name="lessonIcon(lesson)"
                    class="size-3 shrink-0"
                  />
                  <span class="truncate">{{ lesson.title }}</span>
                  <ClientOnly>
                    <UIcon
                      v-if="isComplete(lesson.path)"
                      name="i-lucide-check"
                      class="size-3 shrink-0 text-success"
                    />
                  </ClientOnly>
                </li>

                <li
                  v-if="subject.lessons.length > 4"
                  class="tl__lessons-more"
                >
                  <UIcon
                    name="i-lucide-ellipsis"
                    class="size-3 shrink-0"
                  />
                  <span>and {{ subject.lessons.length - 4 }} more</span>
                </li>
              </ul>

              <!-- The first lesson, one click away. The commonest thing somebody
                   wants from a subject card is not the subject page. -->
              <span
                v-if="subject.lessons[0]"
                class="tl__cta"
              >
                <UIcon
                  name="i-lucide-play"
                  class="size-3.5"
                />
                Start with “{{ subject.lessons[0].title }}”
                <UIcon
                  name="i-lucide-arrow-right"
                  class="size-3.5 tl__cta-arrow"
                />
              </span>

              <span
                v-else
                class="tl__cta tl__cta--quiet"
              >
                <UIcon
                  name="i-lucide-map"
                  class="size-3.5"
                />
                Read what this subject covers
                <UIcon
                  name="i-lucide-arrow-right"
                  class="size-3.5 tl__cta-arrow"
                />
              </span>
            </div>
          </NuxtLink>
        </li>
      </ol>
    </section>

    <!-- The end of the line, so the route reads as having one. -->
    <div class="tl__end">
      <span
        class="tl__node tl__node--end"
        aria-hidden="true"
      >
        <UIcon
          name="i-lucide-flag"
          class="size-4"
        />
      </span>
      <div>
        <p class="tl__title">
          Employed
        </p>
        <p class="tl__desc">
          The job hunt is the last subject on the path, taught with the same
          seriousness as everything before it — not an afterthought once you
          "know enough".
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── The geometry ───────────────────────────────────────────────────────
   The spine, every node and every section marker are centred on one axis,
   and that axis is a variable rather than four hand-tuned `left` values —
   which is how they came to be three pixels apart in the first place.

   `--tl-gutter` is the space to the left of the cards. It holds the axis
   *and* the visible sliver of the big number, which is tucked under the
   card's left edge. */
.tl {
  --tl-gutter: 3.75rem;
  --tl-node: 2.625rem;
  --tl-marker: 1.75rem;
  --tl-axis: 1.5rem;

  position: relative;
  padding-left: var(--tl-gutter);
}

.tl__spine {
  position: absolute;
  left: calc(var(--tl-axis) - 1px);
  top: 0.5rem;
  bottom: 3rem;
  width: 2px;
  border-radius: 999px;
  background: var(--ui-border-accented);
}

.tl__spine--done {
  bottom: auto;
  background: linear-gradient(to bottom, var(--ui-primary), var(--ui-secondary));
  transition: height var(--dgm-t-slow) var(--dgm-ease);
}

.tl__group + .tl__group {
  margin-top: 3rem;
}

/* Scrolled to from the sidebar's jump list, so the heading must not land under
   the sticky site header. */
.tl__group {
  scroll-margin-top: 6rem;
}

.tl__stage {
  position: relative;
  margin-bottom: 1.25rem;
}

/* The section marker sits on the spine rather than beside it, so the headings
   read as points along the route instead of as section breaks in a page. */
.tl__stage-marker {
  position: absolute;
  left: calc(var(--tl-axis) - var(--tl-gutter) - var(--tl-marker) / 2);
  top: 0.1rem;
  display: grid;
  place-items: center;
  width: var(--tl-marker);
  height: var(--tl-marker);
  border-radius: var(--radius-sm);
  background: var(--ui-bg-elevated);
  border: 1px solid var(--ui-border-accented);
  color: var(--ui-text-dimmed);
}

.tl__stage-title {
  font-family: var(--font-display);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-text-highlighted);
}

.tl__stage-blurb {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  max-width: 52ch;
  text-wrap: pretty;
}

.tl__stage-meta,
.tl__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem 0.9rem;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
}

.tl__stage-meta {
  margin-top: 0.6rem;
}

.tl__fact {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.tl__fact--soon {
  color: var(--ui-text-dimmed);
  font-style: italic;
}

.tl__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tl__item {
  position: relative;
}

/* ── The node ───────────────────────────────────────────────────────────
   The circle on the spine is the reader's position in a sixteen-subject
   route, so it carries three facts at once: the number, the ring of how
   much of that subject is done, and whether this is the one they are on. */
.tl__node {
  position: absolute;
  left: calc(var(--tl-axis) - var(--tl-gutter) - var(--tl-node) / 2);
  /* Centred on the card, not pinned near its top. The cards are different
     heights and a fixed offset made every node sit at a different point on its
     own card, which reads as sloppy rather than as a route. */
  top: 50%;
  width: var(--tl-node);
  height: var(--tl-node);
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-border-accented);
  box-shadow: 0 0 0 5px var(--ui-bg);
  /* Above the number, which runs the full width of the gutter behind it. */
  z-index: 2;
  transform: translateY(-50%);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.tl__item:hover .tl__node {
  border-color: color-mix(in oklab, var(--ui-primary) 60%, var(--ui-border-accented));
  transform: translateY(-50%) scale(1.04);
}

.tl__node[data-state='current'] {
  border-color: var(--ui-primary);
  background: color-mix(in oklab, var(--ui-primary) 8%, var(--ui-bg));
}

/* The halo is a second element rather than a wider ring, because the progress
   ring is already drawn on the border and a third circle reads as a target. */
.tl__node[data-state='current']::after {
  content: '';
  position: absolute;
  inset: -0.4rem;
  border-radius: 999px;
  border: 1px solid color-mix(in oklab, var(--ui-primary) 45%, transparent);
  animation: tl-pulse 2.6s var(--dgm-ease) infinite;
}

@keyframes tl-pulse {
  0%,
  100% {
    opacity: 0.9;
    transform: scale(1);
  }

  50% {
    opacity: 0.25;
    transform: scale(1.08);
  }
}

/* The node is drawn at three sizes across the breakpoints; the ring is one SVG
   with a `viewBox`, so let it take whatever size the node currently is rather
   than pinning it to the pixel count it was authored at. */
.tl__node :deep(svg) {
  width: 100%;
  height: 100%;
}

.tl__dot {
  position: relative;
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 999px;
  background: var(--ui-border-accented);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.tl__item:hover .tl__dot {
  background: var(--ui-text-dimmed);
}

.tl__node[data-state='current'] .tl__dot {
  background: var(--ui-primary);
  width: 0.625rem;
  height: 0.625rem;
}

.tl__tick {
  position: relative;
  width: 1.1rem;
  height: 1.1rem;
  color: var(--ui-success);
}

/* ── The card ──────────────────────────────────────────────────────────
   `overflow: hidden` is load-bearing: it is what crops the big number into
   the bottom-right corner. */
.tl__card {
  position: relative;
  z-index: 1;
  overflow: hidden;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.15rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    background-color var(--dgm-t-fast) var(--dgm-ease),
    box-shadow var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.tl__card:hover {
  border-color: color-mix(in oklab, var(--ui-primary) 45%, var(--ui-border));
  background: var(--ui-bg-elevated);
  box-shadow: var(--shadow-md);
  transform: translateX(2px);
}

/* ── The number ─────────────────────────────────────────────────────────
   Bottom-right of the card, outlined, and running off both edges so the
   card's corner takes a bite out of it. Padded to two digits so "1" and
   "11" are clipped by the same amount — left to itself the column would
   look accidental. Behind the content, and quiet enough that the title
   always wins. */
.tl__num {
  position: absolute;
  right: -0.3rem;
  bottom: -0.75rem;
  z-index: 0;
  font-family: var(--font-display);
  font-size: 3rem;
  font-weight: 800;
  line-height: 0.8;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.05em;
  color: transparent;
  -webkit-text-stroke: 1.5px var(--ui-border-accented);
  user-select: none;
  pointer-events: none;
  transition: -webkit-text-stroke-color var(--dgm-t-base) var(--dgm-ease);
}

.tl__card:hover .tl__num {
  -webkit-text-stroke-color: var(--ui-primary);
}

/* Both of these sit above the number. Without it the outlined digits would
   paint over the meta row — a positioned child with `z-index: 0` beats
   in-flow content in the same stacking context. */
.tl__thumb {
  position: relative;
  z-index: 1;
  margin-top: 0.15rem;
}

.tl__body {
  position: relative;
  z-index: 1;
  min-width: 0;
  flex: 1;
}

.tl__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tl__title {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
}

.tl__desc {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  text-wrap: pretty;
}

.tl__meta {
  margin-top: 0.6rem;
}

.tl__progress {
  margin-top: 0.7rem;
  max-width: 18rem;
}

/* What is actually inside the subject. Set quieter than the description — it is
   a peek at the contents, not a second paragraph. */
.tl__lessons {
  list-style: none;
  margin: 0.75rem 0 0;
  padding: 0.6rem 0 0;
  border-top: 1px dashed var(--ui-border);
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  max-width: 34rem;
}

.tl__lessons li {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  min-width: 0;
}

.tl__lessons li :deep(.iconify) {
  color: var(--ui-text-dimmed);
}

.tl__lessons-more {
  color: var(--ui-text-dimmed);
  font-style: italic;
}

.tl__progress-label {
  font-size: 0.75rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  font-variant-numeric: tabular-nums;
}

/* The call to action is drawn as a control rather than a link, because on a
   card of four grey facts a bare coloured line does not read as the thing to
   press. */
.tl__cta {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.8rem;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  border: 1px solid color-mix(in oklab, var(--ui-primary) 30%, transparent);
  background: color-mix(in oklab, var(--ui-primary) 8%, transparent);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--ui-primary);
  max-width: 100%;
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.tl__card:hover .tl__cta {
  background: color-mix(in oklab, var(--ui-primary) 15%, transparent);
}

/* A subject with nothing to open yet still gets a control, because a card with
   no action on it reads as broken — but it is drawn as the lesser one it is. */
.tl__cta--quiet {
  border-color: var(--ui-border-accented);
  background: transparent;
  color: var(--ui-text-muted);
}

.tl__card:hover .tl__cta--quiet {
  background: var(--ui-bg);
  color: var(--ui-text-highlighted);
}

.tl__cta-arrow {
  transition: transform var(--dgm-t-fast) var(--dgm-ease);
}

.tl__card:hover .tl__cta-arrow {
  transform: translateX(2px);
}

.tl__end {
  position: relative;
  margin-top: 2.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

/* The end marker has no card to be centred on — it sits beside a paragraph, so
   it lines up with the top of it rather than with the middle of the block. */
.tl__node--end {
  top: -0.15rem;
  transform: none;
  border-color: var(--ui-secondary);
  color: var(--ui-secondary);
  background: color-mix(in oklab, var(--ui-secondary) 10%, var(--ui-bg));
}

/* Everything on the left rail is derived from the four variables, so a phone is
   four numbers rather than eight hand-adjusted offsets. */
@media (max-width: 639px) {
  .tl {
    --tl-gutter: 3.25rem;
    --tl-node: 2.125rem;
    --tl-marker: 1.5rem;
    --tl-axis: 1.0625rem;
  }

  .tl__card {
    gap: 0.6rem;
    padding: 0.9rem;
  }

  .tl__num {
    font-size: 2.25rem;
    -webkit-text-stroke-width: 1.25px;
  }

  .tl__lessons {
    display: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .tl__card,
  .tl__node,
  .tl__dot,
  .tl__num,
  .tl__cta-arrow,
  .tl__spine--done {
    transition: none;
  }

  .tl__card:hover {
    transform: none;
  }

  .tl__item:hover .tl__node {
    transform: none;
  }

  .tl__node[data-state='current']::after {
    animation: none;
  }
}
</style>
