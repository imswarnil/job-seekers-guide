<script setup lang="ts">
const { data: page } = await useAsyncData('page:/my-story', () => queryCollection('pages').path('/my-story').first())

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || page.value.title,
  description: page.value.seo?.description || page.value.description,
  headline: 'My story'
})

const chapters = computed(() => page.value?.chapters || [])
const stats = computed(() => page.value?.stats || [])

const open = ref(false)
</script>

<template>
  <UContainer v-if="page">
    <div class="py-10 lg:py-14">
      <UBadge
        label="Swarnil · Mahroni → Bangalore → Budapest"
        color="secondary"
        variant="subtle"
      />

      <h1 class="font-display text-4xl sm:text-5xl font-bold text-highlighted tracking-tight text-balance mt-4">
        {{ page.title }}
      </h1>

      <p class="mt-4 text-lg text-muted max-w-2xl text-balance">
        {{ page.description }}
      </p>

      <!-- Four numbers, up front. The story is long and these are the reason to
           start reading it. -->
      <dl
        v-if="stats.length"
        class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4"
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
      <!-- The spine. Sticky on a wide screen, a slideover below it: the story is
           long enough that knowing where you are in it is the difference between
           finishing and abandoning. -->
      <aside class="hidden lg:block">
        <div class="sticky top-[calc(var(--ui-header-height)+2rem)]">
          <StoryTimeline :chapters="chapters" />
        </div>
      </aside>

      <div class="min-w-0">
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
