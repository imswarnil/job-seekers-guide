import type { NavigationMenuItem } from '@nuxt/ui'

/**
 * The site nav, in one place.
 *
 * Three items. It was five, and two of them were doing nothing a visitor
 * needed: About is now a section of the front page rather than a page of its
 * own, and the changelog is a thing you go looking for once, which makes it
 * footer material. A nav people scan rather than read is a nav that has stopped
 * working, and every item removed makes the three that are left easier to see.
 *
 * `/run` is deliberately not here either: it is a component demonstrated inside
 * lessons, not a destination somebody navigates to, and the story is one entry
 * with two doors behind it.
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
  to: '/my-story'
}, {
  label: 'Questions',
  icon: 'i-lucide-life-buoy',
  to: '/faq'
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
