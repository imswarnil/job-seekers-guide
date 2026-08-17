// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/content',
    '@vueuse/nuxt',
    // The SEO stack, assembled by hand rather than via the `@nuxtjs/seo`
    // umbrella — the umbrella brings its own nuxt-og-image and would fight the
    // pinned zero-runtime setup below.
    '@nuxtjs/robots',
    '@nuxtjs/sitemap',
    'nuxt-schema-org',
    'nuxt-seo-utils',
    'nuxt-og-image',
    '~~/modules/reserved-slugs'
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
      dev: true,
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

  // The old two-section site — a `/courses` catalogue and a `/docs` tree — is
  // now one path at the root. These keep the old URLs alive. The per-lesson
  // `/courses/*` → `/*` rewrite needs a splat, which route rules do not have, so
  // it lives in `public/_redirects` alongside these.
  routeRules: {
    '/courses': { redirect: { to: '/path', statusCode: 301 }, prerender: false },
    '/docs': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/docs/getting-started/**': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/docs/curriculum/**': { redirect: { to: '/path', statusCode: 301 }, prerender: false },
    '/docs/help/**': { redirect: { to: '/faq', statusCode: 301 }, prerender: false },
    '/docs/authoring/**': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/docs/**': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/blog': { redirect: { to: '/changelog', statusCode: 301 }, prerender: false },
    '/blog/**': { redirect: { to: '/changelog', statusCode: 301 }, prerender: false }
  },

  compatibilityDate: '2026-06-30',

  nitro: {
    prerender: {
      // Everything else is reached by crawling, which only works because the
      // player rail renders server-side — see app/components/player/PlayerRail.vue.
      routes: [
        '/',
        '/path',
        '/about',
        '/faq',
        '/changelog',
        '/search',
        '/login',
        '/signup'
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
  },

  robots: {
    // Nothing here is secret; these are simply pages with nothing to index.
    disallow: ['/login', '/signup', '/search']
  },

  schemaOrg: {
    identity: {
      type: 'Organization',
      name: 'Job Seekers Guide',
      url: 'https://jobseekersguide.in',
      description: 'A free, ordered learning path from no experience to a first software job.'
    }
  },

  seo: {
    // Canonical links and og:url, derived from `site.url`, on every page.
    redirectToCanonicalSiteUrl: true
  },

  sitemap: {
    // URLs are harvested from the prerender crawl, which reaches every lesson
    // through the server-rendered player rail. The legacy paths below only exist
    // as redirects and must not be advertised as destinations.
    exclude: ['/login', '/signup', '/search', '/courses/**', '/docs/**', '/blog/**']
  }
})
