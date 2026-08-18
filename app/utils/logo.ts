/**
 * The mark, as data.
 *
 * A magnifier over a shortlist. Three bars inside the glass are the listings;
 * the teal one is the match. That is the whole product in one shape — the
 * listings were never hidden, the *right* one was, and this site is about
 * finding it rather than about searching harder.
 *
 * Deliberately static. The mark sits in the header on every page of a site
 * somebody reads for an hour at a time, and a logo that redraws itself on every
 * navigation is a tic, not a brand. Motion is spent on the illustrations, where
 * it is carrying meaning.
 *
 * No plate and no background, so it works on the light header and the dark
 * bands without a second version of itself. Two colours, no gradients, no
 * strokes thinner than 2 — it has to survive a 16px favicon.
 *
 * Drawn twice: in the browser (`AppLogo.vue`) and as a still frame by the OG
 * image renderer (`OgImage/Guide.takumi.vue`), which shares this file so the
 * two can never drift.
 */

/** Viewbox is 32×32. */
export const logoMark = {
  /** The glass. */
  lens: { cx: 13, cy: 13, r: 9.4 },
  lensWidth: 2.6,

  /** The grip, leaving the ring at forty-five degrees. */
  handle: 'M19.7 19.7L28.2 28.2',
  handleWidth: 3.2,

  /**
   * The shortlist, seen through the glass. Widths are set so every bar clears
   * the inner edge of the ring — a bar that collides with the circle turns the
   * whole mark to mush at small sizes.
   */
  rows: [
    { x: 8.3, y: 8.6, w: 9.4, h: 2, match: false },
    { x: 8.3, y: 12, w: 6.4, h: 2, match: true },
    { x: 8.3, y: 15.4, w: 8.2, h: 2, match: false }
  ]
} as const

/**
 * Literal colours, for renderers with no access to the stylesheet. Keep in step
 * with `--color-guide-600`, `--color-guide-300` and `--color-spark-400` in
 * app/assets/css/main.css.
 */
export const logoColors = {
  frame: '#4338ca',
  row: '#a099f5',
  match: '#2dd4bf'
} as const
