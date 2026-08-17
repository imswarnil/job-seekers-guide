<script setup lang="ts">
import type { RunnerLang } from '~/utils/runners/types'
import { runnerLabels, runnerWeight } from '~/utils/runners/types'

/**
 * ::runner — runnable code inside a lesson.
 *
 * ```md
 * ::runner{lang="java" stdin="4200\n60"}
 * ```java
 * public class Main {
 *   public static void main(String[] args) {
 *     System.out.println(4200 / 60);
 *   }
 * }
 * ```
 * ::
 * ```
 *
 * The starter code is a fenced block so it is syntax-highlighted in the document
 * and readable with no JavaScript at all — a reader who never presses Run sees
 * exactly the code they would have run. The editor replaces it on mount, and the
 * language runtime does not download until the first click.
 */
const props = withDefaults(defineProps<{
  lang?: RunnerLang
  /** Starter code. Usually the fenced block in the slot instead. */
  starter?: string
  /** Piped to standard input. */
  stdin?: string
  /** Rows of editor. */
  rows?: number
  /** Hide the editor and only offer Run — for "watch this happen" examples. */
  readonly?: boolean
}>(), {
  lang: 'javascript',
  rows: 10
})

const { run, running, loading, result } = useRunner(() => props.lang)

const source = ref('')
const ready = ref(false)
const block = useTemplateRef<HTMLElement>('block')

// The source of truth is the rendered code block, so authors write ordinary
// markdown and the editor starts from exactly what the page already shows.
onMounted(async () => {
  await nextTick()
  source.value = props.starter ?? block.value?.querySelector('pre code')?.textContent?.replace(/\n$/, '') ?? ''
  ready.value = true
})

const original = ref('')
watch(ready, value => value && (original.value = source.value))

const dirty = computed(() => ready.value && source.value !== original.value)
const visual = computed(() => props.lang === 'html' || props.lang === 'css')
const weight = computed(() => runnerWeight[props.lang])
</script>

<template>
  <div class="dgm-box runner not-prose">
    <div class="runner__head">
      <UIcon
        name="i-lucide-play"
        class="size-3.5 text-secondary shrink-0"
      />
      <span class="text-xs font-medium text-highlighted">
        Run it — {{ runnerLabels[lang] }}
      </span>

      <span class="flex-1" />

      <UButton
        v-if="dirty"
        label="Reset"
        size="xs"
        color="neutral"
        variant="ghost"
        icon="i-lucide-rotate-ccw"
        @click="source = original"
      />

      <ClientOnly>
        <UButton
          :label="running ? 'Running' : 'Run'"
          :loading="running"
          size="xs"
          icon="i-lucide-play"
          :disabled="!ready"
          @click="run(source, stdin)"
        />
      </ClientOnly>
    </div>

    <!-- Before mount this is the highlighted markdown code block; after mount
         the textarea takes over. Both show the same characters. -->
    <div
      v-show="!ready || readonly"
      ref="block"
      class="runner__block"
    >
      <slot />
    </div>

    <textarea
      v-if="ready && !readonly"
      v-model="source"
      class="runner__editor dgm-mono"
      :rows="rows"
      spellcheck="false"
      autocapitalize="off"
      autocomplete="off"
      autocorrect="off"
      :aria-label="`${runnerLabels[lang]} editor`"
    />

    <p
      v-if="stdin"
      class="runner__stdin dgm-mono"
    >
      <span class="dgm-label">stdin</span> {{ stdin }}
    </p>

    <p
      v-if="weight"
      class="runner__note"
    >
      {{ weight }}
    </p>

    <div
      v-if="running && loading"
      class="runner__out runner__out--note"
    >
      {{ loading }}
    </div>

    <template v-else-if="result">
      <!-- html and css produce a page, not a stream of text. -->
      <iframe
        v-if="visual && result.html"
        :srcdoc="result.html"
        sandbox="allow-scripts"
        class="runner__preview"
        title="Output"
      />

      <pre
        v-if="result.stdout"
        class="runner__out"
      >{{ result.stdout }}</pre>

      <pre
        v-if="result.stderr"
        class="runner__out runner__out--error"
      >{{ result.stderr }}</pre>

      <p class="runner__timing">
        {{ result.ok ? 'Finished' : 'Failed' }} in {{ result.ms }} ms
      </p>
    </template>
  </div>
</template>

<style scoped>
.dgm-box.runner {
  margin-block: 1.75rem;
  overflow: hidden;
}

.runner__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid var(--dgm-box-border);
  background: var(--ui-bg);
}

.runner__block :deep(pre) {
  margin: 0;
  border-radius: 0;
  border: 0;
}

.runner__editor {
  display: block;
  width: 100%;
  padding: 0.875rem;
  border: 0;
  resize: vertical;
  background: var(--ui-bg);
  color: var(--ui-text-highlighted);
  line-height: 1.6;
  tab-size: 2;
}

.runner__editor:focus {
  outline: 2px solid var(--dgm-accent);
  outline-offset: -2px;
}

.runner__stdin,
.runner__note {
  padding: 0.5rem 0.875rem;
  border-top: 1px solid var(--dgm-box-border);
  font-size: 0.75rem;
  color: var(--dgm-label);
  white-space: pre-wrap;
}

.runner__note {
  font-family: inherit;
}

.runner__out {
  margin: 0;
  padding: 0.75rem 0.875rem;
  border-top: 1px solid var(--dgm-box-border);
  background: var(--ui-bg);
  font-family: var(--font-mono);
  font-size: 0.8125rem;
  line-height: 1.6;
  white-space: pre-wrap;
  overflow-x: auto;
  color: var(--ui-text-toned);
}

.runner__out--error {
  color: var(--dgm-bad);
}

.runner__out--note {
  color: var(--dgm-label);
  font-family: inherit;
}

.runner__preview {
  display: block;
  width: 100%;
  height: 16rem;
  border: 0;
  border-top: 1px solid var(--dgm-box-border);
  background: white;
}

.runner__timing {
  padding: 0.375rem 0.875rem;
  border-top: 1px solid var(--dgm-box-border);
  font-size: 0.6875rem;
  color: var(--dgm-dim);
  font-variant-numeric: tabular-nums;
}
</style>
