import { defineCollection, z } from '@nuxt/content'

const variantEnum = z.enum(['solid', 'outline', 'subtle', 'soft', 'ghost', 'link'])
const colorEnum = z.enum(['primary', 'secondary', 'neutral', 'error', 'warning', 'success', 'info'])
const sizeEnum = z.enum(['xs', 'sm', 'md', 'lg', 'xl'])
const orientationEnum = z.enum(['vertical', 'horizontal'])

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

export const collections = {
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

  docs: defineCollection({
    source: '1.docs/**/*',
    type: 'page'
  }),

  // Courses are folders, all the way down: a course folder holds an index.md
  // (the syllabus page) and one folder per module, each holding the lessons as
  // plain markdown. The folder tree *is* the curriculum — reordering a module
  // is renaming a directory, and the player reads the same navigation tree the
  // documentation sidebar does.
  courses: defineCollection({
    source: '2.courses/**/*',
    type: 'page',
    schema: z.object({
      // Present on a course index.md
      code: z.string().optional(),
      duration: z.string().optional(),
      level: z.enum(['orientation', 'foundation', 'applied', 'job-search']).optional(),
      icon: z.string().optional().editor({ input: 'icon' }),
      outcomes: z.array(z.string()).optional(),
      prerequisites: z.array(z.string()).optional(),
      // Present on a lesson
      minutes: z.number().optional(),
      kind: z.enum(['lesson', 'practice', 'project', 'quiz', 'reading']).optional(),
      draft: z.boolean().optional()
    })
  }),

  blog: defineCollection({
    source: '3.blog.yml',
    type: 'page'
  }),

  posts: defineCollection({
    source: '3.blog/**/*',
    type: 'page',
    schema: z.object({
      image: z.object({ src: z.string().nonempty().editor({ input: 'media' }) }).optional(),
      authors: z.array(
        z.object({
          name: z.string().nonempty(),
          to: z.string().optional(),
          avatar: z.object({ src: z.string().nonempty().editor({ input: 'media' }) }).optional()
        })
      ).optional(),
      date: z.date(),
      badge: z.object({ label: z.string().nonempty() }).optional()
    })
  }),

  changelog: defineCollection({
    source: '4.changelog.yml',
    type: 'page'
  }),

  versions: defineCollection({
    source: '4.changelog/**/*',
    type: 'page',
    schema: z.object({
      title: z.string().nonempty(),
      description: z.string(),
      date: z.date()
    })
  })
}
