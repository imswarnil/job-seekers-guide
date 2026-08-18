<script setup lang="ts">
import type { SqlVisualData } from '~/utils/runners/sql-analyse'

const props = defineProps<{
  data: SqlVisualData
}>()

/** Bars are relative to the widest step, so the drop is visible at any scale. */
const peak = computed(() => Math.max(1, ...props.data.funnel.map(step => step.rows)))

function width(rows: number) {
  return `${Math.max(2, (rows / peak.value) * 100)}%`
}
</script>

<template>
  <div class="space-y-8">
    <!-- The rows moving, before the numbers about them. Somebody who watches
         this and reads nothing else has understood what a WHERE clause does. -->
    <VisualiserSqlFlow
      v-if="data.funnel.length"
      :funnel="data.funnel"
    />

    <!-- The funnel. Every number here was obtained by running a cut-down version
         of the query against the real data — change a WHERE and the figures move. -->
    <section v-if="data.funnel.length">
      <h3 class="text-sm font-semibold text-highlighted mb-1">
        What each clause did to the rows
      </h3>
      <p class="dgm-label mb-4">
        Measured by running the query with its later clauses removed and counting
        what came back. These are your rows, not an example.
      </p>

      <ol class="space-y-3">
        <li
          v-for="(step, index) in data.funnel"
          :key="index"
          class="funnel__step"
          :style="{ '--i': index }"
        >
          <div class="flex items-baseline gap-2 mb-1 flex-wrap">
            <span class="text-sm font-medium text-highlighted">{{ step.label }}</span>
            <code class="dgm-mono text-xs text-muted truncate">{{ step.clause }}</code>

            <span class="ms-auto flex items-baseline gap-2">
              <span
                v-if="step.delta"
                class="text-xs tabular-nums"
                :class="step.delta < 0 ? 'text-[color:var(--dgm-bad)]' : 'text-[color:var(--dgm-good)]'"
              >{{ step.delta > 0 ? '+' : '' }}{{ step.delta }}</span>
              <span class="font-display font-bold text-highlighted tabular-nums">{{ step.rows }}</span>
            </span>
          </div>

          <div class="funnel__track">
            <div
              class="funnel__bar"
              :style="{ width: width(step.rows) }"
            />
          </div>
        </li>
      </ol>
    </section>

    <p
      v-else-if="data.funnelNote"
      class="text-sm text-muted rounded-lg border border-default bg-elevated/40 px-4 py-3"
    >
      {{ data.funnelNote }}
    </p>

    <!-- SQLite's own plan, verbatim. -->
    <section v-if="data.plan.length">
      <h3 class="text-sm font-semibold text-highlighted mb-1">
        How SQLite decided to find them
      </h3>
      <p class="dgm-label mb-4">
        This is <code class="dgm-mono">EXPLAIN QUERY PLAN</code> for your exact
        statement. <strong>SCAN</strong> means it read every row;
        <strong>SEARCH</strong> means it used an index. That difference is what
        an index is for, and it is the answer to half of "why is this slow".
      </p>

      <ul class="space-y-1.5">
        <li
          v-for="row in data.plan"
          :key="row.id"
          class="flex items-start gap-2.5 text-sm"
          :style="{ paddingLeft: `${row.parent ? 1.25 : 0}rem` }"
        >
          <UIcon
            :name="row.detail.startsWith('SCAN') ? 'i-lucide-scan-line' : row.detail.startsWith('SEARCH') ? 'i-lucide-zap' : 'i-lucide-dot'"
            class="size-4 mt-0.5 shrink-0"
            :class="row.detail.startsWith('SEARCH') ? 'text-[color:var(--dgm-good)]' : 'text-muted'"
          />
          <code class="dgm-mono text-muted">{{ row.detail }}</code>
        </li>
      </ul>
    </section>

    <!-- The rows themselves. -->
    <section v-if="data.columns.length">
      <h3 class="text-sm font-semibold text-highlighted mb-3">
        What came back
      </h3>

      <div class="dgm-scroll rounded-lg border border-default">
        <table class="w-full text-sm dgm-mono">
          <thead>
            <tr>
              <th
                v-for="column in data.columns"
                :key="column"
                class="text-left px-3 py-2 bg-elevated text-muted font-semibold whitespace-nowrap border-b border-default"
              >
                {{ column }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in data.rows.slice(0, 50)"
              :key="index"
              class="border-b border-default last:border-0"
            >
              <td
                v-for="(cell, c) in row"
                :key="c"
                class="px-3 py-1.5 text-toned whitespace-nowrap"
              >
                {{ cell === null ? 'NULL' : cell }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p
        v-if="data.rows.length > 50"
        class="dgm-label mt-2"
      >
        Showing 50 of {{ data.rows.length }} rows.
      </p>
    </section>
  </div>
</template>

<style scoped>
.funnel__track {
  height: 0.5rem;
  border-radius: 999px;
  background: var(--ui-bg-accented);
  overflow: hidden;
}

.funnel__bar {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(to right, var(--ui-primary), var(--ui-secondary));
  transition: width var(--dgm-t-slow) var(--dgm-ease);
  transition-delay: calc(var(--i) * 0.1s);
}
</style>
