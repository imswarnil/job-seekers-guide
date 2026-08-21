<script setup lang="ts">
import type { LearningPath } from '~/utils/path'

/**
 * The four numbers that describe the whole path, counted up once.
 *
 * These were a card in the sidebar, where they were four small grey facts that
 * followed you down the page saying the same thing. They are not sidebar
 * material — they are the scale of the thing, and scale is worth a moment of
 * the reader's attention rather than a permanent corner of the screen.
 *
 * The animation is progressive enhancement, arranged the same way the diagrams
 * are: the server renders the finished numbers, and JavaScript only winds them
 * back to zero *after* mount, once it knows it can wind them forward again. A
 * reader with no JavaScript, a crawler and the prerendered HTML all get real
 * figures; nobody ever sees a permanent row of zeros.
 */
const props = withDefaults(defineProps<{
  path: LearningPath
  /**
   * `band` is the full-width row; `panel` is the narrow two-column block that
   * sits at the top of the sidebar. Same numbers, same count — a 288px column
   * simply cannot carry a 2rem numeral and a full label side by side four
   * times.
   */
  layout?: 'band' | 'panel'
}>(), {
  layout: 'band'
})

/**
 * The honest length of the path, added up from what each subject claims rather
 * than from a round number somebody liked the look of.
 */
const weeks = computed(() => props.path.subjects.reduce((total, subject) => {
  const match = subject.duration?.match(/(\d+)/)
  return total + (match ? Number(match[1]) : 0)
}, 0))

interface Stat {
  icon: string
  label: string
  /** What is counted. */
  to: number
  /** How the counted number is said. */
  format: (n: number) => string
}

const compact = computed(() => props.layout === 'panel')

const stats = computed<Stat[]>(() => [
  {
    icon: 'i-lucide-layers',
    label: compact.value ? 'Subjects' : 'Subjects, in one order',
    to: props.path.subjects.length,
    format: n => String(n)
  },
  {
    icon: 'i-lucide-book-open',
    label: compact.value ? 'Lessons' : 'Lessons written so far',
    to: props.path.lessons.length,
    format: n => String(n)
  },
  {
    icon: 'i-lucide-clock',
    label: compact.value ? 'Reading' : 'Of reading, end to end',
    to: props.path.minutes,
    format: n => formatMinutes(n) || '—'
  },
  {
    icon: 'i-lucide-calendar-days',
    label: compact.value ? 'At that pace' : 'At the stated pace',
    to: weeks.value,
    format: n => n ? `${n} weeks` : '—'
  }
])

const root = useTemplateRef<HTMLElement>('root')
const visible = useElementVisibility(root)

/** 0 → 1 across the count. Starts at 1 so the server renders finished. */
const t = ref(1)

const shown = computed(() => stats.value.map(stat => stat.format(Math.round(stat.to * t.value))))

/* ── The count ─────────────────────────────────────────────────────────
   requestAnimationFrame rather than a CSS counter, because three of these
   four numbers are not plain integers on screen — "5h 36m" and "90 weeks"
   have to be formatted from whatever the tween is currently on. */
let frame = 0

function run() {
  const start = performance.now()
  const DURATION = 1100

  cancelAnimationFrame(frame)

  function step(now: number) {
    const progress = Math.min((now - start) / DURATION, 1)
    // Ease out: the numbers should arrive settling, not braking.
    t.value = 1 - (1 - progress) ** 3

    if (progress < 1) {
      frame = requestAnimationFrame(step)
    }
  }

  frame = requestAnimationFrame(step)
}

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return
  }

  // Wind back only now that we know we can wind forward.
  t.value = 0

  const stop = watch(visible, (isVisible) => {
    if (isVisible) {
      run()
      stop()
    }
  }, { immediate: true })
})

onBeforeUnmount(() => cancelAnimationFrame(frame))
</script>

<template>
  <div
    ref="root"
    class="stats"
    :data-layout="layout"
  >
    <p
      v-if="compact"
      class="stats__heading"
    >
      <UIcon
        name="i-lucide-route"
        class="size-3.5 text-primary"
      />
      The whole path
    </p>

    <dl class="stats__grid">
      <div
        v-for="(stat, index) in stats"
        :key="stat.label"
        class="stats__item"
        :style="{ '--i': index }"
      >
        <UIcon
          :name="stat.icon"
          class="stats__icon"
        />
        <dt class="stats__value">
          {{ shown[index] }}
        </dt>
        <dd class="stats__label">
          {{ stat.label }}
        </dd>
      </div>
    </dl>
  </div>
</template>

<style scoped>
/* ── The band ──────────────────────────────────────────────────────────
   Four separate tiles across the full width of the page. */
.stats__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  margin: 0;
}

@media (min-width: 768px) {
  .stats[data-layout='band'] .stats__grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
  }
}

.stats__item {
  position: relative;
  overflow: hidden;
  padding: 1rem 1.15rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
}

/* ── The panel ─────────────────────────────────────────────────────────
   One card in the sidebar with a two-by-two grid inside it, so it matches
   the section list and the sign-up beneath rather than looking like four
   loose boxes that wandered into the column. */
.stats[data-layout='panel'] {
  padding: 1rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
}

.stats__heading {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
}

.stats[data-layout='panel'] .stats__grid {
  gap: 0.5rem;
}

.stats[data-layout='panel'] .stats__item {
  padding: 0.6rem 0.7rem;
  border-radius: var(--radius-md);
  background: var(--ui-bg-elevated);
}

.stats[data-layout='panel'] .stats__value {
  font-size: 1.25rem;
  margin-top: 0.2rem;
}

.stats[data-layout='panel'] .stats__label {
  font-size: 0.6875rem;
}

.stats[data-layout='panel'] .stats__icon {
  width: 0.8125rem;
  height: 0.8125rem;
}

/* A slow sweep of light across each tile, staggered along the row, so the four
   of them read as one object counting rather than four boxes each doing
   something. It runs three times and stops — a permanent shimmer on a page
   somebody is trying to read is an animation that has outstayed its point. */
.stats__item::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      110deg,
      transparent 35%,
      color-mix(in oklab, var(--ui-primary) 12%, transparent) 50%,
      transparent 65%
    );
  transform: translateX(-100%);
  animation: stats-sweep 2.4s var(--dgm-ease) calc(var(--i) * 120ms) 3;
  pointer-events: none;
}

@keyframes stats-sweep {
  to {
    transform: translateX(100%);
  }
}

.stats__icon {
  width: 1rem;
  height: 1rem;
  color: var(--ui-primary);
}

.stats__value {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 4vw, 2rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.03em;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-highlighted);
  margin-top: 0.4rem;
}

.stats__label {
  margin: 0.2rem 0 0;
  font-size: 0.75rem;
  line-height: 1.35;
  color: var(--ui-text-muted);
  text-wrap: balance;
}

@media (prefers-reduced-motion: reduce) {
  .stats__item::after {
    animation: none;
    opacity: 0;
  }
}
</style>
