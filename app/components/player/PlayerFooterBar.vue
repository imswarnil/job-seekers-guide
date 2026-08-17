<script setup lang="ts">
import type { Lesson } from '~/utils/path'

defineProps<{
  previous?: Lesson
  next?: Lesson
  position: { n: number, total: number }
  crossesSubject?: boolean
  complete?: boolean
}>()

const emit = defineEmits<{
  toggle: []
  advance: []
  openRail: []
}>()
</script>

<template>
  <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 mt-12 border-t border-default bg-default/85 backdrop-blur">
    <div class="flex items-center gap-2 px-4 sm:px-6 h-14">
      <UButton
        icon="i-lucide-panel-left"
        color="neutral"
        variant="ghost"
        aria-label="Show the path"
        class="lg:hidden"
        @click="emit('openRail')"
      />

      <UButton
        :to="previous?.path"
        :disabled="!previous"
        icon="i-lucide-arrow-left"
        color="neutral"
        variant="ghost"
        :aria-label="previous ? `Previous: ${previous.title}` : 'No previous lesson'"
      >
        <span class="hidden sm:inline">Previous</span>
      </UButton>

      <div class="flex-1 min-w-0 flex items-center justify-center gap-3">
        <div class="hidden sm:block w-32 h-1 rounded-full bg-accented overflow-hidden">
          <div
            class="h-full bg-primary rounded-full"
            :style="{
              width: `${position.total ? (position.n / position.total) * 100 : 0}%`,
              transition: 'width var(--dgm-t-base) var(--dgm-ease)'
            }"
          />
        </div>
        <span class="text-xs text-muted tabular-nums whitespace-nowrap">
          Lesson {{ position.n }} of {{ position.total }}
        </span>
      </div>

      <UButton
        :icon="complete ? 'i-lucide-circle-check' : 'i-lucide-circle'"
        :color="complete ? 'success' : 'neutral'"
        variant="ghost"
        :aria-label="complete ? 'Mark as unfinished' : 'Mark as finished'"
        @click="emit('toggle')"
      >
        <span class="hidden md:inline">{{ complete ? 'Finished' : 'Mark finished' }}</span>
      </UButton>

      <!-- Crossing into a new subject is the moment the path stops feeling like
           a course and starts feeling like a path, so the button says so. -->
      <UButton
        v-if="next"
        trailing-icon="i-lucide-arrow-right"
        :label="crossesSubject ? `Start ${next.subjectTitle}` : 'Next'"
        class="shrink-0"
        @click="emit('advance')"
      />

      <UButton
        v-else
        to="/path"
        trailing-icon="i-lucide-flag"
        label="Finish"
        color="success"
        class="shrink-0"
      />
    </div>
  </div>
</template>
