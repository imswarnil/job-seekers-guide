<script setup lang="ts">
// Matches the path pages this one is the front door to. See AppHeader.vue.
definePageMeta({ fluidHeader: true })

const { path } = usePath()
const { state, pathProgress, resume, streak } = useProgress()

const title = 'Start here'
const description = 'One sequence, end to end. What this is and how it is taught first, then the whole route — orientation, foundations, the web, the tools, the build, AI, and the job hunt taught with the same seriousness as everything before it.'

usePageSeo({ title, description, headline: 'Everything, in order' })

const progress = computed(() => pathProgress(path.value))
const resumeTo = computed(() => resume(path.value))

// "Start here" pointing at lesson nine is a lie. Having opened a lesson counts
// as having started, whether or not anything was ever ticked off — a reader who
// stopped half way through does not want to be told to begin.
const returning = computed(() => Boolean(state.value.lastVisited) || pathProgress(path.value).started)

// The counts used to be repeated here. They are the stats band below the
// header now, where they are read once and properly.
const headline = [
  { icon: 'i-lucide-wallet', text: 'Free, all of it' },
  { icon: 'i-lucide-user-x', text: 'No account' },
  { icon: 'i-lucide-hard-drive', text: 'Progress stays on your device' }
]
</script>

<template>
  <UContainer>
    <!-- The two columns start at the title, not below it. The sidebar used to
         begin level with the timeline, which left a tall empty rectangle beside
         the header and made the page look like two unrelated things. -->
    <div class="start">
      <div class="min-w-0">
        <UPageHeader
          :title="title"
          :description="description"
          class="py-[50px]"
        >
          <template #headline>
            <ul class="flex items-center gap-x-4 gap-y-1 flex-wrap text-sm text-muted">
              <li
                v-for="fact in headline"
                :key="fact.text"
                class="flex items-center gap-1.5"
              >
                <UIcon
                  :name="fact.icon"
                  class="size-4 text-primary shrink-0"
                />
                {{ fact.text }}
              </li>
            </ul>
          </template>
        </UPageHeader>

        <UPageBody>
          <!-- The sidebar carries these, and the sidebar is not rendered below
               `xl`. The numbers are too load-bearing to disappear with it. -->
          <PathStats
            :path="path"
            class="mb-10 xl:hidden"
          />
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
                      :icon="returning ? 'i-lucide-bookmark' : 'i-lucide-flag'"
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
                  <p class="text-muted mt-1 text-sm flex items-center gap-1.5 flex-wrap">
                    <UIcon
                      name="i-lucide-map-pin"
                      class="size-3.5 shrink-0"
                    />
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
                  :icon="returning ? 'i-lucide-rotate-cw' : 'i-lucide-play'"
                  trailing-icon="i-lucide-arrow-right"
                  size="lg"
                  class="shrink-0"
                />
              </div>
            </UPageCard>
          </ClientOnly>

          <!-- The route, immediately. Somebody who has clicked "Start here" has
               already decided; the case for the path belongs on the front page,
               where it is still being made, and it is on `/#about`.

               The route as one connected line rather than a grid of cards. A
               grid says "pick one of twenty", which is the decision the visitor
               arrived here unable to make; a line says "this is the order". -->
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
            description="Most of the subjects above are outlines while the lessons are written. What this course is for, how a lesson is put together, and what it deliberately leaves out are all on the front page."
          >
            <template #footer>
              <UButton
                to="/#about"
                label="What this is"
                variant="subtle"
                icon="i-lucide-info"
                trailing-icon="i-lucide-arrow-right"
              />
            </template>
          </UPageCard>
        </UPageBody>
      </div>

      <PathAside
        :path="path"
        class="start__aside"
      />
    </div>
  </UContainer>
</template>

<style scoped>
.start {
  display: grid;
  gap: 2.5rem;
}

.start__aside {
  display: none;
}

/* The sidebar's own ad slot does not render below 1280 either, so the column
   only exists where all of it does.

   No `align-items: start` here, deliberately. Grid items stretch by default,
   and that stretch is what gives the sidebar's sticky inner column a box tall
   enough to travel inside — `start` shrank it to its own content height, and a
   sticky element with nowhere to go simply never sticks. */
@media (min-width: 1280px) {
  .start {
    grid-template-columns: minmax(0, 1fr) 18rem;
  }

  .start__aside {
    display: block;
  }
}
</style>
