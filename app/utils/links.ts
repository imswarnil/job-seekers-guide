import type { NavigationMenuItem } from '@nuxt/ui'

export const navLinks: NavigationMenuItem[] = [{
  label: 'Courses',
  icon: 'i-lucide-graduation-cap',
  to: '/courses'
}, {
  label: 'Docs',
  icon: 'i-lucide-book',
  to: '/docs/getting-started'
}, {
  label: 'Writing',
  icon: 'i-lucide-pencil',
  to: '/blog'
}, {
  label: 'Changelog',
  icon: 'i-lucide-history',
  to: '/changelog'
}]
