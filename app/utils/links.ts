import type { NavigationMenuItem } from '@nuxt/ui'

/**
 * The site nav, in one place.
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
  icon: 'i-lucide-user-round',
  to: '/my-story'
}, {
  label: 'Series',
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
