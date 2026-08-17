/**
 * The mark, as data.
 *
 * The old mark was three rising bars — a chart, meaning "progress", meaning
 * nothing. It could have belonged to an analytics dashboard. This one says the
 * actual thing: a magnifier over a job listing. Somebody looking for work.
 *
 * The geometry lives here because it is drawn twice — animated in the browser
 * (`AppLogo.vue`) and as a still frame by the OG image renderer
 * (`OgImage/Guide.takumi.vue`), which cannot run CSS. When they were two copies
 * of hardcoded markup they drifted.
 *
 * Viewbox is 32×32. The listing is a card with three lines of text; the
 * magnifier sits over its bottom-right corner and sweeps across on entry.
 */

/** The lines of "text" on the listing behind the glass. */
export interface LogoLine {
  x: number
  y: number
  width: number
  /** The line the magnifier lands on — drawn in the accent. */
  accent?: boolean
}

export const logoLines: LogoLine[] = [
  { x: 9, y: 10.5, width: 11 },
  { x: 9, y: 14, width: 8 },
  { x: 9, y: 17.5, width: 9.5, accent: true }
]

/** The card the lines sit on. */
export const logoCard = {
  x: 6,
  y: 5.5,
  width: 17,
  height: 17,
  radius: 3
}

/** The lens: centre and radius, plus the handle it drags behind it. */
export const logoLens = {
  cx: 20.5,
  cy: 19,
  r: 6,
  handle: { x1: 24.9, y1: 23.4, x2: 27.5, y2: 26 }
}

export const logoLineHeight = 1.6

/**
 * Literal colours, for renderers with no access to the stylesheet. Keep in step
 * with `--color-guide-600` and `--color-spark-400` in app/assets/css/main.css.
 */
export const logoColors = {
  plate: '#4338ca',
  card: '#ffffff',
  line: '#ffffff',
  accent: '#2dd4bf'
} as const
