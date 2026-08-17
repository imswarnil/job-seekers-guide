/**
 * The mark, as data.
 *
 * Three steps rising, the last one picked out in the accent — the product in one
 * glyph: a sequence, going somewhere, with the next step marked. The geometry
 * lives here because it is drawn twice: once as an animated SVG in the browser
 * (`AppLogo.vue`) and once as a still frame by the OG image renderer
 * (`OgImage/Guide.takumi.vue`), which cannot run CSS. When they were two copies
 * of hardcoded markup they drifted.
 */

export interface LogoBar {
  /** Rounded-rect path, drawn from the baseline up. */
  d: string
  /** Rise order — index into the animation stagger. */
  step: number
  /** The last step is the accent one: where you are going, not where you are. */
  accent?: boolean
}

export const logoBars: LogoBar[] = [
  { d: 'M8 22.5H12.5V17.5H8V22.5Z', step: 0 },
  { d: 'M13.75 22.5H18.25V13H13.75V22.5Z', step: 1 },
  { d: 'M19.5 22.5H24V8.5H19.5V22.5Z', step: 2, accent: true }
]

/** Opacity of the two non-accent bars, so the eye lands on the third. */
export const logoBarOpacity = [0.55, 0.8, 1]

/**
 * Literal colours, for renderers with no access to the stylesheet. Keep in step
 * with `--color-guide-600` and `--color-spark-400` in app/assets/css/main.css.
 */
export const logoColors = {
  plate: '#4338ca',
  bar: '#ffffff',
  accent: '#2dd4bf'
} as const
