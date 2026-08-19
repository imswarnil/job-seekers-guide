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
    'nuxt-studio',
    '~~/modules/reserved-slugs'
  ],

  /**
   * The content editor, at `/_studio`.
   *
   * This replaces the old hosted Studio, which was configured through
   * `content.preview.api` and pointed at `api.nuxt.studio`. That flow is gone —
   * Studio is a module you run yourself now, so the editor lives on this site
   * rather than on somebody else's.
   *
   * In development it writes straight to the files in `content/`. In production
   * it commits to the repository below, which needs a GitHub OAuth app and the
   * two `NUXT_STUDIO_AUTH_GITHUB_*` environment variables — and a host that can
   * run a server. See the note on the deploy workflow.
   */
  studio: {
    repository: {
      provider: 'github',
      owner: 'imswarnil',
      repo: 'job-seekers-guide',
      branch: 'main'
    }
  },

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  vue: {
    compilerOptions: {
      // `<mux-player>` is a web component, not a Vue one. Without this the
      // compiler tries to resolve it and warns on every episode page.
      isCustomElement: tag => tag.startsWith('mux-')
    }
  },

  site: {
    name: 'Job Seekers Guide',
    // Drives canonicals, og:url, the sitemap and absolute OG image URLs.
    // `NUXT_PUBLIC_SITE_URL` overrides it, which is what the deploy workflow
    // and any preview environment should set rather than editing this.
    url: 'https://jobseekers.imswarnil.com'
  },

  content: {
    // Content is authored as markdown in `content/`, in nested folders that map
    // one-to-one onto the URL. Studio edits the same files, so the repo stays
    // the single source of truth whether a lesson is written in the editor or
    // in a pull request.
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

  runtimeConfig: {
    public: {
      /**
       * The Java runner Worker. Empty means the Java code runner is off and
       * says so — every other language runs in the browser and is unaffected.
       * Set `NUXT_PUBLIC_RUNNER_URL` once `workers/runner` is deployed.
       */
      runnerUrl: ''
    }
  },

  // The old two-section site — a `/courses` catalogue and a `/docs` tree — is
  // now one path at the root. These keep the old URLs alive. The per-lesson
  // `/courses/*` → `/*` rewrite needs a splat, which route rules do not have, so
  // it lives in `public/_redirects` alongside these.
  routeRules: {
    '/courses': { redirect: { to: '/start', statusCode: 301 }, prerender: false },
    // `/path` was the first name for the curriculum index. "Start here" is what
    // somebody frightened of the whole thing actually needs to read.
    '/path': { redirect: { to: '/start', statusCode: 301 }, prerender: false },
    // The story moved under one roof: an overview at `/my-story`, with the
    // series and the book as children of it.
    '/series': { redirect: { to: '/my-story/watch', statusCode: 301 }, prerender: false },
    '/series/read': { redirect: { to: '/my-story/book', statusCode: 301 }, prerender: false },
    '/series/**': { redirect: { to: '/my-story/watch', statusCode: 301 }, prerender: false },
    '/docs': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/docs/getting-started/**': { redirect: { to: '/about', statusCode: 301 }, prerender: false },
    '/docs/curriculum/**': { redirect: { to: '/start', statusCode: 301 }, prerender: false },
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
        '/start',
        '/my-story',
        '/my-story/watch',
        '/my-story/book',
        '/run',
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
      // No `url` on purpose — it is derived from `site.url` above, so moving
      // the site does not leave a stale domain buried in the structured data.
      description: 'A free, ordered learning path from no experience to a first software job.',
      // The person behind it, named. A site whose whole argument is "somebody
      // who has done this is telling you the order" should say who that is in
      // the machine-readable copy as well as in the prose.
      founder: {
        '@type': 'Person',
        'name': 'Swarnil Singhai',
        'jobTitle': 'Salesforce engineer',
        'sameAs': ['https://github.com/imswarnil', 'https://topmate.io/swarnil']
      },
      sameAs: ['https://github.com/imswarnil/job-seekers-guide']
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
    exclude: ['/login', '/signup', '/search', '/courses/**', '/docs/**', '/blog/**', '/path', '/series', '/series/**']
  }
})
