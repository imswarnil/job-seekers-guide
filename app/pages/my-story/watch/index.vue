<script setup lang="ts">
/**
 * The series, on a television.
 *
 * The set gets most of the page and the running order sits beside it, because
 * ten episodes is a list rather than a catalogue and nobody browses ten things.
 * The page itself stays on the site's own surfaces — this used to be a
 * permanently dark band that ignored the theme, which meant the one section of
 * the site that could not be read in daylight was the one people were sent to.
 */
const { data: episodes } = await useAsyncData('watch:index', () =>
  queryCollection('series').order('episode', 'ASC').all()
)

const all = computed(() => episodes.value || [])
/* Placeholder footage is not filmed footage. Counting it would have the page
   claim ten episodes exist on the strength of ten copies of the same stand-in
   clip, which is the kind of small lie that costs a reader's trust in all the
   bigger numbers on the site. */
const isFilmed = (e: { youtubeId?: string, muxPlaybackId?: string, placeholder?: boolean }) =>
  Boolean((e.youtubeId || e.muxPlaybackId) && !e.placeholder)

const filmed = computed(() => all.value.filter(isFilmed).length)

const title = 'Watch my story — a ten-part series about getting hired with no plan and no guidance'
const description = 'Mahroni to Kota to Bangalore to Budapest, in ten episodes. Every rejection, the ₹13,000 first salary and every offer after it. Free, no sign-up.'

usePageSeo({ title: 'Watch my story', description, headline: 'The series' })
useSeoMeta({ ogTitle: title })

const selected = ref(0)
const current = computed(() => all.value[selected.value])
const next = computed(() => all.value[selected.value + 1])
const previous = computed(() => selected.value > 0 ? all.value[selected.value - 1] : undefined)

/**
 * The set owns its own transport now.
 *
 * Play, pause, mute and power used to live on a separate remote beside the
 * television, which is a thing no television has ever needed — and it meant two
 * copies of "is it playing" that had to be kept in step by hand. The buttons are
 * on the cabinet, where the set already knows the answer. The page keeps only
 * what is genuinely the page's: which episode is on.
 */
const on = ref(false)

function pick(index: number) {
  selected.value = index
}
</script>

<template>
  <div>
    <!-- ── The room ───────────────────────────────────────────────────
         One viewport, flush under the header, with the video behind the
         whole of it. There is no page heading above this on purpose: the
         set *is* the heading, and a title bar pushing it down the screen
         meant the thing you came for was never fully in view. -->
    <div class="room">
      <WatchAmbientVideo intensity="high" />

      <div class="room__inner">
        <div class="room__grid">
          <div class="room__tv">
            <WatchCrtScreen
              v-if="current"
              :key="current.path"
              v-model:on="on"
              :youtube-id="current.youtubeId"
              :title="current.title"
              :episode="current.episode"
              :poster="current.poster"
              :runtime="current.runtime"
              :placeholder="current.placeholder"
              :total="all.length"
              :can-previous="selected > 0"
              :can-next="Boolean(next)"
              @previous="pick(selected - 1)"
              @next="pick(selected + 1)"
            />
          </div>

          <!-- ── The running order ────────────────────────────────── -->
          <aside class="room__list">
            <div class="room__list-head">
              <p>Episodes</p>
              <p class="room__list-count">
                {{ filmed }} / {{ all.length }} filmed
              </p>
            </div>

            <ol class="room__items">
              <li
                v-for="(episode, index) in all"
                :key="episode.path"
              >
                <button
                  type="button"
                  class="room__item"
                  :data-state="index === selected ? 'current' : undefined"
                  @click="pick(index)"
                >
                  <WatchEpisodeThumb
                    :episode="episode.episode"
                    :title="episode.title"
                    :poster="episode.poster"
                    :filmed="isFilmed(episode)"
                  />

                  <span class="min-w-0 flex-1">
                    <span class="room__item-title">{{ episode.title }}</span>
                    <span class="room__item-meta">
                      {{ [episode.runtime, episode.year].filter(Boolean).join(' · ') }}
                    </span>
                  </span>
                </button>
              </li>
            </ol>
          </aside>
        </div>
      </div>
    </div>

    <UContainer class="py-8 lg:py-12">
      <!-- ── What you are watching ───────────────────────────────────── -->
      <div
        v-if="current"
        class="mt-8 lg:grid lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-10"
      >
        <div class="min-w-0">
          <p class="flex items-center gap-2 text-sm text-dimmed flex-wrap">
            <span class="tabular-nums">Episode {{ String(current.episode).padStart(2, '0') }}</span>
            <span v-if="current.runtime">· {{ current.runtime }}</span>
            <span v-if="current.year">· {{ current.year }}</span>
            <span v-if="current.place">· {{ current.place }}</span>
          </p>

          <h2 class="font-display text-2xl sm:text-3xl font-bold text-highlighted tracking-tight text-balance mt-1.5">
            {{ current.title }}
          </h2>

          <p class="mt-3 text-lg text-muted text-balance">
            {{ current.description }}
          </p>

          <div
            v-if="current.cliffhanger"
            class="mt-6 rounded-lg border-l-2 border-primary bg-elevated/50 px-5 py-4"
          >
            <p class="text-xs uppercase tracking-wider text-dimmed mb-1.5">
              {{ next ? 'And then' : 'Where it ends' }}
            </p>
            <p class="text-lg text-highlighted text-balance italic">
              {{ current.cliffhanger }}
            </p>
          </div>

          <div class="mt-6 flex flex-wrap items-center gap-3">
            <UButton
              :to="current.path"
              label="Read the full script"
              trailing-icon="i-lucide-arrow-right"
              variant="subtle"
            />
            <UButton
              v-if="previous"
              :label="`Episode ${previous.episode}`"
              icon="i-lucide-chevron-left"
              color="neutral"
              variant="ghost"
              @click="pick(selected - 1)"
            />
            <UButton
              v-if="next"
              :label="`Episode ${next.episode}`"
              trailing-icon="i-lucide-chevron-right"
              color="neutral"
              variant="ghost"
              @click="pick(selected + 1)"
            />
          </div>
        </div>

        <aside class="mt-10 lg:mt-0">
          <div
            v-if="current.chapters?.length"
            class="mb-8"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-3">
              In this episode
            </p>
            <ol class="space-y-1.5">
              <li
                v-for="chapter in current.chapters"
                :key="chapter.at"
                class="flex gap-2.5 text-sm"
              >
                <span class="text-primary tabular-nums shrink-0 font-mono text-xs mt-0.5">{{ chapter.at }}</span>
                <span class="text-muted">{{ chapter.label }}</span>
              </li>
            </ol>
          </div>

          <AdSlot placement="sidebar" />
        </aside>
      </div>
    </UContainer>
  </div>
</template>

<style scoped>
/* Exactly one viewport, starting where the header ends. `svh` rather than `vh`
   because on a phone `vh` is the height with the browser chrome *retracted*,
   which puts the bottom of the set behind the address bar until you scroll. */
.room {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  min-height: calc(100svh - var(--ui-header-height, 4rem));
  display: flex;
  border-bottom: 1px solid rgb(255 255 255 / 0.1);
  background: linear-gradient(165deg, #12142c, #08091a 60%, #05060f);
}

.room__inner {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 100rem;
  margin: auto;
  padding: 1.25rem 1rem;
}

@media (min-width: 1024px) {
  .room__inner {
    padding: 1.75rem 2rem;
  }
}

.room__grid {
  display: grid;
  gap: 1.25rem;
  align-items: center;
}

@media (min-width: 1024px) {
  .room__grid {
    grid-template-columns: minmax(0, 1fr) 21rem;
    gap: 2rem;
  }
}

/* The set is sized off the available height rather than the available width, so
   it always finishes inside the viewport instead of running off the bottom. */
.room__tv {
  margin-inline: auto;
  width: 100%;
  max-width: min(100%, calc((100svh - var(--ui-header-height, 4rem) - 6rem) * 1.5));
}

.room__list {
  border: 1px solid rgb(255 255 255 / 0.12);
  border-radius: var(--radius-lg);
  background: rgb(255 255 255 / 0.04);
  backdrop-filter: blur(8px);
  overflow: hidden;
}

.room__list-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.8rem 1rem;
  border-bottom: 1px solid rgb(255 255 255 / 0.12);
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgb(244 245 255 / 0.75);
}

.room__list-count {
  letter-spacing: 0.06em;
  color: rgb(244 245 255 / 0.45);
}

/* Ten items in a panel that has to finish inside the viewport, so this is the
   one thing on the page that scrolls — and only when it has to. */
.room__items {
  list-style: none;
  margin: 0;
  padding: 0.4rem;
  max-height: min(26rem, calc(100svh - var(--ui-header-height, 4rem) - 10rem));
  overflow-y: auto;
  scrollbar-width: thin;
}

.room__item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  width: 100%;
  text-align: left;
  padding: 0.4rem 0.45rem;
  border-radius: var(--radius-sm);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
  /* The numeral is drawn in the border colour, which on this panel has to be
     the light one rather than the theme's. */
  --ui-border-accented: rgb(255 255 255 / 0.34);
}

.room__item:hover {
  background: rgb(255 255 255 / 0.07);
}

.room__item[data-state='current'] {
  background: rgb(255 255 255 / 0.11);
  --ui-border-accented: var(--color-spark-400);
}

.room__item-title {
  display: block;
  font-size: 0.8125rem;
  font-weight: 500;
  line-height: 1.3;
  color: rgb(244 245 255 / 0.78);
}

.room__item[data-state='current'] .room__item-title {
  color: #f4f5ff;
}

.room__item-meta {
  display: block;
  font-size: 0.625rem;
  color: rgb(244 245 255 / 0.45);
  margin-top: 0.15rem;
}
</style>
