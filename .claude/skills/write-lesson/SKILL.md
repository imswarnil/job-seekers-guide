---
name: write-lesson
description: Write, review or repair a lesson under content/1.path/. Use whenever the task is authoring curriculum — a new lesson, a rewrite, a glossary, an interview Q&A file, an exercises file, or checking an existing lesson against the house rules. Loads the voice spec, the curriculum spec and the platform mechanics in the right precedence order.
---

# Writing a lesson

## Read these first, in this order

Three files govern a lesson. They do not overlap, and when they disagree there
is a fixed precedence.

| File | Governs | Wins on |
| --- | --- | --- |
| `plan/content-writing-guidelines.md` | **Voice and pedagogy** — who is narrating, the eleven beats, the ten teaching habits, the practice rule, the AI rule, tone | How it reads |
| `content-plan.md` | **Substance** — every track, chapter and lesson, in order, with the exact cliffhanger each one ends on | What is taught, in what order |
| `CLAUDE.md` | **Mechanics** — folders, front matter, component names, checks | What actually renders |

The rule when two disagree:

- Voice, teaching approach, narrator, beats → `plan/content-writing-guidelines.md`.
- What gets taught and in what order, and the hook for a given lesson → `content-plan.md`.
- Component names, front-matter fields, folder shape, anything about the
  platform → `CLAUDE.md`. A pedagogy doc cannot invent a component that does not
  exist.

Read all three before writing. Do not work from memory of them.

## The one rule that outranks everything

**Every lesson ends on a cliffhanger, and the first line of the next lesson
answers it, before anything else.**

The exact hook for each lesson is written in `content-plan.md`, Part Three. Use
that one — do not invent a replacement.

If you add, split or reorder a lesson you have taken on re-welding *both* ends of
the chain. Do it in the same change, never "later".

Prev/next spans the whole path, not the subject: the last lesson of a chapter
hooks into the first lesson of the next chapter, and the last lesson of a track
hooks into the first lesson of the next track.

## Before writing — check the neighbours

```bash
# the lesson before this one: what hook does it leave hanging?
ls content/1.path/<track>/<chapter>/
```

Read the **previous** lesson's `## Next` block and the **next** lesson's opening
paragraph. Your first line resolves the former; your last block sets up the
latter. A lesson written without reading its two neighbours will break the chain.

## The shape

Follow `CLAUDE.md` §5 for the headings — that is the version that matches what
renders. `plan/content-writing-guidelines.md` §5 describes the same eleven beats
in prose; the headings in `CLAUDE.md` are how those beats land on this platform.

Beat 1 (resolve the previous hook) and beat 11 (`## Next`) are not optional.
Everything between them flexes with `kind`.

## Naming — non-negotiable

The project is **the University Management App**. Never an abbreviation, never a
codename, never "the app" on first mention in a lesson. The repository and
directory slug is `university-management-app`.

Every example, variable, table, component, page, chart and exercise belongs to
the university world. The cast and the banned names are in `CLAUDE.md` §4. If an
example is not about the university, rewrite it until it is.

## Build lessons

`kind: project` gives the **plan and the acceptance criteria** — files,
functions, routes, columns, and what "done" looks like — and **never the
finished code**. The only exceptions are the assembly lesson at the end of a
track and the finished-system lesson at the very end.

Before writing one, restate to yourself what already exists in the University
Management App and do not contradict it.

## Diagrams

Reach for the `draw-diagram` skill. Do not hand-roll a diagram or invent a
component name.

## Before calling it done

Run the checklist in `CLAUDE.md` §10 honestly, then:

```bash
pnpm lint
```

Two failure modes to check specifically, because they are the ones that slip:

- **A shown output that was never run.** If a result is illustrative rather than
  from a real run, say so in the prose. Never imply a live run.
- **A wall of text.** A component roughly every screen or two. Prose-only lessons
  fail this platform however good the prose is.

Banned words: "simply", "just", "obviously", "as we all know", "it is important
to note", "capstone". No exclamation marks. British spelling.
