/**
 * Ad placements, declared in one place.
 *
 * The registry exists so that "where do ads appear on this site" has a single
 * answer that can be read in ten seconds, rather than being distributed across
 * whichever components happened to grow one. Turning a slot off is a boolean in
 * `app.config.ts`; adding a slot means adding a line here.
 *
 * Every slot declares its own reserved height. That is not decoration — an ad
 * that arrives after layout pushes the paragraph somebody is mid-sentence in,
 * and on a teaching site that is the difference between an annoyance and losing
 * your place.
 */

export type AdSlotId = 'in-article' | 'lesson-footer' | 'rail-bottom' | 'path-parallax'

export interface AdSlot {
  id: AdSlotId
  /** Human name, for the ad-slot label the law generally wants shown. */
  label: string
  /** Reserved box, so nothing shifts when the creative arrives. */
  width: number
  height: number
  /** Below this viewport width the slot renders nothing at all. */
  minViewport?: number
  /** A note for whoever is deciding whether this slot should exist. */
  note?: string
}

export const adSlots: Record<AdSlotId, AdSlot> = {
  'in-article': {
    id: 'in-article',
    label: 'Advertisement',
    width: 728,
    height: 90,
    minViewport: 0,
    note: 'Between blocks of a lesson. The only slot authors can place by hand, with ::ad.'
  },
  'lesson-footer': {
    id: 'lesson-footer',
    label: 'Advertisement',
    width: 728,
    height: 90,
    note: 'After the lesson body, above the up-next cards. The least intrusive slot on the site.'
  },
  'rail-bottom': {
    id: 'rail-bottom',
    label: 'Advertisement',
    width: 240,
    height: 400,
    minViewport: 1280,
    note: 'Foot of the player rail. Off by default — the rail is navigation and should stay navigation.'
  },
  'path-parallax': {
    id: 'path-parallax',
    label: 'Advertisement',
    width: 1200,
    height: 260,
    minViewport: 768,
    note: 'Full-width parallax band on /path. Off by default: it is the single most likely thing to make this site feel cheap, and parallax is the most likely thing to wreck CLS and INP. Prove it before enabling.'
  }
}
