# How a lesson is written

The curriculum this is written against — every track, chapter and lesson, in
order, with the cliffhanger each one ends on — is `content-plan.md` in the
repository root. The working rules for authoring it are `CLAUDE.md`. This file is
the short version: voice, front matter, and the shape of a page.

## The shape

```md
(no heading — one or two sentences answering the previous lesson's cliffhanger)

## Why this matters in a job     Concrete. Not "databases are important" but
                                 "the query that worked on your laptop takes
                                 forty seconds in production, and this is why".

## <the idea, named plainly>     Analogy first, then the technical version.
                                 Draw it with ::flow or ::memory.

## <watch it happen>             A small example that runs. Broken version
                                 first where that teaches something.

## What confuses people here     The trap, named before the reader falls in.

::real-life                      Where it shows up in a job. The single
                                 highest-value block on the platform.

## What you can do now           Plus the actual output, so they can check
                                 their own run.

## Your turn                     Solutions behind an ::accordion, never before
                                 the attempt.

## Check yourself                Exactly two questions. One is the kind an
                                 interviewer asks.

## The interview question this answers

## Next                          The cliffhanger, in a ::callout, unresolved.
                                 Always last.
```

The first beat and the last are not optional. **Every lesson ends on a
cliffhanger, and the first line of the next lesson answers it** — that chain is
the one rule that outranks everything else here. The exact hook for each lesson
is in `content-plan.md`.

The middle headings are named for their content. "The idea, analogy first" is a
scaffold, not a title a reader should see.

## Front matter

```yaml
---
title: How a program actually runs
description: One sentence. It appears on cards, in search results and on the
  social image, so write it for a stranger.
minutes: 14
kind: lesson        # lesson | practice | project | quiz | reading
---
```

`minutes` drives the reading estimates on the subject page and the whole path.
Guess honestly — 200 words a minute, plus a minute for every diagram.

## Voice

Every lesson is written by one person: a job seeker looking back at how he got
hired. Not a professor and not a documentation team — somebody one step ahead of
the reader on the same path, turning around to say "this is the part that
confused me". First person, to one reader.

The reader has been rejected more than once and is short on time and confidence.

- **Say the number.** "Four weeks", "about 40%", "nine seconds". Vague
  encouragement is what the institutes sell.
- **Name your own confusion**, and warn before the hard parts, out loud, so the
  reader never thinks the problem is them.
- **Define every new word the first time it appears**, in the same breath.
- **Say what to skip.** A lesson that says "you can skip this on a first pass,
  here is what you lose" is as valuable as one that teaches. Skipping
  deliberately is studying; skipping because you got lost is drifting.
- **No hype, no exclamation marks, no "amazing".** No "simply" or "just" — if it
  were simple they would not be reading.
- **Never claim outcomes.** No placement percentages, no guaranteed packages.
- **Second person.** "You", not "the student" or "one".
- **British spelling**, to match the rest of the site.

## Things to avoid

- Starting with a dictionary definition.
- A wall of prose where a `::compare` or a `::memory` would do it in one glance.
- Teaching a tool without teaching the thing underneath it.
- Pretending a trade-off does not exist. Use `::pros-cons`.
- More than one accent colour in a single diagram.
- Two ideas in one lesson. Split it instead.
- Generic examples. Everything belongs to the university and to UniversityOS —
  no `foo`, no `Animal`, no `employees` table, no to-do apps.
- Handing over a practice answer before the reader has attempted it.
- The word "capstone". Say "the finished system" or "the assembly lesson".
