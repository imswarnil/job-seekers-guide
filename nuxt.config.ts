// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/content',
    '@vueuse/nuxt',
    'nuxt-og-image'
  ],

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  site: {
    name: 'Job Seekers Guide',
    url: 'https://jobseekersguide.in'
  },

  content: {
    // Content is authored as markdown in `content/`, in nested folders that map
    // one-to-one onto the URL. Nuxt Studio edits the same files, so the repo
    // stays the single source of truth whether a lesson is written in an editor
    // or in a pull request.
    preview: {
      api: 'https://api.nuxt.studio'
    },
    build: {
      markdown: {
        toc: {
          depth: 3,
          searchDepth: 3
        }
      }
    },
    experimental: {
      sqliteConnector: 'native'
    }
  },

  // Section folders that hold no index page of their own land on their first
  // real page rather than on a 404.
  routeRules: {
    '/docs': { redirect: '/docs/getting-started', prerender: false },
    '/docs/authoring': { redirect: '/docs/authoring/course-structure', prerender: false },
    '/docs/help': { redirect: '/docs/help/for-learners', prerender: false }
  },

  compatibilityDate: '2026-06-30',

  nitro: {
    prerender: {
      routes: [
        '/'
      ],
      crawlLinks: true
    }
  },

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },

  ogImage: {
    zeroRuntime: true
  }
})
