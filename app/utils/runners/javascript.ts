import type { RunnerAdapter } from './types'
import { harness, runInSandbox } from './sandbox'

/** JavaScript, in a sandboxed frame. Zero bytes of dependency. */
export default {
  run(source) {
    return runInSandbox({
      build: token => `<!doctype html><meta charset="utf-8">${harness(token, source)}`
    })
  }
} satisfies RunnerAdapter
