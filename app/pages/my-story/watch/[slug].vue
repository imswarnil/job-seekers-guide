<script setup lang="ts">
import { beatOf, episodeDescription, episodeTitle } from '~/utils/story'

const route = useRoute()

const { data: episode } = await useAsyncData(`watch:${route.path}`, () =>
  queryCollection('series').path(route.path).first()
)

if (!episode.value) {
  throw createError({ statusCode: 404, statusMessage: 'Episode not found', fatal: true })
}

// Prev and next across the whole series, so an episode is never a dead end —
// the same rule the lesson player follows.
const { data: siblings } = await useAsyncData(`watch:around:${route.path}`, () =>
  queryCollection('series').order('episode', 'ASC').select('path', 'title', 'episode', 'description').all()
)

const total = computed(() => siblings.value?.length ?? 10)
const index = computed(() => siblings.value?.findIndex(item => item.path === route.path) ?? -1)
const previous = computed(() => index.value > 0 ? siblings.value?.[index.value - 1] : undefined)
const next = computed(() => index.value >= 0 ? siblings.value?.[index.value + 1] : undefined)

/**
 * Titled like television, not like a blog post.
 *
 * `Episode 04: The Mask | My Story` rather than the bare chapter name, because
 * the number and the series are the two things a stranger needs before they can
 * decide whether this result is for them — and because the book publishes the
 * same events and must not compete with itself in the index.
 */
const seoTitle = computed(() => episode.value
  ? episodeTitle(episode.value.episode, episode.value.title)
  : 'Episode')

const seoDescription = computed(() => episode.value
  ? episodeDescription({
      episode: episode.value.episode,
      total: total.value,
      description: episode.value.description,
      year: episode.value.year,
      place: episode.value.place,
      cliffhanger: episode.value.cliffhanger
    })
  : '')

const beat = computed(() => beatOf((episode.value?.episode ?? 1) - 1, total.value))

const beatLabel = computed(() => ({
  opening: 'Where it starts',
  rising: 'It gets worse',
  turn: 'Where it turns',
  finale: 'How it ends'
}[beat.value]))

usePageSeo({
  title: episode.value.seo?.title || seoTitle.value,
  description: episode.value.seo?.description || seoDescription.value,
  headline: `Episode ${episode.value.episode} of ${total.value}`
})

const site = useSiteConfig()

useSchemaOrg([
  defineBreadcrumb({
    itemListElement: [
      { name: 'My story', item: '/my-story' },
      { name: 'The series', item: '/my-story/watch' },
      { name: episode.value.title, item: route.path }
    ]
  }),
  {
    '@type': 'TVEpisode',
    'name': episode.value.title,
    'episodeNumber': episode.value.episode,
    'description': seoDescription.value,
    'url': `${site.url}${route.path}`,
    'partOfSeries': {
      '@type': 'TVSeries',
      'name': 'My Story',
      'url': `${site.url}/my-story/watch`
    }
  }
])

const on = ref(false)
const playing = ref(true)
const muted = ref(false)

const screen = useTemplateRef<{
  toggleplay: () => void
  togglemute: () => void
  skip: (seconds: number) => void
}>('screen')

function toggleplay() {
  playing.value = !playing.value
  screen.value?.toggleplay()
}

function togglemute() {
  muted.value = !muted.value
  screen.value?.togglemute()
}
</script>

<template>
  <UContainer
    v-if="episode"
    class="py-6 lg:py-10"
  >
    <NuxtLink
      to="/my-story/watch"
      class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors mb-5"
    >
      <UIcon
        name="i-lucide-arrow-left"
        class="size-3.5"
      />
      All episodes
    </NuxtLink>

    <div class="room">
      <div class="room__wash" />

      <div class="room__grid">
        <WatchCrtScreen
          ref="screen"
          v-model:on="on"
          :youtube-id="episode.youtubeId"
          :title="episode.title"
          :episode="episode.episode"
          :poster="episode.poster"
          :runtime="episode.runtime"
          :placeholder="episode.placeholder"
        />

        <div class="room__controls">
          <WatchCrtRemote
            :episode="episode.episode"
            :total="total"
            :on="on"
            :playing="playing"
            :muted="muted"
            :can-previous="Boolean(previous)"
            :can-next="Boolean(next)"
            @power="on = !on"
            @previous="previous && navigateTo(previous.path)"
            @next="next && navigateTo(next.path)"
            @toggleplay="toggleplay"
            @togglemute="togglemute"
            @skip="screen?.skip($event)"
          />
        </div>
      </div>
    </div>

    <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_16rem] lg:gap-12 mt-8">
      <div class="min-w-0">
        <p class="flex items-center gap-2 text-sm text-dimmed flex-wrap">
          <span class="tabular-nums">Episode {{ String(episode.episode).padStart(2, '0') }} of {{ total }}</span>
          <span v-if="episode.runtime">· {{ episode.runtime }}</span>
          <span v-if="episode.year">· {{ episode.year }}</span>
          <span v-if="episode.place">· {{ episode.place }}</span>
        </p>

        <h1 class="font-display text-3xl sm:text-4xl font-bold text-highlighted tracking-tight text-balance mt-2">
          {{ episode.title }}
        </h1>

        <p class="mt-3 text-lg text-muted text-balance">
          {{ episode.description }}
        </p>

        <!-- Where this sits in the arc. A ten-part series read out of order is
             ten anecdotes; this is the line that says which part you are on. -->
        <p class="mt-4 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
          <span class="inline-block w-6 h-px bg-primary" />
          {{ beatLabel }}
        </p>

        <USeparator class="my-8" />

        <div class="guide-prose">
          <ContentRenderer :value="episode" />
        </div>

        <div
          v-if="episode.cliffhanger"
          class="mt-10 rounded-lg border-l-2 border-primary bg-elevated/50 px-5 py-4"
        >
          <p class="text-xs uppercase tracking-wider text-dimmed mb-1.5">
            {{ next ? 'And then' : 'Where it ends' }}
          </p>
          <p class="text-lg text-highlighted text-balance italic">
            {{ episode.cliffhanger }}
          </p>
        </div>

        <AdSlot placement="lesson-footer" />

        <div class="grid sm:grid-cols-2 gap-4 mt-10">
          <UPageCard
            v-if="previous"
            :to="previous.path"
            :title="previous.title"
            :description="`Episode ${previous.episode}`"
            icon="i-lucide-arrow-left"
            variant="subtle"
          />
          <span v-else />

          <UPageCard
            v-if="next"
            :to="next.path"
            :title="next.title"
            :description="`Episode ${next.episode}`"
            icon="i-lucide-arrow-right"
            variant="subtle"
          />
          <UPageCard
            v-else
            to="/start"
            title="Start the path"
            description="Everything in the series, in the order it should have been taught."
            icon="i-lucide-compass"
            variant="subtle"
          />
        </div>
      </div>

      <aside class="hidden lg:block">
        <div class="sticky top-[calc(var(--ui-header-height)+2rem)] space-y-6">
          <div v-if="episode.chapters?.length">
            <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-3">
              In this episode
            </p>
            <ol class="space-y-1.5">
              <li
                v-for="chapter in episode.chapters"
                :key="chapter.at"
                class="flex gap-2.5 text-sm"
              >
                <span class="text-primary tabular-nums shrink-0 font-mono text-xs mt-0.5">{{ chapter.at }}</span>
                <span class="text-muted">{{ chapter.label }}</span>
              </li>
            </ol>
          </div>

          <UPageCard
            variant="subtle"
            :ui="{ body: 'p-4' }"
          >
            <p class="text-sm text-highlighted font-medium">
              Prefer to read it?
            </p>
            <p class="text-sm text-muted mt-1">
              The same story as a book, with every number and every date.
            </p>
            <UButton
              to="/my-story/book"
              label="Open the book"
              trailing-icon="i-lucide-arrow-right"
              size="sm"
              variant="subtle"
              class="mt-3"
            />
          </UPageCard>

          <AdSlot placement="sidebar" />
        </div>
      </aside>
    </div>
  </UContainer>
</template>

<style scoped>
.room {
  position: relative;
  border-radius: var(--radius-xl);
  overflow: hidden;
  padding: 1.25rem;
  background: linear-gradient(165deg, #12142c, #08091a 60%, #05060f);
  isolation: isolate;
}

@media (min-width: 1024px) {
  .room {
    padding: 2rem;
  }
}

.room__wash {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background: radial-gradient(ellipse 65% 55% at 45% 40%, rgb(91 80 220 / 0.2), transparent 70%);
}

.room__grid {
  position: relative;
  z-index: 2;
}

@media (min-width: 1024px) {
  .room__grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 1.75rem;
    align-items: center;
  }
}

.room__controls {
  display: flex;
  justify-content: center;
  margin-top: 1.25rem;
}

@media (min-width: 1024px) {
  .room__controls {
    margin-top: 0;
  }
}
</style>
