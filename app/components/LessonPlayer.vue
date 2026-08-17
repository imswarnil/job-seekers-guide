<script setup lang="ts">
import type { PathCollectionItem } from '@nuxt/content'

const props = defineProps<{
  page: PathCollectionItem
}>()

const route = useRoute()

const { subject, lesson, previous, next, position, crossesSubject } = usePathPlayer(() => route.path)
const { state, isComplete, setComplete, toggleComplete, markVisited } = useProgress()
const { toggle: toggleRail } = useRail()

const complete = computed(() => isComplete(route.path))

// Resume-where-you-left-off only works if somebody writes down where you were.
function remember(path: string) {
  markVisited(path, subject.value?.path)
}

onMounted(() => remember(route.path))
watch(() => route.path, remember)

/* -----------------------------------------------------------------------------
   Auto-advance
   -----------------------------------------------------------------------------
   Opt-in, and abandoned the moment the reader does anything at all. An advance
   that fires while somebody is still reading is worse than no advance: it loses
   their place and teaches them not to trust the button.
   -------------------------------------------------------------------------- */

const AUTO_ADVANCE_SECONDS = 8

const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')
const counting = ref(false)
const remaining = ref(AUTO_ADVANCE_SECONDS)

let timer: ReturnType<typeof setInterval> | undefined

function cancelAdvance() {
  counting.value = false
  if (timer) {
    clearInterval(timer)
    timer = undefined
  }
}

function advance() {
  cancelAdvance()
  setComplete(route.path, true)
  return navigateTo(next.value?.path || '/path')
}

function startAdvance() {
  if (!next.value || !state.value.autoAdvance || reduced.value) {
    return
  }

  counting.value = true
  remaining.value = AUTO_ADVANCE_SECONDS

  timer = setInterval(() => {
    remaining.value--
    if (remaining.value <= 0) {
      advance()
    }
  }, 1000)
}

function onToggle() {
  cancelAdvance()
  toggleComplete(route.path)

  if (isComplete(route.path)) {
    startAdvance()
  }
}

useEventListener('keydown', () => counting.value && cancelAdvance())
useEventListener('pointerdown', () => counting.value && cancelAdvance())

onBeforeUnmount(cancelAdvance)
watch(() => route.path, cancelAdvance)

usePlayerShortcuts({
  next: () => next.value && navigateTo(next.value.path),
  previous: () => previous.value && navigateTo(previous.value.path),
  toggleComplete: onToggle,
  toggleRail
})

const toc = computed(() => props.page.body?.toc?.links || [])
</script>

<template>
  <div>
    <LessonHeader
      :title="page.title"
      :description="page.description"
      :lesson="lesson"
      :minutes="page.minutes"
      :kind="page.kind"
    />

    <div class="xl:flex xl:gap-10 xl:items-start">
      <div class="min-w-0 flex-1">
        <div class="guide-prose">
          <ContentRenderer
            v-if="page.body"
            :value="page"
          />
        </div>

        <AdSlot placement="lesson-footer" />

        <USeparator class="my-10" />

        <ClientOnly>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between mb-8">
            <UButton
              :label="complete ? 'Finished' : 'Mark as finished'"
              :icon="complete ? 'i-lucide-circle-check' : 'i-lucide-circle'"
              :color="complete ? 'success' : 'neutral'"
              variant="subtle"
              @click="onToggle"
            />

            <UButton
              :label="next ? 'Finish and continue' : 'Finish the path'"
              trailing-icon="i-lucide-arrow-right"
              @click="advance"
            />
          </div>
        </ClientOnly>

        <PlayerUpNext
          v-model:remaining="remaining"
          :previous="previous"
          :next="next"
          :crosses-subject="crossesSubject"
          :counting="counting"
          :seconds="AUTO_ADVANCE_SECONDS"
          @cancel="cancelAdvance"
        />

        <PlayerFooterBar
          :previous="previous"
          :next="next"
          :position="position"
          :crosses-subject="crossesSubject"
          :complete="complete"
          @toggle="onToggle"
          @advance="advance"
          @open-rail="toggleRail"
        />
      </div>

      <!-- The contents sit beside the prose only where there is room for them.
           Below xl they would push the reading measure into a gutter. -->
      <aside
        v-if="toc.length"
        class="hidden xl:block w-56 shrink-0 sticky top-[calc(var(--ui-header-height)+2rem)]"
      >
        <UContentToc
          :links="toc"
          highlight
          class="!bg-transparent !border-0 !p-0"
        />
      </aside>
    </div>
  </div>
</template>
