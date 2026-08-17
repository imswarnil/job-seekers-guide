import type { ContentNavigationItem } from '@nuxt/content'
import type { CourseMeta } from '~/utils/courses'

/**
 * The course navigation, fetched once in `app.vue` and provided to everything
 * below it. Every component that needs the curriculum reads the same tree, so
 * the catalogue, the player sidebar and the progress figures can never disagree
 * with each other.
 */
export function useCourses() {
  const navigation = inject<Ref<ContentNavigationItem[] | null>>('courses-navigation', ref(null))
  const pages = inject<Ref<CourseMeta[] | null>>('courses-pages', ref(null))

  const courses = computed(() => toCourses(navigation.value, pages.value))

  return { navigation, courses }
}

/**
 * Everything the player needs about where it currently is: the course, the
 * lesson, and the ones on either side of it.
 */
export function useCoursePlayer(path: MaybeRefOrGetter<string>) {
  const { courses } = useCourses()

  const course = computed(() => findCourse(courses.value, toValue(path)))

  const index = computed(() => {
    if (!course.value) {
      return -1
    }
    return course.value.lessons.findIndex(lesson => lesson.path === toValue(path))
  })

  return {
    course,
    index,
    lesson: computed(() => index.value >= 0 ? course.value?.lessons[index.value] : undefined),
    previous: computed(() => index.value > 0 ? course.value?.lessons[index.value - 1] : undefined),
    next: computed(() => index.value >= 0 ? course.value?.lessons[index.value + 1] : undefined)
  }
}
