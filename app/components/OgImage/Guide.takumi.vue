<script setup lang="ts">
import { logoColors, logoTrail } from '~/utils/logo'

defineOptions({
  inheritAttrs: false
})

defineProps<{
  headline?: string
  title?: string
  description?: string
}>()
</script>

<template>
  <div
    class="w-full h-full flex bg-neutral-950 relative overflow-hidden"
    data-theme="dark"
  >
    <div class="absolute top-0 left-0 w-1.5 h-full bg-secondary-400" />

    <div class="flex flex-col justify-between flex-1 px-20 py-16">
      <div />

      <div class="flex flex-col gap-5">
        <span
          v-if="headline"
          class="text-2xl font-medium text-primary-400"
        >
          {{ headline }}
        </span>

        <h1
          v-if="title"
          class="text-6xl font-bold text-highlighted"
        >
          {{ title }}
        </h1>

        <p
          v-if="description"
          class="text-3xl/11 text-muted"
          :style="{ lineClamp: 2, textOverflow: 'ellipsis' }"
        >
          {{ description }}
        </p>
      </div>

      <div class="flex items-center gap-4">
        <!-- The mark, at rest. Same geometry as AppLogo.vue, from the same
             source — this renderer has no stylesheet and no animation, so it
             draws the final frame with literal colours. -->
        <svg
          class="size-10"
          viewBox="0 0 32 32"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            :d="logoTrail.path"
            :stroke="logoColors.trail"
            stroke-width="2.75"
            stroke-linecap="round"
            fill="none"
          />
          <circle
            v-for="(stop, index) in logoTrail.stops"
            :key="index"
            :cx="stop.cx"
            :cy="stop.cy"
            :r="stop.r"
            :fill="logoColors.stop"
          />
          <circle
            :cx="logoTrail.start.cx"
            :cy="logoTrail.start.cy"
            :r="logoTrail.start.r"
            :fill="logoColors.trail"
          />
          <circle
            :cx="logoTrail.end.cx"
            :cy="logoTrail.end.cy"
            :r="logoTrail.end.r"
            :fill="logoColors.end"
          />
        </svg>
        <div class="h-px flex-1 bg-border" />
        <span class="text-xl text-dimmed">Job Seekers Guide</span>
      </div>
    </div>
  </div>
</template>
