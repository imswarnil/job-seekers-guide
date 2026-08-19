<script setup lang="ts">
/**
 * ::side-note — the four asides the writing guidelines ask for that are not
 * about tone.
 *
 * `::note`, `::tip`, `::warning` and `::caution` say how alarmed to be.
 * These four say what *kind* of concern this is, which is a different axis: a
 * security note and a performance note can both be calm, and a reader skimming
 * for one should be able to find it.
 *
 * Deliberately monochrome. Red is reserved for the live wire — the error, the
 * wrong number, the danger — and amber for milestones, so an aside that is
 * merely *about* security does not get to spend either. The icon and the label
 * carry the meaning.
 *
 * ```md
 * ::side-note{kind="security"}
 * Row-level security runs in the database. If you enforce it in the interface
 * instead, anyone with the API key walks straight past it.
 * ::
 * ```
 */
const props = withDefaults(defineProps<{
  kind: 'accessibility' | 'performance' | 'security' | 'go-deeper'
  /** Overrides the default label for this kind. */
  title?: string
}>(), {
  title: undefined
})

/**
 * Explicit rather than computed from `kind`, so a typo in markdown fails
 * visibly here instead of rendering an unlabelled grey box in a lesson.
 */
const KINDS = {
  'accessibility': { label: 'Accessibility', icon: 'i-lucide-accessibility' },
  'performance': { label: 'Performance', icon: 'i-lucide-gauge' },
  'security': { label: 'Security', icon: 'i-lucide-shield' },
  'go-deeper': { label: 'Go deeper', icon: 'i-lucide-compass' }
} as const

const meta = computed(() => KINDS[props.kind] ?? KINDS['go-deeper'])
</script>

<template>
  <aside
    class="side-note not-prose"
    :class="`side-note--${kind}`"
  >
    <p class="side-note__head">
      <UIcon
        :name="meta.icon"
        class="size-4"
      />
      {{ title ?? meta.label }}
    </p>

    <div class="side-note__body">
      <slot />
    </div>
  </aside>
</template>

<style scoped>
.side-note {
  margin-block: 1.75rem;
  padding: 0.875rem 1.125rem;
  border-radius: var(--dgm-box-radius);
  border: 1px solid var(--dgm-box-border);
  border-left-width: 3px;
  background: var(--dgm-box-bg);
}

/* "Go deeper" is optional reading, so it sits back a step from the other three. */
.side-note--go-deeper {
  border-left-color: var(--dgm-dim);
  background: transparent;
}

.side-note__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--dgm-label);
  margin-bottom: 0.5rem;
}

.side-note__body {
  color: var(--ui-text-toned);
  font-size: 0.9375rem;
  line-height: 1.7;
}

.side-note__body :deep(> *:last-child) {
  margin-bottom: 0;
}

.side-note__body :deep(p) {
  margin-bottom: 0.75rem;
}
</style>
