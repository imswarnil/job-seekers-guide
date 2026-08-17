<script setup lang="ts">
import type { Subject } from '~/utils/path'

const props = defineProps<{
  subject: Subject
  /** Position along the whole path, shown so the order reads as an order. */
  index?: number
}>()

const { subjectProgress } = useProgress()

const progress = computed(() => subjectProgress(props.subject))

const meta = computed(() => [
  props.subject.duration,
  props.subject.lessons.length === 1 ? '1 lesson' : `${props.subject.lessons.length} lessons`
].filter(Boolean))
</script>

<template>
  <UPageCard
    :to="subject.path"
    :title="subject.title"
    :description="subject.description"
    :icon="subject.icon || 'i-lucide-book-open'"
    spotlight
  >
    <template #footer>
      <div class="flex items-center gap-2 flex-wrap text-xs text-dimmed">
        <UBadge
          v-if="index !== undefined"
          :label="String(index + 1).padStart(2, '0')"
          color="neutral"
          variant="subtle"
          size="sm"
          class="tabular-nums"
        />
        <UBadge
          v-if="subject.code"
          :label="subject.code"
          color="neutral"
          variant="subtle"
          size="sm"
        />
        <span>{{ meta.join(' · ') }}</span>
      </div>

      <ClientOnly>
        <div
          v-if="progress.started"
          class="mt-3"
        >
          <UProgress
            :model-value="progress.percent"
            size="xs"
            :color="progress.finished ? 'success' : 'primary'"
          />
          <p class="text-xs text-muted mt-1.5">
            {{ progress.finished ? 'Finished' : `${progress.percent}% complete` }}
          </p>
        </div>
      </ClientOnly>
    </template>
  </UPageCard>
</template>
