<script setup lang="ts">
const columns = [{
  label: 'Learn',
  children: [{
    label: 'All courses',
    to: '/courses'
  }, {
    label: 'Start at module zero',
    to: '/courses/how-the-industry-works'
  }, {
    label: 'The curriculum',
    to: '/docs/curriculum'
  }, {
    label: 'What we exclude',
    to: '/docs/curriculum/what-we-exclude'
  }]
}, {
  label: 'About',
  children: [{
    label: 'What this is',
    to: '/docs/getting-started'
  }, {
    label: 'Principles',
    to: '/docs/getting-started/principles'
  }, {
    label: 'Writing',
    to: '/blog'
  }, {
    label: 'Changelog',
    to: '/changelog'
  }]
}, {
  label: 'Contribute',
  children: [{
    label: 'Course structure',
    to: '/docs/authoring/course-structure'
  }, {
    label: 'Writing a lesson',
    to: '/docs/authoring/writing-a-lesson'
  }, {
    label: 'Markdown reference',
    to: '/docs/authoring/markdown-reference'
  }, {
    label: 'Help for learners',
    to: '/docs/help/for-learners'
  }]
}]
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
