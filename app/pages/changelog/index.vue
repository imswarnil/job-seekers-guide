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

/** `1.2.0` → `1-2-0`, so a release has an address somebody can send. */
const anchor = (version?: string, fallback = '') =>
  (version ? `v${version}` : fallback).toLowerCase().replace(/[^\w]+/g, '-')

const latest = computed(() => versions.value?.[0])

const site = useSiteConfig()

/* Releases are the one thing on this site that genuinely is a feed of dated
   articles, so they are marked up as one. */
useSchemaOrg([
  defineItemList({
    name: 'Releases',
    itemListElement: (versions.value || []).map(version => ({
      '@type': 'TechArticle',
      'headline': version.title,
      'description': version.description,
      'datePublished': new Date(version.date).toISOString(),
      'url': `${site.url}/changelog#${anchor(version.version, version.title)}`
    }))
  })
])
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

      <p
        v-if="latest?.version"
        class="mt-5 inline-flex items-center gap-2 text-sm"
      >
        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 text-primary px-2.5 py-1 text-xs font-semibold tabular-nums">
          <UIcon
            name="i-lucide-tag"
            class="size-3"
          />
          v{{ latest.version }}
        </span>
        <span class="text-dimmed">latest, {{ when(latest.date) }}</span>
      </p>
    </div>

    <!-- One spine down the left with a cover hanging off each release. A list
         of dated headings does not read as a history; a line does. -->
    <ol class="log">
      <li
        v-for="version in versions"
        :id="anchor(version.version, version.title)"
        :key="version.path"
        class="log__entry"
      >
        <span
          class="log__node"
          aria-hidden="true"
        />

        <div class="log__cover">
          <ReleaseThumb
            :version="version.version"
            :codename="version.codename"
            :date="version.date"
          />
        </div>

        <div class="min-w-0">
          <p class="log__meta">
            <span
              v-if="version.version"
              class="log__version"
            >v{{ version.version }}</span>
            <time
              :datetime="String(version.date)"
              class="tabular-nums"
            >{{ when(version.date) }}</time>
            <a
              :href="`#${anchor(version.version, version.title)}`"
              class="log__link"
              aria-label="Link to this release"
            >
              <UIcon
                name="i-lucide-link"
                class="size-3"
              />
            </a>
          </p>

          <h2 class="log__title">
            {{ version.title }}
          </h2>
          <p class="log__desc">
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
                class="log__badge"
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
.log {
  list-style: none;
  margin: 3rem 0 0;
  padding: 0 0 0 2rem;
  position: relative;
}

/* The spine, drawn once behind every entry. */
.log::before {
  content: '';
  position: absolute;
  left: 0.28rem;
  top: 0.75rem;
  bottom: 2rem;
  width: 2px;
  border-radius: 999px;
  background: linear-gradient(to bottom, var(--ui-primary), var(--ui-border-accented));
  opacity: 0.4;
}

.log__entry {
  position: relative;
  display: grid;
  gap: 1.25rem;
  padding-bottom: 3.5rem;
  /* Anchored links land under the sticky header without it. */
  scroll-margin-top: calc(var(--ui-header-height) + 2rem);
}

@media (min-width: 900px) {
  .log__entry {
    grid-template-columns: 15rem minmax(0, 1fr);
    gap: 2.5rem;
    align-items: start;
  }
}

.log__node {
  position: absolute;
  left: -2rem;
  top: 0.55rem;
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 999px;
  background: var(--ui-bg);
  border: 2px solid var(--ui-primary);
}

.log__entry:first-child .log__node {
  background: var(--ui-primary);
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--ui-primary) 16%, transparent);
}

.log__cover {
  max-width: 15rem;
}

.log__meta {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
}

.log__version {
  font-weight: 700;
  color: var(--ui-primary);
  font-variant-numeric: tabular-nums;
}

.log__link {
  color: var(--ui-text-dimmed);
  opacity: 0;
  transition: opacity var(--dgm-t-fast) var(--dgm-ease);
}

.log__entry:hover .log__link,
.log__link:focus-visible {
  opacity: 1;
}

.log__title {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.015em;
  color: var(--ui-text-highlighted);
  margin-top: 0.35rem;
  text-wrap: balance;
}

.log__desc {
  font-size: 0.9375rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  text-wrap: pretty;
}

.log__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  flex-shrink: 0;
  padding: 0.1rem 0.45rem;
  border-radius: var(--radius-xs);
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-top: 0.15rem;
}

@media (prefers-reduced-motion: reduce) {
  .log__link {
    transition: none;
  }
}
</style>
