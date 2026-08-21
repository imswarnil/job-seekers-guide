<script setup lang="ts">
import type { Lesson } from '~/utils/path'

const props = defineProps<{
  /** The lesson, module or subject page currently open. */
  current?: string
}>()

defineEmits<{ navigate: [] }>()

const { path } = usePath()
const { pathProgress } = useProgress()

const progress = computed(() => pathProgress(path.value))
const activeSubject = computed(() => props.current ? findSubject(path.value, props.current) : undefined)

/**
 * The rail, cut into the sections of the curriculum.
 *
 * Sixteen collapsed subjects in one undifferentiated column is a list you have
 * to read end to end to find anything in. The dividers turn it into six or
 * seven places — "the web bit", "the tools bit" — which is how a reader already
 * thinks about where they are. Numbering still runs across the whole path
 * (`offset`), because restarting it per section would say the sections are
 * separate courses, and they are not.
 */
const sections = computed(() => byStage(path.value))

/* ── Finding a lesson ──────────────────────────────────────────────────
   Three hundred lessons behind eleven collapsed subjects is a tree you
   have to already know your way around. The filter is the way in for
   everybody else: it searches lesson titles *and* the module and subject
   they sit in, so "interview" finds the lessons and "java" finds the
   subject's worth of them. */
const query = ref('')
const searching = computed(() => query.value.trim().length > 0)

const results = computed<Lesson[]>(() => {
  const needle = query.value.trim().toLowerCase()
  if (!needle) {
    return []
  }

  const words = needle.split(/\s+/)

  return path.value.lessons.filter((lesson) => {
    const haystack = [lesson.title, lesson.moduleTitle, lesson.subjectTitle]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
    return words.every(word => haystack.includes(word))
  }).slice(0, 40)
})

const root = useTemplateRef<HTMLElement>('root')

// Land on the lesson you are on, not at the top of a list of three hundred.
onMounted(async () => {
  await nextTick()
  const active = root.value?.querySelector('[data-active]')
  active?.scrollIntoView({ block: 'center' })
})
</script>

<template>
  <nav
    ref="root"
    aria-label="The learning path"
  >
    <NuxtLink
      to="/start"
      class="flex items-center gap-2.5 group"
      @click="$emit('navigate')"
    >
      <UIcon
        name="i-lucide-route"
        class="size-5 text-primary shrink-0"
      />
      <span class="font-display font-semibold text-highlighted group-hover:text-primary transition-colors">
        Start here
      </span>
    </NuxtLink>

    <ClientOnly>
      <PlayerProgress
        :progress="progress"
        class="mt-4"
      />
    </ClientOnly>

    <UInput
      v-model="query"
      icon="i-lucide-search"
      placeholder="Find a lesson"
      size="sm"
      class="w-full mt-4"
      :ui="{ trailing: 'pe-1' }"
    >
      <template
        v-if="query"
        #trailing
      >
        <UButton
          icon="i-lucide-x"
          color="neutral"
          variant="link"
          size="xs"
          aria-label="Clear the filter"
          @click="query = ''"
        />
      </template>
    </UInput>

    <USeparator class="my-4" />

    <!-- ── Filtered ──────────────────────────────────────────────────── -->
    <div v-if="searching">
      <p class="px-2 mb-2 text-xs text-dimmed tabular-nums">
        {{ results.length }} {{ results.length === 1 ? 'lesson' : 'lessons' }}
      </p>

      <ul
        v-if="results.length"
        class="space-y-px"
      >
        <li
          v-for="lesson in results"
          :key="lesson.path"
        >
          <NuxtLink
            :to="lesson.path"
            :data-active="lesson.path === current || undefined"
            class="block rounded-md px-2 py-1.5 transition-colors"
            :class="lesson.path === current
              ? 'bg-primary/10 text-primary'
              : 'text-muted hover:text-highlighted hover:bg-elevated'"
            @click="$emit('navigate')"
          >
            <span class="block text-sm truncate">{{ lesson.title }}</span>
            <!-- Where it lives. A flat result list without this is a list of
                 titles you cannot place in the path. -->
            <span class="block text-xs text-dimmed truncate">
              {{ [lesson.subjectTitle, lesson.moduleTitle].filter(Boolean).join(' · ') }}
            </span>
          </NuxtLink>
        </li>
      </ul>

      <p
        v-else
        class="px-2 text-sm text-dimmed"
      >
        Nothing matches that. The path is still being written — try a broader
        word, or
        <NuxtLink
          to="/start"
          class="text-primary hover:underline"
        >see every subject</NuxtLink>.
      </p>
    </div>

    <!-- ── The tree ──────────────────────────────────────────────────── -->
    <div v-else>
      <section
        v-for="(section, sectionIndex) in sections"
        :key="section.stage"
        :class="sectionIndex && 'mt-5'"
      >
        <!-- The divider is a heading with a rule running off it rather than a
             separator with a label floating above, so the section reads as
             owning what follows it. -->
        <div class="flex items-center gap-2 px-2 mb-1.5">
          <UIcon
            :name="section.icon"
            class="size-3.5 text-dimmed shrink-0"
          />
          <h2 class="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-dimmed whitespace-nowrap">
            {{ section.label }}
          </h2>
          <span class="h-px flex-1 bg-[var(--ui-border)]" />
          <span class="text-[0.6875rem] text-dimmed tabular-nums shrink-0">
            {{ section.lessons || '—' }}
          </span>
        </div>

        <div class="space-y-0.5">
          <PlayerRailSubject
            v-for="(subject, index) in section.subjects"
            :key="subject.path"
            :subject="subject"
            :index="section.offset + index"
            :current="current"
            :expanded="subject.path === activeSubject?.path"
            @navigate="$emit('navigate')"
          />
        </div>
      </section>
    </div>
  </nav>
</template>
