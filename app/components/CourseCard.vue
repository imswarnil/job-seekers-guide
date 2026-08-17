<script setup lang="ts">
import type { Course } from '~/utils/courses'

const props = defineProps<{
  course: Course
}>()

const { courseProgress } = useCourseProgress()

const progress = computed(() => courseProgress(props.course))

const meta = computed(() => [
  props.course.duration,
  props.course.lessons.length === 1 ? '1 lesson' : `${props.course.lessons.length} lessons`
].filter(Boolean))
</script>

<template>
  <UPageCard
    :to="course.path"
    :title="course.title"
    :description="course.description"
    :icon="course.icon || 'i-lucide-book-open'"
    spotlight
  >
    <template #footer>
      <div class="flex items-center gap-2 flex-wrap text-xs text-dimmed">
        <UBadge
          v-if="course.code"
          :label="course.code"
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
