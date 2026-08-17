/**
 * Scroll-triggered reveal, arranged so that nothing depends on it.
 *
 * The class that *hides* a diagram is added by JavaScript on mount. A reader
 * with no JavaScript, a crawler, and the prerendered HTML all get the finished
 * diagram; the animation is something the browser adds on top when it can. Doing
 * it the other way round — hidden in the markup, revealed by script — is how
 * content disappears for the people least able to work around it.
 */
export function useReveal() {
  const el = useTemplateRef<HTMLElement>('reveal')

  const armed = ref(false)
  const visible = useElementVisibility(el)

  onMounted(() => {
    armed.value = true
  })

  // Once seen, stay seen — re-hiding on scroll-back is motion nobody asked for.
  const seen = ref(false)
  watch(visible, (value) => {
    if (value) {
      seen.value = true
    }
  })

  const classes = computed(() => ({
    'dgm-reveal': armed.value,
    'dgm-in': !armed.value || seen.value
  }))

  return { el, classes }
}
