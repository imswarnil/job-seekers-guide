<script setup lang="ts">
import type { Lesson } from '~/utils/path'

const props = defineProps<{
  previous?: Lesson
  next?: Lesson
  crossesSubject?: boolean
  /** Counting down to an automatic advance, when the reader opted in. */
  counting?: boolean
  seconds?: number
}>()

defineEmits<{ cancel: [] }>()

const remaining = defineModel<number>('remaining', { default: 0 })

const fraction = computed(() => props.seconds ? remaining.value / props.seconds : 0)
</script>

<template>
  <div class="grid sm:grid-cols-2 gap-4">
    <UPageCard
      v-if="previous"
      :to="previous.path"
      :title="previous.title"
      :description="previous.description"
      icon="i-lucide-arrow-left"
      variant="subtle"
      :ui="{ description: 'line-clamp-2' }"
    />
    <span v-else />

    <UPageCard
      v-if="next"
      :to="next.path"
      :title="next.title"
      :description="next.description"
      icon="i-lucide-arrow-right"
      variant="subtle"
      :ui="{ description: 'line-clamp-2' }"
    >
      <template
        v-if="crossesSubject"
        #header
      >
        <UBadge
          :label="`Next subject · ${next.subjectTitle}`"
          color="secondary"
          variant="subtle"
          size="sm"
        />
      </template>

      <template
        v-if="counting"
        #footer
      >
        <div class="flex items-center gap-2.5">
          <div class="flex-1 h-1 rounded-full bg-accented overflow-hidden">
            <div
              class="h-full bg-primary rounded-full"
              :style="{ width: `${fraction * 100}%`, transition: 'width 1s linear' }"
            />
          </div>
          <span class="text-xs text-muted tabular-nums">{{ remaining }}s</span>
          <UButton
            label="Stay"
            size="xs"
            color="neutral"
            variant="subtle"
            @click.stop.prevent="$emit('cancel')"
          />
        </div>
      </template>
    </UPageCard>
  </div>
</template>
