import type { RunnerAdapter, RunResult } from './types'

/**
 * Java, compiled and run on a server.
 *
 * This is the one component on the site that needs an origin. Java cannot run in
 * a browser at any acceptable cost — the WebAssembly JVMs are multiple megabytes
 * and none of them give you `javac`, which is the point: half the teaching value
 * of a Java runner is the compile error.
 *
 * The endpoint is a small Cloudflare Worker (see `workers/runner/`) rather than
 * a public execution API called directly. The Worker is where the key lives, it
 * locks CORS to this origin, it rate-limits per IP, and it caches by hash — and
 * every learner runs the same starter code, so the hit rate is enormous. If the
 * Worker is not deployed, this degrades to a clear message and a link out rather
 * than a spinner that never resolves.
 */

const TIMEOUT_MS = 15000

export default {
  loadingMessage: 'Compiling on the server — javac has to see the code, so this one leaves the browser.',

  async run(source, stdin): Promise<RunResult> {
    const started = performance.now()

    // The endpoint is configuration, not a constant: the site is static and can
    // be hosted anywhere, while the Worker lives wherever it was deployed.
    const endpoint = useRuntimeConfig().public.runnerUrl

    if (!endpoint) {
      return {
        stdout: '',
        stderr: 'The Java runner is not configured for this deployment. Every other language on this page runs in your browser and still works — Java needs a server, because compiling it needs javac.',
        ok: false,
        ms: 0
      }
    }

    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), TIMEOUT_MS)

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({ language: 'java', source, stdin: stdin || '' }),
        signal: controller.signal
      })

      if (response.status === 429) {
        return {
          stdout: '',
          stderr: 'Too many runs from this network in the last minute. Wait a moment and try again.',
          ok: false,
          ms: Math.round(performance.now() - started)
        }
      }

      if (!response.ok) {
        throw new Error(`The runner replied ${response.status}.`)
      }

      const data = await response.json() as { stdout?: string, stderr?: string, ok?: boolean }

      return {
        stdout: data.stdout || '',
        stderr: data.stderr || '',
        ok: data.ok ?? !data.stderr,
        ms: Math.round(performance.now() - started)
      }
    } catch (error) {
      const aborted = error instanceof Error && error.name === 'AbortError'

      return {
        stdout: '',
        stderr: aborted
          ? `The server did not answer within ${TIMEOUT_MS / 1000}s.`
          : `Could not reach the Java runner. Everything else on this page still works — this is the one piece that needs a server.\n\n${error instanceof Error ? error.message : String(error)}`,
        ok: false,
        ms: Math.round(performance.now() - started)
      }
    } finally {
      clearTimeout(timer)
    }
  }
} satisfies RunnerAdapter
