/**
 * Splitting a lesson so ads can be dropped between the paragraphs the author
 * wrote.
 *
 * This returns the body cut into pieces rather than the body with ad nodes
 * spliced into it, and the reason is a bug worth remembering. Nuxt Content only
 * bundles the MDC components it can see used in markdown at build time, so an
 * `['ad', …]` node injected at runtime resolved correctly in dev and shipped as
 * a literal `<Ad placement="in-feed">` tag in the production build — visible in
 * the HTML, rendered by nothing. Cutting the body instead means the caller
 * renders `AdSlot` itself, as an ordinary auto-imported component, and there is
 * no build-time resolution to get wrong.
 *
 * The pieces are still rendered server-side and in order, so the ads are in the
 * prerendered HTML at the right position. An ad inserted into the DOM after
 * paint would shove the paragraph a reader is mid-sentence in.
 */

/** A parsed body node: either a text string or an element tuple. */
type Node = string | [string, Record<string, unknown>, ...unknown[]]

export interface Body {
  type: string
  value: Node[]
}

export interface AutoAdOptions {
  /** Cut after every Nth top-level paragraph. 0 disables the whole thing. */
  every: number
  /** Most cuts to make in one page, so most ads. 0 means no limit. */
  max: number
  /** Leave pages shorter than this alone entirely. */
  minParagraphs: number
}

function isParagraph(node: Node): boolean {
  return Array.isArray(node) && node[0] === 'p'
}

/**
 * Returns the body cut into consecutive pieces. An ad belongs between each
 * neighbouring pair, so a return of length one means this page gets none.
 *
 * Three guards, and they are the reason this is not a one-line chunk:
 *
 * · **Short pages are skipped.** An ad every three paragraphs is reasonable in
 *   a two-thousand-word lesson and absurd in a glossary entry. Below
 *   `minParagraphs` the page is returned whole.
 * · **There is a ceiling.** "More ads than publisher content" is a real AdSense
 *   policy violation and the penalty lands on the account, not the page. `max`
 *   is what stops a long lesson becoming a stack of adverts with prose in the
 *   gaps.
 * · **Nothing lands near the end.** There is already an ad below the lesson
 *   body, so a cut in the last stretch puts two of them together with a
 *   sentence trapped in between.
 */
export function splitForAds(body: Body | undefined | null, options: AutoAdOptions): Body[] {
  const { every, max, minParagraphs } = options

  if (!body?.value?.length) {
    return []
  }
  if (every <= 0) {
    return [body]
  }

  const total = body.value.filter(isParagraph).length
  if (total < minParagraphs) {
    return [body]
  }

  const chunks: Body[] = []
  let current: Node[] = []
  let seen = 0
  let cuts = 0

  for (const node of body.value) {
    current.push(node)

    if (!isParagraph(node)) {
      continue
    }

    seen += 1

    if (seen % every !== 0) {
      continue
    }
    if (max > 0 && cuts >= max) {
      continue
    }
    // Stop once there is not another full run of paragraphs left to justify
    // one, which keeps the last ad away from the one under the lesson.
    if (total - seen < every) {
      continue
    }

    chunks.push({ ...body, value: current })
    current = []
    cuts += 1
  }

  if (current.length) {
    chunks.push({ ...body, value: current })
  }

  return chunks
}
