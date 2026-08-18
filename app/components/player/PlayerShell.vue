<script setup lang="ts">
defineProps<{
  current?: string
  /** Hide the rail entirely — pages that render their own contents. */
  bare?: boolean
}>()

const { isNarrow, open, collapsed, toggle, close } = useRail()
</script>

<template>
  <div class="shell">
    <!-- The rail is a column, not a layout, so the same component serves the
         lesson player, the subject page and the mobile slideover. -->
    <aside
      v-if="!bare"
      class="shell__rail"
      :class="collapsed && 'shell__rail--collapsed'"
    >
      <div
        class="shell__rail-inner"
        :class="collapsed && 'invisible'"
      >
        <PlayerRail :current="current" />
      </div>
    </aside>

    <div class="shell__main">
      <!-- Full width: the header owns the whole content column, edge to edge,
           rather than being squeezed into the reading measure. -->
      <header class="shell__hero">
        <div class="shell__inner">
          <div class="flex items-center gap-2 mb-4">
            <UButton
              v-if="!bare"
              :icon="collapsed ? 'i-lucide-panel-left-open' : 'i-lucide-panel-left-close'"
              color="neutral"
              variant="ghost"
              size="sm"
              :aria-label="collapsed ? 'Show the path' : 'Hide the path'"
              class="hidden lg:inline-flex -ms-2"
              @click="toggle"
            />

            <UButton
              v-if="!bare"
              icon="i-lucide-panel-left"
              color="neutral"
              variant="ghost"
              size="sm"
              aria-label="Show the path"
              class="lg:hidden -ms-2"
              @click="open = true"
            />

            <slot name="toolbar" />
          </div>

          <slot name="hero" />
        </div>
      </header>

      <!-- Two columns: prose, and everything that is about the page rather than
           in it — contents, ads, page actions. -->
      <div class="shell__body">
        <div class="shell__inner shell__columns">
          <div class="min-w-0">
            <slot />
          </div>

          <aside
            v-if="$slots.aside"
            class="shell__aside"
          >
            <div class="shell__aside-inner">
              <slot name="aside" />
            </div>
          </aside>
        </div>
      </div>

      <!-- Full width again: the end of the page should look like the end of the
           page, not like another paragraph. -->
      <footer
        v-if="$slots.pagination"
        class="shell__pagination"
      >
        <div class="shell__inner">
          <slot name="pagination" />
        </div>
      </footer>
    </div>

    <ClientOnly>
      <USlideover
        v-if="!bare && isNarrow"
        v-model:open="open"
        side="left"
        title="The path"
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

<style scoped>
.shell {
  display: flex;
  min-height: calc(100vh - var(--ui-header-height));
}

.shell__rail {
  display: none;
  flex-shrink: 0;
  border-right: 1px solid var(--ui-border);
  width: 18rem;
  transition: width var(--dgm-t-base) var(--dgm-ease);
}

@media (min-width: 1024px) {
  .shell__rail {
    display: block;
  }
}

@media (min-width: 1536px) {
  .shell__rail {
    width: 20rem;
  }
}

.shell__rail--collapsed {
  width: 0;
}

.shell__rail-inner {
  position: sticky;
  top: var(--ui-header-height);
  height: calc(100vh - var(--ui-header-height));
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 1.5rem 1rem;
}

.shell__main {
  flex: 1;
  min-width: 0;
}

/* One inner wrapper, one max width, used by all three bands — so the hero, the
   prose and the pagination line up down the left edge instead of each finding
   their own margin. */
.shell__inner {
  max-width: 88rem;
  margin-inline: auto;
  padding-inline: 1rem;
}

@media (min-width: 640px) {
  .shell__inner {
    padding-inline: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .shell__inner {
    padding-inline: 2.5rem;
  }
}

.shell__hero {
  padding-block: 1.5rem 2rem;
  border-bottom: 1px solid var(--ui-border);
}

.shell__body {
  padding-block: 2.5rem;
}

.shell__columns {
  display: grid;
  gap: 3rem;
}

/* The sidebar is genuinely wide — the old one was 14rem and could not hold a
   heading, an ad and a page action without all three being cramped. */
@media (min-width: 1280px) {
  .shell__columns {
    grid-template-columns: minmax(0, 1fr) 19rem;
    gap: 4rem;
  }
}

.shell__aside {
  display: none;
}

@media (min-width: 1280px) {
  .shell__aside {
    display: block;
  }
}

.shell__aside-inner {
  position: sticky;
  top: calc(var(--ui-header-height) + 2rem);
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  max-height: calc(100vh - var(--ui-header-height) - 3rem);
  overflow-y: auto;
  overscroll-behavior: contain;
}

.shell__pagination {
  border-top: 1px solid var(--ui-border);
  background: var(--ui-bg-elevated);
  padding-block: 2rem 3rem;
}
</style>
