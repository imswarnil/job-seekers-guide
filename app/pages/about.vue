<script setup lang="ts">
/**
 * About, laid out rather than run.
 *
 * This was one column of prose with a table in the middle of it — accurate,
 * and read by nobody past the second heading. The words are unchanged; the
 * shape is not. Everything here comes from structured frontmatter, so the
 * page can put three things side by side and let the ten principles be
 * scanned rather than waded through.
 */
const { data: page } = await useAsyncData('page:/about', () => queryCollection('pages').path('/about').first())

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

usePageSeo({
  title: page.value.seo?.title || page.value.title,
  description: page.value.seo?.description || page.value.description,
  headline: 'About'
})

const { path } = usePath()

const facts = computed(() => [
  { value: `${path.value.subjects.length}`, label: 'Subjects, in one order' },
  { value: `${path.value.lessons.length}`, label: 'Lessons written so far' },
  { value: '₹0', label: 'For the core path, forever' },
  { value: '0', label: 'Accounts, trackers or databases' }
])
</script>

<template>
  <div v-if="page">
    <!-- ── The claim ───────────────────────────────────────────────── -->
    <section class="relative overflow-hidden border-b border-default">
      <div class="absolute inset-0 guide-contour pointer-events-none" />

      <UContainer class="relative py-14 lg:py-20">
        <div class="max-w-3xl">
          <p
            v-if="page.hero?.kicker"
            class="text-xs font-semibold uppercase tracking-[0.2em] text-primary"
          >
            {{ page.hero.kicker }}
          </p>

          <h1 class="font-display text-4xl sm:text-5xl font-bold text-highlighted tracking-tight text-balance mt-4">
            {{ page.hero?.headline || page.title }}
          </h1>

          <p
            v-if="page.hero?.lede"
            class="mt-5 text-lg text-muted text-balance"
          >
            {{ page.hero.lede }}
          </p>

          <div class="mt-8 flex flex-wrap items-center gap-3">
            <UButton
              to="/start"
              label="Start the path"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
            />
            <UButton
              to="/my-story"
              label="Why this exists"
              color="neutral"
              variant="subtle"
              size="lg"
            />
          </div>
        </div>

        <dl class="mt-12 grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="fact in facts"
            :key="fact.label"
            class="rounded-lg border border-default bg-elevated/40 px-4 py-3.5"
          >
            <dt class="font-display text-2xl font-bold text-highlighted tabular-nums">
              {{ fact.value }}
            </dt>
            <dd class="text-xs text-muted mt-0.5 text-balance">
              {{ fact.label }}
            </dd>
          </div>
        </dl>
      </UContainer>
    </section>

    <UContainer class="py-14 lg:py-20">
      <!-- ── Three columns ─────────────────────────────────────────── -->
      <div
        v-if="page.pillars?.length"
        class="grid md:grid-cols-3 gap-8 lg:gap-12"
      >
        <div
          v-for="pillar in page.pillars"
          :key="pillar.title"
        >
          <Illustration
            v-if="pillar.illustration"
            :name="(pillar.illustration as 'job-search')"
            size="sm"
            class="mb-5"
          />
          <h2 class="font-display text-lg font-bold text-highlighted text-balance">
            {{ pillar.title }}
          </h2>
          <div class="guide-prose text-sm mt-2">
            <MDC :value="pillar.body" />
          </div>
        </div>
      </div>

      <!-- ── Who ───────────────────────────────────────────────────── -->
      <section
        v-if="page.audience"
        class="about__band"
      >
        <div class="min-w-0">
          <h2 class="about__title">
            {{ page.audience.title }}
          </h2>
          <div class="guide-prose mt-3">
            <MDC :value="page.audience.body" />
          </div>
        </div>

        <NuxtLink
          to="/my-story"
          class="about__me"
        >
          <p class="text-xs uppercase tracking-wider text-secondary font-semibold">
            Including me
          </p>
          <p class="mt-2 text-sm text-muted text-balance">
            I was an average student who could not clear a single written round
            in college, and I am now a Salesforce engineer in Europe. Every
            rejection and every salary figure is in the story.
          </p>
          <span class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-primary">
            Read it
            <UIcon
              name="i-lucide-arrow-right"
              class="size-3.5"
            />
          </span>
        </NuxtLink>
      </section>

      <!-- ── The ten principles ────────────────────────────────────── -->
      <section
        v-if="page.principles?.length"
        class="mt-16 lg:mt-24"
      >
        <h2 class="about__title">
          The ten principles
        </h2>
        <p class="text-muted mt-2 max-w-2xl text-balance">
          Every lesson, every page and every design decision here is measured
          against these.
        </p>

        <ol class="about__principles">
          <li
            v-for="(principle, index) in page.principles"
            :key="principle.title"
            class="about__principle"
          >
            <span class="about__n">{{ String(index + 1).padStart(2, '0') }}</span>
            <div class="min-w-0">
              <p class="about__principle-title">
                {{ principle.title }}
              </p>
              <p class="about__principle-body">
                {{ principle.body }}
              </p>
            </div>
          </li>
        </ol>
      </section>

      <!-- ── Left out ──────────────────────────────────────────────── -->
      <section
        v-if="page.excluded?.length"
        class="mt-16 lg:mt-24"
      >
        <h2 class="about__title">
          What is deliberately left out
        </h2>
        <p class="text-muted mt-2 max-w-2xl text-balance">
          A lesson that says “skip this, here is why” is as valuable as one that
          teaches.
        </p>

        <ul class="about__excluded">
          <li
            v-for="item in page.excluded"
            :key="item.what"
          >
            <p class="about__excluded-what">
              <UIcon
                name="i-lucide-minus-circle"
                class="size-4 text-dimmed shrink-0 mt-0.5"
              />
              <span>{{ item.what }}</span>
            </p>
            <p class="about__excluded-why">
              {{ item.why }}
            </p>
          </li>
        </ul>
      </section>

      <!-- ── Non-goals and how it is built, side by side ───────────── -->
      <section class="about__split">
        <div v-if="page.nonGoals?.length">
          <h2 class="about__title">
            What this is not
          </h2>
          <ul class="about__nongoals">
            <li
              v-for="item in page.nonGoals"
              :key="item"
            >
              <UIcon
                name="i-lucide-x"
                class="size-4 text-error shrink-0 mt-0.5"
              />
              <span>{{ item }}</span>
            </li>
          </ul>
        </div>

        <div v-if="page.built">
          <h2 class="about__title">
            {{ page.built.title }}
          </h2>
          <div class="guide-prose mt-3 text-sm">
            <MDC :value="page.built.body" />
          </div>
          <Illustration
            name="versioning"
            size="sm"
            class="mt-6"
          />
        </div>
      </section>

      <!-- ── Where to go ───────────────────────────────────────────── -->
      <div
        v-if="page.cards?.length"
        class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-16 lg:mt-24"
      >
        <UPageCard
          v-for="card in page.cards"
          :key="card.title"
          :to="card.to"
          :title="card.title"
          :description="card.body"
          :icon="card.icon"
          variant="subtle"
          spotlight
        />
      </div>

      <AdSlot
        placement="in-article"
        class="mx-auto"
      />
    </UContainer>
  </div>
</template>

<style scoped>
.about__title {
  font-family: var(--font-display);
  font-size: clamp(1.375rem, 3vw, 1.75rem);
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.about__band {
  display: grid;
  gap: 2rem;
  margin-top: 4rem;
  padding-top: 3rem;
  border-top: 1px solid var(--ui-border);
}

@media (min-width: 1024px) {
  .about__band {
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 4rem;
    align-items: start;
    margin-top: 6rem;
  }
}

.about__me {
  display: block;
  padding: 1.35rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--ui-border);
  background: var(--ui-bg-elevated);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease);
}

.about__me:hover {
  border-color: var(--ui-border-accented);
  transform: translateY(-2px);
}

/* Two columns of five rather than one column of ten: a numbered list that runs
   past the fold stops being a list and becomes an essay again. */
.about__principles {
  list-style: none;
  margin: 2rem 0 0;
  padding: 0;
  display: grid;
  gap: 1.5rem 3rem;
}

@media (min-width: 768px) {
  .about__principles {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.about__principle {
  display: flex;
  gap: 1rem;
}

.about__n {
  font-family: var(--font-display);
  font-size: 0.8125rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--ui-primary);
  padding-top: 0.15rem;
  flex-shrink: 0;
}

.about__principle-title {
  font-weight: 600;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.about__principle-body {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.2rem;
  text-wrap: pretty;
}

.about__excluded {
  list-style: none;
  margin: 2rem 0 0;
  padding: 0;
  display: grid;
  gap: 1.25rem 3rem;
}

@media (min-width: 768px) {
  .about__excluded {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.about__excluded-what {
  display: flex;
  gap: 0.6rem;
  font-weight: 600;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.about__excluded-why {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.25rem;
  padding-left: 1.6rem;
  text-wrap: pretty;
}

.about__split {
  display: grid;
  gap: 3rem;
  margin-top: 4rem;
  padding-top: 3rem;
  border-top: 1px solid var(--ui-border);
}

@media (min-width: 1024px) {
  .about__split {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 4rem;
    margin-top: 6rem;
  }
}

.about__nongoals {
  list-style: none;
  margin: 1.5rem 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.about__nongoals li {
  display: flex;
  gap: 0.6rem;
  color: var(--ui-text-muted);
  text-wrap: pretty;
}

@media (prefers-reduced-motion: reduce) {
  .about__me {
    transition: none;
  }

  .about__me:hover {
    transform: none;
  }
}
</style>
