import type { NavigationMenuItem } from '@nuxt/ui'

/**
 * The site nav, in one place.
 *
 * Every item carries an icon, because this list is rendered three ways — the
 * desktop menu, the mobile sheet and the command palette — and the palette is
 * unreadable without them.
 */
export const navLinks: NavigationMenuItem[] = [{
  label: 'The path',
  icon: 'i-lucide-route',
  to: '/path'
}, {
  label: 'About',
  icon: 'i-lucide-compass',
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
