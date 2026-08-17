import type { LearningPath, Lesson, Module, Subject } from '~/utils/path'

/**
 * Progress, stored in the browser and nowhere else.
 *
 * Accountability is the thing a coaching institute actually sold — a streak you
 * do not want to break, and a page that knows what you were doing when you last
 * closed the tab. None of that needs an account, so until there is a backend to
 * sync to, none of it asks for one: this is `localStorage`, it never leaves the
 * device, and the whole path works without signing in.
 */

const STORAGE_KEY = 'guide:progress:v2'
const LEGACY_KEY = 'guide:progress:v1'

interface ProgressState {
  /** Lesson path → ISO timestamp it was completed. */
  completed: Record<string, string>
  /** The single cursor for the whole path — what "Continue" resumes. */
  lastVisited: string | null
  /** Subject path → last lesson opened in it, for per-subject continue buttons. */
  lastVisitedBySubject: Record<string, string>
  /** Advance to the next lesson on its own after marking one complete. */
  autoAdvance: boolean
  /** Player rail collapsed to icons. */
  railCollapsed: boolean
}

interface LegacyState {
  completed?: Record<string, string>
  lastVisited?: Record<string, string>
}

function emptyState(): ProgressState {
  return {
    completed: {},
    lastVisited: null,
    lastVisitedBySubject: {},
    autoAdvance: false,
    railCollapsed: false
  }
}

/**
 * v1 stored lessons under `/courses/…`, and one cursor per course. The URLs moved
 * to the root when courses became one path, so rewrite rather than discard —
 * somebody forty lessons in should not be reset by a restructure. The v1 key is
 * deliberately left in place as insurance.
 */
function migrate(): ProgressState | undefined {
  if (import.meta.server) {
    return undefined
  }

  const raw = window.localStorage.getItem(LEGACY_KEY)
  if (!raw || window.localStorage.getItem(STORAGE_KEY)) {
    return undefined
  }

  try {
    const legacy = JSON.parse(raw) as LegacyState
    const state = emptyState()

    for (const [path, at] of Object.entries(legacy.completed || {})) {
      state.completed[path.replace(/^\/courses\//, '/')] = at
    }

    let newest = ''
    for (const [coursePath, lessonPath] of Object.entries(legacy.lastVisited || {})) {
      const subject = coursePath.replace(/^\/courses\//, '/')
      const lesson = lessonPath.replace(/^\/courses\//, '/')
      state.lastVisitedBySubject[subject] = lesson

      const at = state.completed[lesson] || ''
      if (at >= newest) {
        newest = at
        state.lastVisited = lesson
      }
    }

    return state
  } catch {
    return undefined
  }
}

export interface ProgressSummary {
  total: number
  completed: number
  percent: number
  started: boolean
  finished: boolean
}

export function useProgress() {
  const state = useLocalStorage<ProgressState>(STORAGE_KEY, () => migrate() || emptyState(), {
    mergeDefaults: true
  })

  function isComplete(lessonPath: string) {
    return Boolean(state.value.completed[lessonPath])
  }

  /** Drop a set of keys from a record without mutating the stored object. */
  function omit(record: Record<string, string>, keys: string[]) {
    return Object.fromEntries(
      Object.entries(record).filter(([key]) => !keys.includes(key))
    )
  }

  function setComplete(lessonPath: string, complete = true) {
    const completed = complete
      ? { ...state.value.completed, [lessonPath]: new Date().toISOString() }
      : omit(state.value.completed, [lessonPath])

    state.value = { ...state.value, completed }
  }

  function toggleComplete(lessonPath: string) {
    setComplete(lessonPath, !isComplete(lessonPath))
  }

  function markVisited(lessonPath: string, subjectPath?: string) {
    state.value = {
      ...state.value,
      lastVisited: lessonPath,
      lastVisitedBySubject: subjectPath
        ? { ...state.value.lastVisitedBySubject, [subjectPath]: lessonPath }
        : state.value.lastVisitedBySubject
    }
  }

  function summarise(lessons: Lesson[] | undefined): ProgressSummary {
    const total = lessons?.length || 0
    const completed = lessons?.filter(lesson => isComplete(lesson.path)).length || 0

    return {
      total,
      completed,
      percent: total ? Math.round((completed / total) * 100) : 0,
      started: completed > 0,
      finished: total > 0 && completed === total
    }
  }

  const moduleProgress = (module: Module | undefined) => summarise(module?.lessons)
  const subjectProgress = (subject: Subject | undefined) => summarise(subject?.lessons)
  const pathProgress = (path: LearningPath | undefined) => summarise(path?.lessons)

  /**
   * Where to send someone who presses "Continue": the lesson they were last on
   * if they have not finished it, otherwise the first one they have not
   * finished, otherwise the beginning. Scoped to a subject when given one.
   */
  function resume(path: LearningPath | undefined, subject?: Subject): Lesson | undefined {
    const lessons = subject?.lessons || path?.lessons
    if (!lessons?.length) {
      return undefined
    }

    const cursor = subject
      ? state.value.lastVisitedBySubject[subject.path]
      : state.value.lastVisited

    const last = cursor ? lessons.find(lesson => lesson.path === cursor) : undefined
    if (last && !isComplete(last.path)) {
      return last
    }

    return lessons.find(lesson => !isComplete(lesson.path)) || lessons[0]
  }

  /**
   * Consecutive days ending today (or yesterday) on which something was
   * finished. Free, because completions were already timestamped.
   */
  function streak(): number {
    const days = new Set(Object.values(state.value.completed).map(at => at.slice(0, 10)))
    if (!days.size) {
      return 0
    }

    const cursor = new Date()
    const day = () => cursor.toISOString().slice(0, 10)

    // A streak survives today being empty — you have not broken it until you
    // have missed a whole day.
    if (!days.has(day())) {
      cursor.setDate(cursor.getDate() - 1)
      if (!days.has(day())) {
        return 0
      }
    }

    let count = 0
    while (days.has(day())) {
      count++
      cursor.setDate(cursor.getDate() - 1)
    }
    return count
  }

  function setOption<K extends 'autoAdvance' | 'railCollapsed'>(key: K, value: ProgressState[K]) {
    state.value = { ...state.value, [key]: value }
  }

  function reset(scope?: Subject | LearningPath) {
    if (!scope) {
      state.value = emptyState()
      return
    }

    const paths = scope.lessons.map(lesson => lesson.path)
    const subjectPaths = 'subjects' in scope
      ? scope.subjects.map(subject => subject.path)
      : [(scope as Subject).path]

    state.value = {
      ...state.value,
      completed: omit(state.value.completed, paths),
      lastVisited: state.value.lastVisited && paths.includes(state.value.lastVisited)
        ? null
        : state.value.lastVisited,
      lastVisitedBySubject: omit(state.value.lastVisitedBySubject, subjectPaths)
    }
  }

  return {
    state,
    isComplete,
    setComplete,
    toggleComplete,
    markVisited,
    moduleProgress,
    subjectProgress,
    pathProgress,
    resume,
    streak,
    setOption,
    reset
  }
}
