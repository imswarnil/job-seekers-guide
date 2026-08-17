<script setup lang="ts">
const { data: episodes } = await useAsyncData('series', () =>
  queryCollection('series').order('episode', 'ASC').all()
)

const title = 'The series'
const description = 'Ten episodes. Kota to Mahroni to Bangalore to Budapest — the whole route, told properly, with every rejection and every number left in.'

usePageSeo({ title, description, headline: 'Web series' })

const first = computed(() => episodes.value?.[0])
const rest = computed(() => episodes.value?.slice(1) || [])

const filmed = computed(() => episodes.value?.filter(e => e.muxPlaybackId).length || 0)
</script>

<template>
  <div>
    <!-- A dark band, because this is the part of the site that is television.
         `guide-inverse` ignores colour mode on purpose. -->
    <section class="guide-inverse relative overflow-hidden">
      <div class="absolute inset-0 guide-contour opacity-30" />

      <UContainer class="relative py-16 lg:py-24">
        <div class="lg:grid lg:grid-cols-[1fr_20rem] lg:gap-14 lg:items-center">
          <div>
            <div class="flex items-center gap-2 text-sm text-[color:var(--guide-inverse-muted)]">
              <UIcon
                name="i-lucide-clapperboard"
                class="size-4"
              />
              A web series by Swarnil
            </div>

            <h1 class="font-display text-4xl sm:text-5xl font-bold mt-4 text-[color:var(--guide-inverse-ink)] tracking-tight text-balance">
              {{ title }}
            </h1>

            <p class="mt-4 text-lg max-w-2xl text-[color:var(--guide-inverse-muted)] text-balance">
              {{ description }}
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
              <UButton
                v-if="first"
                :to="first.path"
                label="Start with episode one"
                icon="i-lucide-play"
                size="lg"
              />
              <UButton
                to="/my-story"
                label="Read it instead"
                icon="i-lucide-book-open"
                color="neutral"
                variant="subtle"
                size="lg"
              />
            </div>

            <p
              v-if="episodes?.length"
              class="mt-6 text-sm text-[color:var(--guide-inverse-muted)]"
            >
              {{ episodes.length }} episodes written · {{ filmed }} filmed. The
              scripts go up before the videos do — they were written to be read.
            </p>
          </div>

          <IllustrationJobSearch class="hidden lg:block w-full opacity-90" />
        </div>
      </UContainer>
    </section>

    <UContainer class="py-14">
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
        title="The series is the why. The path is the how."
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
