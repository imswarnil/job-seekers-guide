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
    </UPageHero>

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
