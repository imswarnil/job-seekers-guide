---
name: draw-diagram
description: Draw or review a teaching diagram for a lesson — a sequence, a memory layout, a stack, a graph, a pipeline, a comparison. Use whenever a lesson needs a picture, when choosing between ::flow / ::memory / ::timeline, or when generating SVG with graphviz or mermaid for this course.
---

# Drawing a diagram

The standing habit from `plan/content-writing-guidelines.md` §13: **draw it
whenever a picture teaches faster than a paragraph.** Anything spatial gets
drawn — memory and references, the call stack, a data structure's shape, how a
join matches rows, the order a query is evaluated in, an index versus a full
scan, a deadlock as a cycle, DNS resolution, the box model, the event loop, a
component tree, the server/client boundary, a deploy pipeline.

## Reach for a component first

This platform already has a diagram family, and everything in it is authored in
markdown, works with JavaScript off, and shares one visual frame (`DgmFigure`).
Use it. Do not hand-roll a picture that the component already draws, and do not
invent a component name — the full reference with a runnable example of each is
`.studio/components.md`.

| What you are drawing | Component |
| --- | --- |
| Anything with an order or a sequence | `::flow` (`direction="vertical"` when long) |
| Boxes, pointers, arrays, stacks, queues, lists, tables | `::memory` |
| A step-by-step animation — pushing and popping, a sort swapping, a search halving | `::memory` with `frames` (it becomes a stepper) |
| Walking through code | `::code-trace` — never write "on line three" in prose |
| Wrong versus right, broken versus fixed | `::compare` with `verdict="wrong"` / `verdict="right"` |
| A trade-off between two technologies | `::pros-cons` |
| Things in time | `::timeline` |
| Parallel small points | `::feature-list` |

`::memory` has no `heap` kind on purpose — a wrong tree diagram teaches worse
than a correct table.

## The rules that apply to every diagram

From §13 and `CLAUDE.md` §6, and they are not stylistic preferences:

- **One idea per diagram.** Two ideas means two diagrams.
- **Exactly one accent per diagram** — one `highlight` on a `::flow`, one
  `active` cell in a `::memory`. The accent is the single thing being taught.
- **One red element per view.** Red means the live wire: the hook, the error, the
  wrong number, the danger. If red appears twice it stops meaning anything. This
  is per *view*, not per diagram — a diagram with a red element sitting next to a
  `::caution` block is two reds.
- **Every part labelled.** An unlabelled box teaches nothing.
- **Never invent a URL** for an image or a video.

Colour language for this site is in `.studio/brand.md`: indigo carries structure,
teal is the accent, amber is rationed to milestones.

## When no component fits — `::diagram`

Graph traversal, a hash collision, a packet's journey, a query's evaluation
order: draw those yourself and put them in `::diagram`, which wraps your SVG in
the same frame as everything else (label, caption, and the scroll container that
stops a wide drawing scrolling the page sideways on a phone).

```md
::diagram{label="Two keys, one bucket" caption="The second name queues behind the first."}
  <svg viewBox="0 0 448 152" role="img" aria-label="Two names hash to the same bucket.">
    <rect x="1" y="10" width="120" height="32" rx="4" fill="none" stroke="currentColor" opacity="0.45" />
    <text x="61" y="30" text-anchor="middle">"Anita Rao"</text>
    <rect x="286" y="10" width="146" height="128" rx="4" data-accent fill="none" stroke-width="1.5" />
    <text x="359" y="32" text-anchor="middle">bucket 3</text>
  </svg>
::
```

Three rules the component cannot enforce for you:

- **Draw with `currentColor`**, not hex, so the diagram follows the reader's
  theme instead of fighting it.
- **Mark exactly one element `data-accent`** — that is the accent, and it is the
  single thing the diagram teaches.
- **Always give the `<svg>` a `viewBox` and an `aria-label`.** Without the first
  it will not scale; without the second a screen reader sees nothing.

A worked example is live in
`content/1.path/00.terminal/00.how-this-course-works/02.how-to-read-these-lessons.md`.

### Sketching with graphviz or mermaid

Both are installed. Use them to get a layout right, then **restyle the output** —
neither looks like this site and neither should ship as-is — and optimise before
it goes in a lesson, because inline SVG ships in the HTML on every page view.

```bash
# graphviz: graphs, prerequisite charts, deadlock cycles, state ladders
dot -Tsvg diagram.dot -o diagram.svg

# mermaid: pipelines, sequences, entity relationships
pnpm exec mmdc -i diagram.mmd -o diagram.svg

# always, before it goes anywhere near a lesson
pnpm svg:opt diagram.svg
```

Keep the source (`.dot` / `.mmd`) next to the output so the diagram can be
regenerated rather than hand-patched.

## Check before moving on

- Does this diagram teach one idea, or two?
- Is there exactly one accent, and is it on the thing the lesson is about?
- Is there more than one red element in this view?
- Is every part labelled?
- Would a paragraph have taught it faster? If yes, delete the diagram.
