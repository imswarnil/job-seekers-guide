<script setup lang="ts">
const route = useRoute()

const { data: page } = await useAsyncData('changelog', () => queryCollection('changelog').first())
const { data: versions } = await useAsyncData(route.path, () => queryCollection('versions').order('date', 'DESC').all())

usePageSeo({
  title: page.value?.seo?.title || page.value?.title,
  description: page.value?.seo?.description || page.value?.description,
  headline: 'Changelog'
})

/**
 * Four categories, each with its own icon and colour. A changelog is scanned,
 * not read — somebody wants to know whether the thing they were waiting for has
 * landed, and prose makes them hunt for it.
 */
const kinds = {
  feature: { icon: 'i-lucide-sparkles', label: 'New', tone: 'text-primary', bg: 'bg-primary/10' },
  fix: { icon: 'i-lucide-wrench', label: 'Fixed', tone: 'text-[color:var(--dgm-good)]', bg: 'bg-[color:var(--dgm-good)]/10' },
  content: { icon: 'i-lucide-book-open', label: 'Content', tone: 'text-secondary', bg: 'bg-secondary/10' },
  other: { icon: 'i-lucide-settings-2', label: 'Other', tone: 'text-muted', bg: 'bg-elevated' }
} as const

type Kind = keyof typeof kinds

const formatter = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
const when = (date: string | Date) => formatter.format(new Date(date))
</script>

<template>
  <UContainer class="py-10 lg:py-14">
    <div class="max-w-2xl">
      <h1 class="font-display text-3xl sm:text-4xl font-bold text-highlighted tracking-tight text-balance">
        {{ page?.title }}
      </h1>
      <p class="mt-3 text-lg text-muted text-balance">
        {{ page?.description }}
      </p>
    </div>

    <ol class="changelog mt-12">
      <li
        v-for="version in versions"
        :key="version.path"
        class="changelog__entry"
      >
        <div class="changelog__meta">
          <time
            :datetime="String(version.date)"
            class="text-xs text-dimmed tabular-nums whitespace-nowrap"
          >{{ when(version.date) }}</time>
        </div>

        <div class="min-w-0">
          <h2 class="font-display text-lg font-semibold text-highlighted text-balance">
            {{ version.title }}
          </h2>
          <p class="text-sm text-muted mt-1 text-balance">
            {{ version.description }}
          </p>

          <!-- The scannable part. One line per change, icon-led. -->
          <ul
            v-if="version.changes?.length"
            class="mt-4 space-y-2"
          >
            <li
              v-for="(change, index) in version.changes"
              :key="index"
              class="flex items-start gap-2.5"
            >
              <span
                class="changelog__badge"
                :class="[kinds[change.type as Kind].bg, kinds[change.type as Kind].tone]"
              >
                <UIcon
                  :name="kinds[change.type as Kind].icon"
                  class="size-3"
                />
                {{ kinds[change.type as Kind].label }}
              </span>
              <span class="text-sm text-toned">{{ change.text }}</span>
            </li>
          </ul>

          <!-- The long form, for anybody who wants the reasoning. Collapsed,
               because most people do not. -->
          <UCollapsible
            v-if="version.body?.value?.length"
            class="mt-4"
          >
            <UButton
              label="Why"
              icon="i-lucide-chevron-down"
              color="neutral"
              variant="ghost"
              size="xs"
              :ui="{ leadingIcon: 'group-data-[state=open]:rotate-180 transition-transform' }"
              class="group -ms-2"
            />

            <template #content>
              <div class="guide-prose text-sm pt-3">
                <ContentRenderer :value="version" />
              </div>
            </template>
          </UCollapsible>
        </div>
      </li>
    </ol>
  </UContainer>
</template>

<style scoped>
.changelog {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
}

.changelog__entry {
  display: grid;
  gap: 0.5rem 2rem;
  padding-bottom: 2.5rem;
}

@media (min-width: 768px) {
  .changelog__entry {
    grid-template-columns: 7rem 1fr;
  }
}

.changelog__meta {
  padding-top: 0.35rem;
}

.changelog__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  flex-shrink: 0;
  padding: 0.1rem 0.4rem;
  border-radius: var(--radius-xs);
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  margin-top: 0.1rem;
  min-width: 4.5rem;
}
</style>
