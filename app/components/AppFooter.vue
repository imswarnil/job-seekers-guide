<script setup lang="ts">
const { path } = usePath()

// The footer lists the path itself rather than a made-up set of sections — the
// subjects are the site, and they are already ordered.
const columns = computed(() => [{
  label: 'Start here',
  children: [
    { label: 'Everything, in order', to: '/start' },
    ...path.value.subjects.slice(0, 4).map(subject => ({
      label: subject.title,
      to: subject.path
    }))
  ]
}, {
  // About is a band of the front page rather than a page, and the changelog
  // lives here rather than in the nav — it is something you go looking for
  // once, not something you navigate by.
  label: 'About',
  children: [
    { label: 'What this is', to: '/#about' },
    { label: 'The ten principles', to: '/#the-ten-principles' },
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
          <!-- The fourth column. The list used to be repeated on six pages,
               which meant six chances to ask and one to annoy; in the footer it
               is offered once, everywhere, and never interrupts anything. -->
          <template #right>
            <div class="max-w-sm">
              <NewsletterSignup
                variant="inline"
                title="Get told when something lands"
              />
              <p class="mt-4 text-xs text-dimmed">
                New lessons, new chapters, new episodes. No more than one email a
                month, and nothing else — ever.
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
