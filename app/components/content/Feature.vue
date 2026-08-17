<script setup lang="ts">
/** One item in a `::feature-list`. See `FeatureList.vue` for the example. */
defineProps<{
  title?: string
  icon?: string
}>()
</script>

<template>
  <div class="dgm-box feature not-prose">
    <UIcon
      v-if="icon"
      :name="icon"
      class="size-5 text-primary mb-2.5"
    />

    <p
      v-if="title"
      class="font-medium text-sm text-highlighted mb-1"
    >
      {{ title }}
    </p>

    <div class="text-sm text-muted [&>*:last-child]:mb-0 [&>p]:mb-2">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.feature {
  padding: 1rem;
}
</style>
