/**
 * The player rail's open/closed state.
 *
 * Two different behaviours behind one flag: on a wide screen the rail is a
 * permanent column that can be collapsed away for focus, and below `lg` it is a
 * slideover that is closed by default. Keeping both in one composable means the
 * header, the footer bar and the shell all talk about "the rail" rather than
 * each deciding for itself which one they are looking at.
 */
export function useRail() {
  const { state, setOption } = useProgress()

  const isNarrow = useMediaQuery('(max-width: 1023px)')

  /** Slideover, only used below `lg`. */
  const open = ref(false)

  const collapsed = computed({
    get: () => state.value.railCollapsed,
    set: value => setOption('railCollapsed', value)
  })

  function toggle() {
    if (isNarrow.value) {
      open.value = !open.value
      return
    }
    collapsed.value = !collapsed.value
  }

  // Following a link inside the slideover should close it.
  function close() {
    open.value = false
  }

  return { isNarrow, open, collapsed, toggle, close }
}
