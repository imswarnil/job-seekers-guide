<script setup lang="ts">
const { path } = usePath()
const { pathProgress, resume, streak } = useProgress()

const title = 'The path'
const description = 'One sequence, end to end. Orientation first, foundations second, the job hunt taught with the same seriousness as everything before it.'

usePageSeo({ title, description, headline: 'Everything, in order' })

const groups = computed(() => byStage(path.value))
const progress = computed(() => pathProgress(path.value))
const resumeTo = computed(() => resume(path.value))

/** Running index across the whole path, so numbering does not restart per stage. */
const positions = computed(() => {
  const map = new Map<string, number>()
  path.value.subjects.forEach((subject, index) => map.set(subject.path, index))
  return map
})
</script>

<template>
  <UContainer>
    <UPageHeader
      :title="title"
      :description="description"
      class="py-[50px]"
    >
      <template #headline>
        <div class="flex items-center gap-2 text-sm text-muted">
          <UIcon
            name="i-lucide-route"
            class="size-4 text-primary"
          />
          {{ path.subjects.length }} subjects ·
          {{ path.lessons.length }} lessons ·
          {{ formatMinutes(path.minutes) }} of reading
        </div>
      </template>
    </UPageHeader>

    <UPageBody>
      <ClientOnly>
        <UPageCard
          v-if="resumeTo"
          variant="subtle"
          class="mb-10"
        >
          <div class="flex flex-col sm:flex-row sm:items-center gap-6 justify-between">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <UBadge
                  :label="progress.started ? 'Where you left off' : 'Start here'"
                  color="secondary"
                  variant="subtle"
                />
                <UBadge
                  v-if="streak() > 1"
                  :label="`${streak()} day streak`"
                  icon="i-lucide-flame"
                  variant="subtle"
                  class="text-[color:var(--guide-milestone-strong)] bg-[color:var(--guide-milestone-soft)]"
                />
              </div>

              <h2 class="font-display text-xl font-semibold text-highlighted mt-3 truncate">
                {{ resumeTo.title }}
              </h2>
              <p class="text-muted mt-1 text-sm">
                {{ resumeTo.subjectTitle }}<span v-if="resumeTo.moduleTitle"> · {{ resumeTo.moduleTitle }}</span>
              </p>

              <PlayerProgress
                v-if="progress.started"
                :progress="progress"
                :label="`${progress.completed} of ${progress.total} lessons finished`"
                class="mt-4 max-w-sm"
              />
            </div>

            <UButton
              :to="resumeTo.path"
              :label="progress.started ? 'Continue' : 'Begin'"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
              class="shrink-0"
            />
          </div>
        </UPageCard>
      </ClientOnly>

      <div
        v-for="group in groups"
        :key="group.label"
        class="mb-12"
      >
        <h2 class="font-display text-lg font-semibold text-highlighted mb-4">
          {{ group.label }}
        </h2>

        <UPageGrid>
          <SubjectCard
            v-for="subject in group.subjects"
            :key="subject.path"
            :subject="subject"
            :index="positions.get(subject.path)"
          />
        </UPageGrid>
      </div>

      <UPageCard
        variant="naked"
        title="The rest of the path"
        description="Your programming language, web development, four escalating projects, and the job hunt itself are outlined in About while the lessons are written."
      >
        <template #footer>
          <UButton
            to="/about"
            label="What is coming"
            variant="subtle"
            trailing-icon="i-lucide-arrow-right"
          />
        </template>
      </UPageCard>
    </UPageBody>
  </UContainer>
</template>
