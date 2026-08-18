<script setup lang="ts">
import { repo, repoUrl } from '~/utils/links'

/**
 * Star count, fetched once at build time.
 *
 * Deliberately not a client fetch. A nav element that waits on api.github.com is
 * a nav element that is sometimes empty and always costs a request on every page
 * load — and the number is decoration, not information. It goes stale between
 * deploys, which is fine: nobody is counting.
 *
 * The button works regardless. If the count is missing it simply does not show,
 * rather than rendering a zero that looks like a fact.
 */
const { data: stars } = await useAsyncData('github-stars', async () => {
  try {
    const data = await $fetch<{ stargazers_count?: number }>(
      `https://api.github.com/repos/${repo.owner}/${repo.name}`,
      { headers: { accept: 'application/vnd.github+json' }, timeout: 4000 }
    )
    return data.stargazers_count ?? null
  } catch {
    // Rate limited, offline, repo private — none of which should fail a build.
    return null
  }
}, { server: true, default: () => null })

const formatted = computed(() => {
  const count = stars.value
  if (count === null || count === undefined) {
    return ''
  }
  return count >= 1000 ? `${(count / 1000).toFixed(1)}k` : String(count)
})
</script>

<template>
  <UButton
    :to="repoUrl"
    target="_blank"
    rel="noopener"
    color="neutral"
    variant="ghost"
    size="sm"
    icon="i-simple-icons-github"
    :aria-label="`Star Job Seekers Guide on GitHub${formatted ? ` — ${formatted} stars` : ''}`"
    class="github-star"
  >
    <span class="hidden xl:inline">Star</span>
    <span
      v-if="formatted"
      class="github-star__count"
    >{{ formatted }}</span>
  </UButton>
</template>

<style scoped>
.github-star__count {
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  padding: 0.05rem 0.35rem;
  border-radius: var(--radius-xs);
  background: var(--ui-bg-accented);
  color: var(--ui-text-muted);
}
</style>
