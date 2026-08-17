<script setup lang="ts">
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
        <!-- The mark: three steps rising, the last one in the accent. -->
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
            fill="#4338ca"
          />
          <path
            d="M8 22.5H12.5V17.5H8V22.5Z"
            fill="#ffffff"
            fill-opacity="0.55"
          />
          <path
            d="M13.75 22.5H18.25V13H13.75V22.5Z"
            fill="#ffffff"
            fill-opacity="0.8"
          />
          <path
            d="M19.5 22.5H24V8.5H19.5V22.5Z"
            fill="#2dd4bf"
          />
        </svg>
        <div class="h-px flex-1 bg-border" />
        <span class="text-xl text-dimmed">Job Seekers Guide</span>
      </div>
    </div>
  </div>
</template>
