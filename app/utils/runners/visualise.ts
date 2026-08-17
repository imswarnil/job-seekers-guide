/**
 * Shared shapes for the run visualiser.
 *
 * The rule this file exists to enforce: **every number shown to a reader is
 * measured, never assumed.** A visualiser that draws a plausible-looking
 * pipeline from a template teaches people to trust a picture that does not
 * correspond to anything, which is worse than showing them nothing.
 *
 * So each stage carries where its numbers came from. `measured` means the
 * engine or the compiler produced it. `parsed` means it was read out of the
 * source the reader typed. Nothing is invented, and the UI labels the
 * difference rather than hiding it.
 */

export type StageStatus = 'pending' | 'running' | 'ok' | 'failed' | 'skipped'

export interface RunStage {
  id: string
  label: string
  /** What this stage does, in one line, for somebody who has never seen it. */
  hint: string
  icon: string
  status: StageStatus
  /** Milliseconds, when the stage was actually timed. */
  ms?: number
  /** The headline figure: rows out, bytes of bytecode, lines printed. */
  metric?: string
  metricLabel?: string
  /** Compiler errors, engine messages — the real text, not a summary. */
  detail?: string
  /** How the figures on this stage were obtained. */
  source: 'measured' | 'parsed'
}

export interface Visualisation {
  stages: RunStage[]
  /** Rendered by the language-specific panel under the pipeline. */
  kind: 'sql' | 'java' | 'plain'
  /** Free-form payload for that panel. */
  data?: unknown
}

export function stage(
  id: string,
  label: string,
  hint: string,
  icon: string,
  source: RunStage['source'] = 'measured'
): RunStage {
  return { id, label, hint, icon, status: 'pending', source }
}
