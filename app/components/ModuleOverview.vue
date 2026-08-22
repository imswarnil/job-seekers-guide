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
const props = defineProps<{
  page?: PathCollectionItem
}>()

/**
 * The body, cut into the pieces the repeating ads go between. Short overviews
 * fall under the paragraph floor in `autoAds.ts` and come back in one piece,
 * which is most of them.
 */
const chunks = useAutoAds(() => props.page?.body as never)

const route = useRoute()

const { module } = usePathPlayer(() => route.path)
</script>

<template>
  <div>
    <div
      v-if="page?.body"
      class="guide-prose mb-10"
    >
      <template
        v-for="(chunk, index) in chunks"
        :key="index"
      >
        <ContentRenderer :value="{ ...page!, body: chunk }" />
        <AdSlot
          v-if="index < chunks.length - 1"
          placement="in-feed"
          class="ad-auto"
        />
      </template>
    </div>

    <ModuleContents
      v-if="module?.lessons.length"
      :lessons="module.lessons"
      numbered
    />

    <AdSlot placement="in-article" />
  </div>
</template>
