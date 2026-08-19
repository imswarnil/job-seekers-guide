<script setup lang="ts">
import type { Visualisation } from '~/utils/runners/visualise'

/**
 * The playground.
 *
 * Everything on this page is measured from the code in the editor. The SQL
 * pipeline is SQLite's own query plan and row counts obtained by executing the
 * query; the Java pipeline is a real javac run reported stage by stage. Change a
 * line and the picture changes, because it was never a picture of anything else.
 */
const SAMPLES = {
  sql: {
    label: 'SQL',
    icon: 'i-lucide-database',
    hint: 'Runs a real SQLite compiled to WebAssembly, in this tab.',
    code: `CREATE TABLE applicants (
  name    TEXT,
  branch  TEXT,
  city    TEXT,
  offers  INTEGER
);

INSERT INTO applicants VALUES
  ('Priya',   'mechanical', 'Mahroni',   0),
  ('Arun',    'cse',        'Bangalore', 2),
  ('Fatima',  'commerce',   'Bhopal',    1),
  ('Rohit',   'cse',        'Bangalore', 3),
  ('Anjali',  'civil',      'Indore',    0),
  ('Sameer',  'cse',        'Pune',      1),
  ('Divya',   'mechanical', 'Bangalore', 2),
  ('Kabir',   'commerce',   'Mahroni',   0);

-- Change the WHERE and watch the funnel below move.
SELECT branch,
       COUNT(*)     AS people,
       SUM(offers)  AS offers
FROM applicants
WHERE offers > 0
GROUP BY branch
ORDER BY offers DESC;`
  },
  java: {
    label: 'Java',
    icon: 'i-simple-icons-openjdk',
    hint: 'Compiled by a real javac on a server, because javac does not run in a browser.',
    code: `public class Main {
  static double perSeat(int applicants, int seats) {
    return applicants / seats;
  }

  public static void main(String[] args) {
    int seats = 60;

    for (int year = 2021; year <= 2023; year++) {
      int applicants = 4000 + year;
      System.out.println(year + ": " + perSeat(applicants, seats));
    }
  }
}`
  }
} as const

type Lang = keyof typeof SAMPLES

const route = useRoute()
const router = useRouter()

const lang = ref<Lang>((route.query.lang as Lang) in SAMPLES ? route.query.lang as Lang : 'sql')
const source = ref('')

/** Code travels in the URL, base64'd, so a run is a link somebody can send. */
function decode(value: unknown): string | undefined {
  if (typeof value !== 'string' || !value) {
    return undefined
  }
  try {
    return decodeURIComponent(escape(atob(value.replace(/-/g, '+').replace(/_/g, '/'))))
  } catch {
    return undefined
  }
}

function encode(value: string): string {
  return btoa(unescape(encodeURIComponent(value))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '')
}

source.value = decode(route.query.c) ?? SAMPLES[lang.value].code

/**
 * The page is prerendered with no query string, so a shared link arrives with
 * the server's defaults already baked into the markup. Re-reading the query on
 * mount is what makes `?lang=java&c=…` actually restore a run rather than
 * showing whatever the build happened to render.
 */
onMounted(() => {
  const wanted = route.query.lang
  if (typeof wanted === 'string' && wanted in SAMPLES && wanted !== lang.value) {
    lang.value = wanted as Lang
  }

  const shared = decode(route.query.c)
  if (shared) {
    source.value = shared
  }
})

watch(lang, (next) => {
  source.value = SAMPLES[next].code
  visual.value = undefined
  error.value = ''
})

const running = ref(false)
const loading = ref('')
const error = ref('')
const visual = ref<Visualisation>()

const { public: config } = useRuntimeConfig()

async function run() {
  running.value = true
  error.value = ''
  visual.value = undefined

  try {
    if (lang.value === 'sql') {
      loading.value = 'Starting SQLite — about 1.5 MB the first time, cached after that.'
      const { analyseSql } = await import('~/utils/runners/sql-analyse')
      const result = await analyseSql(source.value)
      visual.value = result.visual
      if (!result.ok) {
        error.value = result.stderr
      }
    } else {
      if (!config.runnerUrl) {
        // Nothing is faked when the Worker is absent. The parse stage is real
        // and still runs; the rest says plainly that it did not happen.
        const { buildJavaVisualisation } = await import('~/utils/runners/java-analyse')
        visual.value = buildJavaVisualisation(
          source.value,
          { stderr: 'The Java runner is not configured for this deployment, so nothing was compiled or run. Everything above the compile stage is still read from your real source.', compileFailed: true },
          { total: 0, network: 0 }
        )
        error.value = 'Java needs a server. SQL, and every runner inside a lesson, works without one.'
        return
      }

      loading.value = 'Compiling on the server — javac has to see the code.'
      const { buildJavaVisualisation } = await import('~/utils/runners/java-analyse')

      const started = performance.now()
      const response = await fetch(config.runnerUrl, {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({ language: 'java', source: source.value, stdin: '' })
      })
      const total = Math.round(performance.now() - started)

      if (!response.ok) {
        throw new Error(`The runner replied ${response.status}.`)
      }

      const data = await response.json()
      visual.value = buildJavaVisualisation(source.value, data, { total, network: total })
      if (!data.ok) {
        error.value = data.stderr || ''
      }
    }
  } catch (thrown) {
    const message = thrown instanceof Error ? thrown.message : String(thrown)
    // "Failed to fetch" is what a browser says for a blocked request, a dead
    // endpoint and an origin the server will not accept. None of those mean
    // anything to a reader, so say what it actually implies.
    error.value = /failed to fetch|networkerror/i.test(message)
      ? `Could not reach the runner.\n\nEverything else on this page runs in your browser and still works — Java is the one piece that needs a server.\n\n(${message})`
      : message
  } finally {
    running.value = false
    loading.value = ''
  }
}

/** Put the current code in the address bar so the run can be shared. */
const { copy, copied, isSupported } = useClipboard({ copiedDuring: 2000 })

function share() {
  const query = { lang: lang.value, c: encode(source.value) }
  router.replace({ query })
  copy(`${window.location.origin}${window.location.pathname}?lang=${query.lang}&c=${query.c}`)
}

const title = 'Run it and watch what happens'
const description = 'A SQL and Java playground that shows the query plan, the row counts at each clause, and the compile and run stages — all measured from the code you type, none of it precomputed.'

usePageSeo({ title, description, headline: 'Playground' })

defineShortcuts({
  meta_enter: run
})
</script>

<template>
  <UContainer class="py-10 lg:py-14">
    <div class="max-w-3xl">
      <UBadge
        label="Playground"
        color="secondary"
        variant="subtle"
      />
      <h1 class="font-display text-3xl sm:text-4xl font-bold text-highlighted tracking-tight text-balance mt-4">
        {{ title }}
      </h1>
      <p class="mt-3 text-lg text-muted text-balance">
        {{ description }}
      </p>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
      <UButton
        v-for="(sample, key) in SAMPLES"
        :key="key"
        :label="sample.label"
        :icon="sample.icon"
        :color="lang === key ? 'primary' : 'neutral'"
        :variant="lang === key ? 'solid' : 'subtle'"
        @click="lang = key as Lang"
      />

      <span class="text-xs text-dimmed hidden sm:inline">{{ SAMPLES[lang].hint }}</span>
    </div>

    <div class="mt-6 lg:grid lg:grid-cols-[minmax(0,26rem)_1fr] lg:gap-6 lg:items-start">
      <div class="dgm-box overflow-hidden">
        <div class="flex items-center gap-2 px-3 py-2 border-b border-default bg-default">
          <UIcon
            :name="SAMPLES[lang].icon"
            class="size-3.5 text-secondary"
          />
          <span class="text-xs font-medium text-highlighted">{{ SAMPLES[lang].label }}</span>

          <span class="flex-1" />

          <UButton
            label="Reset"
            icon="i-lucide-rotate-ccw"
            size="xs"
            color="neutral"
            variant="ghost"
            @click="source = SAMPLES[lang].code"
          />
          <UButton
            v-if="isSupported"
            :label="copied ? 'Copied' : 'Share'"
            :icon="copied ? 'i-lucide-check' : 'i-lucide-link'"
            size="xs"
            color="neutral"
            variant="ghost"
            @click="share"
          />
          <UButton
            :label="running ? 'Running' : 'Run'"
            :loading="running"
            icon="i-lucide-play"
            size="xs"
            @click="run"
          />
        </div>

        <textarea
          v-model="source"
          class="run__editor dgm-mono"
          rows="22"
          spellcheck="false"
          autocapitalize="off"
          autocomplete="off"
          autocorrect="off"
          :aria-label="`${SAMPLES[lang].label} editor`"
        />

        <p class="px-3 py-2 border-t border-default dgm-label">
          <UKbd value="meta" /> <UKbd value="enter" /> to run
        </p>
      </div>

      <div class="mt-6 lg:mt-0 min-w-0">
        <div
          v-if="running"
          class="dgm-box px-4 py-8 text-center"
        >
          <UIcon
            name="i-lucide-loader-circle"
            class="size-5 animate-spin text-primary"
          />
          <p class="text-sm text-muted mt-2">
            {{ loading }}
          </p>
        </div>

        <template v-else-if="visual || error">
          <VisualiserStagePipeline
            v-if="visual"
            :stages="visual.stages"
          />

          <!-- Outside the `visual` branch on purpose. A run that fails before it
               produces any stages — a blocked request, a dead endpoint — used to
               render nothing at all, which is indistinguishable from the button
               not working. -->
          <UAlert
            v-if="error"
            icon="i-lucide-circle-x"
            color="error"
            variant="subtle"
            :class="visual && 'mt-6'"
            :ui="{ description: 'whitespace-pre-wrap font-mono text-xs' }"
            :description="error"
          />

          <div
            v-if="visual"
            class="mt-8"
          >
            <VisualiserSqlPanel
              v-if="visual.kind === 'sql' && visual.data"
              :data="visual.data as any"
            />
            <VisualiserJavaPanel
              v-else-if="visual.kind === 'java' && visual.data"
              :data="visual.data as any"
              :stages="visual.stages"
            />
          </div>
        </template>

        <div
          v-else
          class="dgm-box px-5 py-10 text-center"
        >
          <UIcon
            name="i-lucide-play"
            class="size-6 text-dimmed"
          />
          <p class="text-sm text-muted mt-3 max-w-sm mx-auto">
            Press <strong class="text-highlighted">Run</strong>. Every number that
            appears is measured from this exact code — the query plan comes from
            SQLite itself, and the row counts come from actually running the
            query with its clauses stripped back one at a time.
          </p>
        </div>
      </div>
    </div>

    <AdSlot
      placement="in-article"
      class="mx-auto"
    />

    <UPageCard
      variant="naked"
      class="mt-10"
      title="This is a lesson component, not a separate toy"
      description="The same runner is available inside any lesson with ::runner, in six languages. Nothing downloads until somebody presses Run."
    >
      <template #footer>
        <UButton
          to="/terminal/how-this-course-works/how-to-read-these-lessons"
          label="See it in a lesson"
          variant="subtle"
          trailing-icon="i-lucide-arrow-right"
        />
      </template>
    </UPageCard>
  </UContainer>
</template>

<style scoped>
.run__editor {
  display: block;
  width: 100%;
  padding: 0.875rem;
  border: 0;
  resize: vertical;
  background: var(--ui-bg);
  color: var(--ui-text-highlighted);
  font-size: 0.8125rem;
  line-height: 1.65;
  tab-size: 2;
}

.run__editor:focus {
  outline: 2px solid var(--dgm-accent);
  outline-offset: -2px;
}
</style>
