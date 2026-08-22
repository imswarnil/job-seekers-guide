import type { Body } from '~/utils/autoAds'
import { splitForAds } from '~/utils/autoAds'

/**
 * A page body, cut into the pieces an ad goes between.
 *
 * Three components render lesson-shaped prose — the lesson itself and the two
 * overview pages — and all three want the same treatment, so the settings are
 * read in one place rather than three. The rules live in `~/utils/autoAds.ts`;
 * the knobs are `ads.autoInsert` in `app.config.ts`.
 *
 * A return of one chunk means the page gets no automatic ads, which is the
 * normal answer for anything short.
 */
export function useAutoAds(body: MaybeRefOrGetter<Body | undefined | null>) {
  const { ads } = useAppConfig()

  return computed(() => splitForAds(toValue(body), {
    every: ads?.autoInsert?.afterParagraphs ?? 0,
    max: ads?.autoInsert?.max ?? 0,
    minParagraphs: ads?.autoInsert?.minParagraphs ?? 0
  }))
}
