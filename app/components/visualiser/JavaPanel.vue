<script setup lang="ts">
import type { JavaVisualData } from '~/utils/runners/java-analyse'
import type { RunStage } from '~/utils/runners/visualise'

defineProps<{
  data: JavaVisualData
  stages: RunStage[]
}>()

const kindIcon = {
  class: 'i-lucide-box',
  method: 'i-lucide-function-square',
  field: 'i-lucide-variable'
} as const
</script>

<template>
  <div class="space-y-8">
    <!-- The mechanism, before the details about it. -->
    <VisualiserJavaFlow
      :stages="stages"
      :data="data"
    />

    <!-- Where the compiler pointed. Given its own block because a beginner's
         first hundred hours are mostly this one number. -->
    <section
      v-if="data.errorLine"
      class="rounded-lg border-l-2 border-[color:var(--dgm-bad)] bg-elevated/50 px-4 py-3"
    >
      <p class="text-sm text-highlighted">
        <UIcon
          name="i-lucide-map-pin"
          class="size-4 inline-block -mt-0.5 text-[color:var(--dgm-bad)]"
        />
        javac stopped at <strong>line {{ data.errorLine }}</strong>.
      </p>
      <p class="dgm-label mt-1">
        Nothing ran. A compile error means the program never existed as
        bytecode — there is no crash to debug, only a sentence to fix.
      </p>
    </section>

    <!-- What the program printed, as a sequence rather than a blob. -->
    <section v-if="data.output.length">
      <h3 class="text-sm font-semibold text-highlighted mb-1">
        What it printed, in order
      </h3>
      <p class="dgm-label mb-4">
        Each line is one <code class="dgm-mono">System.out.println</code> that
        actually executed. If you expected five and got three, the loop is where
        to look.
      </p>

      <ol class="space-y-1">
        <li
          v-for="(line, index) in data.output"
          :key="index"
          class="java-out"
          :style="{ '--i': index }"
        >
          <span class="java-out__n">{{ index + 1 }}</span>
          <code class="dgm-mono text-toned whitespace-pre-wrap">{{ line }}</code>
        </li>
      </ol>
    </section>

    <!-- The outline. Marked as read-from-source, because that is what it is. -->
    <section v-if="data.members.length">
      <h3 class="text-sm font-semibold text-highlighted mb-1">
        What is in your file
      </h3>
      <p class="dgm-label mb-4">
        <UIcon
          name="i-lucide-scan-text"
          class="size-3 inline-block -mt-0.5"
        />
        Read from the source you typed, not reported by the JVM. Delete a method
        and it disappears from here.
      </p>

      <ul class="space-y-1">
        <li
          v-for="member in data.members"
          :key="`${member.line}-${member.name}`"
          class="flex items-center gap-2.5 text-sm py-1"
          :style="{ paddingLeft: `${Math.min(member.depth, 3) * 1.1}rem` }"
        >
          <UIcon
            :name="kindIcon[member.kind]"
            class="size-4 shrink-0"
            :class="member.kind === 'class' ? 'text-primary' : member.kind === 'method' ? 'text-secondary' : 'text-dimmed'"
          />
          <code class="dgm-mono text-toned truncate">{{ member.signature }}</code>
          <span class="dgm-label ms-auto tabular-nums shrink-0">L{{ member.line }}</span>
        </li>
      </ul>
    </section>

    <section>
      <h3 class="text-sm font-semibold text-highlighted mb-3">
        Shape of the program
      </h3>
      <dl class="grid grid-cols-3 gap-3">
        <div
          v-for="metric in [
            { label: 'Loops', value: data.loops, icon: 'i-lucide-repeat' },
            { label: 'Branches', value: data.conditionals, icon: 'i-lucide-git-fork' },
            { label: 'Print calls', value: data.prints, icon: 'i-lucide-terminal' }
          ]"
          :key="metric.label"
          class="dgm-box px-3 py-2.5"
        >
          <dt class="flex items-center gap-1.5 dgm-label">
            <UIcon
              :name="metric.icon"
              class="size-3.5"
            />
            {{ metric.label }}
          </dt>
          <dd class="font-display text-xl font-bold text-highlighted tabular-nums mt-0.5">
            {{ metric.value }}
          </dd>
        </div>
      </dl>
    </section>
  </div>
</template>

<style scoped>
.java-out {
  display: flex;
  align-items: baseline;
  gap: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-xs);
  background: var(--ui-bg-elevated);
  animation: java-out-in var(--dgm-t-base) var(--dgm-ease) backwards;
  animation-delay: calc(var(--i) * 0.06s);
}

@keyframes java-out-in {
  from { opacity: 0; transform: translateX(-4px) }
  to { opacity: 1; transform: none }
}

.java-out__n {
  font-size: 0.6875rem;
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
  min-width: 1.25rem;
  text-align: right;
  flex-shrink: 0;
}
</style>
