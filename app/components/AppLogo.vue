<script setup lang="ts">
defineProps<{
  /** Mark only, no wordmark — for tight spaces and the mobile header. */
  markOnly?: boolean
}>()
</script>

<template>
  <span class="inline-flex items-center gap-2.5">
    <!-- The mark is the path itself: three steps rising, with the last one
         picked out in the accent. It is the product in one glyph — a sequence,
         going somewhere, with the next step marked. -->
    <svg
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="size-7 shrink-0"
      aria-hidden="true"
    >
      <rect
        width="32"
        height="32"
        rx="9"
        class="fill-primary-600"
      />
      <path
        d="M8 22.5H12.5V17.5H8V22.5Z"
        fill="white"
        fill-opacity="0.55"
      />
      <path
        d="M13.75 22.5H18.25V13H13.75V22.5Z"
        fill="white"
        fill-opacity="0.8"
      />
      <path
        d="M19.5 22.5H24V8.5H19.5V22.5Z"
        class="fill-secondary-400"
      />
    </svg>

    <span
      v-if="!markOnly"
      class="font-display font-semibold text-lg tracking-tight text-highlighted whitespace-nowrap"
    >
      Job Seekers <span class="text-primary">Guide</span>
    </span>
  </span>
</template>
