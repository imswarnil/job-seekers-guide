<script setup lang="ts">
import type { JavaVisualData } from '~/utils/runners/java-analyse'
import type { RunStage } from '~/utils/runners/visualise'

/**
 * Source becoming bytecode becoming output, animated.
 *
 * The stage strip already says what happened. This shows it: a file travels into
 * `javac`, a class file drops out the other side, and it travels into the JVM,
 * which prints. When compilation failed, the file visibly stops at `javac` and
 * nothing comes out — which is the single most useful thing a beginner can learn
 * from a Java error, and it is very hard to say in a sentence.
 *
 * Every state here comes from the run: whether compilation succeeded, how long
 * each stage took, what actually printed. Nothing is invented.
 */
const props = defineProps<{
  stages: RunStage[]
  data: JavaVisualData
}>()

const compileFailed = computed(() => props.stages.find(s => s.id === 'compile')?.status === 'failed')
const ranAtAll = computed(() => props.stages.find(s => s.id === 'jvm')?.status !== 'skipped')

const step = ref(0)
const reduced = useMediaQuery('(prefers-reduced-motion: reduce)')

let timer: ReturnType<typeof setTimeout> | undefined

function stop() {
  if (timer) {
    clearTimeout(timer)
    timer = undefined
  }
}

/** 0 source · 1 into javac · 2 bytecode out · 3 into the JVM · 4 printing. */
function play() {
  stop()
  step.value = 0

  const last = compileFailed.value ? 1 : 4
  const gap = reduced.value ? 250 : 750

  const advance = () => {
    if (step.value >= last) {
      return
    }
    step.value++
    timer = setTimeout(advance, gap)
  }

  timer = setTimeout(advance, gap)
}

onBeforeUnmount(stop)

watch(() => props.stages, () => nextTick(play), { immediate: true })
</script>

<template>
  <section class="jflow">
    <header class="flex items-start justify-between gap-4 flex-wrap mb-4">
      <div>
        <h3 class="text-sm font-semibold text-highlighted">
          What happened to your file
        </h3>
        <p class="dgm-label mt-1 max-w-lg">
          Text becomes bytecode becomes output. Each stage is separately
          reported, which is why a compile error can stop the whole thing before
          the JVM ever starts.
        </p>
      </div>

      <UButton
        label="Replay"
        icon="i-lucide-rotate-ccw"
        size="xs"
        color="neutral"
        variant="subtle"
        @click="play"
      />
    </header>

    <div class="jflow__track">
      <!-- Source file -->
      <div class="jflow__node">
        <UIcon
          name="i-lucide-file-code"
          class="size-5"
        />
        <span class="jflow__node-label">Main.java</span>
        <span class="jflow__node-sub">text</span>
      </div>

      <div
        class="jflow__wire"
        :data-on="step >= 1 || undefined"
      >
        <span
          class="jflow__packet"
          :data-move="step >= 1 || undefined"
        />
      </div>

      <!-- javac -->
      <div
        class="jflow__node jflow__node--machine"
        :data-state="compileFailed ? 'failed' : step >= 2 ? 'done' : step >= 1 ? 'busy' : undefined"
      >
        <UIcon
          :name="compileFailed && step >= 1 ? 'i-lucide-circle-x' : 'i-lucide-cog'"
          class="size-5"
          :class="!compileFailed && step === 1 && 'animate-spin'"
        />
        <span class="jflow__node-label">javac</span>
        <span class="jflow__node-sub">compiles</span>
      </div>

      <div
        class="jflow__wire"
        :data-on="step >= 3 || undefined"
        :data-dead="compileFailed || undefined"
      >
        <span
          class="jflow__packet"
          :data-move="step >= 3 || undefined"
        />
      </div>

      <!-- JVM -->
      <div
        class="jflow__node jflow__node--machine"
        :data-state="compileFailed ? 'never' : step >= 4 ? 'done' : step >= 3 ? 'busy' : undefined"
      >
        <UIcon
          name="i-lucide-cpu"
          class="size-5"
          :class="!compileFailed && step === 3 && 'animate-pulse'"
        />
        <span class="jflow__node-label">JVM</span>
        <span class="jflow__node-sub">{{ compileFailed ? 'never started' : 'runs it' }}</span>
      </div>

      <div
        class="jflow__wire"
        :data-on="step >= 4 || undefined"
        :data-dead="compileFailed || undefined"
      />

      <!-- Output -->
      <div
        class="jflow__node"
        :data-state="compileFailed ? 'never' : step >= 4 ? 'done' : undefined"
      >
        <UIcon
          name="i-lucide-terminal"
          class="size-5"
        />
        <span class="jflow__node-label">Output</span>
        <span class="jflow__node-sub">
          {{ compileFailed ? 'nothing' : `${data.output.length} line${data.output.length === 1 ? '' : 's'}` }}
        </span>
      </div>
    </div>

    <!-- The console, filling in as the last stage runs. -->
    <div
      v-if="ranAtAll && data.output.length"
      class="jflow__console"
    >
      <p
        v-for="(line, index) in data.output"
        :key="index"
        class="jflow__line"
        :data-shown="step >= 4 || undefined"
        :style="{ '--i': index }"
      >
        <span class="jflow__prompt">›</span>
        <code class="dgm-mono">{{ line }}</code>
      </p>
    </div>

    <p
      v-else-if="compileFailed"
      class="jflow__stopped"
    >
      <UIcon
        name="i-lucide-octagon-x"
        class="size-4 shrink-0"
      />
      Stopped at <strong>javac</strong><span v-if="data.errorLine">, line {{ data.errorLine }}</span>.
      There is no crash to debug — the program never existed as bytecode.
    </p>
  </section>
</template>

<style scoped>
.jflow__track {
  display: flex;
  align-items: center;
  gap: 0;
  padding: 1.25rem 1rem;
  border: 1px solid var(--dgm-box-border);
  border-radius: var(--dgm-box-radius);
  background: var(--dgm-box-bg);
  overflow-x: auto;
}

.jflow__node {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  flex-shrink: 0;
  min-width: 5rem;
  padding: 0.65rem 0.5rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--dgm-box-border);
  background: var(--ui-bg);
  color: var(--ui-text-dimmed);
  transition:
    border-color var(--dgm-t-base) var(--dgm-ease),
    color var(--dgm-t-base) var(--dgm-ease),
    opacity var(--dgm-t-base) var(--dgm-ease);
}

.jflow__node[data-state='busy'] {
  border-color: var(--ui-primary);
  color: var(--ui-primary);
}

.jflow__node[data-state='done'] {
  border-color: var(--dgm-good);
  color: var(--dgm-good);
}

.jflow__node[data-state='failed'] {
  border-color: var(--dgm-bad);
  color: var(--dgm-bad);
}

/* Never started is drawn dashed and faded rather than hidden — "the JVM did not
   run" is the whole lesson when compilation fails. */
.jflow__node[data-state='never'] {
  border-style: dashed;
  opacity: 0.4;
}

.jflow__node-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
}

.jflow__node[data-state='never'] .jflow__node-label {
  color: var(--ui-text-dimmed);
}

.jflow__node-sub {
  font-size: 0.625rem;
  color: var(--ui-text-dimmed);
  white-space: nowrap;
}

.jflow__wire {
  position: relative;
  flex: 1 1 1.5rem;
  min-width: 1.75rem;
  height: 2px;
  background: var(--ui-border-accented);
}

.jflow__wire[data-on] {
  background: linear-gradient(to right, var(--ui-primary), var(--dgm-good));
}

.jflow__wire[data-dead] {
  background: repeating-linear-gradient(
    to right,
    var(--ui-border) 0 4px,
    transparent 4px 8px
  );
}

.jflow__packet {
  position: absolute;
  top: 50%;
  left: 0;
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 999px;
  background: var(--ui-primary);
  transform: translate(0, -50%) scale(0);
  opacity: 0;
}

.jflow__packet[data-move] {
  animation: jflow-travel var(--dgm-t-slow) var(--dgm-ease) forwards;
}

@keyframes jflow-travel {
  0% { transform: translate(0, -50%) scale(1); opacity: 1 }
  85% { transform: translate(calc(100% * 12), -50%) scale(1); opacity: 1 }
  100% { transform: translate(calc(100% * 14), -50%) scale(0); opacity: 0 }
}

.jflow__console {
  margin-top: 0.75rem;
  padding: 0.75rem 0.875rem;
  border: 1px solid var(--dgm-box-border);
  border-radius: var(--dgm-box-radius);
  background: var(--ui-bg);
}

.jflow__line {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: var(--ui-text-toned);
  opacity: 0;
  transform: translateX(-4px);
  transition:
    opacity var(--dgm-t-base) var(--dgm-ease),
    transform var(--dgm-t-base) var(--dgm-ease);
  transition-delay: calc(var(--i) * 0.12s);
}

.jflow__line[data-shown] {
  opacity: 1;
  transform: none;
}

.jflow__prompt {
  color: var(--dgm-good);
}

.jflow__stopped {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding: 0.75rem 0.875rem;
  border-radius: var(--dgm-box-radius);
  border-left: 2px solid var(--dgm-bad);
  background: var(--ui-bg-elevated);
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  line-height: 1.6;
}
</style>
