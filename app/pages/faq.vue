<script setup lang="ts">
const { data: page } = await useAsyncData('page:/faq', () => queryCollection('pages').path('/faq').first())

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || page.value.title,
  description: page.value.seo?.description || page.value.description,
  headline: 'Questions'
})

const { brand } = useAppConfig()

const groups = computed(() => page.value?.groups || [])
const totalQuestions = computed(() => groups.value.reduce((n, group) => n + group.questions.length, 0))

/* ── Finding an answer ─────────────────────────────────────────────────
   Ten questions is already more than anybody reads top to bottom, and this
   page only grows. The filter searches the answers as well as the questions,
   because people search for the thing they are worried about ("maths",
   "free", "placement") rather than for the wording somebody else chose. */
const query = ref('')
const searching = computed(() => query.value.trim().length > 0)

const filtered = computed(() => {
  const needle = query.value.trim().toLowerCase()
  if (!needle) {
    return groups.value
  }

  const words = needle.split(/\s+/)

  return groups.value
    .map(group => ({
      ...group,
      questions: group.questions.filter((item) => {
        const haystack = `${item.q} ${item.a} ${group.label}`.toLowerCase()
        return words.every(word => haystack.includes(word))
      })
    }))
    .filter(group => group.questions.length)
})

const matches = computed(() => filtered.value.reduce((n, group) => n + group.questions.length, 0))

/** Anchors, so a single question can be linked to directly. */
function slug(text: string) {
  return text.toLowerCase().replace(/[^\w]+/g, '-').replace(/^-|-$/g, '')
}

/* Search engines show these as expandable results, which for a page like this
   is most of the traffic it will ever get. */
useSchemaOrg([
  defineWebPage({ '@type': 'FAQPage' }),
  {
    '@type': 'FAQPage',
    'mainEntity': groups.value.flatMap(group => group.questions.map(item => ({
      '@type': 'Question',
      'name': item.q,
      'acceptedAnswer': { '@type': 'Answer', 'text': item.a }
    })))
  }
])
</script>

<template>
  <UContainer
    v-if="page"
    class="py-8 lg:py-12"
  >
    <UPageHeader
      :title="page.title"
      :description="page.description"
    />

    <div class="faq">
      <div class="min-w-0">
        <!-- Search first. Somebody arrives here with one question, not with an
             appetite for a list of ten. -->
        <div class="faq__search">
          <UInput
            v-model="query"
            icon="i-lucide-search"
            size="xl"
            class="w-full"
            placeholder="Search the questions — maths, free, how long…"
          >
            <template
              v-if="query"
              #trailing
            >
              <UButton
                icon="i-lucide-x"
                color="neutral"
                variant="link"
                size="xs"
                aria-label="Clear the search"
                @click="query = ''"
              />
            </template>
          </UInput>

          <p class="faq__count">
            <span v-if="searching">{{ matches }} of {{ totalQuestions }} questions</span>
            <span v-else>{{ totalQuestions }} questions, grouped</span>
          </p>
        </div>

        <div
          v-for="group in filtered"
          :key="group.label"
          class="faq__group"
        >
          <div class="faq__head">
            <UIcon
              v-if="group.icon"
              :name="group.icon"
              class="size-4 text-primary shrink-0"
            />
            <h2 class="faq__label">
              {{ group.label }}
            </h2>
            <span class="faq__n">{{ group.questions.length }}</span>
          </div>

          <p
            v-if="group.description && !searching"
            class="faq__desc"
          >
            {{ group.description }}
          </p>

          <!-- Open by default while filtering: hiding the answer behind another
               click after somebody has already told you what they want is a
               second search. -->
          <UAccordion
            :items="group.questions.map(item => ({
              label: item.q,
              value: slug(item.q),
              content: item.a
            }))"
            :default-value="searching ? group.questions.map(item => slug(item.q)) : undefined"
            type="multiple"
          >
            <template #content="{ item }">
              <div class="guide-prose faq__answer">
                <MDC :value="item.content" />
              </div>
            </template>
          </UAccordion>
        </div>

        <div
          v-if="searching && !matches"
          class="faq__empty"
        >
          <p class="font-display font-semibold text-highlighted">
            Nothing here matches “{{ query }}”.
          </p>
          <p class="text-sm text-muted mt-1">
            That is a good sign it should be on this page. Ask it and it will be.
          </p>
          <UButton
            :to="`${repoUrl}/discussions/new?category=q-a`"
            target="_blank"
            rel="noopener"
            label="Ask it"
            icon="i-lucide-message-circle-question"
            class="mt-4"
          />
        </div>
      </div>

      <!-- ── The sidebar ───────────────────────────────────────────────
           Sticky, so the two things somebody might want after reading an
           answer — asking their own, and the ad that pays for this — do not
           require scrolling back to find. -->
      <aside class="faq__aside">
        <div class="faq__aside-inner">
          <div class="faq__ask">
            <p class="faq__ask-title">
              Not answered here?
            </p>
            <p class="faq__ask-note">
              I answer these myself, and the good ones end up on this page for
              the next person.
            </p>
            <UButton
              :to="`${repoUrl}/discussions/new?category=q-a`"
              target="_blank"
              rel="noopener"
              label="Ask a question"
              icon="i-lucide-message-circle-question"
              block
              class="mt-4"
            />
            <UButton
              v-if="brand.topmate"
              :to="brand.topmate"
              target="_blank"
              rel="noopener"
              label="Book a call"
              icon="i-lucide-calendar-clock"
              color="neutral"
              variant="subtle"
              block
              class="mt-2"
            />
            <p class="faq__ask-note mt-3">
              A call is the part an institute charges ₹40,000 for and gives you
              once.
            </p>
          </div>

          <AdSlot
            placement="sidebar"
            variant="card"
          />

          <UPageCard
            variant="subtle"
            :ui="{ body: 'p-4' }"
          >
            <p class="text-sm text-highlighted font-medium">
              Still stuck?
            </p>
            <p class="text-sm text-muted mt-1">
              The path answers most of this in more detail than a paragraph can.
            </p>
            <UButton
              to="/start"
              label="Start here"
              trailing-icon="i-lucide-arrow-right"
              size="sm"
              variant="subtle"
              class="mt-3"
            />
          </UPageCard>
        </div>
      </aside>
    </div>
  </UContainer>
</template>

<style scoped>
.faq {
  display: grid;
  gap: 3rem;
  margin-top: 2rem;
}

@media (min-width: 1024px) {
  .faq {
    grid-template-columns: minmax(0, 1fr) 20rem;
    gap: 4rem;
    align-items: start;
  }
}

.faq__search {
  margin-bottom: 2.5rem;
}

.faq__count {
  margin-top: 0.6rem;
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  font-variant-numeric: tabular-nums;
}

.faq__group + .faq__group {
  margin-top: 2.75rem;
}

.faq__head {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin-bottom: 0.35rem;
}

.faq__label {
  font-family: var(--font-display);
  font-size: 1.0625rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
}

.faq__n {
  font-size: 0.6875rem;
  font-variant-numeric: tabular-nums;
  color: var(--ui-text-dimmed);
  background: var(--ui-bg-elevated);
  border-radius: 999px;
  padding: 0.05rem 0.45rem;
}

.faq__desc {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-bottom: 0.85rem;
  text-wrap: pretty;
}

.faq__answer {
  padding-bottom: 0.5rem;
}

.faq__empty {
  border: 1px dashed var(--ui-border-accented);
  border-radius: var(--radius-lg);
  padding: 2rem 1.5rem;
  text-align: center;
}

.faq__aside {
  display: none;
}

@media (min-width: 1024px) {
  .faq__aside {
    display: block;
  }
}

.faq__aside-inner {
  position: sticky;
  top: calc(var(--ui-header-height) + 2rem);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.faq__ask {
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg-elevated);
  padding: 1.25rem;
}

.faq__ask-title {
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--ui-text-highlighted);
}

.faq__ask-note {
  font-size: 0.8125rem;
  color: var(--ui-text-muted);
  margin-top: 0.35rem;
  text-wrap: pretty;
}
</style>
