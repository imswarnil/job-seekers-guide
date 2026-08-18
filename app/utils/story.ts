/**
 * How the story titles itself.
 *
 * The same events are published twice — as a ten-part series and as a book —
 * and search engines are entitled to treat two pages with the same title as one
 * page worth indexing. So the two surfaces are titled from different templates
 * and described in different language: the series is billed like television
 * (episode number, what happens, what it sets up), the book like a book
 * (chapter, place, year).
 *
 * The other job here is context. "The Mask" tells a stranger nothing. "Episode
 * 4: The Mask — pretending to be a developer at a job I could not do" tells
 * them what they would be watching, which is the entire difference between a
 * result that gets clicked and one that does not.
 */

/** Where an instalment sits in the arc, which changes how it should be sold. */
export type Beat = 'opening' | 'rising' | 'turn' | 'finale'

export function beatOf(index: number, total: number): Beat {
  if (index === 0) {
    return 'opening'
  }
  if (index >= total - 1) {
    return 'finale'
  }
  return index / total < 0.6 ? 'rising' : 'turn'
}

const SERIES_NAME = 'My Story'

/**
 * `Episode 04: The Mask | My Story` — the number first, because a series is an
 * order and the order is the thing a search result has to communicate.
 */
export function episodeTitle(episode: number, title: string) {
  return `Episode ${String(episode).padStart(2, '0')}: ${title} | ${SERIES_NAME}`
}

/** The line under the title. Never the same shape twice in a row. */
export function episodeDescription(options: {
  episode: number
  total: number
  description: string
  year?: string
  place?: string
  cliffhanger?: string
}) {
  const { episode, total, description, year, place, cliffhanger } = options
  const beat = beatOf(episode - 1, total)

  const where = [place, year].filter(Boolean).join(', ')
  const setting = where ? `${where}. ` : ''

  const frame = {
    opening: `Episode ${episode} of ${total}, where it starts.`,
    rising: `Episode ${episode} of ${total}.`,
    turn: `Episode ${episode} of ${total}, where it turns.`,
    finale: `Episode ${episode} of ${total}, the last one.`
  }[beat]

  const tail = cliffhanger ? ` ${cliffhanger}` : ''

  return `${frame} ${setting}${description}${tail}`.trim()
}

/**
 * `Chapter 4: The Mask — Bangalore, 2018` — a book locates you in time and
 * place, which is also exactly the context a search result is missing.
 */
export function chapterTitle(chapter: number, title: string, options?: { place?: string, year?: string }) {
  const label = chapter === 0 ? 'Prologue' : `Chapter ${chapter}`
  const where = [options?.place, options?.year].filter(Boolean).join(', ')
  return where
    ? `${label}: ${title} — ${where}`
    : `${label}: ${title}`
}

export function chapterDescription(options: {
  chapter: number
  total: number
  description?: string
  subtitle?: string
  place?: string
  year?: string
}) {
  const { chapter, total, description, subtitle, place, year } = options
  const label = chapter === 0 ? 'The prologue' : `Chapter ${chapter} of ${total}`
  const where = [place, year].filter(Boolean).join(', ')
  const body = description || subtitle || ''
  return [`${label}${where ? ` — ${where}` : ''}.`, body, 'Read free, no sign-up.']
    .filter(Boolean)
    .join(' ')
}

/**
 * The ambient loop behind the television.
 *
 * Deliberately not any episode: it is the room, not the broadcast, and passing
 * an unrelated video off as an episode would be a lie told by a design detail.
 */
export const ambientVideoId = 'ecOkmTD7KhU'
