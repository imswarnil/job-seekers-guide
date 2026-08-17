<script setup lang="ts">
const { data: page } = await useAsyncData('index', () => queryCollection('index').first())

const { path } = usePath()

const title = page.value?.seo?.title || page.value?.title
const description = page.value?.seo?.description || page.value?.description

useSeoMeta({
  titleTemplate: '',
  title,
  ogTitle: title,
  description,
  ogDescription: description,
  ogType: 'website'
})

defineOgImage('Guide', {
  title: 'Getting a job in software is a navigation problem',
  description,
  headline: 'Job Seekers Guide'
})

const site = useSiteConfig()

useSchemaOrg([
  defineWebSite({
    name: site.name,
    description,
    potentialAction: defineSearchAction({ target: '/search?q={search_term_string}' })
  }),
  defineItemList({
    name: 'The learning path',
    itemListElement: path.value.subjects.map(subject => ({
      '@type': 'Course',
      'name': subject.title,
      'description': subject.description,
      'url': `${site.url}${subject.path}`,
      'provider': { '@id': `${site.url}/#identity` }
    }))
  })
])
</script>

<template>
  <div v-if="page">
    <UPageHero
      :title="page.title"
      :description="page.description"
      :headline="page.hero.headline"
      :links="page.hero.links"
    >
      <template #top>
        <div class="absolute inset-0 guide-contour pointer-events-none" />
        <HeroBackground />
      </template>

      <template #title>
        <MDC
          :value="page.title"
          unwrap="p"
        />
      </template>

      <!-- The argument, animated: every opening is already on the page and
           always was. What moves is the glass. -->
      <template #description>
        <div class="lg:grid lg:grid-cols-[1fr_auto] lg:gap-12 lg:items-center">
          <p class="text-balance">
            {{ page.description }}
          </p>

          <IllustrationJobSearch class="mx-auto mt-8 lg:mt-0 w-full max-w-[19rem] shrink-0" />
        </div>
      </template>

      <!-- The start of the path, on the front page. The fastest possible answer
           to "where do I start" is the first subject, visible without a click. -->
      <UPageGrid class="lg:grid-cols-3">
        <SubjectCard
          v-for="(subject, index) in path.subjects.slice(0, 3)"
          :key="subject.path"
          :subject="subject"
          :index="index"
        />
      </UPageGrid>

      <!-- What the path is made of, without a paragraph about it. -->
      <div class="mt-10 flex flex-col items-center gap-3">
        <p class="text-xs uppercase tracking-wider text-dimmed">
          What you will actually learn
        </p>
        <div class="flex flex-wrap justify-center gap-2.5">
          <TechThumb
            v-for="name in ['os', 'sql', 'java', 'oops', 'dsa', 'networks', 'html', 'css', 'git', 'system-design']"
            :key="name"
            :name="name"
            size="sm"
          />
        </div>
      </div>
    </UPageHero>

    <!-- The story sits between the pitch and the detail, because it is the
         evidence for the pitch. Tighter than a normal section: it belongs to the
         hero above it rather than starting a new thought. -->
    <UPageSection :ui="{ container: 'py-8 sm:py-10 lg:py-12' }">
      <UPageCard
        variant="subtle"
        class="overflow-hidden"
      >
        <div class="lg:grid lg:grid-cols-[auto_1fr] lg:gap-10 lg:items-center">
          <IllustrationResume class="w-40 mx-auto lg:mx-0 shrink-0 mb-6 lg:mb-0" />

          <div>
            <UBadge
              label="Why this exists"
              color="secondary"
              variant="subtle"
            />
            <h2 class="font-display text-2xl font-bold text-highlighted mt-3 text-balance">
              I could not clear a single written round. I am now a Salesforce engineer in Europe.
            </h2>
            <p class="mt-3 text-muted text-balance">
              Average student, no plan, no guidance, and a family that could not
              fund one. This is the whole route — the rejections, the ₹13,000
              first salary, and every offer after it — written down exactly as it
              happened.
            </p>
            <UButton
              to="/my-story"
              label="Read my story"
              trailing-icon="i-lucide-arrow-right"
              size="lg"
              class="mt-6"
            />
          </div>
        </div>
      </UPageCard>
    </UPageSection>

    <UPageSection
      v-for="(section, index) in page.sections"
      :id="section.id"
      :key="index"
      :title="section.title"
      :description="section.description"
      :orientation="section.orientation"
      :reverse="section.reverse"
      :features="section.features"
    >
      <ImagePlaceholder />
    </UPageSection>

    <UPageSection
      :title="page.features.title"
      :description="page.features.description"
    >
      <UPageGrid>
        <UPageCard
          v-for="(item, index) in page.features.items"
          :key="index"
          v-bind="item"
          spotlight
        />
      </UPageGrid>
    </UPageSection>

    <UPageSection
      id="who-for"
      :headline="page.testimonials.headline"
      :title="page.testimonials.title"
      :description="page.testimonials.description"
    >
      <UPageColumns class="xl:columns-2">
        <UPageCard
          v-for="(testimonial, index) in page.testimonials.items"
          :key="index"
          variant="subtle"
          :description="testimonial.quote"
          :ui="{ description: 'before:content-[open-quote] after:content-[close-quote]' }"
        >
          <template #footer>
            <UUser
              v-bind="testimonial.user"
              size="lg"
            />
          </template>
        </UPageCard>
      </UPageColumns>
    </UPageSection>

    <USeparator />

    <UPageCTA
      v-bind="page.cta"
      variant="naked"
      class="overflow-hidden"
    >
      <LazyStarsBg />
    </UPageCTA>
  </div>
</template>
