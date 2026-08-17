<script setup lang="ts">
const props = defineProps<{
  /** The lesson, module or subject page currently open. */
  current?: string
}>()

defineEmits<{ navigate: [] }>()

const { path } = usePath()
const { pathProgress } = useProgress()

const progress = computed(() => pathProgress(path.value))
const activeSubject = computed(() => props.current ? findSubject(path.value, props.current) : undefined)

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

    <USeparator class="my-4" />

    <!-- Every subject on the platform is listed. Only the one you are inside is
         expanded — a rail that opened all of them would be thousands of nodes
         and no more useful for it. -->
    <div class="space-y-0.5">
      <PlayerRailSubject
        v-for="(subject, index) in path.subjects"
        :key="subject.path"
        :subject="subject"
        :index="index"
        :current="current"
        :expanded="subject.path === activeSubject?.path"
        @navigate="$emit('navigate')"
      />
    </div>
  </nav>
</template>
