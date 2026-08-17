<script setup lang="ts">
import type { AdSlotId } from '~/utils/ads'

/**
 * ::ad — an ad placed by hand inside a lesson.
 *
 * A two-line wrapper on purpose: the same `AdSlot` renders here and everywhere
 * else on the site, so the master switch in `app.config.ts` turns off the ones
 * authors wrote into markdown too.
 *
 * ```md
 * ::ad{placement="in-article"}
 * ::
 * ```
 */
withDefaults(defineProps<{
  placement?: AdSlotId
  variant?: 'inline' | 'banner' | 'card'
}>(), {
  placement: 'in-article',
  variant: 'inline'
})
</script>

<template>
  <AdSlot
    :placement="placement"
    :variant="variant"
  />
</template>
