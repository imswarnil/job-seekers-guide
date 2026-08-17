/**
 * Runtime configuration that is safe to edit without touching code.
 *
 * Nuxt Content ships the schema derived from this file to Nuxt Studio, so every
 * field below appears as a labelled control in the visual editor — which is why
 * the ad switches and the player defaults live here and not in `nuxt.config.ts`.
 * Turning ads off should not require a deploy.
 */
export default defineAppConfig({
  ui: {
    colors: {
      primary: 'guide',
      secondary: 'spark',
      neutral: 'ink',
      success: 'emerald',
      warning: 'amber',
      error: 'red',
      info: 'spark'
    },
    button: {
      defaultVariants: {
        size: 'md'
      }
    }
  },

  /** Site-wide identity. */
  brand: {
    /** Shown in the header, the footer and every social card. */
    name: 'Job Seekers Guide',
    /** One sentence. Used as the fallback meta description. */
    tagline: 'One ordered path from no experience to a first software job.',
    /** Repository the content is authored in. Empty hides the link. */
    github: 'https://github.com',
    /** Channel companion videos are published to. Empty hides the link. */
    youtube: ''
  },

  /** Lesson player behaviour. */
  player: {
    /** Move to the next lesson on its own after one is marked finished. */
    autoAdvance: false,
    /** Seconds to wait before that happens. Any keypress cancels it. */
    autoAdvanceSeconds: 8,
    /** Show the path rail by default on wide screens. */
    showRailByDefault: true
  },

  /**
   * Advertising. `enabled: false` removes every slot site-wide, including the
   * ones authors placed inline in markdown.
   */
  ads: {
    /** Master switch. Nothing renders while this is off. */
    enabled: false,
    /** `house` shows the built-in promo; `adsense` loads a third-party script. */
    provider: 'house' as 'house' | 'adsense' | 'none',
    /** Publisher id, for the adsense provider only. */
    client: '',
    /** Per-placement switches. See app/utils/ads.ts for what each one is. */
    slots: {
      'in-article': true,
      'lesson-footer': true,
      'rail-bottom': false,
      'path-parallax': false
    },
    /** Inject an in-article slot automatically after this many blocks. 0 = never. */
    autoInsertAfterBlocks: 0
  }
})
