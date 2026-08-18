<script setup lang="ts">
const { path } = usePath()
const { state, pathProgress, resume, streak } = useProgress()

const title = 'Start here'
const description = 'One sequence, end to end. Orientation first, foundations second, the job hunt taught with the same seriousness as everything before it.'

usePageSeo({ title, description, headline: 'Everything, in order' })

const progress = computed(() => pathProgress(path.value))
const resumeTo = computed(() => resume(path.value))

// "Start here" pointing at lesson nine is a lie. Having opened a lesson counts
// as having started, whether or not anything was ever ticked off — a reader who
// stopped half way through does not want to be told to begin.
const returning = computed(() => Boolean(state.value.lastVisited) || pathProgress(path.value).started)
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
                  :label="returning ? 'Where you left off' : 'Start here'"
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
              :label="returning ? 'Continue' : 'Begin'"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
              class="shrink-0"
            />
          </div>
        </UPageCard>
      </ClientOnly>

      <!-- The route as one connected line rather than a grid of cards. A grid
           says "pick one of eleven", which is the decision the visitor arrived
           here unable to make; a line says "this is the order". -->
      <PathTimeline
        :path="path"
        class="mb-14"
      />

      <AdSlot
        placement="in-article"
        class="mx-auto"
      />

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
