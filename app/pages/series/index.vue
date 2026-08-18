<script setup lang="ts">
const { data: episodes } = await useAsyncData('series', () =>
  queryCollection('series').where('episode', '>', 0).order('episode', 'ASC').all()
)

const title = 'My story'
const description = 'Ten episodes. Kota to Mahroni to Bangalore to Budapest — the whole route, with every rejection and every number left in.'

usePageSeo({ title, description, headline: 'My story' })

const first = computed(() => episodes.value?.[0])
const rest = computed(() => episodes.value?.slice(1) || [])
const filmed = computed(() => episodes.value?.filter(e => e.muxPlaybackId).length || 0)

/** The numbers, up front. They are the reason to start reading or watching. */
const stats = [
  { value: '0', label: 'Written rounds cleared in college' },
  { value: '₹13,000', label: 'First salary, per month' },
  { value: '1 of ~1000', label: 'Selected in that first interview' },
  { value: '₹32 LPA', label: 'Six years later' }
]
</script>

<template>
  <div>
    <StoryHero
      :episodes="episodes?.length || 0"
      :filmed="filmed"
    />

    <UContainer class="py-12">
      <!-- Facts before narrative. Somebody who scrolls past everything else has
           still been given the argument. -->
      <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-lg border border-default bg-elevated/40 px-4 py-3"
        >
          <dt class="text-2xl font-display font-bold text-highlighted tabular-nums">
            {{ stat.value }}
          </dt>
          <dd class="text-xs text-muted mt-0.5 text-balance">
            {{ stat.label }}
          </dd>
        </div>
      </dl>

      <StorySalaryChart class="mt-12" />

      <USeparator class="my-14" />

      <div
        v-if="first"
        class="mb-14"
      >
        <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-4">
          Start here
        </p>
        <div class="lg:max-w-2xl">
          <EpisodeCard
            :episode="first"
            featured
          />
        </div>
      </div>

      <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-4">
        All episodes
      </p>

      <UPageGrid class="lg:grid-cols-3">
        <EpisodeCard
          v-for="episode in rest"
          :key="episode.path"
          :episode="episode"
        />
      </UPageGrid>

      <AdSlot
        placement="in-article"
        class="mx-auto"
      />

      <UPageCard
        variant="naked"
        class="mt-8"
        title="The story is the why. The path is the how."
        description="Everything I learned the slow way, in the order it should have been given to me."
      >
        <template #footer>
          <UButton
            to="/start"
            label="Start here"
            trailing-icon="i-lucide-arrow-right"
            variant="subtle"
          />
        </template>
      </UPageCard>
    </UContainer>
  </div>
</template>
