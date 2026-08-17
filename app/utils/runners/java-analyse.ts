import type { RunStage, Visualisation } from './visualise'
import { stage } from './visualise'

/**
 * What actually happens when your Java runs.
 *
 * The four stages are real and separately observed: the source is parsed here in
 * the browser, `javac` compiles it on the Worker, the JVM runs the bytecode, and
 * stdout comes back. The Worker reports compile and run outcomes separately,
 * which is the whole reason this is worth drawing — **a compile error and a
 * runtime crash are different failures with different fixes**, and a beginner
 * who cannot tell them apart pastes both into the same search box.
 *
 * The structure panel is parsed from the source the reader typed. It is marked
 * as parsed rather than measured in the UI, because it is: it is a reading of
 * the text, not a report from the JVM. What it is not is a fixed diagram — edit
 * a method away and it disappears.
 */

export interface JavaMember {
  kind: 'class' | 'method' | 'field'
  name: string
  signature: string
  line: number
  /** Nesting, so methods sit under their class. */
  depth: number
}

export interface JavaVisualData {
  members: JavaMember[]
  /** stdout, split into the lines the program actually printed. */
  output: string[]
  /** Where a compile error points, when javac gave a line number. */
  errorLine?: number
  loops: number
  conditionals: number
  prints: number
}

/**
 * A deliberately small reader. It is looking for the shape of a teaching-sized
 * program — classes, methods, fields — not parsing Java. Anything it is unsure
 * about it leaves out, because an outline missing a method is a much smaller
 * problem than an outline inventing one.
 */
export function parseJava(source: string): JavaVisualData {
  const members: JavaMember[] = []
  const lines = source.split('\n')

  const MODIFIERS = '(?:public|private|protected|static|final|abstract|synchronized|native|transient|volatile|default)'
  const classRe = new RegExp(`^\\s*(?:${MODIFIERS}\\s+)*(class|interface|enum|record)\\s+(\\w+)`)
  const methodRe = new RegExp(`^\\s*(?:${MODIFIERS}\\s+)*([\\w<>\\[\\],.?\\s]+?)\\s+(\\w+)\\s*\\(([^)]*)\\)\\s*(?:throws [\\w,.\\s]+)?\\{`)
  const fieldRe = new RegExp(`^\\s*(?:${MODIFIERS}\\s+)+([\\w<>\\[\\],.?]+)\\s+(\\w+)\\s*(?:=[^;]*)?;`)

  let depth = 0

  lines.forEach((raw, index) => {
    const line = raw.replace(/\/\/.*$/, '')
    const trimmed = line.trim()

    if (!trimmed || trimmed.startsWith('*') || trimmed.startsWith('/*') || trimmed.startsWith('import') || trimmed.startsWith('package')) {
      depth += (line.match(/\{/g)?.length || 0) - (line.match(/\}/g)?.length || 0)
      return
    }

    const asClass = classRe.exec(line)
    if (asClass) {
      members.push({
        kind: 'class',
        name: asClass[2]!,
        signature: `${asClass[1]} ${asClass[2]}`,
        line: index + 1,
        depth
      })
      depth += (line.match(/\{/g)?.length || 0) - (line.match(/\}/g)?.length || 0)
      return
    }

    const asMethod = methodRe.exec(line)
    // `if (...) {` and `for (...) {` match the method shape; the keyword check
    // is what keeps control flow out of the outline.
    if (asMethod && !/^\s*(if|for|while|switch|catch|try|else|do|return|new)\b/.test(trimmed)) {
      const returns = asMethod[1]!.trim()
      const name = asMethod[2]!
      const args = asMethod[3]!.trim()
      members.push({
        kind: 'method',
        name,
        signature: `${returns} ${name}(${args})`,
        line: index + 1,
        depth
      })
      depth += (line.match(/\{/g)?.length || 0) - (line.match(/\}/g)?.length || 0)
      return
    }

    const asField = fieldRe.exec(line)
    if (asField && depth > 0) {
      members.push({
        kind: 'field',
        name: asField[2]!,
        signature: `${asField[1]} ${asField[2]}`,
        line: index + 1,
        depth
      })
    }

    depth += (line.match(/\{/g)?.length || 0) - (line.match(/\}/g)?.length || 0)
  })

  const stripped = source.replace(/\/\/.*$/gm, '').replace(/\/\*[\s\S]*?\*\//g, '')

  return {
    members,
    output: [],
    loops: (stripped.match(/\b(for|while)\s*\(/g) || []).length,
    conditionals: (stripped.match(/\b(if|switch)\s*\(/g) || []).length,
    prints: (stripped.match(/System\.out\.print(ln|f)?\s*\(/g) || []).length
  }
}

/** javac reports `Main.java:7: error: …` — pull the line out when it is there. */
function errorLineFrom(stderr: string): number | undefined {
  const match = /:(\d+):\s*error:/.exec(stderr)
  return match ? Number(match[1]) : undefined
}

interface RunnerResponse {
  stdout?: string
  stderr?: string
  ok?: boolean
  compileFailed?: boolean
  cached?: boolean
}

export function buildJavaVisualisation(
  source: string,
  response: RunnerResponse,
  timing: { total: number, network: number }
): Visualisation {
  const parsed = parseJava(source)

  const stages: RunStage[] = [
    stage('read', 'Your source', 'Text in a file. The machine has no idea what it means yet.', 'i-lucide-file-code', 'parsed'),
    stage('compile', 'javac', 'Checks the types and turns the text into bytecode. Errors here are compile errors.', 'i-lucide-cog'),
    stage('jvm', 'The JVM', 'Runs the bytecode. Errors here are runtime errors — a completely different problem.', 'i-lucide-cpu'),
    stage('output', 'Output', 'What the program actually printed.', 'i-lucide-terminal')
  ]

  const set = (id: string, patch: Partial<RunStage>) => {
    Object.assign(stages.find(s => s.id === id)!, patch)
  }

  const nonEmpty = source.split('\n').filter(l => l.trim()).length

  set('read', {
    status: 'ok',
    metric: String(nonEmpty),
    metricLabel: nonEmpty === 1 ? 'line' : 'lines',
    detail: `${parsed.members.filter(m => m.kind === 'class').length} class(es), ${parsed.members.filter(m => m.kind === 'method').length} method(s) — read from your source.`
  })

  const stderr = response.stderr || ''
  const compileFailed = response.compileFailed ?? /error:/.test(stderr)

  if (compileFailed) {
    // The stage that failed is the whole lesson: nothing ran, so nothing could
    // have crashed. Saying that explicitly is the point of the picture.
    set('compile', { status: 'failed', detail: stderr, ms: timing.network })
    set('jvm', { status: 'skipped', detail: 'Never started. The program did not compile, so there was no bytecode to run.' })
    set('output', { status: 'skipped', detail: 'Nothing printed, because nothing ran.' })

    return {
      stages,
      kind: 'java',
      data: { ...parsed, errorLine: errorLineFrom(stderr) } satisfies JavaVisualData
    }
  }

  set('compile', { status: 'ok', ms: timing.network, metricLabel: 'compiled' })

  const output = (response.stdout || '').split('\n').filter((line, i, all) => line !== '' || i < all.length - 1)
  const crashed = Boolean(stderr) && !response.ok

  set('jvm', {
    status: crashed ? 'failed' : 'ok',
    detail: crashed ? stderr : undefined,
    ms: timing.total
  })

  set('output', {
    status: output.length ? 'ok' : 'skipped',
    metric: String(output.length),
    metricLabel: output.length === 1 ? 'line printed' : 'lines printed',
    detail: output.length ? undefined : 'The program ran and printed nothing.'
  })

  return {
    stages,
    kind: 'java',
    data: { ...parsed, output } satisfies JavaVisualData
  }
}
