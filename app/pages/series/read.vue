<script setup lang="ts">
const { data: page } = await useAsyncData('series:read', () =>
  queryCollection('series').path('/series/read').first()
)

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || 'My story, in full',
  description: page.value.seo?.description || page.value.description,
  headline: 'Read the story'
})

const chapters = computed(() => page.value?.storyChapters || [])
const stats = computed(() => page.value?.stats || [])

const open = ref(false)
</script>

<template>
  <UContainer v-if="page">
    <div class="py-10 lg:py-14 max-w-3xl">
      <NuxtLink
        to="/series"
        class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors mb-6"
      >
        <UIcon
          name="i-lucide-arrow-left"
          class="size-3.5"
        />
        My story
      </NuxtLink>

      <UBadge
        label="Mahroni → Bangalore → Budapest"
        color="secondary"
        variant="subtle"
      />

      <h1 class="font-display text-4xl sm:text-5xl font-bold text-highlighted tracking-tight text-balance mt-4">
        {{ page.title }}
      </h1>

      <p class="mt-4 text-lg text-muted text-balance">
        {{ page.description }}
      </p>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <UButton
          to="/series/always-seventy-percent"
          label="Watch it instead"
          icon="i-lucide-play"
          color="neutral"
          variant="subtle"
        />
        <span class="text-sm text-dimmed">{{ page.runtime }}</span>
      </div>

      <dl
        v-if="stats.length"
        class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
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
    </div>

    <div class="lg:grid lg:grid-cols-[15rem_1fr] lg:gap-12 pb-16">
      <!-- The spine. The story gets worse before it gets better, and knowing how
           much of the climb is behind you is the difference between finishing
           and abandoning. -->
      <aside class="hidden lg:block">
        <div class="sticky top-[calc(var(--ui-header-height)+2rem)]">
          <StoryTimeline :chapters="chapters" />
        </div>
      </aside>

      <div class="min-w-0 max-w-3xl">
        <UButton
          icon="i-lucide-list"
          label="Chapters"
          color="neutral"
          variant="subtle"
          size="sm"
          class="lg:hidden mb-6"
          @click="open = true"
        />

        <div class="guide-prose story-body">
          <ContentRenderer :value="page" />
        </div>

        <AdSlot placement="in-article" />

        <USeparator class="my-12" />

        <UPageCard
          variant="subtle"
          title="This is the sequence I paid for"
          description="Everything I learned in that institute, in the order it should have been given to me, free."
        >
          <template #footer>
            <UButton
              to="/start"
              label="Start here"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
            />
          </template>
        </UPageCard>
      </div>
    </div>

    <ClientOnly>
      <USlideover
        v-model:open="open"
        side="left"
        title="Chapters"
      >
        <template #body>
          <StoryTimeline
            :chapters="chapters"
            @navigate="open = false"
          />
        </template>
      </USlideover>
    </ClientOnly>
  </UContainer>
</template>

<style scoped>
/* Chapter headings are anchor targets for the timeline, so they need clearance
   under the sticky header or the observer fires on a heading nobody can see. */
.story-body :deep(h2) {
  scroll-margin-top: calc(var(--ui-header-height) + 2rem);
  margin-top: 3.5rem;
}

.story-body :deep(h2:first-child) {
  margin-top: 0;
}
</style>
