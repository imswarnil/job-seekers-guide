<script setup lang="ts">
import type { Subject } from '~/utils/path'

const props = defineProps<{
  subject: Subject
  current?: string
  /** The subject the reader is inside, expanded. Others collapse to one row. */
  expanded?: boolean
  index: number
}>()

defineEmits<{ navigate: [] }>()

const { subjectProgress } = useProgress()

const progress = computed(() => subjectProgress(props.subject))
const open = ref(props.expanded)

watch(() => props.expanded, (value) => {
  if (value) {
    open.value = true
  }
})

/**
 * Which module the reader is inside.
 *
 * A subject with six modules and forty lessons opened all at once is a wall —
 * the reader has to scroll past everything they are not doing to reach the one
 * thing they are. So the modules collapse too, and only the one containing the
 * current page starts open.
 */
const activeModule = computed(() => {
  if (!props.current) {
    return undefined
  }
  return props.subject.modules.find(module =>
    module.path === props.current || module.lessons.some(lesson => lesson.path === props.current)
  )?.path
})

/**
 * Modules the reader has opened or shut by hand.
 *
 * Three states, not two: unset means "follow the active module", and an
 * explicit `true`/`false` overrides it. Two booleans were not enough — without
 * the unset case, either the active module could never be shut or opening a
 * different one would not stick.
 */
const override = ref<Record<string, boolean>>({})

function shown(path: string) {
  return override.value[path] ?? path === activeModule.value
}

function toggleModule(path: string) {
  override.value = { ...override.value, [path]: !shown(path) }
}

/* Following a link into a different module opens that module and forgets what
   the reader had shut by hand in the one they left. */
watch(activeModule, () => {
  override.value = {}
})
</script>

<template>
  <div>
    <!-- The header is a button, not a link, so opening a subject to look inside
         never loses the lesson you are on. The subject's own page is reachable
         from its title link below. -->
    <button
      type="button"
      class="w-full flex items-center gap-2.5 rounded-md px-2 py-2 text-left transition-colors hover:bg-elevated"
      :aria-expanded="open"
      @click="open = !open"
    >
      <span class="flex items-center justify-center size-6 shrink-0 relative">
        <ClientOnly>
          <PlayerProgress
            :progress="progress"
            variant="ring"
            :size="24"
            class="absolute inset-0"
          />
        </ClientOnly>
        <UIcon
          :name="subject.icon || 'i-lucide-book-open'"
          class="size-3.5"
          :class="expanded ? 'text-primary' : 'text-dimmed'"
        />
      </span>

      <span class="flex-1 min-w-0">
        <span
          class="block truncate text-sm font-medium"
          :class="expanded ? 'text-highlighted' : 'text-muted'"
        >{{ subject.title }}</span>
        <span class="block text-xs text-dimmed tabular-nums">
          {{ index + 1 }} · {{ subject.lessons.length }} {{ subject.lessons.length === 1 ? 'lesson' : 'lessons' }}
        </span>
      </span>

      <UIcon
        name="i-lucide-chevron-down"
        class="size-4 text-dimmed shrink-0 transition-transform"
        :class="open && 'rotate-180'"
        :style="{ transitionDuration: 'var(--dgm-t-fast)' }"
      />
    </button>

    <div
      v-if="open"
      class="mt-1 mb-3 pl-3 ml-3 border-l border-default space-y-2"
    >
      <NuxtLink
        :to="subject.path"
        class="block px-2 py-1 text-xs font-medium uppercase tracking-wider transition-colors"
        :class="current === subject.path ? 'text-primary' : 'text-dimmed hover:text-highlighted'"
        @click="$emit('navigate')"
      >
        Overview
      </NuxtLink>

      <div
        v-for="module in subject.modules"
        :key="module.path"
      >
        <!-- Two controls in one row: the title is a real link, because the
             prerender crawler discovers module pages through exactly these, and
             the chevron beside it opens the section without navigating. -->
        <div
          class="flex items-center gap-1 rounded-md pr-1 transition-colors"
          :class="shown(module.path) ? '' : 'hover:bg-elevated'"
        >
          <NuxtLink
            :to="module.path"
            class="flex items-center gap-2 px-2 py-1 flex-1 min-w-0 text-xs font-semibold uppercase tracking-wider transition-colors"
            :class="current === module.path ? 'text-primary' : 'text-dimmed hover:text-highlighted'"
            @click="$emit('navigate')"
          >
            <UIcon
              v-if="module.icon"
              :name="module.icon"
              class="size-3.5 shrink-0"
            />
            <span class="truncate">{{ module.title }}</span>
          </NuxtLink>

          <button
            type="button"
            class="shrink-0 p-1 rounded text-dimmed hover:text-highlighted transition-colors"
            :aria-expanded="shown(module.path)"
            :aria-label="`${shown(module.path) ? 'Collapse' : 'Expand'} ${module.title}`"
            @click="toggleModule(module.path)"
          >
            <UIcon
              name="i-lucide-chevron-down"
              class="size-3.5 transition-transform"
              :class="shown(module.path) && 'rotate-180'"
              :style="{ transitionDuration: 'var(--dgm-t-fast)' }"
            />
          </button>
        </div>

        <ul
          v-if="shown(module.path)"
          class="space-y-px mt-0.5 mb-2"
        >
          <li
            v-for="lesson in module.lessons"
            :key="lesson.path"
          >
            <PlayerRailLesson
              :lesson="lesson"
              :active="lesson.path === current"
              @navigate="$emit('navigate')"
            />
          </li>
        </ul>

        <!-- Collapsed sections still say how much is inside them, so closing
             one does not hide the fact that it exists. -->
        <p
          v-else
          class="px-2 pb-1.5 text-xs text-dimmed tabular-nums"
        >
          {{ module.lessons.length }} {{ module.lessons.length === 1 ? 'lesson' : 'lessons' }}
        </p>
      </div>

      <ul
        v-if="!subject.modules.length && subject.lessons.length"
        class="space-y-px"
      >
        <li
          v-for="lesson in subject.lessons"
          :key="lesson.path"
        >
          <PlayerRailLesson
            :lesson="lesson"
            :active="lesson.path === current"
            @navigate="$emit('navigate')"
          />
        </li>
      </ul>
    </div>
  </div>
</template>
