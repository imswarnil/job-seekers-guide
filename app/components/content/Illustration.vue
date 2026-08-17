<script setup lang="ts">
import IllustrationCode from '~/components/illustration/Code.vue'
import IllustrationDatabase from '~/components/illustration/Database.vue'
import IllustrationFoundations from '~/components/illustration/Foundations.vue'
import IllustrationInterview from '~/components/illustration/Interview.vue'
import IllustrationJobSearch from '~/components/illustration/JobSearch.vue'
import IllustrationResume from '~/components/illustration/Resume.vue'
import IllustrationVersioning from '~/components/illustration/Versioning.vue'

/**
 * ::illustration — one of the animated SVGs, dropped into a lesson or the story.
 *
 * ```md
 * ::illustration{name="resume" caption="Nine seconds. This is how far they get."}
 * ::
 * ```
 *
 * The registry is explicit rather than a dynamic component name, so a typo in
 * markdown fails visibly here instead of rendering an empty box in a lesson.
 */
const props = withDefaults(defineProps<{
  name: 'job-search' | 'resume' | 'code' | 'database' | 'foundations' | 'versioning' | 'interview'
  caption?: string
  label?: string
  /** Narrow it — these are drawn at 200×150 and read fine small. */
  size?: 'sm' | 'md' | 'lg'
}>(), {
  size: 'md'
})

const registry = {
  'job-search': IllustrationJobSearch,
  'resume': IllustrationResume,
  'code': IllustrationCode,
  'database': IllustrationDatabase,
  'foundations': IllustrationFoundations,
  'versioning': IllustrationVersioning,
  'interview': IllustrationInterview
}

const component = computed(() => registry[props.name])

const widths = { sm: 'max-w-[15rem]', md: 'max-w-sm', lg: 'max-w-lg' }
</script>

<template>
  <DgmFigure
    :label="label"
    :caption="caption"
    icon="i-lucide-image"
  >
    <div :class="['mx-auto w-full', widths[size]]">
      <component
        :is="component"
        v-if="component"
      />
      <p
        v-else
        class="dgm-label text-center"
      >
        No illustration called “{{ name }}”.
      </p>
    </div>
  </DgmFigure>
</template>
