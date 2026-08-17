<script setup lang="ts">
const route = useRoute()

const { data: episode } = await useAsyncData(`series:${route.path}`, () =>
  queryCollection('series').path(route.path).first()
)

if (!episode.value) {
  throw createError({ statusCode: 404, statusMessage: 'Episode not found', fatal: true })
}

// Prev and next across the whole series, so an episode is never a dead end —
// the same rule the lesson player follows.
const { data: siblings } = await useAsyncData(`series:around:${route.path}`, () =>
  queryCollection('series').order('episode', 'ASC').select('path', 'title', 'episode', 'description').all()
)

const index = computed(() => siblings.value?.findIndex(item => item.path === route.path) ?? -1)
const previous = computed(() => index.value > 0 ? siblings.value?.[index.value - 1] : undefined)
const next = computed(() => index.value >= 0 ? siblings.value?.[index.value + 1] : undefined)

usePageSeo({
  title: episode.value.seo?.title || episode.value.title,
  description: episode.value.seo?.description || episode.value.description,
  headline: `Episode ${episode.value.episode}`
})
</script>

<template>
  <UContainer
    v-if="episode"
    class="py-8 lg:py-12"
  >
    <NuxtLink
      to="/series"
      class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors mb-5"
    >
      <UIcon
        name="i-lucide-arrow-left"
        class="size-3.5"
      />
      All episodes
    </NuxtLink>

    <EpisodePlayer
      :playback-id="episode.muxPlaybackId"
      :title="episode.title"
      :episode="episode.episode"
      :poster="episode.poster"
      :runtime="episode.runtime"
    />

    <div class="lg:grid lg:grid-cols-[1fr_16rem] lg:gap-12 mt-8">
      <div class="min-w-0">
        <p class="flex items-center gap-2 text-sm text-dimmed flex-wrap">
          <span class="tabular-nums">Episode {{ String(episode.episode).padStart(2, '0') }}</span>
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

        <USeparator class="my-8" />

        <div class="guide-prose">
          <ContentRenderer :value="episode" />
        </div>

        <!-- The hook for the next one, given the weight it deserves. -->
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
              The written version
            </p>
            <p class="text-sm text-muted mt-1">
              The whole story on one page, with every number.
            </p>
            <UButton
              to="/my-story"
              label="My story"
              trailing-icon="i-lucide-arrow-right"
              size="sm"
              variant="subtle"
              class="mt-3"
            />
          </UPageCard>
        </div>
      </aside>
    </div>
  </UContainer>
</template>
