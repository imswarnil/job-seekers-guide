<script setup lang="ts">
import { logoCard, logoColors, logoLens, logoLineHeight, logoLines } from '~/utils/logo'

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
          <rect
            width="32"
            height="32"
            rx="9"
            :fill="logoColors.plate"
          />
          <rect
            :x="logoCard.x"
            :y="logoCard.y"
            :width="logoCard.width"
            :height="logoCard.height"
            :rx="logoCard.radius"
            :fill="logoColors.card"
            fill-opacity="0.16"
          />
          <rect
            v-for="(line, index) in logoLines"
            :key="index"
            :x="line.x"
            :y="line.y"
            :width="line.width"
            :height="logoLineHeight"
            :rx="logoLineHeight / 2"
            :fill="line.accent ? logoColors.accent : logoColors.line"
            :fill-opacity="line.accent ? 1 : 0.62"
          />
          <circle
            :cx="logoLens.cx"
            :cy="logoLens.cy"
            :r="logoLens.r"
            :fill="logoColors.plate"
            fill-opacity="0.55"
          />
          <circle
            :cx="logoLens.cx"
            :cy="logoLens.cy"
            :r="logoLens.r"
            fill="none"
            stroke="#ffffff"
            stroke-width="2"
          />
          <line
            :x1="logoLens.handle.x1"
            :y1="logoLens.handle.y1"
            :x2="logoLens.handle.x2"
            :y2="logoLens.handle.y2"
            stroke="#ffffff"
            stroke-width="2.4"
            stroke-linecap="round"
          />
        </svg>
        <div class="h-px flex-1 bg-border" />
        <span class="text-xl text-dimmed">Job Seekers Guide</span>
      </div>
    </div>
  </div>
</template>
