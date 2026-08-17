import type { RunnerAdapter } from './types'
import { harness, runInSandbox } from './sandbox'

/**
 * CSS, applied to sample markup.
 *
 * A stylesheet with nothing to style teaches nothing, so the runner supplies a
 * small document to style. Authors override it by putting their own markup after
 * a `/* --- html --- *\/` divider in the same block, which keeps a CSS lesson to
 * a single editor.
 */
const DEFAULT_MARKUP = `
<div class="card">
  <h2>A card</h2>
  <p>Edit the CSS and press Run.</p>
  <button>An action</button>
</div>
`

export default {
  run(source) {
    const [css = '', markup] = source.split(/\/\*\s*-{2,}\s*html\s*-{2,}\s*\*\//i)

    return runInSandbox({
      visual: true,
      build: token => `<!doctype html><html><head><meta charset="utf-8">`
        + `<meta name="viewport" content="width=device-width,initial-scale=1">`
        + `<style>body{font-family:system-ui,sans-serif;margin:1rem}</style>`
        + `<style>${css}</style></head>`
        + `<body>${markup || DEFAULT_MARKUP}${harness(token, '')}</body></html>`
    })
  }
} satisfies RunnerAdapter
