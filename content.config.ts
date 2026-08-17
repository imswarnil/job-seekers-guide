import { defineCollection, defineContentConfig, z } from '@nuxt/content'

const variantEnum = z.enum(['solid', 'outline', 'subtle', 'soft', 'ghost', 'link'])
const colorEnum = z.enum(['primary', 'secondary', 'neutral', 'error', 'warning', 'success', 'info'])
const sizeEnum = z.enum(['xs', 'sm', 'md', 'lg', 'xl'])
const orientationEnum = z.enum(['vertical', 'horizontal'])

/** Display grouping on `/path`. Never ordering — order is the folder numbering. */
const stageEnum = z.enum(['orientation', 'foundation', 'language', 'applied', 'projects', 'job-search'])
const kindEnum = z.enum(['lesson', 'practice', 'project', 'quiz', 'reading'])

const createBaseSchema = () => z.object({
  title: z.string().nonempty(),
  description: z.string().nonempty()
})

const createFeatureItemSchema = () => createBaseSchema().extend({
  icon: z.string().nonempty().editor({ input: 'icon' })
})

const createLinkSchema = () => z.object({
  label: z.string().nonempty(),
  to: z.string().nonempty(),
  icon: z.string().optional().editor({ input: 'icon' }),
  size: sizeEnum.optional(),
  trailing: z.boolean().optional(),
  target: z.string().optional(),
  color: colorEnum.optional(),
  variant: variantEnum.optional()
})

const createImageSchema = () => z.object({
  src: z.string().nonempty().editor({ input: 'media' }),
  alt: z.string().optional(),
  loading: z.enum(['lazy', 'eager']).optional(),
  srcset: z.string().optional()
})

/** Optional per-page overrides for the title and description search engines see. */
const createSeoSchema = () => z.object({
  title: z.string().optional().editor({ label: 'SEO title' }),
  description: z.string().optional().editor({ input: 'textarea', label: 'SEO description' })
}).optional().editor({ label: 'SEO overrides', description: 'Leave empty to use the page title and description.' })

export default defineContentConfig({
  collections: {
    index: defineCollection({
      source: '0.index.yml',
      type: 'page',
      schema: z.object({
        hero: z.object({
          headline: z.string().optional(),
          links: z.array(createLinkSchema())
        }),
        sections: z.array(
          createBaseSchema().extend({
            id: z.string().nonempty(),
            orientation: orientationEnum.optional(),
            reverse: z.boolean().optional(),
            features: z.array(createFeatureItemSchema())
          })
        ),
        features: createBaseSchema().extend({
          items: z.array(createFeatureItemSchema())
        }),
        testimonials: createBaseSchema().extend({
          headline: z.string().optional(),
          items: z.array(
            z.object({
              quote: z.string().nonempty(),
              user: z.object({
                name: z.string().nonempty(),
                description: z.string().nonempty(),
                to: z.string().optional(),
                target: z.string().optional(),
                avatar: createImageSchema().optional()
              })
            })
          )
        }),
        cta: createBaseSchema().extend({
          links: z.array(createLinkSchema())
        })
      })
    }),

    // Standalone root-level pages: /about, /faq. Each one needs a matching
    // app/pages/<slug>.vue — that file is what reserves the slug from the path.
    pages: defineCollection({
      source: '*.md',
      type: 'page',
      schema: z.object({
        icon: z.string().optional().editor({ input: 'icon' }),
        seo: createSeoSchema()
      })
    }),

    // The whole learning path, as one ordered tree: subject folder → module
    // folder → lesson file. The folder numbering *is* the sequence — there is no
    // manifest and no `order:` front matter to drift out of sync, so reordering
    // the curriculum is renaming a directory.
    //
    // `prefix: '/'` overrides the prefix Content would derive from the source
    // glob, so `1.path/3.java/1.collections/1.generics.md` lands on
    // `/java/collections/generics` rather than under a `/path` segment.
    path: defineCollection({
      source: { include: '1.path/**', prefix: '/' },
      type: 'page',
      schema: z.object({
        // A subject — the index.md one level down from `1.path/`.
        code: z.string().optional()
          .editor({ label: 'Subject code', description: 'Shown as a badge, e.g. CS-OS-101' }),
        stage: stageEnum.optional()
          .editor({ label: 'Stage', description: 'Groups subjects on /path. The order of the path comes from folder numbering, never from this.' }),
        duration: z.string().optional()
          .editor({ label: 'Duration', description: 'Human estimate, e.g. "4 weeks"' }),
        outcomes: z.array(z.string()).optional()
          .editor({ label: 'By the end you can…' }),
        prerequisites: z.array(z.string()).optional()
          .editor({ label: 'Prerequisite subject slugs' }),

        // A lesson — any markdown file three levels down.
        minutes: z.number().optional()
          .editor({ label: 'Reading time (minutes)' }),
        kind: kindEnum.optional()
          .editor({ label: 'Lesson kind' }),
        draft: z.boolean().optional()
          .editor({ label: 'Draft', description: 'Hidden from the path until unticked.' }),

        // Any level.
        icon: z.string().optional().editor({ input: 'icon' }),
        seo: createSeoSchema()
      })
    }),

    changelog: defineCollection({
      source: '4.changelog.yml',
      type: 'page'
    }),

    versions: defineCollection({
      source: '4.changelog/**',
      type: 'page',
      schema: z.object({
        title: z.string().nonempty(),
        description: z.string(),
        date: z.date()
      })
    })
  }
})
