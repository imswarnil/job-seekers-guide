<script setup lang="ts">
/**
 * The rendering for /privacy, /terms and /contact.
 *
 * One component rather than three near-identical pages, because the only thing
 * that differs between them is which markdown file is read. Each of those
 * routes still needs its own file under `app/pages/` — that is what reserves
 * the slug from the learning path, which lives at the root of the site.
 *
 * These pages carry no ad slot, on purpose. A privacy page that explains the
 * advertising while running an advert next to it undercuts itself, and the ads
 * are already paying for the site elsewhere.
 */
const props = defineProps<{
  /** Route path of the markdown file in the `pages` collection, e.g. `/privacy`. */
  path: string
  /** Small line above the title on the social card. */
  headline?: string
}>()

const { data: page } = await useAsyncData(`page:${props.path}`, () =>
  queryCollection('pages').path(props.path).first()
)

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || page.value.title,
  description: page.value.seo?.description || page.value.description,
  headline: props.headline
})

/**
 * The date the page itself declares, not the git timestamp.
 *
 * "Last updated" is the first thing anybody checks on a privacy policy, and a
 * date that moves because a typo was fixed is worse than no date — it claims a
 * review that did not happen. So it is written in the front matter by hand, and
 * only changes when the meaning does.
 */
const updated = computed(() => {
  const value = page.value?.updated
  if (!value) {
    return null
  }
  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(new Date(value))
})

const others = [
  { label: 'Privacy', to: '/privacy', icon: 'i-lucide-shield-check' },
  { label: 'Terms', to: '/terms', icon: 'i-lucide-scale' },
  { label: 'Contact', to: '/contact', icon: 'i-lucide-mail' }
]
</script>

<template>
  <UContainer
    v-if="page"
    class="py-8 lg:py-12"
  >
    <div class="legal">
      <UPageHeader
        :title="page.title"
        :description="page.description"
      />

      <p
        v-if="updated"
        class="legal__updated"
      >
        <UIcon
          name="i-lucide-calendar-days"
          class="size-3.5 shrink-0"
        />
        Last updated {{ updated }}
      </p>

      <div class="guide-prose legal__body">
        <ContentRenderer :value="page" />
      </div>

      <!-- The other two, at the bottom, because somebody who has read one of
           these is usually looking for another. -->
      <nav
        class="legal__siblings"
        aria-label="Other pages"
      >
        <UButton
          v-for="other in others.filter(item => item.to !== path)"
          :key="other.to"
          :to="other.to"
          :label="other.label"
          :icon="other.icon"
          color="neutral"
          variant="subtle"
          size="sm"
        />
      </nav>
    </div>
  </UContainer>
</template>

<style scoped>
/* Narrower than the lessons. This is dense prose somebody is scanning for one
   clause, and a long measure makes that harder. */
.legal {
  max-width: 44rem;
  margin-inline: auto;
}

.legal__updated {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 1.25rem;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  border: 1px solid var(--ui-border);
  border-radius: 999px;
  padding: 0.2rem 0.65rem;
}

.legal__body {
  margin-top: 2rem;
}

.legal__siblings {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 3.5rem;
  padding-top: 1.75rem;
  border-top: 1px solid var(--ui-border);
}
</style>
