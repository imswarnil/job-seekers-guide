<script setup lang="ts">
const { path } = usePath()

// The footer lists the path itself rather than a made-up set of sections — the
// subjects are the site, and they are already ordered.
const columns = computed(() => [{
  label: 'The path',
  children: [
    { label: 'Everything, in order', to: '/path' },
    ...path.value.subjects.slice(0, 4).map(subject => ({
      label: subject.title,
      to: subject.path
    }))
  ]
}, {
  label: 'About',
  children: [
    { label: 'What this is', to: '/about' },
    { label: 'Principles', to: '/about#the-ten-principles' },
    { label: 'Questions', to: '/faq' },
    { label: 'Changelog', to: '/changelog' }
  ]
}, {
  label: 'Honest answers',
  children: [
    { label: 'Do I need maths?', to: '/faq' },
    { label: 'What is left out', to: '/faq' },
    { label: 'Is it really free?', to: '/faq' },
    { label: 'Will you get me a job?', to: '/faq' }
  ]
}])
</script>

<template>
  <USeparator class="h-px" />

  <UFooter :ui="{ top: 'border-b border-default' }">
    <template #top>
      <UContainer>
        <UFooterColumns :columns="columns">
          <template #right>
            <div class="max-w-xs">
              <p class="font-display font-semibold text-highlighted">
                Free core path, forever
              </p>
              <p class="mt-2 text-sm text-muted">
                Orientation, foundations, one language, projects and the job hunt
                stay free. Anything charged for later has to be genuinely
                additional — never the map itself.
              </p>
            </div>
          </template>
        </UFooterColumns>
      </UContainer>
    </template>

    <template #left>
      <p class="text-muted text-sm">
        © {{ new Date().getFullYear() }} Job Seekers Guide — no fees, no guarantees, no bonds.
      </p>
    </template>

    <template #right>
      <UButton
        to="https://github.com"
        target="_blank"
        icon="i-simple-icons-github"
        aria-label="Job Seekers Guide on GitHub"
        color="neutral"
        variant="ghost"
      />
    </template>
  </UFooter>
</template>
