<script setup lang="ts">
/**
 * The list.
 *
 * The site is prerendered onto static hosting, so there is no server here to
 * post to — the form hands off to whatever provider is configured in
 * `app.config.ts` (`newsletter.action`). Until one is, the component says so in
 * the console and renders a disabled state rather than a box that silently eats
 * addresses, which is the worst possible version of this.
 */
const props = withDefaults(defineProps<{
  /** `inline` is the compact one-line version for the footer. */
  variant?: 'card' | 'inline'
  title?: string
  note?: string
}>(), {
  variant: 'card',
  title: 'Get told when something lands',
  note: 'New chapters, new episodes, new subjects on the path. No more than one email a month, and nothing else — ever.'
})

const app = useAppConfig()
const action = computed(() => (app as { newsletter?: { action?: string } }).newsletter?.action || '')
const configured = computed(() => Boolean(action.value))

const email = ref('')
const state = ref<'idle' | 'sending' | 'done' | 'error'>('idle')

async function submit() {
  if (!configured.value || state.value === 'sending' || !email.value) {
    return
  }

  state.value = 'sending'

  try {
    await $fetch(action.value, {
      method: 'POST',
      body: { email: email.value },
      headers: { Accept: 'application/json' }
    })
    state.value = 'done'
    email.value = ''
  } catch {
    state.value = 'error'
  }
}
</script>

<template>
  <section
    class="news"
    :data-variant="props.variant"
  >
    <div class="news__copy">
      <p class="news__title">
        {{ props.title }}
      </p>
      <p
        v-if="props.variant === 'card'"
        class="news__note"
      >
        {{ props.note }}
      </p>
    </div>

    <form
      class="news__form"
      @submit.prevent="submit"
    >
      <UInput
        v-model="email"
        type="email"
        name="email"
        required
        autocomplete="email"
        placeholder="you@example.com"
        size="lg"
        class="flex-1 min-w-0"
        :disabled="!configured || state === 'done'"
      />

      <UButton
        type="submit"
        :label="state === 'done' ? 'Done' : 'Subscribe'"
        :icon="state === 'done' ? 'i-lucide-check' : undefined"
        :color="state === 'done' ? 'success' : 'primary'"
        size="lg"
        :loading="state === 'sending'"
        :disabled="!configured || state === 'done'"
        class="shrink-0"
      />
    </form>

    <p
      v-if="state === 'done'"
      class="news__msg news__msg--ok"
    >
      You are on the list. Check your inbox for the confirmation.
    </p>
    <p
      v-else-if="state === 'error'"
      class="news__msg news__msg--bad"
    >
      That did not go through. Try again in a moment.
    </p>
    <p
      v-else-if="!configured"
      class="news__msg"
    >
      Sign-up opens shortly.
    </p>
  </section>
</template>

<style scoped>
.news {
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  background: var(--ui-bg-elevated);
}

.news[data-variant='inline'] {
  border: 0;
  padding: 0;
  background: none;
}

.news__title {
  font-family: var(--font-display);
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--ui-text-highlighted);
  text-wrap: balance;
}

.news[data-variant='inline'] .news__title {
  font-size: 0.875rem;
}

.news__note {
  font-size: 0.875rem;
  color: var(--ui-text-muted);
  margin-top: 0.4rem;
  max-width: 38rem;
  text-wrap: balance;
}

.news__form {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  max-width: 30rem;
}

.news__msg {
  font-size: 0.75rem;
  color: var(--ui-text-dimmed);
  margin-top: 0.5rem;
}

.news__msg--ok {
  color: var(--ui-success);
}

.news__msg--bad {
  color: var(--ui-error);
}
</style>
