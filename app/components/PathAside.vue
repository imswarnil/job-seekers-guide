<script setup lang="ts">
import type { LearningPath } from '~/utils/path'

/**
 * The column beside the route.
 *
 * `/start` is a long page — twenty subjects and eight sections — and once the
 * reader is a screen and a half down it, the section list is gone. So it
 * travels, along with the two things that are honest to keep on screen without
 * interrupting anybody: the sign-up and the ad slot.
 *
 * Below `xl` the column is not rendered at all rather than stacked underneath.
 * A jump list under the thing it jumps to is furniture — `/start` renders the
 * stats on their own at those widths instead, so the numbers never vanish with
 * the column.
 */
const props = defineProps<{
  path: LearningPath
}>()

const sections = computed(() => byStage(props.path))

/* ── Which section the reader is looking at ──────────────────────────────
   Cheap scroll-spy: the sections announce themselves as they cross the
   upper third of the viewport. Client only, and the list is a plain set of
   anchors without it, so nothing breaks if the observer never runs. */
const active = ref<string>()

onMounted(() => {
  const targets = sections.value
    .map(section => document.getElementById(section.id))
    .filter((element): element is HTMLElement => Boolean(element))

  if (!targets.length || !('IntersectionObserver' in window)) {
    return
  }

  const observer = new IntersectionObserver((entries) => {
    const visible = entries.filter(entry => entry.isIntersecting)
    if (visible.length) {
      active.value = visible[0]!.target.id
    }
  }, { rootMargin: '-15% 0px -70% 0px' })

  targets.forEach(target => observer.observe(target))
  onBeforeUnmount(() => observer.disconnect())
})
</script>

<template>
  <aside class="aside">
    <div class="aside__inner">
      <!-- ── The whole path, in four numbers ────────────────────────── -->
      <PathStats
        :path="path"
        layout="panel"
      />

      <!-- ── The sections ───────────────────────────────────────────── -->
      <nav
        class="aside__card"
        aria-label="Sections of the path"
      >
        <h2 class="aside__title">
          <UIcon
            name="i-lucide-list"
            class="size-3.5 text-primary"
          />
          Sections
        </h2>

        <ul class="aside__jump">
          <li
            v-for="section in sections"
            :key="section.stage"
          >
            <a
              :href="`#${section.id}`"
              :data-active="active === section.id || undefined"
            >
              <UIcon
                :name="section.icon"
                class="size-3.5 shrink-0"
              />
              <span class="truncate">{{ section.label }}</span>
              <!-- A count of zero on a section still being written says less
                   than nothing; the dash says "not yet" without a number. -->
              <span class="aside__count">{{ section.lessons || '—' }}</span>
            </a>
          </li>
        </ul>
      </nav>

      <NewsletterSignup
        variant="card"
        title="Told when a subject lands"
        note="One email a month at the very most, and nothing else — ever."
      />

      <AdSlot
        placement="sidebar"
        variant="card"
      />
    </div>
  </aside>
</template>

<style scoped>
.aside {
  /* Clears the sticky site header. */
  --aside-top: 5rem;
  /* A sticky child can only travel inside its parent's box, so the column has
     to be as tall as the row it is in. This was the whole reason the sidebar
     never stuck: the grid was `align-items: start`, which shrank this element
     to its own content height and left the sticky inner div nowhere to go. */
  height: 100%;
}

/* Stuck to the top, and exactly as tall as what is in it.
   It was `100vh` with `overflow-y: auto`, which gave the page a second
   scrollbar and a column of dead space under the last card — a scrolling region
   inside a scrolling page is one of the few things guaranteed to make a mouse
   wheel do the wrong thing. Three cards fit a laptop window comfortably now
   that the totals have moved to the top of the page. */
.aside__inner {
  position: sticky;
  top: var(--aside-top);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* The ad slot and the sign-up card carry their own vertical margins for the
   in-article contexts they normally appear in. Here the column sets the
   spacing and nothing else does. */
.aside__inner > * {
  margin-block: 0;
}

.aside__card {
  padding: 1rem;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
}

.aside__title {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ui-text-dimmed);
}

.aside__jump {
  list-style: none;
  margin: 0.6rem 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.aside__jump a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.5rem;
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  transition:
    color var(--dgm-t-fast) var(--dgm-ease),
    background-color var(--dgm-t-fast) var(--dgm-ease);
}

.aside__jump a:hover {
  color: var(--ui-text-highlighted);
  background: var(--ui-bg-elevated);
}

.aside__jump a[data-active] {
  color: var(--ui-primary);
  background: color-mix(in oklab, var(--ui-primary) 10%, transparent);
  font-weight: 500;
}

.aside__count {
  margin-left: auto;
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-dimmed);
}

@media (prefers-reduced-motion: reduce) {
  .aside__jump a {
    transition: none;
  }
}
</style>
