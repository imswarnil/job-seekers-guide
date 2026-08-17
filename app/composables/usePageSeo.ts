import type { LearningPath, Module, Subject } from '~/utils/path'

interface SchemaContext {
  kind: 'lesson' | 'module' | 'subject'
  page?: { title?: string, description?: string, code?: string, stage?: string, minutes?: number, kind?: string } | null
  path?: LearningPath
  subject?: Subject
  module?: Module
}

interface PageSeoOptions {
  title: MaybeRefOrGetter<string | undefined>
  description?: MaybeRefOrGetter<string | undefined>
  /** Small line above the title on the social card. */
  headline?: MaybeRefOrGetter<string | undefined>
  schema?: MaybeRefOrGetter<SchemaContext | undefined>
}

/**
 * Everything a page owes a search engine, in one call.
 *
 * This was five near-identical copy-pasted blocks across five pages, and they
 * had already drifted — one of them set a description and no OG description, one
 * set neither. Meta tags, the social image and the structured data are one
 * decision per page, so they are one function.
 */
export function usePageSeo(options: PageSeoOptions) {
  const route = useRoute()
  const site = useSiteConfig()

  const title = computed(() => toValue(options.title))
  const description = computed(() => toValue(options.description))

  useSeoMeta({
    title,
    ogTitle: title,
    description,
    ogDescription: description,
    ogType: 'article',
    ogUrl: () => `${site.url}${route.path}`
  })

  defineOgImage('Guide', {
    title: () => toValue(options.title),
    description: () => toValue(options.description),
    headline: () => toValue(options.headline)
  })

  if (!options.schema) {
    return
  }

  // Breadcrumbs come free: the path tree already knows subject → module →
  // lesson, so nothing has to be threaded through the page for them.
  const nodes = computed(() => {
    const context = toValue(options.schema)
    if (!context) {
      return []
    }

    const { kind, page, subject, module } = context

    const crumbs = [{ name: 'The path', item: '/path' }]
    if (subject) {
      crumbs.push({ name: subject.title, item: subject.path })
    }
    if (module && kind !== 'subject') {
      crumbs.push({ name: module.title, item: module.path })
    }
    if (kind === 'lesson' && page?.title) {
      crumbs.push({ name: page.title, item: route.path })
    }

    const nodes: Record<string, unknown>[] = [defineBreadcrumb({ itemListElement: crumbs })]

    if (kind === 'subject' && subject) {
      nodes.push(defineCourse({
        name: subject.title,
        description: subject.description,
        courseCode: subject.code,
        educationalLevel: subject.stage,
        url: subject.path,
        provider: { '@id': `${site.url}/#identity` },
        hasCourseInstance: {
          '@type': 'CourseInstance',
          'courseMode': 'online',
          'courseWorkload': subject.minutes ? `PT${subject.minutes}M` : undefined
        }
      }))
    }

    // A lesson is a LearningResource, which schema-org's helpers do not have a
    // definer for — a plain node is exactly as valid.
    if (kind === 'lesson' && page) {
      nodes.push({
        '@type': 'LearningResource',
        'name': page.title,
        'description': page.description,
        'learningResourceType': page.kind || 'lesson',
        'timeRequired': page.minutes ? `PT${page.minutes}M` : undefined,
        'isPartOf': subject
          ? { '@type': 'Course', 'name': subject.title, 'url': `${site.url}${subject.path}` }
          : undefined
      })
    }

    return nodes
  })

  // The nodes are reactive because the catch-all page keeps one component
  // instance across lesson navigations. `useSchemaOrg` accepts a ref at runtime;
  // its published type only names the writable-ref branch of that union.
  useSchemaOrg(nodes as unknown as Parameters<typeof useSchemaOrg>[0])
}
