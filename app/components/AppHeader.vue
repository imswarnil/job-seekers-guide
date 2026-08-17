<script setup lang="ts">
import type { ContentNavigationItem } from '@nuxt/content'

const route = useRoute()

const navigation = inject<Ref<ContentNavigationItem[]>>('navigation')

const { open: searchOpen } = useContentSearch()

const open = ref(false)

const isDocs = computed(() => route.path === '/docs' || route.path.startsWith('/docs/'))

// Both modals portal to `body` with no z-index, so after a client-side layout
// change the menu can end up painted over the search
watch(searchOpen, (value) => {
  if (value) {
    open.value = false
  }
})

const items = computed(() => [{
  label: 'Courses',
  to: '/courses',
  active: route.path.startsWith('/courses')
}, {
  label: 'Docs',
  to: '/docs',
  active: isDocs.value
}, {
  label: 'Writing',
  to: '/blog'
}, {
  label: 'Changelog',
  to: '/changelog'
}])
</script>

<template>
  <UHeader v-model:open="open">
    <template #left>
      <NuxtLink
        to="/"
        aria-label="Job Seekers Guide"
      >
        <AppLogo />
      </NuxtLink>
    </template>

    <UNavigationMenu
      :items="items"
      variant="link"
    />

    <template #right>
      <UColorModeButton />

      <UContentSearchButton class="lg:hidden" />

      <UButton
        icon="i-lucide-graduation-cap"
        color="neutral"
        variant="ghost"
        to="/courses"
        aria-label="Courses"
        class="lg:hidden"
      />

      <UButton
        label="Start learning"
        trailing-icon="i-lucide-arrow-right"
        class="hidden lg:inline-flex"
        to="/courses"
      />
    </template>

    <template #body>
      <UNavigationMenu
        :items="items"
        orientation="vertical"
        class="-mx-2.5"
      />

      <template v-if="isDocs">
        <USeparator class="my-6" />

        <UContentNavigation
          :navigation="navigation"
          highlight
        />
      </template>

      <USeparator class="my-6" />

      <UButton
        label="Start learning"
        to="/courses"
        trailing-icon="i-lucide-arrow-right"
        block
      />
    </template>
  </UHeader>
</template>
