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
      class="mt-1 mb-3 pl-3 ml-3 border-l border-default space-y-3"
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
        <!-- Real links, not buttons: the prerender crawler discovers module
             pages through exactly these. -->
        <NuxtLink
          :to="module.path"
          class="flex items-center gap-2 px-2 mb-1 text-xs font-semibold uppercase tracking-wider transition-colors"
          :class="current === module.path ? 'text-primary' : 'text-dimmed hover:text-highlighted'"
          @click="$emit('navigate')"
        >
          <UIcon
            v-if="module.icon"
            :name="module.icon"
            class="size-3.5"
          />
          <span class="truncate">{{ module.title }}</span>
        </NuxtLink>

        <ul class="space-y-px">
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
