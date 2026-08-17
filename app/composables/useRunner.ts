import type { RunnerAdapter, RunnerLang, RunResult } from '~/utils/runners/types'

/**
 * Loads the adapter for one language, on demand.
 *
 * The `switch` of literal `import()` calls is not stylistic. Vite splits a
 * chunk per literal specifier; a template literal (`import(\`./${lang}.ts\`)`)
 * makes it bundle every possible match into one chunk, which would put the
 * Python and SQL loaders into the same download as the JavaScript one. Writing
 * them out is what keeps a lesson that runs JavaScript from paying for SQLite.
 */
function adapterFor(lang: RunnerLang): Promise<{ default: RunnerAdapter }> {
  switch (lang) {
    case 'html':
      return import('~/utils/runners/html')
    case 'css':
      return import('~/utils/runners/css')
    case 'javascript':
      return import('~/utils/runners/javascript')
    case 'python':
      return import('~/utils/runners/python')
    case 'sql':
      return import('~/utils/runners/sql')
    case 'java':
      return import('~/utils/runners/java')
  }
}

export function useRunner(lang: MaybeRefOrGetter<RunnerLang>) {
  const running = ref(false)
  const loading = ref('')
  const result = ref<RunResult>()

  const cache = new Map<RunnerLang, RunnerAdapter>()

  async function run(source: string, stdin?: string) {
    const language = toValue(lang)

    running.value = true
    result.value = undefined

    try {
      let adapter = cache.get(language)

      if (!adapter) {
        // Only now — on a click, never on mount — does anything download.
        const module = await adapterFor(language)
        adapter = module.default
        cache.set(language, adapter)
      }

      loading.value = adapter.loadingMessage || ''
      result.value = await adapter.run(source, stdin)
    } catch (error) {
      result.value = {
        stdout: '',
        stderr: error instanceof Error ? error.message : String(error),
        ok: false,
        ms: 0
      }
    } finally {
      running.value = false
      loading.value = ''
    }
  }

  return { run, running, loading, result }
}
