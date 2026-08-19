/**
 * Teaching diagrams are inlined into lesson markup, so every byte ships on every
 * page view. This strips what a hand-drawn or generated SVG carries by default
 * without touching the two things the diagrams depend on.
 *
 * Kept on purpose:
 *   - viewBox, so a diagram scales inside DgmFigure's scroll container
 *   - ids and class names, because the lesson CSS and any step-through
 *     visualiser target them
 */
export default {
  multipass: true,
  js2svg: { indent: 2, pretty: true },
  plugins: [
    {
      name: 'preset-default',
      params: {
        overrides: {
          removeViewBox: false,
          cleanupIds: false
        }
      }
    },
    'removeDimensions',
    'sortAttrs'
  ]
}
