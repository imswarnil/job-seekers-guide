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
</script>

<template>
  <UContainer v-if="page">
    <UPage>
      <UPageHeader
        :title="page.title"
        :description="page.description"
      />

      <UPageBody>
        <div class="guide-prose">
          <ContentRenderer :value="page" />
        </div>

        <USeparator class="my-10" />

        <UPageCard
          variant="naked"
          title="Still stuck?"
          description="The path itself answers most of this in more detail than a one-paragraph answer can."
        >
          <template #footer>
            <UButton
              to="/start"
              label="See the whole path"
              variant="subtle"
              trailing-icon="i-lucide-arrow-right"
            />
          </template>
        </UPageCard>
      </UPageBody>
    </UPage>
  </UContainer>
</template>
