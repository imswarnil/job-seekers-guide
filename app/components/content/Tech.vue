<script setup lang="ts">
/**
 * ::tech — technology tiles inside a lesson or the story.
 *
 * ```md
 * ::tech{names="java,sql,html,css" labelled}
 * ::
 * ```
 *
 * Or one, inline in a sentence, as a pill:
 *
 * ```md
 * I learned :tech{name="java" variant="pill"} first, then :tech{name="sql" variant="pill"}.
 * ```
 */
const props = withDefaults(defineProps<{
  /** One technology. */
  name?: string
  /** Or several, comma separated. */
  names?: string
  size?: 'xs' | 'sm' | 'md' | 'lg'
  labelled?: boolean
  variant?: 'tile' | 'pill'
  label?: string
}>(), {
  size: 'md',
  variant: 'tile'
})

const list = computed(() =>
  (props.names || props.name || '').split(',').map(n => n.trim()).filter(Boolean)
)

/** Inline use — a single pill in a sentence — must not open a figure block. */
const inline = computed(() => props.variant === 'pill' && list.value.length === 1)
</script>

<template>
  <TechThumb
    v-if="inline"
    :name="list[0]!"
    :size="size"
    variant="pill"
  />

  <DgmFigure
    v-else
    :label="label"
    icon="i-lucide-layers"
  >
    <div class="flex flex-wrap items-center gap-3">
      <TechThumb
        v-for="key in list"
        :key="key"
        :name="key"
        :size="size"
        :labelled="labelled"
        :variant="variant"
      />
    </div>
  </DgmFigure>
</template>
