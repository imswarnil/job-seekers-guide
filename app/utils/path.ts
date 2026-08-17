import type { ContentNavigationItem } from '@nuxt/content'

/**
 * The learning path, derived from the folder tree.
 *
 * `content/1.path/` is three levels deep and the levels mean something: subject
 * folder → module folder → lesson file. The numeric prefixes on those folders
 * are the only place the order of the curriculum is written down, so reordering
 * it is a `git mv` and there is no manifest to drift out of sync.
 *
 * Nuxt Content hands the tree back generically; these helpers give it the names
 * the player actually thinks in, and — the part that matters — flatten it into
 * one ordered array of every lesson on the platform. That flat array is what
 * makes the path end to end: the last lesson of a subject knows that the thing
 * after it is the first lesson of the next subject, not the end of the road.
 */

export type Stage = 'orientation' | 'foundation' | 'language' | 'applied' | 'projects' | 'job-search'

export interface Lesson {
  title: string
  path: string
  description?: string
  icon?: string
  minutes?: number
  kind?: string
  moduleTitle?: string
  modulePath?: string
  subjectTitle?: string
  subjectPath?: string
}

export interface Module {
  title: string
  path: string
  icon?: string
  description?: string
  lessons: Lesson[]
  minutes: number
}

export interface Subject {
  title: string
  path: string
  slug: string
  description?: string
  icon?: string
  code?: string
  duration?: string
  stage?: Stage
  modules: Module[]
  lessons: Lesson[]
  minutes: number
}

export interface LearningPath {
  subjects: Subject[]
  /** Every lesson on the platform, in path order. The spine of the player. */
  lessons: Lesson[]
  minutes: number
}

/**
 * The navigation tree carries titles, icons and structure; it does not carry the
 * front matter of the page behind each item. That arrives separately as a flat
 * list of pages, keyed by path, and is merged in here — so a subject card can
 * show its code and duration without fetching every subject page.
 */
export interface PageMeta {
  path: string
  title?: string
  description?: string
  icon?: string
  code?: string
  duration?: string
  stage?: Stage
  minutes?: number
  kind?: string
}

type NavItem = ContentNavigationItem & Partial<Omit<PageMeta, 'path'>> & {
  children?: NavItem[]
}

const kindIcons: Record<string, string> = {
  lesson: 'i-lucide-book-open',
  reading: 'i-lucide-book-marked',
  practice: 'i-lucide-terminal',
  project: 'i-lucide-hammer',
  quiz: 'i-lucide-list-checks'
}

export function lessonIcon(lesson: Pick<Lesson, 'kind' | 'icon'>): string {
  return lesson.icon || kindIcons[lesson.kind || 'lesson'] || kindIcons.lesson!
}

export const stageLabels: Record<Stage, string> = {
  'orientation': 'Orientation',
  'foundation': 'Foundations',
  'language': 'Your language',
  'applied': 'Applied craft',
  'projects': 'Projects',
  'job-search': 'The job hunt'
}

/** The order stages are shown in on `/path`, when a subject declares one. */
export const stageOrder: Stage[] = ['orientation', 'foundation', 'language', 'applied', 'projects', 'job-search']

/**
 * A directory that has an `index.md` shows up both as the directory item and, in
 * some shapes, as a child pointing at the same path. Drop the duplicate so a
 * subject overview never appears as its own first lesson.
 */
function realChildren(item: NavItem): NavItem[] {
  return ((item.children || []) as NavItem[]).filter(child => child.path !== item.path)
}

export const emptyPath: LearningPath = { subjects: [], lessons: [], minutes: 0 }

export function toPath(
  navigation: ContentNavigationItem[] | null | undefined,
  pages?: PageMeta[] | null
): LearningPath {
  const root = (navigation || []) as NavItem[]
  const meta = new Map((pages || []).map(page => [page.path, page]))

  // With `prefix: '/'` on the collection the subjects arrive at the top level
  // already. The unwrap is kept for the shape where they don't.
  const subjectItems = root.length === 1 && root[0]?.path === '/'
    ? realChildren(root[0])
    : root.filter(item => item.path !== '/')

  const subjects = subjectItems.map((subjectItem) => {
    const subjectMeta = meta.get(subjectItem.path)
    const subjectTitle = subjectItem.title
    const subjectPath = subjectItem.path

    const modules: Module[] = []
    const lessons: Lesson[] = []

    function toLesson(item: NavItem, module?: NavItem): Lesson {
      const lessonMeta = meta.get(item.path)

      return {
        title: item.title,
        path: item.path,
        description: item.description ?? lessonMeta?.description,
        icon: item.icon ?? lessonMeta?.icon,
        minutes: item.minutes ?? lessonMeta?.minutes,
        kind: item.kind ?? lessonMeta?.kind,
        moduleTitle: module?.title,
        modulePath: module?.path,
        subjectTitle,
        subjectPath
      }
    }

    for (const child of realChildren(subjectItem)) {
      const grandchildren = realChildren(child)
      const childMeta = meta.get(child.path)

      if (grandchildren.length) {
        const moduleLessons = grandchildren.map(lesson => toLesson(lesson, child))

        modules.push({
          title: child.title,
          path: child.path,
          icon: child.icon ?? childMeta?.icon,
          description: child.description ?? childMeta?.description,
          lessons: moduleLessons,
          minutes: moduleLessons.reduce((total, lesson) => total + (lesson.minutes || 0), 0)
        })
        lessons.push(...moduleLessons)
      } else {
        // A lesson sitting directly in the subject folder, with no module around
        // it. Legal, and short subjects are written this way.
        lessons.push(toLesson(child))
      }
    }

    return {
      title: subjectTitle,
      path: subjectPath,
      slug: subjectPath.split('/').filter(Boolean).pop() || '',
      description: subjectItem.description ?? subjectMeta?.description,
      icon: subjectItem.icon ?? subjectMeta?.icon,
      code: subjectItem.code ?? subjectMeta?.code,
      duration: subjectItem.duration ?? subjectMeta?.duration,
      stage: subjectItem.stage ?? subjectMeta?.stage,
      modules,
      lessons,
      minutes: lessons.reduce((total, lesson) => total + (lesson.minutes || 0), 0)
    } satisfies Subject
  })

  const lessons = subjects.flatMap(subject => subject.lessons)

  return {
    subjects,
    lessons,
    minutes: lessons.reduce((total, lesson) => total + (lesson.minutes || 0), 0)
  }
}

export function findSubject(path: LearningPath, url: string): Subject | undefined {
  return path.subjects.find(subject => url === subject.path || url.startsWith(`${subject.path}/`))
}

export function findModule(path: LearningPath, url: string): Module | undefined {
  return findSubject(path, url)?.modules
    .find(module => url === module.path || url.startsWith(`${module.path}/`))
}

/** Subjects grouped for display on `/path`, in stage order, ungrouped last. */
export function byStage(path: LearningPath) {
  const groups = stageOrder
    .map(stage => ({
      stage,
      label: stageLabels[stage],
      subjects: path.subjects.filter(subject => subject.stage === stage)
    }))
    .filter(group => group.subjects.length)

  const ungrouped = path.subjects.filter(subject => !subject.stage)
  if (ungrouped.length) {
    groups.push({ stage: 'applied' as Stage, label: 'More', subjects: ungrouped })
  }

  return groups
}

/** "1h 20m" / "45m" — total time, said the way a person would say it. */
export function formatMinutes(minutes: number): string {
  if (!minutes) {
    return ''
  }
  const hours = Math.floor(minutes / 60)
  const rest = minutes % 60

  if (!hours) {
    return `${rest}m`
  }
  return rest ? `${hours}h ${rest}m` : `${hours}h`
}
