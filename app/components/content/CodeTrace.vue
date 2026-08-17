<script setup lang="ts">
import { codeTraceKey } from '~/utils/diagram-keys'

/**
 * ::code-trace — step through a code block one highlighted region at a time.
 *
 * The Pluralsight move: the code sits still and the explanation walks down it.
 * The lines are highlighted in place rather than re-printed, so the reader never
 * loses the shape of the program.
 *
 * ```md
 * ::code-trace
 *   ```java [Admissions.java]
 *   int seats = 60;
 *   int applicants = 4200;
 *   double ratio = applicants / seats;
 *   ```
 *
 *   :::code-trace-step{lines="1-2" caption="Both are ints. Nothing wrong yet."}
 *   :::
 *   :::code-trace-step{lines="3" caption="Integer division. ratio is 70.0, not 70.0 — the fraction was thrown away before the double ever saw it."}
 *   :::
 * ::
 * ```
 *
 * Without JavaScript the reader gets the whole code block followed by the
 * captions as an ordered list, each labelled with its line numbers — which is
 * the same information, just not animated.
 */
defineProps<{
  label?: string
  caption?: string
}>()

interface Step { lines: string, caption?: string }

const steps = ref<Step[]>([])
const active = ref(0)

provide(codeTraceKey, {
  register: (step: Step) => steps.value.push(step) - 1,
  active: computed(() => active.value)
})

/** "3", "1-2", "3,7-9" → a set of 1-based line numbers. */
function parseLines(spec: string): Set<number> {
  const out = new Set<number>()

  for (const part of String(spec).split(',')) {
    const bounds = part.trim().split('-').map(n => Number.parseInt(n, 10))
    const from = bounds[0]
    if (from === undefined || Number.isNaN(from)) {
      continue
    }

    const to = bounds[1]
    const last = to === undefined || Number.isNaN(to) ? from : to

    for (let n = from; n <= last; n++) {
      out.add(n)
    }
  }

  return out
}

const code = useTemplateRef<HTMLElement>('code')
const armed = ref(false)

/**
 * Shiki has already emitted one `.line` span per line inside the rendered code
 * block. Marking those is far cheaper and far more faithful than re-highlighting
 * the source — the tokens, the theme and the language are whatever the rest of
 * the site uses.
 */
function paint() {
  const lines = code.value?.querySelectorAll('pre code .line')
  if (!lines?.length) {
    return
  }

  const wanted = parseLines(steps.value[active.value]?.lines || '')

  lines.forEach((line, index) => {
    line.classList.toggle('ct-on', wanted.has(index + 1))
    line.classList.toggle('ct-off', wanted.size > 0 && !wanted.has(index + 1))
  })
}

onMounted(async () => {
  await nextTick()
  if (steps.value.length) {
    armed.value = true
    paint()
  }
})

watch(active, paint)

function go(index: number) {
  active.value = Math.max(0, Math.min(steps.value.length - 1, index))
}
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-scan-line"
  >
    <div
      ref="code"
      class="code-trace"
      :class="armed && 'code-trace--armed'"
    >
      <slot />
    </div>

    <!-- The steps render as a plain ordered list until JavaScript turns them
         into a walkthrough. Both are readable; only one is interactive. -->
    <div
      v-if="armed"
      class="code-trace__panel"
    >
      <div class="flex items-center gap-2 mb-2">
        <UButton
          icon="i-lucide-chevron-left"
          size="xs"
          color="neutral"
          variant="ghost"
          :disabled="active === 0"
          aria-label="Previous step"
          @click="go(active - 1)"
        />
        <span class="dgm-label tabular-nums">
          Step {{ active + 1 }} of {{ steps.length }}
        </span>
        <UButton
          icon="i-lucide-chevron-right"
          size="xs"
          color="neutral"
          variant="ghost"
          :disabled="active === steps.length - 1"
          aria-label="Next step"
          @click="go(active + 1)"
        />

        <span class="flex-1" />

        <button
          v-for="(step, index) in steps"
          :key="index"
          type="button"
          class="code-trace__dot"
          :data-on="index === active || undefined"
          :aria-label="`Step ${index + 1}`"
          @click="go(index)"
        />
      </div>

      <p
        class="code-trace__caption"
        aria-live="polite"
      >
        {{ steps[active]?.caption }}
      </p>
    </div>

    <ol
      v-else
      class="code-trace__fallback"
    >
      <li
        v-for="(step, index) in steps"
        :key="index"
      >
        <span class="dgm-mono text-primary">L{{ step.lines }}</span>
        {{ step.caption }}
      </li>
    </ol>

    <slot name="steps" />
  </DgmFigure>
</template>

<style scoped>
/* Dimming the lines that are not the point, rather than colouring the ones that
   are, keeps the code readable at every step. */
.code-trace--armed :deep(pre code .line) {
  transition: opacity var(--dgm-t-fast) var(--dgm-ease), background-color var(--dgm-t-fast) var(--dgm-ease);
  display: inline-block;
  width: 100%;
}

.code-trace--armed :deep(pre code .line.ct-off) {
  opacity: 0.32;
}

.code-trace--armed :deep(pre code .line.ct-on) {
  background: var(--dgm-accent-soft);
  box-shadow: inset 2px 0 0 var(--dgm-accent);
}

.code-trace__panel {
  margin-top: 0.75rem;
  padding: 0.75rem 0.875rem;
  border: 1px solid var(--dgm-box-border);
  border-radius: var(--dgm-box-radius);
  background: var(--dgm-box-bg);
}

.code-trace__caption {
  font-size: 0.875rem;
  line-height: 1.6;
  color: var(--ui-text-toned);
  min-height: 2.8em;
}

.code-trace__dot {
  width: 0.4375rem;
  height: 0.4375rem;
  border-radius: 999px;
  background: var(--ui-bg-accented);
  transition: background-color var(--dgm-t-fast) var(--dgm-ease);
}

.code-trace__dot[data-on] {
  background: var(--dgm-accent);
}

.code-trace__fallback {
  margin-top: 0.75rem;
  padding-left: 1.25rem;
  font-size: 0.875rem;
  color: var(--dgm-label);
  line-height: 1.6;
}

.code-trace__fallback li {
  margin-bottom: 0.375rem;
}
</style>
