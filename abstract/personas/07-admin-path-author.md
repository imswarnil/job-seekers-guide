# Persona 7 — The Admin / Path Author (internal)

**Shorthand:** the person running the platform — initially the founder, later
mentors and content collaborators.

## Situation

Needs to serve a new kind of learner *without shipping code*. A new persona
appears ("B.Sc. Agriculture graduates asking about agri-tech"), or a role gains
demand, or an existing path proves too long — and the response must be a
composition change, not a release.

## What they need

1. **A course library** — every course, lesson, article, video, and quiz visible
   in one place, reusable across any number of paths.
2. **A visual path builder** — drag courses and standalone items into an ordered
   sequence of milestones, name it, publish it. Already built; see
   `TODO.md` §3.
3. **Path targeting rules** — map onboarding answers (degree, branch, level,
   target role) to a recommended path, editable without code.
4. **Content authoring in place** — write lessons, attach video with clip
   ranges, build quizzes, all inside the console.
5. **Analytics that answer product questions** — where learners drop off, which
   module stalls, which path completes.

## The core constraint this persona imposes

> **A path must be data.** If serving a new persona requires a developer, the
> platform has failed its own design.

Concretely, this is why the LMS models `Course`, `Lesson`, and `Learning Path`
as content types with a builder UI, rather than hard-coding curricula into
templates.

## Workflow

```
Identify persona gap
      ↓
Reuse existing courses where possible
      ↓
Author only the genuinely new lessons
      ↓
Compose a path in the visual builder
      ↓
Attach onboarding targeting rules
      ↓
Publish → watch analytics → adjust order
```

## Seed data expectation

Every persona and path described in this folder must be representable using the
seed content in [../markdown/](../markdown/) — dummy end-to-end, from
"what is software" through "how to switch jobs" — so the flow can be
demonstrated before real content exists.
