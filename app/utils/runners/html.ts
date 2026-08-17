import type { RunnerAdapter } from './types'
import { harness, runInSandbox } from './sandbox'

/**
 * HTML, rendered in a sandboxed frame.
 *
 * The author writes a fragment, not a document — teaching "here is a form"
 * should not require boilerplate — so the doctype and charset are added here.
 * A full document is passed through untouched.
 */
export default {
  run(source) {
    const isDocument = /<!doctype|<html[\s>]/i.test(source)

    return runInSandbox({
      visual: true,
      build: token => isDocument
        ? source + harness(token, '')
        : `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body>${source}${harness(token, '')}</body></html>`
    })
  }
} satisfies RunnerAdapter
