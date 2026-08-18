<script setup lang="ts">
const { data: page } = await useAsyncData('page:/faq', () => queryCollection('pages').path('/faq').first())

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || page.value.title,
  description: page.value.seo?.description || page.value.description,
  headline: 'Questions'
})

const { brand } = useAppConfig()
</script>

<template>
  <UContainer v-if="page">
    <UPage>
      <UPageHeader
        :title="page.title"
        :description="page.description"
      />

      <UPageBody>
        <!-- Asking comes before the answers on purpose. Somebody whose question
             is not on this page should not have to read to the bottom to find
             out they can ask it. -->
        <div class="ask">
          <div class="ask__row">
            <div class="min-w-0">
              <p class="font-display font-semibold text-highlighted">
                Your question is not here?
              </p>
              <p class="text-sm text-muted mt-1 text-balance">
                Ask it. I answer these myself, and the good ones end up on this
                page for the next person.
              </p>
            </div>

            <div class="flex flex-wrap gap-2 shrink-0">
              <UButton
                :to="`${repoUrl}/discussions/new?category=q-a`"
                target="_blank"
                rel="noopener"
                label="Ask a question"
                icon="i-lucide-message-circle-question"
              />
            </div>
          </div>

          <USeparator class="my-5" />

          <div class="ask__row">
            <div class="min-w-0">
              <p class="font-display font-semibold text-highlighted">
                Want to talk it through properly?
              </p>
              <p class="text-sm text-muted mt-1 text-balance">
                Book a call. Bring your resume, your situation, or the decision
                you are stuck on — this is the part an institute charges ₹40,000
                for and gives you once.
              </p>
            </div>

            <UButton
              v-if="brand.topmate"
              :to="brand.topmate"
              target="_blank"
              rel="noopener"
              label="Book a call"
              icon="i-lucide-calendar-clock"
              color="secondary"
              class="shrink-0"
            />
          </div>
        </div>

        <div class="guide-prose">
          <ContentRenderer :value="page" />
        </div>

        <AdSlot placement="in-article" />

        <USeparator class="my-10" />

        <UPageCard
          variant="naked"
          title="Still stuck?"
          description="The path itself answers most of this in more detail than a one-paragraph answer can."
        >
          <template #footer>
            <UButton
              to="/start"
              label="Start here"
              variant="subtle"
              trailing-icon="i-lucide-arrow-right"
            />
          </template>
        </UPageCard>
      </UPageBody>
    </UPage>
  </UContainer>
</template>

<style scoped>
.ask {
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg-elevated);
  padding: 1.25rem;
  margin-bottom: 2.5rem;
}

.ask__row {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 640px) {
  .ask__row {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
  }
}
</style>
