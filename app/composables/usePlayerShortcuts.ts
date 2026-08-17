interface PlayerShortcutHandlers {
  next?: () => void
  previous?: () => void
  toggleComplete?: () => void
  toggleRail?: () => void
}

/**
 * Keyboard control for the player.
 *
 * The reason a video course feels faster than a documentation site is that you
 * never touch the mouse between one lesson and the next. Nuxt UI's
 * `defineShortcuts` already ignores keypresses inside inputs and
 * `contenteditable`, so a reader typing in a code runner is safe.
 */
export function usePlayerShortcuts(handlers: PlayerShortcutHandlers) {
  const noop = () => {}

  defineShortcuts({
    'j': handlers.next || noop,
    'arrowright': handlers.next || noop,
    'k': handlers.previous || noop,
    'arrowleft': handlers.previous || noop,
    'm': handlers.toggleComplete || noop,
    '[': handlers.toggleRail || noop,
    'g-p': () => navigateTo('/path'),
    'g-h': () => navigateTo('/')
  })
}

/** Documented once, shown in the help sheet and nowhere hardcoded twice. */
export const playerShortcuts = [
  { keys: ['J'], label: 'Next lesson' },
  { keys: ['K'], label: 'Previous lesson' },
  { keys: ['M'], label: 'Mark finished' },
  { keys: ['['], label: 'Show or hide the path' },
  { keys: ['G', 'P'], label: 'Go to the whole path' },
  { keys: ['/'], label: 'Search' }
]
