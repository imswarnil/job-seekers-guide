<script setup lang="ts">
import { editUrl } from '~/utils/links'

/**
 * "Edit this page", and where the words came from.
 *
 * Every lesson on this site is a markdown file in a public repository, and until
 * now nothing on the page said so. That is worth fixing for two reasons: a
 * reader who spots a mistake can fix it in about ninety seconds, and a reader
 * who can see the source can check whether they are being sold something.
 */
const props = defineProps<{
  /** Repo-relative path of the file behind this page. */
  file?: string
  updatedAt?: string | Date
}>()

const formatter = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })

const updated = computed(() => props.updatedAt ? formatter.format(new Date(props.updatedAt)) : undefined)
</script>

<template>
  <div class="page-actions">
    <p class="text-xs font-semibold uppercase tracking-wider text-dimmed mb-3">
      This page
    </p>

    <div class="flex flex-col gap-1 items-start">
      <UButton
        v-if="file"
        :to="editUrl(file)"
        target="_blank"
        rel="noopener"
        label="Edit this page"
        icon="i-lucide-pencil"
        color="neutral"
        variant="ghost"
        size="sm"
        class="-ms-2"
      />

      <UButton
        :to="`${repoUrl}/issues/new?title=${encodeURIComponent(`Correction: ${$route.path}`)}`"
        target="_blank"
        rel="noopener"
        label="Report a mistake"
        icon="i-lucide-flag"
        color="neutral"
        variant="ghost"
        size="sm"
        class="-ms-2"
      />
    </div>

    <p
      v-if="updated"
      class="text-xs text-dimmed mt-3"
    >
      Last updated {{ updated }}
    </p>
  </div>
</template>

<style scoped>
.page-actions {
  border-top: 1px solid var(--ui-border);
  padding-top: 1.25rem;
}
</style>
