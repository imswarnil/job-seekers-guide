<script setup lang="ts">
/**
 * ::prompt — a prompt the reader is meant to copy and run against an AI tool.
 *
 * The job-search part of the path teaches AI as a tool rather than a shortcut,
 * which means handing over the actual words. The "why" slot exists because a
 * prompt with no explanation teaches copying, not judgement.
 *
 * ```md
 * ::ai-prompt{title="Rewrite your resume bullets" model="Any"}
 * You are a hiring manager at a mid-size product company. Here are six bullets
 * from my resume. For each, tell me what a reader learns about my impact...
 *
 *   #why
 *   It asks for a critique, not a rewrite. A rewrite gives you somebody else's
 *   words; a critique tells you what yours were missing.
 * ::
 * ```
 */
defineProps<{
  title?: string
  /** Which tool this was written for, when it matters. */
  model?: string
  tags?: string[]
}>()

const slots = useSlots()

const body = useTemplateRef<HTMLElement>('body')
const source = ref('')

const { copy, copied, isSupported } = useClipboard({ source, copiedDuring: 2000 })

// Read the rendered text rather than a prop, so authors write the prompt as
// ordinary markdown and still get an exact copy of what they see.
onMounted(() => {
  source.value = body.value?.innerText.trim() || ''
})
</script>

<template>
  <div class="dgm-box prompt not-prose">
    <div class="prompt__head">
      <UIcon
        name="i-lucide-sparkles"
        class="size-4 text-secondary shrink-0"
      />
      <span class="font-medium text-sm text-highlighted flex-1 min-w-0">
        {{ title || 'Prompt' }}
      </span>

      <UBadge
        v-if="model"
        :label="model"
        color="neutral"
        variant="subtle"
        size="sm"
      />

      <ClientOnly>
        <UButton
          v-if="isSupported"
          :icon="copied ? 'i-lucide-check' : 'i-lucide-copy'"
          :label="copied ? 'Copied' : 'Copy'"
          size="xs"
          color="neutral"
          variant="ghost"
          @click="copy(source)"
        />
      </ClientOnly>
    </div>

    <!-- Selectable text, not an image of text — it is still copyable by hand
         with no JavaScript at all. -->
    <div
      ref="body"
      class="prompt__body"
    >
      <slot />
    </div>

    <div
      v-if="slots.why"
      class="prompt__why"
    >
      <p class="dgm-label font-medium uppercase tracking-wider mb-1.5">
        Why this prompt
      </p>
      <slot name="why" />
    </div>

    <div
      v-if="tags?.length"
      class="flex flex-wrap gap-1.5 mt-3"
    >
      <UBadge
        v-for="tag in tags"
        :key="tag"
        :label="tag"
        color="neutral"
        variant="subtle"
        size="sm"
      />
    </div>
  </div>
</template>

<style scoped>
.dgm-box.prompt {
  margin-block: 1.75rem;
  overflow: hidden;
}

.prompt__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 0.875rem;
  border-bottom: 1px solid var(--dgm-box-border);
  background: var(--ui-bg);
}

.prompt__body {
  padding: 0.875rem;
  font-family: var(--font-mono);
  font-size: 0.8125rem;
  line-height: 1.65;
  color: var(--ui-text-toned);
  white-space: pre-wrap;
}

.prompt__body :deep(> *:last-child) {
  margin-bottom: 0;
}

.prompt__body :deep(p) {
  margin-bottom: 0.75rem;
}

.prompt__why {
  padding: 0.875rem;
  border-top: 1px solid var(--dgm-box-border);
  background: var(--ui-bg);
  font-size: 0.8125rem;
  color: var(--dgm-label);
  line-height: 1.6;
}

.prompt__why :deep(> *:last-child) {
  margin-bottom: 0;
}
</style>
