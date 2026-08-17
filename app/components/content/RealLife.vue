<script setup lang="ts">
/**
 * ::real-life — where the idea you just read shows up in an actual job.
 *
 * This is the component that carries the platform's whole argument: the subjects
 * are not secret, what is missing is somebody connecting them to the work. It is
 * the one place amber is allowed, because "this is real" is a landmark.
 *
 * ```md
 * ::real-life{title="Why your deploy failed at 2am" source="A payments team, 2019"}
 * The service had a connection pool of ten and a thread pool of two hundred...
 * ::
 * ```
 */
withDefaults(defineProps<{
  title?: string
  icon?: string
  /** Where it happened, if it can be said without naming anyone. */
  source?: string
}>(), {
  title: 'In the job',
  icon: 'i-lucide-building-2'
})
</script>

<template>
  <aside class="real-life not-prose">
    <p class="real-life__head">
      <UIcon
        :name="icon"
        class="size-4"
      />
      {{ title }}
    </p>

    <div class="real-life__body">
      <slot />
    </div>

    <p
      v-if="source"
      class="real-life__source"
    >
      — {{ source }}
    </p>
  </aside>
</template>

<style scoped>
.real-life {
  margin-block: 1.75rem;
  padding: 1rem 1.125rem;
  border-radius: var(--dgm-box-radius);
  border: 1px solid color-mix(in oklab, var(--dgm-milestone) 35%, transparent);
  border-left-width: 3px;
  border-left-color: var(--dgm-milestone);
  background: var(--guide-milestone-soft);
}

.real-life__head {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--guide-milestone-strong);
  margin-bottom: 0.625rem;
}

.real-life__body {
  color: var(--ui-text-toned);
  font-size: 0.9375rem;
  line-height: 1.7;
}

.real-life__body :deep(> *:last-child) {
  margin-bottom: 0;
}

.real-life__body :deep(p) {
  margin-bottom: 0.75rem;
}

.real-life__source {
  margin-top: 0.75rem;
  font-size: 0.75rem;
  color: var(--dgm-label);
}
</style>
