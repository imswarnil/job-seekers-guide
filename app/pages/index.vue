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
    <!-- Two columns: the claim on the left, the route on the right. Somebody
         arriving here has been told to "learn to code" a hundred times and has
         never been shown the whole thing as one object with an end. -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 guide-contour pointer-events-none" />
      <HeroBackground />

      <UContainer class="relative py-14 lg:py-20">
        <div class="lg:grid lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:items-center">
          <div>
            <p class="text-sm font-medium text-primary">
              {{ page.hero.headline }}
            </p>

            <h1 class="font-display text-4xl sm:text-5xl xl:text-6xl font-bold text-highlighted tracking-tight text-balance mt-4">
              <MDC
                :value="page.title"
                unwrap="p"
              />
            </h1>

            <p class="mt-5 text-lg text-muted text-balance max-w-xl">
              {{ page.description }}
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
              <UButton
                v-for="(link, index) in page.hero.links"
                :key="index"
                v-bind="link"
              />
            </div>
          </div>

          <!-- The argument in one loop: every subject is already out there,
               free and in no order, and the product is the volume they land
               in. -->
          <div class="mt-12 lg:mt-0">
            <HeroConverge />
          </div>
        </div>
      </UContainer>
    </section>

    <!-- The start of the path, on the front page. The fastest possible answer
         to "where do I start" is the first subject, visible without a click. -->
    <UContainer class="pb-4">
      <UPageGrid class="lg:grid-cols-3">
        <SubjectCard
          v-for="(subject, index) in path.subjects.slice(0, 3)"
          :key="subject.path"
          :subject="subject"
          :index="index"
        />
      </UPageGrid>

      <AdSlot
        placement="in-article"
        class="mx-auto"
      />
    </UContainer>

    <UContainer class="py-10 lg:py-16">
      <HomeContrast />
    </UContainer>

    <!-- The evidence for everything above it. Its own band, because a
         sentence this load-bearing inside a card reads as a third feature. -->
    <HomeStory />

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
      <!-- A drawing that says something about the section, rather than the
           dashed grey box that used to sit here saying nothing. -->
      <Illustration
        :name="section.id === 'how' ? 'foundations' : 'job-search'"
        size="lg"
        class="mx-auto"
      />
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

    <UContainer class="py-10 lg:py-16">
      <HomePace />
    </UContainer>

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

    <UContainer class="py-10 lg:py-16">
      <HomeQuestions />
    </UContainer>

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
