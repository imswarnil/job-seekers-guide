<script setup lang="ts">
/**
 * Why this exists, and the two ways in.
 *
 * The band used to be a solid dark slab. It is the page's own background now,
 * with the trailer bled into it: invisible at the top edge, present through the
 * middle, gone again by the bottom, and desaturated into the page rather than
 * sitting on top of it. Nothing has a hard edge, so the section has no seam
 * above or below it.
 *
 * The television plays no video — a picture inside a picture, both of the same
 * film, was one too many. It runs the title-card sequence, and the trailer is
 * the thing behind everything.
 *
 * Each door is the object itself: a set that switches on, a book that opens.
 * The whole card is the link. All CSS on a fixed timeline started by a class on
 * mount; the finished frame is what the server renders and what a reader who
 * has asked for less motion sees.
 */
const root = useTemplateRef<HTMLElement>('root')
const visible = useElementVisibility(root)

/** `true` once the browser has told us it is allowed to move things. */
const armed = ref(false)
const playing = ref(false)

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return
  }

  armed.value = true

  const stop = watch(visible, (isVisible) => {
    if (isVisible) {
      playing.value = true
      stop()
    }
  }, { immediate: true })
})

/* ── The picture behind the band ───────────────────────────────────────
   Muted, looping, controlless, from `youtube-nocookie`, and not requested
   at all until the band is on screen. Muted and `playsinline` are what
   make autoplay legal on a phone; without both, mobile Safari and Chrome
   simply refuse. */
const app = useAppConfig()
const brand = computed(() => (app as {
  brand?: { storyVideo?: string, storyVideoStart?: number }
}).brand)

const videoSrc = computed(() => {
  const id = brand.value?.storyVideo || ''
  if (!id) {
    return ''
  }

  const start = Math.max(0, Math.floor(brand.value?.storyVideoStart ?? 0))

  const params = new URLSearchParams({
    autoplay: '1',
    mute: '1',
    controls: '0',
    loop: '1',
    // `loop` is ignored on a single video unless the playlist is that video.
    playlist: id,
    playsinline: '1',
    modestbranding: '1',
    rel: '0',
    iv_load_policy: '3',
    disablekb: '1'
  })

  if (start) {
    params.set('start', String(start))
  }

  return `https://www.youtube-nocookie.com/embed/${id}?${params}`
})

const showVideo = ref(false)
watch(visible, (isVisible) => {
  if (isVisible && videoSrc.value) {
    showVideo.value = true
  }
}, { immediate: true })

/* An iframe cannot do `object-fit: cover`, and the band's height is
   content-driven, so there is no pure-CSS way to size a 16:9 embed to cover it
   — every viewport-unit trick is right at one window size and wrong at the
   next, which is exactly how you end up with bare strips above and below.
   Measuring is exact and costs one ResizeObserver. */
const { width: bandWidth, height: bandHeight } = useElementSize(root)

const coverStyle = computed(() => {
  const w = bandWidth.value
  const h = bandHeight.value

  if (!w || !h) {
    return undefined
  }

  // Whichever dimension runs out first decides the scale.
  const width = Math.max(w, (h * 16) / 9)

  return {
    width: `${Math.ceil(width)}px`,
    height: `${Math.ceil((width * 9) / 16)}px`
  }
})

/* ── Leaving through the door you chose ────────────────────────────────
   Clicking the set fills the screen with static before the episode page
   arrives; clicking the book turns a page over the top of the site. It is
   a plain overlay and a delayed `navigateTo`, not a router transition,
   because only these two links should do this — every other way to reach
   the story pages stays instant. */
type Exit = 'tv' | 'book'

const leaving = ref<Exit | null>(null)

const EXIT_MS = 620

function open(event: MouseEvent, to: string, mode: Exit) {
  // Anything that means "open this somewhere else" is not ours to intercept.
  if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
    return
  }

  event.preventDefault()

  if (leaving.value) {
    return
  }

  if (import.meta.client && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    navigateTo(to)
    return
  }

  leaving.value = mode
  setTimeout(() => navigateTo(to), EXIT_MS)
}
</script>

<template>
  <section
    ref="root"
    class="story"
    :class="[armed && 'story--armed', playing && 'story--playing']"
  >
    <!-- ── The picture, bled into the page ───────────────────────────── -->
    <div
      class="story__bg"
      aria-hidden="true"
    >
      <iframe
        v-if="showVideo"
        class="story__video"
        :style="coverStyle"
        :src="videoSrc"
        title=""
        loading="lazy"
        tabindex="-1"
        allow="autoplay; encrypted-media"
        referrerpolicy="strict-origin-when-cross-origin"
        frameborder="0"
      />
      <span class="story__wash" />
      <span class="story__grid" />
    </div>

    <UContainer class="relative py-12 lg:py-16">
      <!-- ── The claim ─────────────────────────────────────────────── -->
      <div class="story__claim">
        <p class="story__kicker">
          Why this exists
        </p>

        <!-- The two clauses, with the six years between them drawn. A single
             run-on sentence hides the only thing that matters about it: how far
             apart the two halves are. -->
        <blockquote class="story__quote">
          <p class="story__before">
            I could not clear a single written round.
          </p>

          <span
            class="story__gap"
            aria-hidden="true"
          >
            <span class="story__gap-line" />
            <span class="story__gap-label">six years</span>
            <span class="story__gap-line" />
          </span>

          <p class="story__after">
            I am now a <span class="story__role">Salesforce engineer in Europe</span>.
          </p>
        </blockquote>

        <p class="story__lede">
          Average student, no plan, no guidance, and a family that could not fund
          one. This is the whole route — the rejections, the ₹13,000 first
          salary, and every offer after it — written down exactly as it happened.
        </p>

        <UButton
          to="/my-story"
          label="The whole story"
          icon="i-lucide-clapperboard"
          trailing-icon="i-lucide-arrow-right"
          size="lg"
          class="mt-6"
        />
      </div>

      <!-- ── The two doors ─────────────────────────────────────────── -->
      <div class="doors">
        <NuxtLink
          to="/my-story/watch"
          class="door"
          @click="open($event, '/my-story/watch', 'tv')"
        >
          <div
            class="crt"
            aria-hidden="true"
          >
            <div class="crt__body">
              <div class="crt__screen">
                <span class="crt__flash" />
                <span class="crt__scan" />
                <span class="crt__flicker" />
                <span class="crt__title">My Story</span>
                <span class="crt__sub">Ten episodes</span>
              </div>

              <div class="crt__panel">
                <span class="crt__brand">JSG—TV</span>
                <span class="crt__dial" />
                <span class="crt__dial crt__dial--small" />
                <span class="crt__led" />
              </div>
            </div>
            <span class="crt__neck" />
            <span class="crt__base" />
          </div>

          <div class="door__text">
            <p class="door__label">
              <UIcon
                name="i-lucide-play"
                class="size-4"
              />
              Watch my story
            </p>
            <p class="door__preview">
              Ten episodes, about eight minutes each. The call from London, the
              ₹13,000 offer, and the evening my sister said no — told out loud,
              in order.
            </p>
            <span class="door__cta">
              Start at episode one
              <UIcon
                name="i-lucide-arrow-right"
                class="size-3.5 door__arrow"
              />
            </span>
          </div>
        </NuxtLink>

        <NuxtLink
          to="/my-story/book"
          class="door"
          @click="open($event, '/my-story/book', 'book')"
        >
          <div
            class="book"
            aria-hidden="true"
          >
            <div class="book__block">
              <span class="book__leaf book__leaf--left">
                <span class="book__rule" />
                <span class="book__rule" />
                <span class="book__rule" />
                <span class="book__rule book__rule--short" />
              </span>
              <span class="book__leaf book__leaf--right">
                <span class="book__rule" />
                <span class="book__rule" />
                <span class="book__rule book__rule--short" />
              </span>
              <span class="book__gutter" />

              <!-- Hinged on the spine, swinging left, on a loop. -->
              <span class="book__cover">
                <span class="book__cover-title">My Story</span>
                <span class="book__cover-rule" />
                <span class="book__cover-by">Swarnil Singhai</span>
              </span>
            </div>
            <span class="book__shadow" />
          </div>

          <div class="door__text">
            <p class="door__label">
              <UIcon
                name="i-lucide-book-open"
                class="size-4"
              />
              Read my story
            </p>
            <p class="door__preview">
              The same six years as something you can sit with. Every rejection,
              every number, and the parts that are harder to say out loud than
              they are to write down.
            </p>
            <span class="door__cta">
              Open the book
              <UIcon
                name="i-lucide-arrow-right"
                class="size-3.5 door__arrow"
              />
            </span>
          </div>
        </NuxtLink>
      </div>
    </UContainer>

    <!-- The way out. Mounted only for the moment it plays. -->
    <Teleport to="body">
      <div
        v-if="leaving"
        class="exit"
        :data-mode="leaving"
        aria-hidden="true"
      >
        <span class="exit__static" />
        <span class="exit__page" />
      </div>
    </Teleport>
  </section>
</template>

<style scoped>
/* The band is the page, not a slab on it. `isolation` gives the blended video
   its own context so it mixes with this background and nothing above it. */
.story {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background: var(--ui-bg);
}

.story__bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  overflow: hidden;
  pointer-events: none;
}

/* Centred on the band and sized in script to cover it, so there is never a bare
   strip above or below.

   `luminosity` throws the video's colour away and keeps only its light, which
   is what makes it read as texture in the page rather than as a video playing
   behind the text. The mask is the rest of it: nothing at the very top, present
   through the middle, gone again before the bottom edge — so the section has no
   seam at either end. */
.story__video {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border: 0;
  pointer-events: none;
  opacity: 0.5;
  mix-blend-mode: luminosity;
  mask-image: linear-gradient(
    to bottom,
    transparent 0%,
    rgb(0 0 0 / 0.45) 14%,
    #000 38%,
    #000 58%,
    rgb(0 0 0 / 0.35) 80%,
    transparent 96%
  );
}

.dark .story__video {
  opacity: 0.32;
  mix-blend-mode: screen;
}

/* A second pass of the page colour over the top and bottom thirds. The mask
   alone leaves the middle band strong enough to fight the paragraph sitting on
   it; this puts the page back over both ends and keeps the text first. */
.story__wash {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      180deg,
      var(--ui-bg) 0%,
      color-mix(in oklab, var(--ui-bg) 55%, transparent) 26%,
      color-mix(in oklab, var(--ui-bg) 45%, transparent) 62%,
      var(--ui-bg) 98%
    ),
    radial-gradient(ellipse 55% 45% at 18% 24%, color-mix(in oklab, var(--ui-bg) 78%, transparent), transparent);
}

/* Graph paper through the middle, fading out at both ends with the video. */
.story__grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(to right, var(--ui-border) 1px, transparent 1px),
    linear-gradient(to bottom, var(--ui-border) 1px, transparent 1px);
  background-size: 4rem 4rem;
  opacity: 0.7;
  mask-image: linear-gradient(
    to bottom,
    transparent 0%,
    #000 20%,
    #000 68%,
    transparent 94%
  );
}

.story__kicker {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--ui-primary);
}

.story__quote {
  margin: 1.25rem 0 0;
  padding: 0;
  border: 0;
  max-width: 42rem;
}

.story__before,
.story__after {
  font-family: var(--font-display);
  font-size: clamp(1.375rem, 3.2vw, 2.125rem);
  font-weight: 700;
  line-height: 1.15;
  letter-spacing: -0.025em;
  text-wrap: balance;
}

/* The first clause is where he was — present, but not the point. */
.story__before {
  color: var(--ui-text-dimmed);
}

.story__after {
  color: var(--ui-text-highlighted);
}

.story__role {
  color: var(--ui-secondary);
}

/* The distance, drawn. */
.story__gap {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin: 0.9rem 0;
}

.story__gap-line {
  height: 1px;
  flex: 1;
  max-width: 5rem;
  background: linear-gradient(to right, transparent, var(--ui-secondary));
}

.story__gap-line:last-child {
  background: linear-gradient(to left, transparent, var(--ui-secondary));
  max-width: none;
}

.story__gap-label {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ui-secondary);
  flex-shrink: 0;
}

.story__lede {
  margin-top: 1.4rem;
  font-size: 1.0625rem;
  line-height: 1.65;
  color: var(--ui-text-muted);
  max-width: 38rem;
  text-wrap: pretty;
}

/* ── The doors ──────────────────────────────────────────────────────── */
.doors {
  display: grid;
  gap: 1.25rem;
  margin-top: 3rem;
}

@media (min-width: 768px) {
  .doors {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.75rem;
  }
}

/* Translucent, so the picture behind the band carries on through the cards
   instead of stopping dead at their edges. */
.door {
  display: flex;
  flex-direction: column;
  padding: 1.75rem 1.5rem 1.5rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--ui-border);
  background: color-mix(in oklab, var(--ui-bg) 76%, transparent);
  backdrop-filter: blur(6px);
  transition:
    border-color var(--dgm-t-base) var(--dgm-ease),
    background-color var(--dgm-t-base) var(--dgm-ease),
    box-shadow var(--dgm-t-base) var(--dgm-ease);
}

.door:hover {
  border-color: color-mix(in oklab, var(--ui-primary) 45%, var(--ui-border));
  background: color-mix(in oklab, var(--ui-bg) 92%, transparent);
  box-shadow: var(--shadow-lg);
}

/* One stage height for both, so the set and the book sit on the same line
   however differently they are built. */
.door > [aria-hidden] {
  position: relative;
  height: 13rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
}

@media (min-width: 1024px) {
  .door > [aria-hidden] {
    height: 15rem;
  }
}

.door__text {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.door__label {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-family: var(--font-display);
  font-size: 1.1875rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  transition: color var(--dgm-t-fast) var(--dgm-ease);
}

.door:hover .door__label {
  color: var(--ui-primary);
}

.door__preview {
  margin-top: 0.55rem;
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}

.door__cta {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: auto;
  padding-top: 1.1rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--ui-primary);
}

.door__arrow {
  transition: transform var(--dgm-t-fast) var(--dgm-ease);
}

.door:hover .door__arrow {
  transform: translateX(3px);
}

/* ── The set ────────────────────────────────────────────────────────── */
.crt__body {
  position: relative;
  width: 15.5rem;
  max-width: 100%;
  padding: 0.9rem 0.9rem 0.45rem;
  border-radius: 1.3rem 1.3rem 0.55rem 0.55rem;
  /* Near-black, tinted with the site's own indigo rather than being a neutral
     grey — an old set, but this site's old set. */
  background:
    linear-gradient(
      165deg,
      color-mix(in oklab, var(--ui-primary) 26%, #06070f),
      color-mix(in oklab, var(--ui-primary) 12%, #04050b) 62%,
      #03040a
    );
  box-shadow:
    inset 0 2px 0 rgb(255 255 255 / 0.14),
    inset 0 -2px 0 rgb(0 0 0 / 0.5),
    0 18px 34px -18px rgb(0 0 0 / 0.6);
  transition: transform var(--dgm-t-base) var(--dgm-ease);
}

.door:hover .crt__body {
  transform: translateY(-3px);
}

.crt__screen {
  position: relative;
  aspect-ratio: 4 / 3;
  /* Fat, uneven rounding: a tube is not a rectangle. */
  border-radius: 22% / 16%;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.15rem;
  background: radial-gradient(ellipse 85% 75% at 50% 42%, #1d2450, #05060f 78%);
  box-shadow:
    inset 0 0 26px 8px rgb(0 0 0 / 0.8),
    0 0 0 3px #02030a;
}

/* The tube, dark. Its own layer rather than a background swap on the screen,
   because a solid colour and a radial gradient do not interpolate — the
   transition would snap halfway instead of warming up. */
.crt__screen::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  background: #05060f;
  opacity: 0;
  pointer-events: none;
}

.crt__title,
.crt__sub {
  position: relative;
  z-index: 1;
}

.crt__scan,
.crt__flicker {
  z-index: 2;
}

.crt__title {
  font-family: var(--font-display);
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #fff;
  text-shadow: 0 0 22px rgb(150 165 255 / 0.75);
}

.crt__sub {
  font-size: 0.6rem;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--color-spark-300);
}

.crt__scan {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    to bottom,
    rgb(255 255 255 / 0.06) 0 1px,
    transparent 1px 3px
  );
  pointer-events: none;
}

/* The one thing every real tube did: never sit perfectly still. */
.crt__flicker {
  position: absolute;
  inset: 0;
  background: rgb(180 200 255 / 0.05);
  opacity: 0;
  pointer-events: none;
}

.crt__flash {
  position: absolute;
  left: 8%;
  right: 8%;
  top: 50%;
  height: 2px;
  border-radius: 999px;
  background: #fff;
  box-shadow: 0 0 26px 5px rgb(255 255 255 / 0.7);
  opacity: 0;
  z-index: 3;
}

.crt__panel {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.15rem 0.2rem;
}

.crt__brand {
  font-family: var(--font-mono, monospace);
  font-size: 0.5rem;
  letter-spacing: 0.18em;
  color: rgb(255 255 255 / 0.4);
  margin-right: auto;
}

.crt__dial {
  width: 0.8rem;
  height: 0.8rem;
  border-radius: 999px;
  background: radial-gradient(circle at 35% 30%, #4a4f6d, #14161f);
  box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.5);
}

.crt__dial--small {
  width: 0.55rem;
  height: 0.55rem;
}

.crt__led {
  width: 0.375rem;
  height: 0.375rem;
  border-radius: 999px;
  background: var(--color-spark-400);
  box-shadow: 0 0 8px 1px color-mix(in oklab, var(--color-spark-400) 70%, transparent);
}

.crt__neck {
  width: 3.25rem;
  height: 1.05rem;
  background: linear-gradient(180deg, color-mix(in oklab, var(--ui-primary) 14%, #06070f), #03040a);
}

.crt__base {
  width: 7.5rem;
  height: 0.65rem;
  border-radius: 0.35rem;
  background: linear-gradient(180deg, color-mix(in oklab, var(--ui-primary) 22%, #080913), #03040a);
  box-shadow: 0 12px 20px -14px rgb(0 0 0 / 0.75);
}

/* ── The book ───────────────────────────────────────────────────────── */
.book__block {
  position: relative;
  width: 15.5rem;
  max-width: 100%;
  aspect-ratio: 8 / 5.6;
  display: flex;
  perspective: 1200px;
  transition: transform var(--dgm-t-base) var(--dgm-ease);
}

.door:hover .book__block {
  transform: translateY(-3px);
}

.book__leaf {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.5rem;
  padding: 9% 10%;
  background: linear-gradient(180deg, #faf9f5, #e8e6dd);
  border: 1px solid rgb(0 0 0 / 0.14);
}

.book__leaf--left {
  border-radius: 0.35rem 0 0 0.35rem;
  box-shadow: inset -16px 0 24px -20px rgb(0 0 0 / 0.8);
}

.book__leaf--right {
  border-radius: 0 0.35rem 0.35rem 0;
  box-shadow: inset 16px 0 24px -20px rgb(0 0 0 / 0.8);
}

.book__gutter {
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 3px;
  transform: translateX(-50%);
  background: linear-gradient(to bottom, transparent, rgb(30 32 50 / 0.35), transparent);
}

.book__rule {
  height: 2px;
  border-radius: 999px;
  background: rgb(20 22 40 / 0.2);
}

.book__rule--short {
  width: 55%;
}

.book__shadow {
  width: 11rem;
  max-width: 80%;
  height: 0.8rem;
  margin-top: 0.4rem;
  border-radius: 999px;
  background: radial-gradient(ellipse at center, rgb(0 0 0 / 0.22), transparent 70%);
}

/* Hinged on the spine and resting fully open against the left page, so the
   still frame is a book somebody is reading. Past ninety degrees the cover is
   showing its back, which `backface-visibility` hides for free. */
.book__cover {
  position: absolute;
  left: 0;
  top: 0;
  width: 50%;
  height: 100%;
  transform-origin: right center;
  transform: rotateY(-178deg);
  backface-visibility: hidden;
  border-radius: 0.35rem 0 0 0.35rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.4rem;
  padding: 11%;
  background: linear-gradient(150deg, var(--color-guide-600), var(--color-guide-900));
  border: 1px solid rgb(255 255 255 / 0.14);
  box-shadow: 6px 0 18px -10px rgb(0 0 0 / 0.6);
}

.book__cover-title {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: #f4f5ff;
}

.book__cover-rule {
  width: 2.25rem;
  height: 2px;
  background: var(--color-spark-400);
}

.book__cover-by {
  font-size: 0.65rem;
  color: rgb(244 245 255 / 0.72);
}

/* ── Wound back to the first frame ──────────────────────────────────── */
.story--armed .crt__screen::after {
  opacity: 1;
}

.story--armed .crt__title,
.story--armed .crt__sub,
.story--armed .crt__scan {
  opacity: 0;
}

.story--armed .crt__led {
  background: rgb(255 255 255 / 0.18);
  box-shadow: none;
}

.story--armed .book__cover {
  transform: rotateY(0deg);
}

.story--armed .book__leaf,
.story--armed .book__gutter {
  opacity: 0;
}

/* ── The sequence ───────────────────────────────────────────────────── */
.story--playing .crt__flash {
  animation: crt-power 0.85s var(--dgm-ease) forwards;
}

.story--playing .crt__screen::after {
  animation: crt-warm 1.1s var(--dgm-ease) 0.15s forwards;
}

.story--playing .crt__scan {
  animation: crt-fade 0.6s linear 0.75s forwards;
}

.story--playing .crt__led {
  animation: crt-led 0.4s var(--dgm-ease) 0.5s forwards;
}

.story--playing .crt__title {
  animation: crt-title 0.7s var(--dgm-ease) 0.95s forwards;
}

.story--playing .crt__sub {
  animation: crt-title 0.7s var(--dgm-ease) 1.15s forwards;
}

/* Once it has warmed up the tube keeps breathing, on a long enough cycle that
   it registers as a set being on rather than as something blinking at you. */
.story--playing .crt__flicker {
  animation: crt-breathe 7s ease-in-out 1.8s infinite;
}

.story--playing .book__leaf,
.story--playing .book__gutter {
  animation: crt-fade 0.5s linear 1.5s forwards;
}

/* Open, hold, close, hold — and again. The book is the one object here that
   has a thing it *does*, and doing it once and stopping wastes it. */
.story--playing .book__cover {
  animation: book-loop 9s cubic-bezier(0.4, 0.08, 0.2, 1) 1.35s infinite;
}

@keyframes crt-power {
  0% {
    opacity: 0;
    transform: scaleX(0.05);
  }

  35% {
    opacity: 1;
    transform: scaleX(1);
  }

  100% {
    opacity: 0;
    transform: scaleX(1);
  }
}

@keyframes crt-warm {
  to {
    opacity: 0;
  }
}

@keyframes crt-fade {
  to {
    opacity: 1;
  }
}

@keyframes crt-led {
  to {
    background: var(--color-spark-400);
    box-shadow: 0 0 8px 1px color-mix(in oklab, var(--color-spark-400) 70%, transparent);
  }
}

@keyframes crt-title {
  from {
    opacity: 0;
    transform: scale(1.12);
    filter: blur(5px);
  }

  to {
    opacity: 1;
    transform: none;
    filter: blur(0);
  }
}

@keyframes crt-breathe {
  0%,
  92%,
  100% {
    opacity: 0;
  }

  94% {
    opacity: 1;
  }

  96% {
    opacity: 0;
  }

  98% {
    opacity: 0.7;
  }
}

@keyframes book-loop {
  0%,
  8% {
    transform: rotateY(0deg);
  }

  32%,
  62% {
    transform: rotateY(-178deg);
  }

  86%,
  100% {
    transform: rotateY(0deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .story--armed .crt__title,
  .story--armed .crt__sub,
  .story--armed .crt__scan,
  .story--armed .book__leaf,
  .story--armed .book__gutter {
    opacity: 1;
  }

  .story--armed .crt__screen::after {
    opacity: 0;
  }

  .story--armed .book__cover {
    transform: rotateY(-178deg);
  }

  .story--playing .crt__flash,
  .story--playing .crt__screen::after,
  .story--playing .crt__scan,
  .story--playing .crt__led,
  .story--playing .crt__title,
  .story--playing .crt__sub,
  .story--playing .crt__flicker,
  .story--playing .book__leaf,
  .story--playing .book__gutter,
  .story--playing .book__cover {
    animation: none;
  }

  .door:hover .crt__body,
  .door:hover .book__block,
  .door:hover .door__arrow {
    transform: none;
  }
}
</style>

<style>
/* ── The way out ───────────────────────────────────────────────────────
   Unscoped because it is teleported to `<body>` and would otherwise carry
   a scope attribute nothing in `<body>` matches. `pointer-events: none`
   throughout: it is a picture of leaving, never something to click. */
.exit {
  position: fixed;
  inset: 0;
  z-index: 100;
  pointer-events: none;
  overflow: hidden;
}

.exit__static,
.exit__page {
  position: absolute;
  inset: 0;
  display: none;
}

/* Static, out of two moving stripe layers rather than an image — nothing to
   download for something on screen for half a second. */
.exit[data-mode='tv'] {
  background: #05060f;
  animation: exit-in 120ms linear forwards;
}

.exit[data-mode='tv'] .exit__static {
  display: block;
  background-image:
    repeating-linear-gradient(0deg, rgb(255 255 255 / 0.3) 0 1px, transparent 1px 2px),
    repeating-linear-gradient(90deg, rgb(255 255 255 / 0.16) 0 1px, transparent 1px 3px);
  background-size: 100% 3px, 4px 100%;
  animation: exit-noise 90ms steps(4) infinite;
}

/* A page turning over the top of the whole site. */
.exit[data-mode='book'] .exit__page {
  display: block;
  transform-origin: left center;
  background: linear-gradient(115deg, #faf9f5, #e6e3d9);
  box-shadow: -18px 0 40px -10px rgb(0 0 0 / 0.45);
  animation: exit-flip 620ms cubic-bezier(0.4, 0.08, 0.2, 1) forwards;
}

@keyframes exit-in {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes exit-noise {
  0% {
    background-position: 0 0, 0 0;
  }

  33% {
    background-position: 3px -2px, -2px 1px;
  }

  66% {
    background-position: -2px 2px, 1px -3px;
  }

  100% {
    background-position: 1px -1px, -3px 2px;
  }
}

@keyframes exit-flip {
  from {
    transform: perspective(1600px) rotateY(105deg);
  }

  to {
    transform: perspective(1600px) rotateY(0deg);
  }
}
</style>
