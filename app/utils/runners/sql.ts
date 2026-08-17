import type { RunnerAdapter, RunResult } from './types'
import { CDN, loadScript } from './cdn'

/**
 * SQL, against a real SQLite compiled to WebAssembly.
 *
 * A fresh in-memory database per run, so a `CREATE TABLE` at the top of an
 * example always works and nothing carries over from the reader's last attempt.
 * That matters more here than the startup cost: a database lesson where the
 * second run fails because the table already exists teaches the wrong lesson.
 *
 * Results come back as text tables rather than a grid component — the shape of
 * a result set is part of what is being taught, and monospace shows it.
 */

interface SqlValue { toString: () => string }
interface QueryResult { columns: string[], values: SqlValue[][] }
interface Database { exec: (sql: string) => QueryResult[], close: () => void }
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

/** Render a result set as a padded text table. */
function table({ columns, values }: QueryResult): string {
  const rows = values.map(row => row.map(cell => cell === null ? 'NULL' : String(cell)))
  const widths = columns.map((column, i) =>
    Math.max(column.length, ...rows.map(row => (row[i] || '').length))
  )

  const line = (cells: string[]) => cells.map((cell, i) => cell.padEnd(widths[i]!)).join('  ')

  return [
    line(columns),
    widths.map(width => '─'.repeat(width)).join('  '),
    ...rows.map(line)
  ].join('\n')
}

export default {
  loadingMessage: 'Starting SQLite — about 1.5 MB the first time, cached after that.',

  async run(source): Promise<RunResult> {
    const started = performance.now()

    try {
      const SQL = await boot()
      const db = new SQL.Database()

      try {
        const results = db.exec(source)

        const stdout = results.length
          ? results.map(table).join('\n\n')
          : 'Statement ran. No rows returned.'

        return {
          stdout,
          stderr: '',
          ok: true,
          ms: Math.round(performance.now() - started)
        }
      } finally {
        db.close()
      }
    } catch (error) {
      return {
        stdout: '',
        stderr: error instanceof Error ? error.message : String(error),
        ok: false,
        ms: Math.round(performance.now() - started)
      }
    }
  }
} satisfies RunnerAdapter
