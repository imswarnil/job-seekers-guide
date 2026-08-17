import type { ContentNavigationItem } from '@nuxt/content'

/**
 * The course tree, derived from the folder tree.
 *
 * `content/2.courses/` is three levels deep and the levels mean something:
 * course folder → module folder → lesson file. Nuxt Content hands that back as
 * a generic navigation tree; these helpers give it the names the player and the
 * catalogue actually think in, so no component has to know that a "module" is
 * really `children[n].children`.
 */

export type CourseLevel = 'orientation' | 'foundation' | 'applied' | 'job-search'

export interface CourseLesson {
  title: string
  path: string
  description?: string
  icon?: string
  minutes?: number
  kind?: string
  moduleTitle?: string
}

export interface CourseModule {
  title: string
  path: string
  icon?: string
  lessons: CourseLesson[]
  minutes: number
}

export interface Course {
  title: string
  path: string
  slug: string
  description?: string
  icon?: string
  code?: string
  duration?: string
  level?: CourseLevel
  modules: CourseModule[]
  lessons: CourseLesson[]
  minutes: number
}

/**
 * The navigation tree carries titles, icons and structure; it does not carry
 * the front matter of the page behind each item. That arrives separately as a
 * flat list of pages, keyed by path, and is merged in here — so a course card
 * can show its code and duration without the catalogue fetching every course.
 */
export interface CourseMeta {
  path: string
  title?: string
  description?: string
  icon?: string
  code?: string
  duration?: string
  level?: CourseLevel
  minutes?: number
  kind?: string
}

type NavItem = ContentNavigationItem & Partial<Omit<CourseMeta, 'path'>> & {
  children?: NavItem[]
}

const kindIcons: Record<string, string> = {
  lesson: 'i-lucide-book-open',
  reading: 'i-lucide-book-marked',
  practice: 'i-lucide-terminal',
  project: 'i-lucide-hammer',
  quiz: 'i-lucide-list-checks'
}

export function lessonIcon(lesson: Pick<CourseLesson, 'kind' | 'icon'>): string {
  return lesson.icon || kindIcons[lesson.kind || 'lesson'] || kindIcons.lesson!
}

export const levelLabels: Record<CourseLevel, string> = {
  'orientation': 'Orientation',
  'foundation': 'Foundation',
  'applied': 'Applied craft',
  'job-search': 'Job search'
}

/**
 * A directory that has an `index.md` shows up both as the directory item and,
 * in some shapes, as a child pointing at the same path. Drop the duplicate so a
 * course syllabus never appears as its own first lesson.
 */
function realChildren(item: NavItem): NavItem[] {
  return ((item.children || []) as NavItem[]).filter(child => child.path !== item.path)
}

function toLesson(item: NavItem, meta: CourseMeta | undefined, moduleTitle?: string): CourseLesson {
  return {
    title: item.title,
    path: item.path,
    description: item.description ?? meta?.description,
    icon: item.icon ?? meta?.icon,
    minutes: item.minutes ?? meta?.minutes,
    kind: item.kind ?? meta?.kind,
    moduleTitle
  }
}

export function toCourses(
  navigation: ContentNavigationItem[] | null | undefined,
  pages?: CourseMeta[] | null
): Course[] {
  const root = (navigation || []) as NavItem[]
  const meta = new Map((pages || []).map(page => [page.path, page]))

  // The tree may arrive rooted at `/courses` or already unwrapped to its children.
  const courseItems = root.length === 1 && root[0]?.path === '/courses'
    ? realChildren(root[0])
    : root.filter(item => item.path !== '/courses')

  return courseItems.map((courseItem) => {
    const modules: CourseModule[] = []
    const lessons: CourseLesson[] = []
    const courseMeta = meta.get(courseItem.path)

    for (const child of realChildren(courseItem)) {
      const grandchildren = realChildren(child)

      if (grandchildren.length) {
        const moduleLessons = grandchildren.map(lesson => toLesson(lesson, meta.get(lesson.path), child.title))

        modules.push({
          title: child.title,
          path: child.path,
          icon: child.icon,
          lessons: moduleLessons,
          minutes: moduleLessons.reduce((total, lesson) => total + (lesson.minutes || 0), 0)
        })
        lessons.push(...moduleLessons)
      } else {
        // A lesson sitting directly in the course folder, with no module around
        // it. Legal, and short courses are written this way.
        lessons.push(toLesson(child, meta.get(child.path)))
      }
    }

    return {
      title: courseItem.title,
      path: courseItem.path,
      slug: courseItem.path.split('/').filter(Boolean).pop() || '',
      description: courseItem.description ?? courseMeta?.description,
      icon: courseItem.icon ?? courseMeta?.icon,
      code: courseItem.code ?? courseMeta?.code,
      duration: courseItem.duration ?? courseMeta?.duration,
      level: courseItem.level ?? courseMeta?.level,
      modules,
      lessons,
      minutes: lessons.reduce((total, lesson) => total + (lesson.minutes || 0), 0)
    }
  })
}

export function findCourse(courses: Course[], path: string): Course | undefined {
  return courses.find(course => path === course.path || path.startsWith(`${course.path}/`))
}
