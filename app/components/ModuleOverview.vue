<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'

/**
 * A module page. `index.md` inside a module folder is optional — most modules
 * are just a container for their lessons — so everything here has to be able to
 * come from the navigation tree alone, with the markdown body as a bonus.
 *
 * The header lives in `ModuleHeader.vue`, because the shell puts it in a
 * full-width band above this one.
 */
defineProps<{
  page?: PathCollectionItem
}>()

const route = useRoute()

const { module } = usePathPlayer(() => route.path)
</script>

<template>
  <div>
    <div
      v-if="page?.body"
      class="guide-prose mb-10"
    >
      <ContentRenderer :value="page" />
    </div>

    <ModuleContents
      v-if="module?.lessons.length"
      :lessons="module.lessons"
      numbered
    />

    <AdSlot placement="in-article" />
  </div>
</template>
