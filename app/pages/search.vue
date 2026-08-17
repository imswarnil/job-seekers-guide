<script setup lang="ts">
/**
 * A shareable URL for the search that otherwise only exists as a modal, and —
 * just as usefully — the thing that reserves `/search` from ever being a subject
 * slug. It opens the palette and stays out of the way.
 */
const { open } = useContentSearch()

usePageSeo({
  title: 'Search',
  description: 'Search every lesson on the path.'
})

onMounted(() => {
  open.value = true
})
</script>

<template>
  <UContainer>
    <UPage>
      <UPageHeader
        title="Search"
        description="Every lesson on the path, and the pages around it."
      />

      <UPageBody>
        <UButton
          label="Open search"
          icon="i-lucide-search"
          size="lg"
          @click="open = true"
        />
      </UPageBody>
    </UPage>
  </UContainer>
</template>
