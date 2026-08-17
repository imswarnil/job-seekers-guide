import type { RunResult } from './types'

/**
 * Run untrusted code in a throwaway iframe.
 *
 * `sandbox="allow-scripts"` without `allow-same-origin` puts the frame in an
 * opaque origin: it cannot read this page's DOM, its cookies or its storage, and
 * it cannot navigate the top frame. The only channel back out is `postMessage`,
 * which is why console output is shimmed rather than read.
 *
 * Shared by the html, css and javascript adapters — the only difference between
 * them is what gets written into the document.
 */

const TIMEOUT_MS = 5000

/**
 * The prelude every sandboxed document gets: console redirected to
 * postMessage, errors caught, and a `done` signal at the end. The token is
 * handed in so that a stale frame from a previous run cannot post into this one.
 */
export function harness(token: string, body: string) {
  return `<script>
(function () {
  var TOKEN = ${JSON.stringify(token)};
  function send(type, args) {
    parent.postMessage({
      token: TOKEN,
      type: type,
      text: Array.prototype.map.call(args, function (a) {
        if (typeof a === 'string') return a
        try { return JSON.stringify(a, null, 2) } catch (e) { return String(a) }
      }).join(' ')
    }, '*')
  }
  console.log = function () { send('log', arguments) };
  console.info = console.log;
  console.debug = console.log;
  console.warn = function () { send('log', arguments) };
  console.error = function () { send('error', arguments) };
  window.onerror = function (message) { send('error', [message]); return true };
  window.addEventListener('unhandledrejection', function (e) { send('error', [String(e.reason)]) });
  try {
${body}
  } catch (e) {
    send('error', [e && e.stack ? e.stack : String(e)])
  }
  parent.postMessage({ token: TOKEN, type: 'done' }, '*')
})()
</script>`
}

interface SandboxOptions {
  /** Builds the full document. Receives the token to pass to `harness`. */
  build: (token: string) => string
  /** Return the document as rendered output, for html and css. */
  visual?: boolean
}

export function runInSandbox({ build, visual }: SandboxOptions): Promise<RunResult> {
  return new Promise((resolve) => {
    const token = Math.random().toString(36).slice(2)
    const srcdoc = build(token)
    const started = performance.now()

    const frame = document.createElement('iframe')
    frame.setAttribute('sandbox', 'allow-scripts')
    frame.setAttribute('aria-hidden', 'true')
    frame.style.cssText = 'position:absolute;width:0;height:0;border:0;visibility:hidden'

    const out: string[] = []
    const err: string[] = []
    let settled = false

    function finish(ok: boolean, extra?: string) {
      if (settled) {
        return
      }
      settled = true

      window.removeEventListener('message', onMessage)
      clearTimeout(timer)
      frame.remove()

      if (extra) {
        err.push(extra)
      }

      resolve({
        stdout: out.join('\n'),
        stderr: err.join('\n'),
        ok: ok && !err.length,
        ms: Math.round(performance.now() - started),
        html: visual ? srcdoc : undefined
      })
    }

    function onMessage(event: MessageEvent) {
      const data = event.data
      if (!data || data.token !== token) {
        return
      }

      if (data.type === 'log') {
        out.push(data.text)
      } else if (data.type === 'error') {
        err.push(data.text)
      } else if (data.type === 'done') {
        finish(true)
      }
    }

    const timer = setTimeout(
      () => finish(false, `Stopped after ${TIMEOUT_MS / 1000}s — the code did not finish. An infinite loop is the usual reason.`),
      TIMEOUT_MS
    )

    window.addEventListener('message', onMessage)

    frame.srcdoc = srcdoc
    document.body.appendChild(frame)
  })
}
