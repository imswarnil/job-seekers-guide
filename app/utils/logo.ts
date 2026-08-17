/**
 * The mark, as data.
 *
 * Two marks came before this one and both were wrong in the same way: they were
 * a shape on a coloured square. Three rising bars said "chart". A magnifier on a
 * plate said "search", and searching is the thing this platform argues is *not*
 * the problem — the listings were never hidden.
 *
 * This one is the product: a start node, a trail, a destination. No plate, no
 * background — the mark sits directly on whatever it is placed on, which is why
 * it works on the dark hero band and the light header without a variant.
 *
 * Drawn twice: animated in the browser (`AppLogo.vue`) and as a still frame by
 * the OG image renderer (`OgImage/Guide.takumi.vue`), which cannot run CSS.
 */

/** Viewbox is 32×32. The trail runs bottom-left to top-right — the direction of travel. */
export const logoTrail = {
  /** Where you are now. Small, solid, unremarkable. */
  start: { cx: 5.5, cy: 25.5, r: 3 },

  /** The route. One curve rather than a straight line: it is not a shortcut. */
  path: 'M5.5 25.5C5.5 25.5 9 17.5 16 16C23 14.5 26 7 26 7',

  /** Waypoints along the way — the subjects between here and employed. */
  stops: [
    { cx: 11.5, cy: 19.5, r: 1.6 },
    { cx: 20, cy: 12.5, r: 1.6 }
  ],

  /** Where you are going. Larger, ringed, in the accent. */
  end: { cx: 26, cy: 7, r: 4.5 }
} as const

/**
 * Literal colours, for renderers with no access to the stylesheet. Keep in step
 * with `--color-guide-600` and `--color-spark-400` in app/assets/css/main.css.
 */
export const logoColors = {
  trail: '#4338ca',
  stop: '#a099f5',
  end: '#2dd4bf'
} as const
