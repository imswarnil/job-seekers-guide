<script setup lang="ts">
const route = useRoute()

const { open: searchOpen } = useContentSearch()
const { path } = usePath()
const { resume, pathProgress } = useProgress()

const open = ref(false)

// Both modals portal to `body` with no z-index, so after a client-side layout
// change the menu can end up painted over the search.
watch(searchOpen, (value) => {
  if (value) {
    open.value = false
  }
})

/** A lesson page hands the mobile sheet over to the player rail instead. */
const isLesson = computed(() => route.path.split('/').filter(Boolean).length === 3)

const items = computed(() => navLinks.map(link => ({
  ...link,
  active: link.to === '/start'
    ? route.path === '/start'
    : route.path === link.to || route.path.startsWith(`${link.to}/`)
})))

const progress = computed(() => pathProgress(path.value))
const continueTo = computed(() => resume(path.value)?.path || '/start')
const continueLabel = computed(() => progress.value.started ? 'Continue' : 'Start learning')
</script>

<template>
  <div>
    <!-- The leaderboard sits above the header rather than inside it, so it can
         never push the nav around: the header keeps its own height whatever the
         slot does, and the slot reserves its box before anything loads. -->
    <AdSlot
      placement="nav-leaderboard"
      variant="banner"
    />

    <UHeader v-model:open="open">
      <template #left>
        <NuxtLink
          to="/"
          aria-label="Job Seekers Guide"
        >
          <AppLogo interactive />
        </NuxtLink>
      </template>

      <UNavigationMenu
        :items="items"
        variant="link"
      />

      <template #right>
        <!-- Search is a first-class action, not a mobile afterthought. At every
             width, with the shortcut visible so it gets learned. -->
        <UTooltip
          text="Search"
          :kbds="['meta', 'K']"
        >
          <UButton
            icon="i-lucide-search"
            color="neutral"
            variant="ghost"
            size="sm"
            aria-label="Search"
            @click="searchOpen = true"
          />
        </UTooltip>

        <GithubStar />

        <UColorModeButton />

        <!-- Resuming needs localStorage, so the server renders the plain start
             button and the client swaps in "Continue" once it knows better. -->
        <ClientOnly>
          <UButton
            :to="continueTo"
            :label="continueLabel"
            trailing-icon="i-lucide-arrow-right"
            class="hidden lg:inline-flex"
          />

          <template #fallback>
            <UButton
              to="/start"
              label="Start learning"
              trailing-icon="i-lucide-arrow-right"
              class="hidden lg:inline-flex"
            />
          </template>
        </ClientOnly>
      </template>

      <template #body>
        <UNavigationMenu
          :items="items"
          orientation="vertical"
          class="-mx-2.5"
        />

        <USeparator class="my-6" />

        <!-- On a lesson, the useful thing in the mobile sheet is not the site nav
             a second time — it is where you are in the path. -->
        <PlayerRail
          v-if="isLesson"
          :current="route.path"
          class="-mx-1"
          @navigate="open = false"
        />

        <template v-else>
          <ClientOnly>
            <UButton
              :to="continueTo"
              :label="continueLabel"
              trailing-icon="i-lucide-arrow-right"
              block
            />

            <template #fallback>
              <UButton
                to="/start"
                label="Start learning"
                trailing-icon="i-lucide-arrow-right"
                block
              />
            </template>
          </ClientOnly>
        </template>
      </template>
    </UHeader>
  </div>
</template>
