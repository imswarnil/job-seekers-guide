<script setup lang="ts">
/**
 * ::flow — a sequence of labelled steps with arrows between them.
 *
 * For "how a request travels", "how a program runs", "what happens when you
 * push". The steps draw themselves in order as the diagram scrolls into view;
 * without JavaScript the finished diagram is simply there.
 *
 * ```md
 * ::flow{direction="horizontal" numbered caption="From text to a running process"}
 *   :::flow-step{label="You write text" icon="i-lucide-file-code"}
 *   `Admissions.java` is just characters in a file.
 *   :::
 *   :::flow-step{label="javac compiles" icon="i-lucide-cog"}
 *   Produces `Admissions.class` — bytecode, not machine code.
 *   :::
 * ::
 * ```
 */
const props = withDefaults(defineProps<{
  /** `horizontal` folds to vertical below `sm` on its own. */
  direction?: 'horizontal' | 'vertical'
  /** Number the steps. */
  numbered?: boolean
  label?: string
  caption?: string
}>(), {
  direction: 'horizontal'
})

const { classes } = useReveal()

// Steps claim their index on setup, which drives both the number they show and
// their place in the stagger. Claiming during setup is deterministic: children
// set up in document order.
let cursor = 0

provide(flowKey, {
  numbered: computed(() => Boolean(props.numbered)),
  direction: computed(() => props.direction),
  claim: () => cursor++
})
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-git-commit-horizontal"
    :scroll="direction === 'horizontal'"
  >
    <div
      ref="reveal"
      class="flow"
      :class="[classes, direction === 'horizontal' ? 'flow--h' : 'flow--v']"
    >
      <slot />
    </div>
  </DgmFigure>
</template>

<style scoped>
.flow {
  display: flex;
  gap: var(--dgm-gap);
  flex-direction: column;
}

.flow--h {
  align-items: stretch;
}

@media (min-width: 640px) {
  .flow--h {
    flex-direction: row;
  }
}
</style>
