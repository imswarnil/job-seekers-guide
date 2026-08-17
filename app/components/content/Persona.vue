<script setup lang="ts">
/**
 * ::persona — a named person the lesson is about, so an abstract point lands as
 * somebody's actual situation.
 *
 * ```md
 * ::persona
 * ---
 * name: Priya
 * role: Mechanical engineering, 2023, no offers
 * background: Third-tier college, campus placements came and went
 * goal: A first software job inside a year
 * blocker: Believes she needs to be good at maths
 * ---
 * I kept starting tutorials and stopping because I could never tell whether the
 * thing I was learning was the thing I was supposed to be learning.
 * ::
 * ```
 */
defineProps<{
  name: string
  role?: string
  /** Path under `public/`; Studio's media picker writes here. */
  avatar?: string
  background?: string
  goal?: string
  blocker?: string
}>()

const slots = useSlots()
</script>

<template>
  <div class="dgm-box persona not-prose">
    <div class="flex items-start gap-3">
      <UAvatar
        :src="avatar"
        :alt="name"
        :text="name.slice(0, 1)"
        size="lg"
        class="shrink-0"
      />

      <div class="min-w-0">
        <p class="font-display font-semibold text-highlighted">
          {{ name }}
        </p>
        <p
          v-if="role"
          class="text-sm text-muted"
        >
          {{ role }}
        </p>
      </div>
    </div>

    <blockquote
      v-if="slots.default"
      class="persona__quote"
    >
      <slot />
    </blockquote>

    <dl
      v-if="background || goal || blocker"
      class="persona__facts"
    >
      <template v-if="background">
        <dt>Where they are</dt>
        <dd>{{ background }}</dd>
      </template>
      <template v-if="goal">
        <dt>What they want</dt>
        <dd>{{ goal }}</dd>
      </template>
      <template v-if="blocker">
        <dt>What is in the way</dt>
        <dd>{{ blocker }}</dd>
      </template>
    </dl>
  </div>
</template>

<style scoped>
.persona {
  padding: 1.125rem;
  margin-block: 1.75rem;
}

.persona__quote {
  margin-top: 1rem;
  padding-left: 0.875rem;
  border-left: 2px solid var(--dgm-accent);
  color: var(--ui-text-toned);
  font-size: 0.9375rem;
  line-height: 1.7;
}

.persona__quote :deep(> *:last-child) {
  margin-bottom: 0;
}

.persona__facts {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.375rem 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--dgm-box-border);
  font-size: 0.8125rem;
}

.persona__facts dt {
  color: var(--dgm-dim);
  white-space: nowrap;
}

.persona__facts dd {
  color: var(--dgm-label);
  margin: 0;
}
</style>
