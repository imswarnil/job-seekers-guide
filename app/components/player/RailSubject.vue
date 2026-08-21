<script setup lang="ts">
import type { Subject } from '~/utils/path'
import { findTech } from '~/utils/tech'

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

/**
 * The subject's own colour, from the tech registry.
 *
 * The icon used to sit inside a progress ring, which put twenty small circles
 * down the rail and made every subject look the same until you read the label.
 * The mark in its own brand colour is recognisable at a glance — Supabase is
 * green, Java is red — and the progress moved to a hairline under the row,
 * where it does not compete with it.
 */
const accent = computed(() => findTech(props.subject.slug)?.color)

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
      class="rail-row w-full flex items-center gap-2.5 rounded-md px-2 py-2 text-left transition-colors hover:bg-elevated"
      :aria-expanded="open"
      @click="open = !open"
    >
      <UIcon
        :name="subject.icon || 'i-lucide-book-open'"
        class="size-[1.15rem] shrink-0"
        :class="[accent ? 'rail-row__mark' : (expanded ? 'text-primary' : 'text-dimmed')]"
        :style="accent ? { '--tech': accent } : undefined"
      />

      <span class="flex-1 min-w-0">
        <span
          class="block truncate text-sm font-medium"
          :class="expanded ? 'text-highlighted' : 'text-muted'"
        >{{ subject.title }}</span>
        <span class="block text-xs text-dimmed tabular-nums">
          {{ index + 1 }} ·
          <template v-if="subject.lessons.length">
            {{ subject.lessons.length }} {{ subject.lessons.length === 1 ? 'lesson' : 'lessons' }}
          </template>
          <template v-else>being written</template>
        </span>

        <!-- Progress, as a hairline under the row. It was a ring around the
             icon, which put twenty identical circles down the rail; here it
             is only drawn once there is something to draw. -->
        <ClientOnly>
          <span
            v-if="progress.started"
            class="rail-row__bar"
            :data-done="progress.finished ? '' : undefined"
            :style="{ '--pct': `${progress.percent}%` }"
          />
        </ClientOnly>
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

<style scoped>
/* The mark in the technology's own colour. `.dark` rather than `:global(.dark)`
   — scoped CSS puts the scope attribute on the last compound only, so this
   compiles to `.dark .rail-row__mark[data-v-…]` and matches the class on
   `<html>`. Wrapping the ancestor in `:global()` does not survive the build. */
.rail-row__mark {
  color: var(--tech);
}

/* Several of the brand colours (MySQL, Oracle, SQLite, PostgreSQL) go to mud on
   a dark surface, so they lift toward the page instead of sitting in it. */
.dark .rail-row__mark {
  color: color-mix(in oklab, var(--tech) 74%, white);
}

.rail-row__bar {
  display: block;
  height: 2px;
  margin-top: 0.35rem;
  border-radius: 999px;
  background: var(--ui-bg-accented);
  overflow: hidden;
}

.rail-row__bar::after {
  content: '';
  display: block;
  height: 100%;
  width: var(--pct);
  border-radius: 999px;
  background: var(--ui-primary);
  transition: width var(--dgm-t-base) var(--dgm-ease);
}

.rail-row__bar[data-done]::after {
  background: var(--ui-success);
}

@media (prefers-reduced-motion: reduce) {
  .rail-row__bar::after {
    transition: none;
  }
}
</style>
