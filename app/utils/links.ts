import type { NavigationMenuItem } from '@nuxt/ui'

/**
 * The site nav, in one place.
 *
 * Five items, and that is close to the ceiling — a nav people scan rather than
 * read is a nav that has stopped working. `/run` is deliberately not here: it is
 * a component demonstrated inside lessons, not a destination somebody navigates
 * to, and the story lives under the series rather than as a separate entry.
 *
 * Every item carries an icon, because this list is rendered three ways — the
 * desktop menu, the mobile sheet and the command palette — and the palette is
 * unreadable without them.
 */
export const navLinks: NavigationMenuItem[] = [{
  label: 'Start here',
  icon: 'i-lucide-compass',
  to: '/start'
}, {
  label: 'My story',
  icon: 'i-lucide-clapperboard',
  to: '/series'
}, {
  label: 'About',
  icon: 'i-lucide-info',
  to: '/about'
}, {
  label: 'Questions',
  icon: 'i-lucide-life-buoy',
  to: '/faq'
}, {
  label: 'Changelog',
  icon: 'i-lucide-history',
  to: '/changelog'
}]

/** Where the source lives. Used by the header star and by "edit this page". */
export const repo = {
  owner: 'imswarnil',
  name: 'job-seekers-guide',
  branch: 'main'
} as const

export const repoUrl = `https://github.com/${repo.owner}/${repo.name}`

/** Deep link to edit one content file on GitHub. */
export function editUrl(file: string) {
  return `${repoUrl}/edit/${repo.branch}/${file}`
}
