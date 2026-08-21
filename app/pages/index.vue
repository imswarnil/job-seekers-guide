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

/**
 * Structured data for the front page.
 *
 * Three nodes, and every claim in them is one the site already makes in prose:
 * the site itself, the path as a free educational programme aimed at people
 * trying to get a first software job, and the ordered list of subjects as
 * `Course` entries.
 *
 * The audience is named — job seekers and students, Bengaluru and India — for
 * the same reason the copy is written the way it is: this is for somebody
 * chasing a first role in that market. Nothing here is restricted to a city;
 * `areaServed` says where the roles are, not where the course is.
 *
 * Deliberately absent: any employment outcome, salary range or placement
 * figure. There are schema fields for all three, and filling them in would be
 * the exact claim this course refuses to make.
 */
const audience = {
  '@type': 'EducationalAudience',
  'educationalRole': 'student',
  'audienceType': 'Job seekers and students pursuing a first software engineering role',
  'geographicArea': [
    { '@type': 'City', 'name': 'Bengaluru', 'alternateName': 'Bangalore' },
    { '@type': 'Country', 'name': 'India' }
  ]
}

const free = {
  '@type': 'Offer',
  'price': '0',
  'priceCurrency': 'INR',
  'availability': 'https://schema.org/InStock',
  'category': 'Free'
}

useSchemaOrg([
  defineWebSite({
    name: site.name,
    description,
    inLanguage: 'en-IN',
    potentialAction: defineSearchAction({ target: '/search?q={search_term_string}' })
  }),

  {
    '@type': 'EducationalOccupationalProgram',
    '@id': `${site.url}/#programme`,
    'name': 'Job Seekers Guide — the whole path',
    'description': description,
    'url': `${site.url}/start`,
    'provider': { '@id': `${site.url}/#identity` },
    'programPrerequisites': 'None. The path starts from no experience and assumes no degree.',
    'educationalProgramMode': 'part-time',
    'occupationalCategory': [
      'Software Developer',
      'Software Engineer',
      'Front-End Developer',
      'Back-End Developer',
      'Full-Stack Developer',
      'Database Developer'
    ],
    'numberOfCredits': 0,
    'isAccessibleForFree': true,
    'inLanguage': 'en-IN',
    'audience': audience,
    'offers': free,
    'hasCourse': path.value.subjects.map(subject => ({
      '@type': 'Course',
      '@id': `${site.url}${subject.path}#course`,
      'name': subject.title,
      'url': `${site.url}${subject.path}`
    }))
  },

  defineItemList({
    name: 'The learning path',
    itemListElement: path.value.subjects.map(subject => ({
      '@type': 'Course',
      '@id': `${site.url}${subject.path}#course`,
      'name': subject.title,
      'description': subject.description,
      'url': `${site.url}${subject.path}`,
      'provider': { '@id': `${site.url}/#identity` },
      'isAccessibleForFree': true,
      'inLanguage': 'en-IN',
      'audience': audience,
      'offers': free,
      'hasCourseInstance': {
        '@type': 'CourseInstance',
        'courseMode': 'online',
        'courseWorkload': subject.duration || undefined
      }
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

    <!-- Straight after the hero: the path itself, across rather than down.
         The fastest possible answer to "where do I start" is the order, and a
         row you travel along says "sequence" where a grid says "catalogue". -->
    <UContainer class="py-10 lg:py-14">
      <HomeCourseRail :path="path" />
    </UContainer>

    <!-- Then who is telling you all this, and the two ways in. One band: the
         sentence the site rests on, the set and the book drawn as themselves,
         and the numbers underneath. -->
    <StoryReel />

    <UContainer class="py-8 lg:py-10">
      <AdSlot
        placement="in-article"
        class="mx-auto"
      />
    </UContainer>

    <!-- Six claims drawn rather than written. The four prose bands that used to
         run here in a row were the slowest possible answer to "what is this". -->
    <UContainer class="py-10 lg:py-16">
      <HomeShowcase />
    </UContainer>

    <UContainer class="pb-10 lg:pb-16">
      <HomeContrast />
    </UContainer>

    <!-- What this is, how it teaches, and what it refuses to do. This was
         `/about`, a good page behind a nav item competing with "Start here";
         it belongs where somebody is deciding whether to trust the path. -->
    <HomeAbout />

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
