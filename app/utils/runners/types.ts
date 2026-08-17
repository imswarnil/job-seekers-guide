/**
 * The contract every language adapter implements.
 *
 * Nothing in here imports anything. The whole bundle-size story for the runner
 * depends on adapters being reachable only through the literal dynamic imports
 * in `useRunner`, so this file must stay free of runtime dependencies.
 */

export type RunnerLang = 'html' | 'css' | 'javascript' | 'python' | 'sql' | 'java'

export interface RunResult {
  stdout: string
  stderr: string
  ok: boolean
  /** Wall-clock milliseconds, shown so a reader can see what "slow" costs. */
  ms: number
  /** Rendered output, for html/css — displayed in a sandboxed frame. */
  html?: string
}

export interface RunnerAdapter {
  run(source: string, stdin?: string): Promise<RunResult>
  /** Shown while a heavy runtime downloads. */
  loadingMessage?: string
}

export const runnerLabels: Record<RunnerLang, string> = {
  html: 'HTML',
  css: 'CSS',
  javascript: 'JavaScript',
  python: 'Python',
  sql: 'SQL',
  java: 'Java'
}

/** Which adapters need the network on first run, so the UI can warn honestly. */
export const runnerWeight: Record<RunnerLang, string> = {
  html: '',
  css: '',
  javascript: '',
  python: 'Downloads a ~12 MB Python runtime the first time. Cached afterwards.',
  sql: 'Downloads a ~1.5 MB SQLite engine the first time. Cached afterwards.',
  java: 'Runs on a server, because compiling Java in a browser is not a thing.'
}
