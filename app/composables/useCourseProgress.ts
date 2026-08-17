import type { Course, CourseLesson } from '~/utils/courses'

/**
 * Progress, stored in the browser and nowhere else.
 *
 * Accountability is the thing a coaching institute actually sold — a streak you
 * do not want to break, and a page that knows what you were doing when you last
 * closed the tab. None of that needs an account, so until there is a backend to
 * sync to, none of it asks for one: this is `localStorage`, it never leaves the
 * device, and the whole path works without signing in.
 */

const STORAGE_KEY = 'guide:progress:v1'

interface ProgressState {
  /** Lesson path → ISO timestamp it was completed. */
  completed: Record<string, string>
  /** Course path → the lesson path last opened in that course. */
  lastVisited: Record<string, string>
}

export function useCourseProgress() {
  const state = useLocalStorage<ProgressState>(STORAGE_KEY, () => ({
    completed: {},
    lastVisited: {}
  }), { mergeDefaults: true })

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

  function markVisited(coursePath: string, lessonPath: string) {
    state.value = {
      ...state.value,
      lastVisited: { ...state.value.lastVisited, [coursePath]: lessonPath }
    }
  }

  /** Completed count, total, and percentage for one course. */
  function courseProgress(course: Course | undefined) {
    const total = course?.lessons.length || 0
    const completed = course?.lessons.filter(lesson => isComplete(lesson.path)).length || 0

    return {
      total,
      completed,
      percent: total ? Math.round((completed / total) * 100) : 0,
      started: completed > 0,
      finished: total > 0 && completed === total
    }
  }

  /**
   * Where to send someone who presses "continue": the lesson they were last on
   * if they have one, otherwise the first lesson they have not finished,
   * otherwise the beginning.
   */
  function resumeLesson(course: Course | undefined): CourseLesson | undefined {
    if (!course?.lessons.length) {
      return undefined
    }

    const last = state.value.lastVisited[course.path]
    const lastLesson = last && course.lessons.find(lesson => lesson.path === last)
    if (lastLesson && !isComplete(lastLesson.path)) {
      return lastLesson
    }

    return course.lessons.find(lesson => !isComplete(lesson.path)) || course.lessons[0]
  }

  function reset(course?: Course) {
    if (!course) {
      state.value = { completed: {}, lastVisited: {} }
      return
    }

    state.value = {
      completed: omit(state.value.completed, course.lessons.map(lesson => lesson.path)),
      lastVisited: omit(state.value.lastVisited, [course.path])
    }
  }

  return {
    state,
    isComplete,
    setComplete,
    toggleComplete,
    markVisited,
    courseProgress,
    resumeLesson,
    reset
  }
}
