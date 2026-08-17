<script setup lang="ts">
defineProps<{
  current?: string
  /** Hide the rail entirely — overview pages that render their own contents. */
  bare?: boolean
}>()

const { isNarrow, open, collapsed, toggle, close } = useRail()
</script>

<template>
  <div class="flex min-h-[calc(100vh-var(--ui-header-height))]">
    <!-- The rail is a column, not a layout, so the same component serves the
         lesson player, the subject page and the mobile slideover. -->
    <aside
      v-if="!bare"
      class="hidden lg:block shrink-0 border-r border-default transition-[width]"
      :class="collapsed ? 'w-0' : 'w-72 xl:w-80'"
      :style="{ transitionDuration: 'var(--dgm-t-base)', transitionTimingFunction: 'var(--dgm-ease)' }"
    >
      <div
        class="sticky top-[var(--ui-header-height)] h-[calc(100vh-var(--ui-header-height))] overflow-y-auto overscroll-contain px-4 py-6"
        :class="collapsed && 'invisible'"
      >
        <PlayerRail :current="current" />
      </div>
    </aside>

    <div class="flex-1 min-w-0">
      <div class="px-4 sm:px-6 lg:px-10 py-8 max-w-4xl mx-auto">
        <div class="flex items-center gap-2 mb-4">
          <UButton
            v-if="!bare"
            :icon="collapsed ? 'i-lucide-panel-left-open' : 'i-lucide-panel-left-close'"
            color="neutral"
            variant="ghost"
            size="sm"
            :aria-label="collapsed ? 'Show the path' : 'Hide the path'"
            class="hidden lg:inline-flex"
            @click="toggle"
          />

          <UButton
            v-if="!bare"
            icon="i-lucide-panel-left"
            color="neutral"
            variant="ghost"
            size="sm"
            aria-label="Show the path"
            class="lg:hidden"
            @click="open = true"
          />

          <slot name="toolbar" />
        </div>

        <slot />
      </div>
    </div>

    <ClientOnly>
      <USlideover
        v-if="!bare && isNarrow"
        v-model:open="open"
        side="left"
        title="Start here"
      >
        <template #body>
          <PlayerRail
            :current="current"
            @navigate="close"
          />
        </template>
      </USlideover>
    </ClientOnly>
  </div>
</template>
