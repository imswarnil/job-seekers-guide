<script lang="ts">
/**
 * ::memory — boxes and pointers. Arrays, stacks, queues, linked lists, tables.
 *
 * The thing textbooks draw on a whiteboard and web courses replace with a wall
 * of prose. Every frame is rendered in the document, so with no JavaScript the
 * reader gets each state in order rather than an empty box.
 *
 * ```md
 * ::memory{kind="stack" caption="The call stack during fact(3)"}
 * ---
 * cells:
 *   - { value: "fact(1)", label: "top" }
 *   - { value: "fact(2)" }
 *   - { value: "fact(3)", label: "bottom" }
 * ---
 * ::
 * ```
 *
 * Several states become a stepper:
 *
 * ```md
 * ::memory{kind="array" caption="Insertion sort, first two passes"}
 * ---
 * frames:
 *   - label: "Start"
 *     cells: [{ value: 5 }, { value: 2 }, { value: 9 }]
 *   - label: "After pass 1"
 *     cells: [{ value: 2, state: "active" }, { value: 5, state: "active" }, { value: 9 }]
 * ---
 * ::
 * ```
 *
 * `heap` is deliberately not supported: tree layout with pointer routing is a
 * project of its own, and a wrong tree diagram is worse than a table.
 */
export interface MemoryCell {
  value?: string | number
  /** Small text under the cell — an index, a variable name, "top". */
  label?: string
  /** `active` picks the cell out; `ghost` greys it as removed or unset. */
  state?: 'active' | 'ghost'
}

export interface MemoryFrame {
  label?: string
  cells?: MemoryCell[]
  note?: string
}
</script>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  kind?: 'array' | 'stack' | 'queue' | 'list' | 'table'
  cells?: MemoryCell[]
  frames?: MemoryFrame[]
  /** Column headings, for `kind="table"`. */
  columns?: string[]
  label?: string
  caption?: string
}>(), {
  kind: 'array'
})

const frames = computed<MemoryFrame[]>(() =>
  props.frames?.length ? props.frames : [{ cells: props.cells || [] }]
)

const armed = ref(false)
const active = ref(0)

onMounted(() => {
  armed.value = frames.value.length > 1
})

const shown = computed(() => armed.value ? [frames.value[active.value]!] : frames.value)

/** A stack is drawn bottom-up; everything else reads left to right. */
const isVertical = computed(() => props.kind === 'stack' || props.kind === 'list')
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-box"
    scroll
  >
    <div
      v-for="(frame, index) in shown"
      :key="index"
      class="memory__frame"
    >
      <p
        v-if="frame.label"
        class="dgm-label mb-2"
      >
        {{ frame.label }}
      </p>

      <table
        v-if="kind === 'table'"
        class="memory__table dgm-mono"
      >
        <thead v-if="columns?.length">
          <tr>
            <th
              v-for="column in columns"
              :key="column"
            >
              {{ column }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td
              v-for="(cell, i) in frame.cells"
              :key="i"
              :data-state="cell.state"
            >
              {{ cell.value }}
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-else
        class="memory__cells"
        :class="[
          isVertical && 'memory__cells--v',
          kind === 'stack' && 'memory__cells--stack',
          kind === 'list' && 'memory__cells--list'
        ]"
      >
        <div
          v-for="(cell, i) in frame.cells"
          :key="i"
          class="memory__cell-wrap"
        >
          <div
            class="dgm-box memory__cell dgm-mono"
            :data-state="cell.state"
          >
            {{ cell.value }}
          </div>

          <span
            v-if="cell.label"
            class="dgm-label memory__cell-label"
          >{{ cell.label }}</span>

          <!-- A linked list is the one shape where the arrows between cells are
               the whole point, so it gets them. -->
          <UIcon
            v-if="kind === 'list' && i < (frame.cells?.length || 0) - 1"
            name="i-lucide-arrow-down"
            class="size-3.5 memory__link"
          />
        </div>
      </div>

      <p
        v-if="frame.note"
        class="dgm-label mt-2"
      >
        {{ frame.note }}
      </p>
    </div>

    <div
      v-if="armed"
      class="flex items-center gap-2 mt-3"
    >
      <UButton
        icon="i-lucide-chevron-left"
        size="xs"
        color="neutral"
        variant="ghost"
        :disabled="active === 0"
        aria-label="Previous frame"
        @click="active--"
      />
      <span class="dgm-label tabular-nums">{{ active + 1 }} / {{ frames.length }}</span>
      <UButton
        icon="i-lucide-chevron-right"
        size="xs"
        color="neutral"
        variant="ghost"
        :disabled="active === frames.length - 1"
        aria-label="Next frame"
        @click="active++"
      />
    </div>
  </DgmFigure>
</template>

<style scoped>
.memory__frame + .memory__frame {
  margin-top: 1.25rem;
}

.memory__cells {
  display: flex;
  gap: 0.25rem;
  align-items: flex-start;
}

.memory__cells--v {
  flex-direction: column;
  align-items: flex-start;
  gap: 0.375rem;
}

/* A stack grows upward, so the first cell in the list is the top of it. */
.memory__cells--stack {
  gap: 0;
}

.memory__cells--stack .memory__cell {
  border-radius: 0;
  margin-top: -1px;
}

.memory__cells--stack .memory__cell-wrap:first-child .memory__cell {
  border-top-left-radius: var(--dgm-box-radius);
  border-top-right-radius: var(--dgm-box-radius);
  margin-top: 0;
}

.memory__cells--stack .memory__cell-wrap:last-child .memory__cell {
  border-bottom-left-radius: var(--dgm-box-radius);
  border-bottom-right-radius: var(--dgm-box-radius);
}

.memory__cell-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.memory__cells--v .memory__cell-wrap {
  align-items: flex-start;
  flex-direction: row;
  gap: 0.5rem;
}

.memory__cells--list .memory__cell-wrap {
  flex-direction: column;
  align-items: flex-start;
}

.memory__cell {
  min-width: 3.25rem;
  padding: 0.5rem 0.75rem;
  text-align: center;
  white-space: nowrap;
}

.memory__cell[data-state='ghost'] {
  opacity: 0.4;
  border-style: dashed;
}

.memory__cell-label {
  white-space: nowrap;
}

.memory__link {
  color: var(--dgm-dim);
  margin-left: 1.25rem;
}

.memory__table {
  border-collapse: collapse;
  font-size: 0.8125rem;
}

.memory__table th,
.memory__table td {
  border: 1px solid var(--dgm-box-border);
  padding: 0.4375rem 0.75rem;
  text-align: left;
}

.memory__table th {
  background: var(--ui-bg-elevated);
  color: var(--dgm-label);
  font-weight: 600;
}

.memory__table td[data-state='active'] {
  background: var(--dgm-accent-soft);
  color: var(--dgm-accent);
}

.memory__table td[data-state='ghost'] {
  opacity: 0.45;
}
</style>
