<script setup lang="ts">
import type { StoryCollectionItem } from '@nuxt/content'

/**
 * The book.
 *
 * It owns the whole viewport and nothing scrolls. That is the point: a book is
 * a fixed rectangle you turn pages inside, and the moment a scrollbar appears
 * the illusion collapses into a web page with an animation bolted on. So the
 * chrome, the index and both covers all live inside the same rectangle.
 *
 * Three things carry it, and all three have to be right together:
 *
 *   1. Real pagination. Chapters are poured into a page-sized multi-column box
 *      (`BookFlow`) and broken into discrete pages by the browser, then
 *      measured. Page counts are a function of the window, so resizing
 *      repaginates exactly like changing the type size on an e-reader.
 *   2. A leaf with two sides. The turning sheet carries the page you are
 *      leaving on its front and the page you are arriving at on its back, and
 *      it lands precisely where the static layer already has that page — so
 *      the hand-off at the end of the turn is invisible.
 *   3. Weight. The turn tracks your pointer, keeps going if you let go past a
 *      third, and falls back if you do not.
 *
 * The flip is the first thing to go: under reduced motion it is an instant
 * change, and there is a permanent "read as one column" toggle for anyone who
 * would rather scroll. Neither is a degraded mode — some people read that way,
 * and the paginator also falls back to it on its own if a browser ever refuses
 * to make overflow columns.
 */
const props = defineProps<{
  chapters: StoryCollectionItem[]
  minutes: number
  /** The running head on left-hand pages. */
  book?: string
  /** Open straight onto this chapter's path instead of on the cover. */
  startAt?: string
  /** Where the way out goes. */
  exitTo?: string
}>()

const emit = defineEmits<{
  /** Fires when the visible chapter changes, so the URL can follow it. */
  chapter: [chapter: StoryCollectionItem | undefined]
}>()

interface Leaf {
  chapter?: StoryCollectionItem
  column: number
  folio: number
}

/** Has to match the CSS transition on `.book__leaf`. */
const FLIP_MS = 620
/** Gutter between overflow columns. Invisible — it only enters the arithmetic. */
const COL_GAP = 48

const sound = useBookSound()
const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')

const stage = useTemplateRef<HTMLElement>('stage')

/**
 * The measuring probes, keyed by chapter index.
 *
 * Deliberately a map built by a function ref rather than `ref` on the `v-for`:
 * Vue does not guarantee that a ref array comes back in source order, and a
 * shuffled array here would hand chapter four's page count to chapter one and
 * lose the difference somewhere in the middle of the book.
 */
type Probe = { measure: () => { pages: number, overflowed: boolean } }
const probes = new Map<number, Probe>()

function setProbe(el: unknown, index: number) {
  if (el) {
    probes.set(index, el as Probe)
  } else {
    probes.delete(index)
  }
}

/* ── Geometry ─────────────────────────────────────────────────────────── */

/* Both dimensions come from the stage rather than from the window: the stage is
   a flex child that has already been given whatever height is left after the
   bar and the footer, which is the only number that is actually true. */
const { width: stageW, height: stageH } = useElementSize(stage)

const spread = computed(() => stageW.value >= 900)

/**
 * The page, capped.
 *
 * A page is as wide as the room allows only up to a point: past about sixty
 * characters a line the eye loses its place on the return sweep, and a book
 * that fills a 27-inch monitor edge to edge is less readable than the paperback
 * it is imitating. So the sheet stops growing and centres itself, exactly like
 * a real book on a large desk.
 */
const MAX_PAGE_W = 460
const pageW = computed(() => {
  const available = (spread.value ? stageW.value / 2 : stageW.value) || 320
  return Math.max(260, Math.floor(Math.min(MAX_PAGE_W, available)))
})
const pageH = computed(() => Math.max(320, Math.floor(stageH.value || 520)))

const vars = computed(() => ({
  '--page-w': `${pageW.value}px`,
  '--page-h': `${pageH.value}px`,
  '--book-col-gap': `${COL_GAP}px`
}))

/* ── Pagination ───────────────────────────────────────────────────────── */

const counts = ref<number[]>([])
const probing = ref(true)
/** Set if the browser clipped instead of spilling into overflow columns. */
const broken = ref(false)

/**
 * Which repagination is current.
 *
 * Two runs overlap constantly — one on mount, one the moment the stage reports
 * its real size — and each awaits fonts and a frame in the middle. Without a
 * token the first run finishes last, unmounts the probes out from under the
 * second, and the second measures nothing and reports every chapter as one page.
 */
let run = 0

async function repaginate() {
  // The watcher below is `immediate`, so this is reached during SSR too — where
  // there is no layout to measure and no `requestAnimationFrame` to wait on.
  if (!import.meta.client) {
    return
  }

  const mine = ++run
  probing.value = true
  await nextTick()

  // Measuring before the webfont has swapped in measures the fallback, and the
  // answer is wrong by a page or two on a long chapter.
  try {
    await document.fonts?.ready
  } catch {
    // No Font Loading API. The layout below is still measurable.
  }
  await new Promise(resolve => requestAnimationFrame(resolve))

  if (mine !== run) {
    return
  }

  const next: number[] = []
  let failed = false

  for (let i = 0; i < props.chapters.length; i++) {
    const result = probes.get(i)?.measure() ?? { pages: 1, overflowed: false }
    next.push(result.pages)
    failed = failed || result.overflowed
  }

  counts.value = next
  broken.value = failed
  probing.value = false
}

const schedule = useDebounceFn(repaginate, 220)

/* `immediate` rather than a separate `onMounted`: the stage reports zero on the
   first tick and its real size on the next, and the debounce collapses the pair
   into one measurement at the size that actually matters. */
watch([pageW, pageH, spread], schedule, { immediate: true })

/**
 * Every page in the book, in order.
 *
 * Chapters open on a fresh left-hand page, which is why blank leaves exist:
 * padding to an even count is what a printed book does, and without it half the
 * chapters open on the right of a spread and the rhythm falls apart.
 */
const leaves = computed<Leaf[]>(() => {
  const out: Leaf[] = []

  props.chapters.forEach((chapter, index) => {
    const pages = Math.max(1, counts.value[index] ?? 1)
    for (let column = 0; column < pages; column++) {
      out.push({ chapter, column, folio: out.length + 1 })
    }

    const last = index === props.chapters.length - 1
    if (spread.value && !last && out.length % 2 === 1) {
      out.push({ column: 0, folio: out.length + 1 })
    }
  })

  return out
})

const total = computed(() => leaves.value.length)

function at(index: number): Leaf | undefined {
  return index >= 0 && index < total.value ? leaves.value[index] : undefined
}

/* ── Where the reader is ──────────────────────────────────────────────── */

const cursor = ref(0)
const step = computed(() => spread.value ? 2 : 1)
const canForward = computed(() => cursor.value + step.value < total.value)
const canBack = computed(() => cursor.value - step.value >= 0)

/* Chapters are saved, not page numbers: a page number means nothing once the
   window is a different width, and it will be. */
const saved = useLocalStorage<{ chapter: number }>('guide:story:v2', () => ({ chapter: -1 }))

function firstLeafOf(chapterIndex: number) {
  const target = props.chapters[chapterIndex]
  const index = leaves.value.findIndex(leaf => leaf.chapter === target)
  if (index < 0) {
    return 0
  }
  return spread.value ? index - (index % 2) : index
}

const chapterOf = computed(() => {
  const leaf = at(cursor.value) ?? at(cursor.value + 1)
  return leaf?.chapter
})

const readingChapterIndex = computed(() => {
  const chapter = chapterOf.value
  return chapter ? props.chapters.indexOf(chapter) : -1
})

/* ── The covers ───────────────────────────────────────────────────────── */

const mode = ref<'cover' | 'reading' | 'back'>('cover')
const opening = ref(false)

watch(chapterOf, (chapter) => {
  const index = chapter ? props.chapters.indexOf(chapter) : -1
  if (index >= 0) {
    saved.value = { chapter: index }
  }
  if (mode.value === 'reading') {
    emit('chapter', chapter)
  }
})

/* Keep the cursor on a left-hand page when the window crosses into spreads. */
watch(spread, (isSpread) => {
  if (isSpread && cursor.value % 2 === 1) {
    cursor.value -= 1
  }
})

/* Repagination moves pages around under the reader; keep them on the chapter
   they were reading rather than on a page index that now means something else. */
watch(total, (next, previous) => {
  if (previous && cursor.value >= next) {
    cursor.value = Math.max(0, next - step.value)
  }
})

const resumeChapter = computed(() => {
  const index = saved.value.chapter
  return index >= 0 && index < props.chapters.length ? props.chapters[index] : undefined
})

function openBook(page = 0) {
  sound.unlock()
  sound.open()
  cursor.value = page
  opening.value = true
  window.setTimeout(() => {
    mode.value = 'reading'
    emit('chapter', chapterOf.value)
  }, reduced.value ? 0 : 700)
}

function resume() {
  const index = saved.value.chapter
  openBook(index >= 0 ? firstLeafOf(index) : 0)
}

function closeBook() {
  sound.close()
  opening.value = false
  mode.value = 'cover'
  emit('chapter', undefined)
}

/* Arriving on a chapter URL skips the cover: a shared link should land on the
   page it names, not on a door the reader has to open again. */
onMounted(() => {
  if (!props.startAt) {
    return
  }
  const index = props.chapters.findIndex(chapter => chapter.path === props.startAt)
  if (index < 0) {
    return
  }
  mode.value = 'reading'
  cursor.value = firstLeafOf(index)

  // Page counts are not known on the first tick, so the jump is repeated once
  // the measurement lands or it points at the wrong leaf.
  const stop = watch(total, () => {
    cursor.value = firstLeafOf(index)
    stop()
  })
})

/* ── The turn ─────────────────────────────────────────────────────────── */

const flip = ref<{ dir: 'forward' | 'back', p: number, animating: boolean } | undefined>()

const angle = computed(() => {
  const state = flip.value
  if (!state) {
    return 0
  }
  return (state.dir === 'forward' ? -180 : 180) * state.p
})

/** Peaks in the middle of the turn, which is where a real sheet casts most. */
const lift = computed(() => Math.sin(Math.min(1, flip.value?.p ?? 0) * Math.PI))

/* The four surfaces in play. In a spread, turning forward lifts the right-hand
   page: its front is the page you were reading, its back is the page that
   becomes the new left — which is exactly where it lands. */
const staticLeft = computed(() => {
  const state = flip.value
  if (!spread.value) {
    if (!state) {
      return at(cursor.value)
    }
    return state.dir === 'forward' ? at(cursor.value + 1) : at(cursor.value - 1)
  }
  if (!state) {
    return at(cursor.value)
  }
  return state.dir === 'forward' ? at(cursor.value) : at(cursor.value - 2)
})

const staticRight = computed(() => {
  if (!spread.value) {
    return undefined
  }
  const state = flip.value
  if (!state) {
    return at(cursor.value + 1)
  }
  return state.dir === 'forward' ? at(cursor.value + 3) : at(cursor.value + 1)
})

const leafFront = computed(() => {
  const state = flip.value
  if (!state) {
    return undefined
  }
  return state.dir === 'forward' ? at(cursor.value + step.value - 1) : at(cursor.value)
})

const leafBack = computed(() => {
  const state = flip.value
  if (!state) {
    return undefined
  }
  return state.dir === 'forward' ? at(cursor.value + step.value) : at(cursor.value - 1)
})

let settleTimer: number | undefined

function begin(dir: 'forward' | 'back') {
  if (flip.value) {
    return false
  }
  if (dir === 'forward' ? !canForward.value : !canBack.value) {
    return false
  }
  flip.value = { dir, p: 0, animating: false }
  return true
}

function settle(commit: boolean) {
  const state = flip.value
  if (!state) {
    return
  }
  if (commit) {
    sound.turn(state.dir)
  }

  flip.value = { ...state, p: commit ? 1 : 0, animating: true }

  window.clearTimeout(settleTimer)
  settleTimer = window.setTimeout(() => {
    if (commit) {
      cursor.value += state.dir === 'forward' ? step.value : -step.value
    }
    flip.value = undefined
  }, reduced.value ? 0 : FLIP_MS)
}

function turn(dir: 'forward' | 'back') {
  sound.unlock()

  /* Past the last page the next thing is the back cover, the way it is on a
     real book — not a dead button. */
  if (dir === 'forward' && !canForward.value && mode.value === 'reading') {
    sound.close()
    mode.value = 'back'
    return
  }

  if (reduced.value) {
    if (dir === 'forward' ? !canForward.value : !canBack.value) {
      return
    }
    sound.turn(dir)
    cursor.value += dir === 'forward' ? step.value : -step.value
    return
  }

  if (!begin(dir)) {
    return
  }
  // Two frames: the leaf has to be painted flat before the transition to 180°
  // has anything to animate from.
  requestAnimationFrame(() => requestAnimationFrame(() => settle(true)))
}

/* ── Dragging the corner ──────────────────────────────────────────────── */

let dragging = false
let engaged = false
let startX = 0
let startY = 0
let dragDir: 'forward' | 'back' = 'forward'

function onDown(event: PointerEvent) {
  if (flip.value || mode.value !== 'reading' || reduced.value) {
    return
  }
  // Links inside the prose stay links.
  if ((event.target as HTMLElement | null)?.closest('a, button')) {
    return
  }
  dragging = true
  engaged = false
  startX = event.clientX
  startY = event.clientY
}

function onMove(event: PointerEvent) {
  if (!dragging) {
    return
  }

  const dx = event.clientX - startX
  const dy = event.clientY - startY

  if (!engaged) {
    // Sideways, and decisively — otherwise this is a scroll or a selection.
    if (Math.abs(dx) < 16 || Math.abs(dx) <= Math.abs(dy)) {
      return
    }
    dragDir = dx < 0 ? 'forward' : 'back'
    sound.unlock()
    if (!begin(dragDir)) {
      dragging = false
      return
    }
    engaged = true
    ;(event.currentTarget as HTMLElement).setPointerCapture(event.pointerId)
  }

  const travel = dragDir === 'forward' ? -dx : dx
  flip.value = {
    dir: dragDir,
    p: Math.min(1, Math.max(0, travel / pageW.value)),
    animating: false
  }
}

function onUp(event: PointerEvent) {
  if (!dragging) {
    return
  }
  dragging = false

  if (!engaged) {
    // A tap. Right half forward, left half back — the way every reader works.
    const rect = stage.value?.getBoundingClientRect()
    if (rect) {
      turn(event.clientX - rect.left > rect.width / 2 ? 'forward' : 'back')
    }
    return
  }

  engaged = false
  settle((flip.value?.p ?? 0) > 0.3)
}

function onCancel() {
  if (!dragging) {
    return
  }
  dragging = false
  if (engaged) {
    engaged = false
    settle(false)
  }
}

onBeforeUnmount(() => window.clearTimeout(settleTimer))

/* ── The index ────────────────────────────────────────────────────────── */

const indexOpen = ref(false)
const preferScroll = ref(false)
const scrollMode = computed(() => preferScroll.value || broken.value)

function goTo(chapterIndex: number) {
  indexOpen.value = false
  if (mode.value !== 'reading') {
    openBook(firstLeafOf(chapterIndex))
    return
  }
  const target = firstLeafOf(chapterIndex)
  if (target === cursor.value) {
    return
  }
  sound.unlock()
  sound.turn(target > cursor.value ? 'forward' : 'back')
  cursor.value = target
}

defineShortcuts({
  arrowright: () => turn('forward'),
  arrowleft: () => turn('back'),
  escape: () => {
    indexOpen.value = false
  }
})

const progress = computed(() => total.value
  ? Math.round((Math.min(cursor.value + step.value, total.value) / total.value) * 100)
  : 0)
</script>

<template>
  <div class="book">
    <ClientOnly>
      <div
        class="book__client"
        :style="vars"
      >
        <!-- ── Chrome ───────────────────────────────────────────────
             Deliberately slim. This is the only furniture the reader
             gets, so it carries exactly four things: the way out, the
             index, where you are, and the two switches. -->
        <header class="book__bar">
          <UButton
            :to="exitTo || '/my-story'"
            icon="i-lucide-x"
            color="neutral"
            variant="ghost"
            size="sm"
            aria-label="Close the book"
          />

          <UButton
            icon="i-lucide-list"
            label="Contents"
            color="neutral"
            variant="ghost"
            size="sm"
            :aria-expanded="indexOpen"
            @click="indexOpen = !indexOpen"
          />

          <p class="book__title">
            {{ book }}
          </p>

          <div class="book__progress">
            <div
              class="book__progress-fill"
              :style="{ width: `${mode === 'reading' ? progress : 0}%` }"
            />
          </div>

          <UButton
            :icon="sound.enabled.value ? 'i-lucide-volume-2' : 'i-lucide-volume-x'"
            color="neutral"
            variant="ghost"
            size="sm"
            :aria-label="sound.enabled.value ? 'Turn page sound off' : 'Turn page sound on'"
            @click="sound.enabled.value = !sound.enabled.value"
          />

          <UButton
            v-if="!broken"
            :icon="preferScroll ? 'i-lucide-book-open' : 'i-lucide-scroll-text'"
            color="neutral"
            variant="ghost"
            size="sm"
            :aria-label="preferScroll ? 'Read as a book' : 'Read as one column'"
            @click="preferScroll = !preferScroll"
          />
        </header>

        <!-- ── The stage ────────────────────────────────────────────
             Everything visible lives inside this box — the leaf, the
             index and both covers — so the perspective origin is the
             binding and nothing can escape the viewport. -->
        <div
          ref="stage"
          class="book__stage"
          :data-spread="spread ? '' : undefined"
          @pointerdown="onDown"
          @pointermove="onMove"
          @pointerup="onUp"
          @pointercancel="onCancel"
        >
          <template v-if="!scrollMode">
            <div
              class="book__sheet"
              :style="{ '--sheet-w': `calc(var(--page-w) * ${spread ? 2 : 1})` }"
            >
              <BookPage
                :chapter="staticLeft?.chapter"
                :column="staticLeft?.column"
                :folio="staticLeft?.folio"
                :side="spread ? 'left' : 'right'"
                :book="book"
              />
              <BookPage
                v-if="spread"
                :chapter="staticRight?.chapter"
                :column="staticRight?.column"
                :folio="staticRight?.folio"
                side="right"
                :book="book"
              />

              <span
                v-if="spread"
                class="book__binding"
              />
              <!-- The rest of the book, seen edge-on. A spread with nothing at
                   its outer edges reads as two sheets of paper; these are what
                   say there are two hundred more underneath. -->
              <span class="book__edge book__edge--left" />
              <span class="book__edge book__edge--right" />
              <span class="book__ribbon" />
            </div>

            <!-- The turning sheet. -->
            <div
              v-if="flip"
              class="book__leaf"
              :style="{
                left: flip.dir === 'forward' && spread
                  ? `calc(50% + 0px)`
                  : `calc(50% - var(--page-w) * ${spread ? 1 : 0.5})`,
                transformOrigin: flip.dir === 'forward' ? 'left center' : 'right center',
                transform: `rotateY(${angle}deg)`,
                transitionDuration: flip.animating ? `${FLIP_MS}ms` : '0ms',
                boxShadow: `0 ${18 * lift}px ${46 * lift}px rgb(16 19 64 / ${0.05 + 0.2 * lift})`
              }"
            >
              <div class="book__face">
                <BookPage
                  :chapter="leafFront?.chapter"
                  :column="leafFront?.column"
                  :folio="leafFront?.folio"
                  :side="flip.dir === 'forward' && spread ? 'right' : 'left'"
                  :book="book"
                />
                <span
                  class="book__shade book__shade--front"
                  :style="{ opacity: 0.45 * lift }"
                />
              </div>

              <div class="book__face book__face--back">
                <BookPage
                  :chapter="leafBack?.chapter"
                  :column="leafBack?.column"
                  :folio="leafBack?.folio"
                  :side="flip.dir === 'forward' && spread ? 'left' : 'right'"
                  :book="book"
                />
                <span
                  class="book__shade book__shade--back"
                  :style="{ opacity: 0.4 * lift }"
                />
              </div>
            </div>

            <!-- The corner you can take hold of. An affordance, not a control:
                 the whole page is draggable, this is just where a hand expects
                 to find the edge. -->
            <span
              v-if="canForward && !flip && mode === 'reading'"
              class="book__corner"
              :style="{ right: `calc(50% - var(--page-w) * ${spread ? 1 : 0.5})` }"
              aria-hidden="true"
            />
          </template>

          <!-- Or all of it in one column, for people who read that way. -->
          <div
            v-else
            class="book__scroll guide-prose"
          >
            <article
              v-for="chapter in chapters"
              :key="chapter.path"
              class="book__scroll-chapter"
            >
              <p class="book__scroll-eyebrow">
                {{ chapter.chapter === 0 ? 'Prologue' : `Chapter ${chapter.chapter}` }}
                <span v-if="chapter.year">· {{ chapter.year }}</span>
              </p>
              <h2 class="book__scroll-title">
                {{ chapter.title }}
              </h2>
              <ContentRenderer :value="chapter" />
            </article>
          </div>

          <!-- ── The index, bound into the book ───────────────────── -->
          <Transition name="index">
            <nav
              v-if="indexOpen"
              class="book__index"
            >
              <div class="book__index-head">
                <p>Contents</p>
                <UButton
                  icon="i-lucide-x"
                  color="neutral"
                  variant="ghost"
                  size="xs"
                  aria-label="Close the contents"
                  @click="indexOpen = false"
                />
              </div>

              <ol class="book__index-list">
                <li
                  v-for="(chapter, index) in chapters"
                  :key="chapter.path"
                >
                  <button
                    type="button"
                    class="book__index-item"
                    :data-state="index === readingChapterIndex
                      ? 'current'
                      : index < readingChapterIndex ? 'read' : undefined"
                    @click="goTo(index)"
                  >
                    <span class="book__index-n">{{ chapter.chapter === 0 ? '—' : chapter.chapter }}</span>
                    <span class="min-w-0">
                      <span class="book__index-title">{{ chapter.title }}</span>
                      <span
                        v-if="chapter.year"
                        class="book__index-year"
                      >{{ chapter.year }}</span>
                    </span>
                  </button>
                </li>
              </ol>
            </nav>
          </Transition>

          <!-- ── Covers ──────────────────────────────────────────── -->
          <BookCover
            v-if="mode === 'cover'"
            :chapters="chapters.length"
            :minutes="minutes"
            :opening="opening"
            :resume-label="resumeChapter ? `Back to ${resumeChapter.title}` : undefined"
            @open="openBook(0)"
            @resume="resume"
          />

          <BookBackCover
            v-else-if="mode === 'back'"
            :chapters="chapters.length"
            :minutes="minutes"
            :exit-to="exitTo || '/my-story'"
            @reopen="mode = 'reading'"
            @restart="openBook(0)"
          />
        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer
          v-if="!scrollMode"
          class="book__nav"
          :data-hidden="mode === 'reading' ? undefined : ''"
        >
          <UButton
            icon="i-lucide-arrow-left"
            :label="canBack ? 'Back' : 'Cover'"
            color="neutral"
            variant="ghost"
            size="sm"
            @click="canBack ? turn('back') : closeBook()"
          />

          <p class="book__folio">
            <span v-if="chapterOf">
              {{ chapterOf.chapter === 0 ? 'Prologue' : `Chapter ${chapterOf.chapter}` }}
            </span>
            <span class="text-dimmed">
              · page {{ Math.min(cursor + step, total) }} of {{ total }}
            </span>
          </p>

          <UButton
            trailing-icon="i-lucide-arrow-right"
            :label="canForward ? 'Turn the page' : 'The end'"
            size="sm"
            @click="turn('forward')"
          />
        </footer>

        <!-- The measuring rig. Same component, same padding, same type — laid
             out but never painted, and unmounted the moment it has answered. -->
        <div
          v-if="probing"
          class="book__probe"
          aria-hidden="true"
        >
          <BookPage
            v-for="(chapter, index) in chapters"
            :ref="el => setProbe(el, index)"
            :key="chapter.path"
            :chapter="chapter"
            :column="0"
          />
        </div>
      </div>

      <!-- Server-rendered, and what a crawler reads: the whole story, in order,
           as plain prose.

           The titles are real links, and that is load-bearing rather than
           decorative. Every chapter has its own permalink, but the only
           navigation to those permalinks lives inside the reader — which is
           client-only — so the prerender crawler had nothing to follow and
           shipped a build where every chapter URL 404'd. These links are the
           link graph. -->
      <template #fallback>
        <div class="book__ssr guide-prose">
          <article
            v-for="chapter in chapters"
            :key="chapter.path"
            class="book__scroll-chapter"
          >
            <p class="book__scroll-eyebrow">
              {{ chapter.chapter === 0 ? 'Prologue' : `Chapter ${chapter.chapter}` }}
            </p>
            <h2 class="book__scroll-title">
              <NuxtLink :to="chapter.path">
                {{ chapter.title }}
              </NuxtLink>
            </h2>
            <ContentRenderer :value="chapter" />
          </article>
        </div>
      </template>
    </ClientOnly>
  </div>
</template>

<style scoped>
.book {
  height: 100%;
  --page-pad-x: 1.35rem;
  --page-pad-y: 1rem;
  --book-type-size: 1rem;
  --book-leading: 1.62;
  /* A book is not set in a UI sans. This is the single biggest reason the page
     read as a web page with a page-turn on it — and a system stack costs
     nothing to download: Iowan and Palatino cover Apple, Georgia covers
     everything else, and all three were drawn for reading at length. */
  --book-serif: 'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, 'Times New Roman', serif;
  /* Warm white, not screen white — paper is never #fff. */
  --book-paper: #fdfcf8;
  --book-gutter: rgb(30 27 75 / 0.08);
  /* The thin grey rule down the middle of the spread. */
  --book-rule: rgb(30 27 75 / 0.16);
}

@media (min-width: 640px) {
  .book {
    --page-pad-x: 2.4rem;
    --page-pad-y: 1.5rem;
    --book-type-size: 1.0625rem;
  }
}

/* `.dark` rather than `:global(.dark)`. Scoped CSS already puts the scope
   attribute on the last compound only, so `.dark .x` compiles to
   `.dark .x[data-v-…]` and matches the class on `<html>`. Wrapping the ancestor
   in `:global()` here does not survive the build — the descendant is dropped
   and the declarations land on `<html>` itself. */
.dark .book {
  --book-paper: #16142f;
  --book-gutter: rgb(0 0 0 / 0.4);
  --book-rule: rgb(255 255 255 / 0.14);
}

/* A column that exactly fills the viewport: bar, stage, footer. The stage is
   the only flexible row, which is what makes "nothing scrolls" true rather than
   aspirational. */
.book__client {
  display: flex;
  flex-direction: column;
  height: 100dvh;
  overflow: hidden;
}

.book__bar {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.6rem;
  border-bottom: 1px solid var(--ui-border);
  background: var(--ui-bg);
  flex-shrink: 0;
}

.book__title {
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 16rem;
}

@media (max-width: 767px) {
  .book__title {
    display: none;
  }
}

.book__progress {
  flex: 1;
  min-width: 2rem;
  height: 0.2rem;
  border-radius: 999px;
  background: var(--ui-bg-accented);
  overflow: hidden;
}

.book__progress-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(to right, var(--ui-primary), var(--ui-secondary));
  transition: width var(--dgm-t-base) var(--dgm-ease);
}

/* Perspective on the stage, rotation on the leaf — the standard pairing, and
   the reason a turn reads as paper rather than as a slide. */
.book__stage {
  position: relative;
  flex: 1;
  min-height: 0;
  overflow: hidden;
  perspective: 2600px;
  perspective-origin: 50% 50%;
  touch-action: pan-y;
  user-select: none;
  -webkit-user-select: none;
  /* The desk. Visible only either side of the sheet once the page width caps
     out, which is what stops a wide monitor from stretching the book. */
  background:
    radial-gradient(ellipse 60% 50% at 50% 0%, color-mix(in oklab, var(--ui-primary) 7%, transparent), transparent),
    var(--ui-bg-muted, var(--ui-bg));
}

.book__sheet {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: center;
  height: 100%;
  overflow: hidden;
}

/* The divider: one hairline where the two pages meet, with the paper shading
   either side of it drawn by BookPage. That pairing is the whole difference
   between a spread and two cards sitting next to each other. */
.book__binding {
  position: absolute;
  top: 0;
  bottom: 0;
  left: calc(50% - 0.5px);
  width: 1px;
  transform: translateX(-0.5px);
  background: var(--book-rule);
  pointer-events: none;
  z-index: 3;
}

/* The ribbon. Every hardback has one and nobody notices until it is missing.
   It hangs at the outer edge, where the only thing it can cover is the running
   head — which is given room for it rather than being overlapped. */
.book__ribbon {
  position: absolute;
  top: 0;
  right: calc(50% - var(--sheet-w) / 2 + 1.1rem);
  width: 0.5rem;
  height: 4.5rem;
  background: linear-gradient(to bottom, var(--color-spark-500), var(--color-spark-600));
  clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 82%, 0 100%);
  opacity: 0.85;
  pointer-events: none;
  z-index: 3;
}

.book__edge {
  position: absolute;
  top: 0.5rem;
  bottom: 0.5rem;
  width: 0.7rem;
  pointer-events: none;
  z-index: 2;
  background: repeating-linear-gradient(
    to right,
    color-mix(in oklab, var(--book-rule) 55%, transparent) 0 1px,
    transparent 1px 3px
  );
}

.book__edge--left {
  left: calc(50% - var(--sheet-w) / 2);
  border-radius: 2px 0 0 2px;
  mask-image: linear-gradient(to right, #000, transparent);
}

.book__edge--right {
  right: calc(50% - var(--sheet-w) / 2);
  border-radius: 0 2px 2px 0;
  mask-image: linear-gradient(to left, #000, transparent);
}

.book__leaf {
  position: absolute;
  top: 0;
  width: var(--page-w);
  height: var(--page-h);
  transform-style: preserve-3d;
  transition-property: transform, box-shadow;
  transition-timing-function: cubic-bezier(0.33, 0.02, 0.2, 1);
  will-change: transform;
  z-index: 5;
}

.book__face {
  position: absolute;
  inset: 0;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  overflow: hidden;
  background: var(--book-paper);
}

.book__face--back {
  transform: rotateY(180deg);
}

/* Paper darkens as it comes off the flat — the shading is what stops the turn
   reading as a rotating image. */
.book__shade {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.book__shade--front {
  background: linear-gradient(to left, rgb(16 19 64 / 0.6), transparent 62%);
}

.book__shade--back {
  background: linear-gradient(to right, rgb(16 19 64 / 0.55), transparent 62%);
}

.book__corner {
  position: absolute;
  bottom: 0;
  width: 3rem;
  height: 3rem;
  pointer-events: none;
  background: linear-gradient(
    315deg,
    color-mix(in oklab, var(--ui-bg-elevated) 92%, transparent) 45%,
    var(--book-rule) 46%,
    transparent 62%
  );
  transition: width var(--dgm-t-base) var(--dgm-ease), height var(--dgm-t-base) var(--dgm-ease);
  z-index: 4;
}

.book__stage:hover .book__corner {
  width: 4.25rem;
  height: 4.25rem;
}

.book__nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.4rem 0.6rem;
  border-top: 1px solid var(--ui-border);
  background: var(--ui-bg);
  flex-shrink: 0;
  transition: opacity var(--dgm-t-base) var(--dgm-ease);
}

.book__nav[data-hidden] {
  opacity: 0;
  pointer-events: none;
}

.book__folio {
  font-size: 0.75rem;
  color: var(--ui-text-muted);
  text-align: center;
  font-variant-numeric: tabular-nums;
}

/* ── The index ────────────────────────────────────────────────────────── */

.book__index {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  width: min(21rem, 88%);
  z-index: 25;
  display: flex;
  flex-direction: column;
  background: var(--book-paper);
  border-right: 1px solid var(--book-rule);
  box-shadow: 14px 0 40px rgb(16 19 64 / 0.18);
}

.book__index-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.7rem 0.7rem 0.7rem 1.25rem;
  border-bottom: 1px solid var(--book-rule);
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
  flex-shrink: 0;
}

.book__index-list {
  list-style: none;
  margin: 0;
  padding: 0.5rem;
  overflow-y: auto;
  flex: 1;
}

.book__index-item {
  display: flex;
  align-items: baseline;
  gap: 0.875rem;
  width: 100%;
  text-align: left;
  padding: 0.5rem 0.6rem;
  border-radius: var(--radius-sm);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.book__index-item:hover {
  background: color-mix(in oklab, var(--ui-primary) 8%, transparent);
}

.book__index-n {
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-dimmed);
  min-width: 1.25rem;
  text-align: right;
  flex-shrink: 0;
}

.book__index-title {
  display: block;
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  line-height: 1.35;
}

.book__index-year {
  display: block;
  font-size: 0.6875rem;
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
}

.book__index-item[data-state='read'] .book__index-title {
  color: var(--ui-text-dimmed);
}

.book__index-item[data-state='current'] .book__index-title {
  color: var(--ui-text-highlighted);
  font-weight: 600;
}

.book__index-item[data-state='current'] .book__index-n {
  color: var(--ui-primary);
}

.index-enter-active,
.index-leave-active {
  transition: transform var(--dgm-t-base) var(--dgm-ease), opacity var(--dgm-t-base) var(--dgm-ease);
}

.index-enter-from,
.index-leave-to {
  transform: translateX(-101%);
  opacity: 0.4;
}

/* ── One column, for readers who would rather scroll ──────────────────── */

.book__scroll {
  height: 100%;
  overflow-y: auto;
  padding: 2rem 1.5rem 4rem;
  margin: 0 auto;
  max-width: 44rem;
}

.book__ssr {
  padding: 2rem 1.5rem;
  margin: 0 auto;
  max-width: 44rem;
}

.book__scroll-chapter + .book__scroll-chapter {
  margin-top: 3rem;
  padding-top: 2.5rem;
  border-top: 1px solid var(--book-rule);
}

.book__scroll-eyebrow {
  font-size: 0.6875rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.book__scroll-title {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 3.5vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  margin: 0.5rem 0 1.25rem;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

/* Laid out — it has to be, or there is nothing to measure — but never painted
   and never in the accessibility tree. */
.book__probe {
  position: absolute;
  top: 0;
  left: -200vw;
  visibility: hidden;
  pointer-events: none;
  width: var(--page-w);
  height: var(--page-h);
}

@media (prefers-reduced-motion: reduce) {
  .book__leaf,
  .book__corner,
  .book__progress-fill,
  .index-enter-active,
  .index-leave-active {
    transition: none;
  }
}
</style>
