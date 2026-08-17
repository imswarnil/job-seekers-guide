/**
 * Load a script from a CDN, once, at the moment it is first needed.
 *
 * Pyodide and sql.js are deliberately **not** npm dependencies. Twelve megabytes
 * of WebAssembly in the dependency graph would be pulled into the build, walked
 * by the prerenderer and chunked by Vite for the sake of the handful of lessons
 * that use them. Loading them by URL at click time keeps them out of the graph
 * entirely — the cost is paid by the reader who presses Run on a Python lesson
 * and by nobody else.
 */

const loaded = new Map<string, Promise<void>>()

export function loadScript(url: string): Promise<void> {
  const existing = loaded.get(url)
  if (existing) {
    return existing
  }

  const promise = new Promise<void>((resolve, reject) => {
    const script = document.createElement('script')
    script.src = url
    script.async = true
    script.onload = () => resolve()
    script.onerror = () => reject(new Error(`Could not load ${url}. Check the network connection.`))
    document.head.appendChild(script)
  })

  loaded.set(url, promise)
  return promise
}

export const CDN = {
  pyodide: 'https://cdn.jsdelivr.net/pyodide/v0.28.5/full/',
  sqlJs: 'https://cdn.jsdelivr.net/npm/sql.js@1.13.0/dist/'
} as const
