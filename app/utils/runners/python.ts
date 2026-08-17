import type { RunnerAdapter } from './types'
import { CDN, loadScript } from './cdn'

/**
 * Python, via Pyodide — CPython compiled to WebAssembly, running in the tab.
 *
 * Loaded from a CDN on first Run, never as a dependency. The interpreter is kept
 * alive between runs of the same page because starting it is by far the
 * expensive part; the globals are cleared each time so one run cannot leak state
 * into the next and make a broken example look like it works.
 */

interface Pyodide {
  runPythonAsync: (code: string) => Promise<unknown>
  setStdout: (options: { batched: (text: string) => void }) => void
  setStderr: (options: { batched: (text: string) => void }) => void
  setStdin: (options: { stdin: () => string }) => void
  globals: { clear: () => void }
}

let instance: Pyodide | undefined

async function boot(): Promise<Pyodide> {
  if (instance) {
    return instance
  }

  await loadScript(`${CDN.pyodide}pyodide.js`)

  const factory = (window as unknown as { loadPyodide?: (o: object) => Promise<Pyodide> }).loadPyodide
  if (!factory) {
    throw new Error('Pyodide loaded but did not register itself.')
  }

  instance = await factory({ indexURL: CDN.pyodide })
  return instance
}

export default {
  loadingMessage: 'Starting Python — about 12 MB the first time, cached after that.',

  async run(source, stdin) {
    const started = performance.now()
    const out: string[] = []
    const err: string[] = []

    try {
      const py = await boot()

      py.setStdout({ batched: text => out.push(text) })
      py.setStderr({ batched: text => err.push(text) })

      // stdin is handed over one line per call, which is what `input()` expects.
      const lines = (stdin || '').split('\n')
      let cursor = 0
      py.setStdin({ stdin: () => lines[cursor++] ?? '' })

      py.globals.clear()
      await py.runPythonAsync(source)
    } catch (error) {
      // Pyodide puts the Python traceback in the message, which is the useful
      // half — a JS stack on top of it would only be noise to the reader.
      err.push(error instanceof Error ? error.message : String(error))
    }

    return {
      stdout: out.join('\n'),
      stderr: err.join('\n'),
      ok: !err.length,
      ms: Math.round(performance.now() - started)
    }
  }
} satisfies RunnerAdapter
