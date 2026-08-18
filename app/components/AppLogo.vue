<script setup lang="ts">
import { logoMark } from '~/utils/logo'

defineProps<{
  /** Mark only, no wordmark — for tight spaces and the mobile header. */
  markOnly?: boolean
}>()
</script>

<template>
  <span class="app-logo inline-flex items-center gap-2.5">
    <!-- A magnifier over a shortlist, with the match in the accent. Static on
         purpose: this sits in the header of every page on a site people read
         for an hour at a time, and a logo that redraws itself on every
         navigation is a tic rather than a brand. -->
    <svg
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="size-7 shrink-0"
      aria-hidden="true"
    >
      <rect
        v-for="(row, index) in logoMark.rows"
        :key="index"
        :x="row.x"
        :y="row.y"
        :width="row.w"
        :height="row.h"
        :rx="row.h / 2"
        :class="row.match ? 'fill-secondary-400' : 'fill-primary-300'"
      />

      <circle
        :cx="logoMark.lens.cx"
        :cy="logoMark.lens.cy"
        :r="logoMark.lens.r"
        class="stroke-primary"
        :stroke-width="logoMark.lensWidth"
        fill="none"
      />

      <path
        :d="logoMark.handle"
        class="stroke-primary"
        :stroke-width="logoMark.handleWidth"
        stroke-linecap="round"
        fill="none"
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
