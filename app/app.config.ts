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
    },
    prose: {
      /**
       * The little icon on a code block that has a filename, keyed by extension
       * or by whole filename.
       *
       * Nuxt UI's own map points every entry at the `vscode-icons` collection,
       * which this site does not install — so an unmapped extension renders a
       * blank square. Only Lucide and Simple Icons are installed, and both are
       * bundled at build time, so these never cost a request. Add a line here
       * before writing a lesson that names a file type not listed.
       */
      codeIcon: {
        // Simple Icons has no `java`; OpenJDK is the mark this site uses. See
        // app/utils/tech.ts, which makes the same substitution.
        'java': 'i-simple-icons-openjdk',
        'sql': 'i-lucide-database',
        'html': 'i-simple-icons-html5',
        'htm': 'i-simple-icons-html5',
        'css': 'i-simple-icons-css',
        'js': 'i-simple-icons-javascript',
        'mjs': 'i-simple-icons-javascript',
        'cjs': 'i-simple-icons-javascript',
        'jsx': 'i-simple-icons-react',
        'ts': 'i-simple-icons-typescript',
        'tsx': 'i-simple-icons-react',
        'vue': 'i-simple-icons-vuedotjs',
        'py': 'i-simple-icons-python',
        'sh': 'i-lucide-terminal',
        'bash': 'i-lucide-terminal',
        'zsh': 'i-lucide-terminal',
        'json': 'i-lucide-braces',
        'yml': 'i-lucide-file-cog',
        'yaml': 'i-lucide-file-cog',
        'md': 'i-lucide-file-text',
        'txt': 'i-lucide-file-text',
        'csv': 'i-lucide-table',
        'package.json': 'i-simple-icons-npm',
        '.env': 'i-lucide-key-round',
        '.gitignore': 'i-simple-icons-git'
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
    youtube: '',
    /**
     * YouTube id of the trailer that plays inside the television on the front
     * page — the 11 characters after `watch?v=`, nothing else.
     *
     * It loads muted, looping, without controls, and only once the set has
     * scrolled into view. Empty leaves the animated title card in the tube
     * instead, which is why this can stay empty forever without anything
     * looking broken.
     */
    storyVideo: 'CGoFUxCmBdA',
    /** Seconds into that video to start each loop. 0 starts at the beginning. */
    storyVideoStart: 100,
    /** Booking link on the Questions page. Empty hides the button. */
    topmate: 'https://topmate.io/swarnil'
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
  /**
   * Where the newsletter form posts.
   *
   * The site is static, so there is no endpoint of our own to post to — set
   * this to a provider's form URL (Buttondown, ConvertKit, Formspark, a Worker,
   * whatever) and the form starts working. Empty means the component renders a
   * disabled state rather than quietly discarding addresses.
   */
  newsletter: {
    action: ''
  },

  ads: {
    /** Master switch. No real ad renders while this is off. */
    enabled: false,
    /**
     * Draw the reserved boxes even when ads are off, labelled and dashed.
     * Makes the layout honest during development and proves the size
     * reservation works before a single real ad exists. Turn off for production
     * while `enabled` is still false.
     */
    showPlaceholders: true,
    /** `house` shows the built-in promo; `adsense` loads a third-party script. */
    provider: 'house' as 'house' | 'adsense' | 'none',
    /** Publisher id, for the adsense provider only. */
    client: '',
    /** Per-placement switches. See app/utils/ads.ts for what each one is. */
    slots: {
      'in-article': true,
      'lesson-footer': true,
      'sidebar': true,
      'rail-bottom': false,
      'path-parallax': false
    },
    /** Inject an in-article slot automatically after this many blocks. 0 = never. */
    autoInsertAfterBlocks: 0
  }
})
