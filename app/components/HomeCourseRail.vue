<script setup lang="ts">
import type { LearningPath } from '~/utils/path'

/**
 * The path across, rather than down.
 *
 * Ten subjects in a three-column grid is four rows and most of a screen, and it
 * reads as a catalogue — a set of things to choose between. The whole claim of
 * this site is that they are not a set, they are a sequence, and a sequence
 * belongs on one line you travel along. So: one row, snapped, with the order
 * running left to right and the eleventh card being the rest of it.
 *
 * Scrolling works with a wheel, a trackpad, a touch drag and the keyboard
 * without any of this script — the arrows are an affordance on top, because a
 * horizontal scroller with no visible control is one most people never realise
 * they can move.
 */
const props = withDefaults(defineProps<{
  path: LearningPath
  /** How many subjects to show before the "everything else" card. */
  count?: number
}>(), {
  count: 10
})

const subjects = computed(() => props.path.subjects.slice(0, props.count))
const rest = computed(() => Math.max(props.path.subjects.length - subjects.value.length, 0))

const track = useTemplateRef<HTMLElement>('track')

const atStart = ref(true)
const atEnd = ref(false)

function measure() {
  const element = track.value
  if (!element) {
    return
  }

  const max = element.scrollWidth - element.clientWidth
  atStart.value = element.scrollLeft <= 2
  // Not `>= max`: sub-pixel widths mean the last scroll position is routinely a
  // fraction short of the maximum and the arrow would never disable.
  atEnd.value = element.scrollLeft >= max - 2
}

/** One card and its gap, so a press moves by exactly one position. */
function step(direction: 1 | -1) {
  const element = track.value
  if (!element) {
    return
  }

  const card = element.firstElementChild as HTMLElement | null
  const distance = card ? card.offsetWidth + 24 : element.clientWidth * 0.8

  element.scrollBy({ left: distance * direction, behavior: 'smooth' })
}

onMounted(() => {
  measure()
  useEventListener(track, 'scroll', measure, { passive: true })
  useResizeObserver(track, measure)
})
</script>

<template>
  <div class="rail">
    <div class="rail__head">
      <div class="min-w-0">
        <h2 class="rail__title">
          The whole thing, in order
        </h2>
        <p class="rail__lede">
          {{ path.subjects.length }} subjects, one sequence. Each one exists
          because the one before it hit a wall.
        </p>
      </div>

      <!-- Hidden from assistive tech: the row is already reachable and
           scrollable without them, and announcing two unlabelled directions
           adds nothing a screen reader user can act on. -->
      <div
        class="rail__arrows"
        aria-hidden="true"
      >
        <button
          type="button"
          class="rail__arrow"
          :disabled="atStart"
          tabindex="-1"
          @click="step(-1)"
        >
          <UIcon
            name="i-lucide-chevron-left"
            class="size-4"
          />
        </button>
        <button
          type="button"
          class="rail__arrow"
          :disabled="atEnd"
          tabindex="-1"
          @click="step(1)"
        >
          <UIcon
            name="i-lucide-chevron-right"
            class="size-4"
          />
        </button>
      </div>
    </div>

    <div class="rail__viewport">
      <ol
        ref="track"
        class="rail__track"
      >
        <li
          v-for="(subject, index) in subjects"
          :key="subject.path"
          class="rail__item"
        >
          <SubjectCard
            :subject="subject"
            :index="index"
          />
        </li>

        <!-- The last card is the rest of the path. Stopping a numbered row at
             ten with nothing after it reads as "that is all there is". -->
        <li class="rail__item">
          <div class="rail__more">
            <span
              class="rail__more-n"
              aria-hidden="true"
            >{{ subjects.length + 1 }}</span>

            <NuxtLink
              to="/start"
              class="rail__more-card"
            >
              <UIcon
                name="i-lucide-route"
                class="size-6 text-primary"
              />
              <span class="rail__more-title">View the full curriculum</span>
              <span class="rail__more-note">
                <template v-if="rest">
                  The remaining {{ rest }} subjects, every chapter inside them,
                  and where the job hunt sits.
                </template>
                <template v-else>
                  Every chapter inside each subject, and where the job hunt sits.
                </template>
              </span>
              <span class="rail__more-cta">
                Start here
                <UIcon
                  name="i-lucide-arrow-right"
                  class="size-3.5"
                />
              </span>
            </NuxtLink>
          </div>
        </li>
      </ol>
    </div>
  </div>
</template>

<style scoped>
/* UContainer is `px-4 sm:px-6 lg:px-8`. The row has to back out of exactly that
   to reach the edge of the screen and then pad itself back in by the same
   amount, so the first card lines up under the heading. Mirrored here rather
   than read from a variable because the theme expresses it as Tailwind classes,
   not as a custom property. */
.rail {
  --rail-pad: 1rem;
}

@media (min-width: 640px) {
  .rail {
    --rail-pad: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .rail {
    --rail-pad: 2rem;
  }
}

.rail__head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.rail__title {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.rail__lede {
  margin-top: 0.5rem;
  color: var(--ui-text-muted);
  max-width: 42rem;
  text-wrap: pretty;
}

.rail__arrows {
  display: none;
  gap: 0.5rem;
  flex-shrink: 0;
}

@media (min-width: 768px) {
  .rail__arrows {
    display: flex;
  }
}

.rail__arrow {
  display: grid;
  place-items: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 999px;
  border: 1px solid var(--ui-border-accented);
  background: var(--ui-bg);
  color: var(--ui-text-muted);
  transition:
    color var(--dgm-t-fast) var(--dgm-ease),
    border-color var(--dgm-t-fast) var(--dgm-ease),
    opacity var(--dgm-t-fast) var(--dgm-ease);
}

.rail__arrow:hover:not(:disabled) {
  color: var(--ui-primary);
  border-color: var(--ui-primary);
}

.rail__arrow:disabled {
  opacity: 0.35;
  cursor: default;
}

/* The row bleeds to the edges of the viewport rather than stopping at the
   container, so a card cut off at the right says "there is more" instead of
   looking like the row simply ends there.

   The negative margin and matching padding are what let the first card line up
   with the heading while the overflow still runs to the screen edge. */
.rail__viewport {
  margin-inline: calc(-1 * var(--rail-pad));
}

.rail__track {
  list-style: none;
  margin: 0;
  padding-inline: var(--rail-pad);
  /* Room for the cards' hover lift and their drop shadow, which a tight
     overflow box would otherwise clip. */
  padding-block: 0.5rem 1.25rem;
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-snap-type: x mandatory;
  /* Hidden, not absent: the row still scrolls with a wheel, a trackpad, a drag
     and the keyboard. The arrows above are the visible affordance instead.
     `overscroll-behavior-x` stops a horizontal overscroll from turning into a
     browser back-swipe; the y axis is left alone on purpose so a vertical wheel
     over the row still scrolls the page. */
  scrollbar-width: none;
  overscroll-behavior-x: contain;
  overscroll-behavior-y: auto;
}

.rail__track::-webkit-scrollbar {
  display: none;
}

.rail__item {
  flex: 0 0 17rem;
  scroll-snap-align: start;
  display: flex;
}

.rail__item > * {
  width: 100%;
}

@media (min-width: 640px) {
  .rail__item {
    flex-basis: 19rem;
  }
}

/* ── The last card ─────────────────────────────────────────────────────
   Built to SubjectCard's measurements rather than being one, because it is
   not a subject — it is the door to the others, and it should read as the
   end of the row rather than as an eleventh course. */
.rail__more {
  position: relative;
  padding-left: 1.75rem;
  width: 100%;
}

@media (min-width: 640px) {
  .rail__more {
    padding-left: 2.25rem;
  }
}

.rail__more-n {
  position: absolute;
  left: 0;
  bottom: -0.35rem;
  z-index: 0;
  font-family: var(--font-display);
  font-size: 5.5rem;
  font-weight: 800;
  line-height: 0.8;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.06em;
  color: transparent;
  -webkit-text-stroke: 2px var(--ui-border-accented);
  pointer-events: none;
  user-select: none;
  transition: -webkit-text-stroke-color var(--dgm-t-base) var(--dgm-ease);
}

@media (min-width: 640px) {
  .rail__more-n {
    font-size: 7rem;
    -webkit-text-stroke-width: 2.5px;
  }
}

.rail__more:hover .rail__more-n {
  -webkit-text-stroke-color: var(--ui-primary);
}

.rail__more-card {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1.25rem;
  border: 1px dashed var(--ui-border-accented);
  border-radius: var(--radius-lg);
  background: color-mix(in oklab, var(--ui-primary) 4%, var(--ui-bg));
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease),
    box-shadow var(--dgm-t-fast) var(--dgm-ease);
}

.rail__more-card:hover {
  border-color: var(--ui-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.rail__more-title {
  font-family: var(--font-display);
  font-size: 1.0625rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
  margin-top: 0.75rem;
  text-wrap: balance;
}

.rail__more-note {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.375rem;
  text-wrap: pretty;
}

.rail__more-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: auto;
  padding-top: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--ui-primary);
}

@media (prefers-reduced-motion: reduce) {
  .rail__track {
    scroll-behavior: auto;
  }

  .rail__arrow,
  .rail__more-n,
  .rail__more-card {
    transition: none;
  }

  .rail__more-card:hover {
    transform: none;
  }
}
</style>
