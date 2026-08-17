import type { RunStage, Visualisation } from './visualise'
import { CDN, loadScript } from './cdn'
import { stage } from './visualise'

/**
 * What actually happens when SQLite runs your query.
 *
 * Every figure here comes out of the engine. The query plan is SQLite's own
 * `EXPLAIN QUERY PLAN` for the exact statement typed. The row count at each
 * stage is measured by **executing** a cut-down version of the query and
 * counting what comes back — so "WHERE removed 3,180 rows" is a fact about this
 * data, not an illustration of what a WHERE clause does in general.
 *
 * That is the whole point. A learner who sees `GROUP BY: 4200 → 3` and can
 * change one line and watch it become `4200 → 7` has understood grouping. A
 * learner shown a diagram of grouping has seen a diagram.
 */

interface QueryResult { columns: string[], values: unknown[][] }
interface Database { exec: (sql: string) => QueryResult[], close: () => void, run: (sql: string) => void }
interface SqlJs { Database: new () => Database }

let engine: SqlJs | undefined

async function boot(): Promise<SqlJs> {
  if (engine) {
    return engine
  }
  await loadScript(`${CDN.sqlJs}sql-wasm.js`)
  const factory = (window as unknown as { initSqlJs?: (o: object) => Promise<SqlJs> }).initSqlJs
  if (!factory) {
    throw new Error('sql.js loaded but did not register itself.')
  }
  engine = await factory({ locateFile: (file: string) => `${CDN.sqlJs}${file}` })
  return engine
}

/** Split on semicolons that are not inside a string literal. */
export function splitStatements(sql: string): string[] {
  const out: string[] = []
  let current = ''
  let quote: string | undefined

  for (let i = 0; i < sql.length; i++) {
    const char = sql[i]!

    if (quote) {
      current += char
      if (char === quote) {
        quote = undefined
      }
      continue
    }

    if (char === '\'' || char === '"') {
      quote = char
      current += char
      continue
    }

    if (char === ';') {
      if (current.trim()) {
        out.push(current.trim())
      }
      current = ''
      continue
    }

    current += char
  }

  if (current.trim()) {
    out.push(current.trim())
  }
  return out
}

export interface PlanRow {
  id: number
  parent: number
  detail: string
}

export interface FunnelStep {
  label: string
  clause: string
  rows: number
  /** Change from the previous step. Negative is rows removed. */
  delta: number
}

export interface SqlVisualData {
  plan: PlanRow[]
  funnel: FunnelStep[]
  columns: string[]
  rows: unknown[][]
  statements: number
  /** Set when the funnel could not be measured, with the honest reason. */
  funnelNote?: string
}

/**
 * Rebuild the final SELECT with its trailing clauses progressively removed, so
 * each cut-down version can be executed and counted.
 *
 * Only the clauses that are safe to strip from the end are handled — this is
 * string surgery on SQL, and the failure mode has to be "no funnel shown"
 * rather than "a wrong funnel shown". Anything it cannot parse confidently
 * returns nothing and the panel says so.
 */
function buildFunnelQueries(statement: string): { label: string, clause: string, sql: string }[] | undefined {
  // Statements are split on semicolons, so a comment written above a query
  // arrives attached to the front of it. Strip leading comments before deciding
  // what kind of statement this is, or every commented query is refused.
  const select = statement
    .replace(/^(?:\s*(?:--[^\n]*\n|\/\*[\s\S]*?\*\/))+\s*/, '')
    .trim()

  if (!/^select\b/i.test(select)) {
    return undefined
  }

  // A subquery or a CTE means the clause positions found below could belong to
  // an inner query, and counting those would be a lie. Refuse instead.
  if (/\bwith\b/i.test(select) || /\bselect\b[\s\S]*\(\s*select\b/i.test(select)) {
    return undefined
  }

  /**
   * Find a clause keyword. Matched as a regex on whitespace rather than a
   * literal " from " — real SQL is written across lines, and searching for
   * spaces silently fails on every formatted query.
   */
  const at = (keyword: string, after = 0): number | undefined => {
    const re = new RegExp(`\\s${keyword.replace(/ /g, '\\s+')}\\s`, 'i')
    const found = re.exec(select.slice(after))
    return found ? after + found.index : undefined
  }

  const from = at('from')
  if (from === undefined) {
    return undefined
  }

  const where = at('where', from)
  const group = at('group by', from)
  const having = at('having', from)
  const order = at('order by', from)
  const limit = at('limit', from)

  /** The nearest clause that starts after `position`, i.e. where it ends. */
  const endOf = (position: number) =>
    [where, group, having, order, limit]
      .filter((n): n is number => n !== undefined && n > position)
      .sort((a, b) => a - b)[0]

  const fromClause = select.slice(from, endOf(from))
  const whereClause = where === undefined ? '' : select.slice(where, endOf(where))
  const groupClause = group === undefined ? '' : select.slice(group, endOf(group))

  const steps: { label: string, clause: string, sql: string }[] = []

  // Base: the rows in the table, before anything filters them.
  steps.push({
    label: 'Source rows',
    clause: fromClause.trim().replace(/\s+/g, ' '),
    sql: `SELECT COUNT(*) ${fromClause}`
  })

  if (whereClause) {
    steps.push({
      label: 'After WHERE',
      clause: whereClause.trim().replace(/\s+/g, ' '),
      sql: `SELECT COUNT(*) ${fromClause} ${whereClause}`
    })
  }

  if (groupClause) {
    steps.push({
      label: 'After GROUP BY',
      clause: groupClause.trim().replace(/\s+/g, ' '),
      // Counting groups means counting the rows the grouped query returns.
      sql: `SELECT COUNT(*) FROM (SELECT 1 ${fromClause} ${whereClause} ${groupClause})`
    })
  }

  return steps
}

export async function analyseSql(source: string): Promise<{ visual: Visualisation, stdout: string, stderr: string, ok: boolean, ms: number }> {
  const started = performance.now()

  const stages: RunStage[] = [
    stage('parse', 'Parse', 'SQLite reads the text and checks it is valid SQL.', 'i-lucide-file-code'),
    stage('plan', 'Plan', 'The query planner decides how to find the rows — scan, or use an index.', 'i-lucide-git-branch'),
    stage('execute', 'Execute', 'The plan runs against the actual data.', 'i-lucide-play'),
    stage('return', 'Return', 'The rows that survived every clause come back.', 'i-lucide-table')
  ]

  const set = (id: string, patch: Partial<RunStage>) => {
    const found = stages.find(s => s.id === id)
    if (found) {
      Object.assign(found, patch)
    }
  }

  const statements = splitStatements(source)
  const last = statements[statements.length - 1]

  let db: Database | undefined

  try {
    const SQL = await boot()
    db = new SQL.Database()

    // Everything before the final statement is setup — schema and data. It runs
    // first so the query being visualised has something real to run against.
    const setup = statements.slice(0, -1)
    for (const statement of setup) {
      db.exec(statement)
    }

    set('parse', {
      status: 'ok',
      metric: String(statements.length),
      metricLabel: statements.length === 1 ? 'statement' : 'statements'
    })

    if (!last) {
      throw new Error('There is no statement to run.')
    }

    // The plan, straight from SQLite. Not a description of what it might do.
    let plan: PlanRow[] = []
    try {
      const explained = db.exec(`EXPLAIN QUERY PLAN ${last}`)
      const table = explained[0]
      if (table) {
        const idx = (name: string) => table.columns.indexOf(name)
        plan = table.values.map(row => ({
          id: Number(row[idx('id')] ?? 0),
          parent: Number(row[idx('parent')] ?? 0),
          detail: String(row[idx('detail')] ?? '')
        }))
      }
      set('plan', {
        status: 'ok',
        metric: String(plan.length),
        metricLabel: plan.length === 1 ? 'step' : 'steps'
      })
    } catch {
      // Not every statement has a plan — INSERT and CREATE do not.
      set('plan', { status: 'skipped', detail: 'This statement has no query plan — only queries do.' })
    }

    // The funnel: each clause's effect, measured by running it.
    let funnel: FunnelStep[] = []
    let funnelNote: string | undefined

    const queries = buildFunnelQueries(last)
    if (!queries) {
      funnelNote = 'The row funnel is only measured for a single, straightforward SELECT. This statement is something else, so nothing is guessed here.'
    } else {
      try {
        let previous: number | undefined
        for (const query of queries) {
          const result = db.exec(query.sql)
          const rows = Number(result[0]?.values[0]?.[0] ?? 0)
          funnel.push({
            label: query.label,
            clause: query.clause,
            rows,
            delta: previous === undefined ? 0 : rows - previous
          })
          previous = rows
        }
      } catch {
        funnel = []
        funnelNote = 'The row funnel could not be measured for this query, so it is not shown rather than estimated.'
      }
    }

    const executeAt = performance.now()
    const results = db.exec(last)
    const executeMs = Math.round(performance.now() - executeAt)

    const table = results[0]
    const rowCount = table?.values.length ?? 0

    set('execute', { status: 'ok', ms: executeMs })

    // The final row count belongs on the funnel too — it is the last measurement
    // and it is the one the reader can see in the table below.
    if (funnel.length) {
      const previous = funnel[funnel.length - 1]!.rows
      funnel.push({
        label: 'Returned',
        clause: 'SELECT',
        rows: rowCount,
        delta: rowCount - previous
      })
    }

    set('return', {
      status: 'ok',
      metric: String(rowCount),
      metricLabel: rowCount === 1 ? 'row' : 'rows'
    })

    const data: SqlVisualData = {
      plan,
      funnel,
      columns: table?.columns ?? [],
      rows: table?.values ?? [],
      statements: statements.length,
      funnelNote
    }

    return {
      visual: { stages, kind: 'sql', data },
      stdout: table ? '' : 'Statement ran. No rows returned.',
      stderr: '',
      ok: true,
      ms: Math.round(performance.now() - started)
    }
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error)
    const pending = stages.find(s => s.status === 'pending')
    if (pending) {
      pending.status = 'failed'
      pending.detail = message
    }
    for (const s of stages) {
      if (s.status === 'pending') {
        s.status = 'skipped'
      }
    }

    return {
      visual: { stages, kind: 'sql' },
      stdout: '',
      stderr: message,
      ok: false,
      ms: Math.round(performance.now() - started)
    }
  } finally {
    db?.close()
  }
}
