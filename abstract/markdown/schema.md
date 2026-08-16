# Data Schema

The frontmatter contract for every content type. Maps directly onto the LMS
plugin's CPTs and custom tables.

## Learning Path

```yaml
---
type: learning_path
slug: non-cs-to-web-developer
title: "Non-CS Graduate → Web Developer"
summary: "For graduates from any branch, starting from zero."
persona: non-cs-graduate          # informational; targeting is below
duration_weeks: 30
targeting:                        # scored against onboarding answers
  level: [1, 2]
  branch: [non-cs]
  goal: [first-job]
  target_role: [web-developer, full-stack, any]
  priority: 20
milestones:                       # ordered; course code or standalone item
  - course: CS-000
  - course: CS-OS-101
  - item: video/why-your-branch-doesnt-matter
  - course: CS-NET-101
---
```

## Course

```yaml
---
type: course
code: CS-OS-101
slug: operating-systems
title: "Operating Systems"
category: foundations             # foundations | language | applied | projects | career
summary: "Processes, memory, deadlocks — and why your server fell over."
duration_hours: 24
level: 1                          # 1 intro · 2 intermediate · 3 advanced
prerequisites: [CS-000]
outcomes:
  - "Explain what happens on a context switch"
  - "Diagnose a deadlock and describe three ways to break it"
interview_weight: high            # high | medium | low
modules:                          # ordered groups of lessons
  - title: "Processes & Threads"
    lessons: [what-is-a-process, threads-vs-processes, context-switching]
  - title: "Memory"
    lessons: [memory-layout, virtual-memory, paging]
---
```

## Lesson

```yaml
---
type: lesson
slug: what-is-a-process
title: "What is a process, really"
course: CS-OS-101
format: article                   # article | video | quiz | exercise
duration_min: 12
free_preview: true
video:                            # only when format: video
  url: https://www.youtube.com/watch?v=XXXXXXXXXXX
  start: 42                       # clip range, seconds
  end: 940
source: curated                   # original | curated | practice
interview_link: "Asked as 'process vs thread' in almost every fresher round."
---

Lesson body in markdown. Follows the teaching principles in
../curriculum/README.md: why before what, show then explain, name what to skip,
end with a check.
```

## Quiz

```yaml
---
type: quiz
slug: os-quiz-1
title: "Operating Systems — checkpoint 1"
course: CS-OS-101
pass_percent: 70
completes_lesson: true            # passing auto-completes the lesson
questions:
  - q: "A context switch saves..."
    options:
      - "the process's registers and program counter"
      - "the entire contents of RAM"
      - "the source code of the program"
      - "nothing; it starts fresh"
    answer: 0
    explain: "Only the execution state needs saving — that's what makes it fast."
---
```

Answers are stored **server-side only** and never sent to the browser — already
implemented in the quiz engine (`TODO.md` §3).

## Standalone path item

An article, video, or quiz attached directly to a path without a parent course:

```yaml
---
type: item
slug: why-your-branch-doesnt-matter
title: "Why your branch doesn't matter"
format: video
duration_min: 8
video: { url: "...", start: 0, end: 480 }
---
```

## Project

```yaml
---
type: project
slug: job-application-tracker
title: "Project 3 — Job Application Tracker"
track: web
number: 3
format: spec                      # walkthrough | guided | spec | own-idea
duration_hours: 20
deliverables:
  - "Deployed at a public URL"
  - "Public GitHub repo with real commit history"
  - "README a stranger can follow"
resume_block: true                # generates a resume bullet set on completion
---
```

## Field notes

- `slug` is unique within its type and is the permalink segment.
- `code` is unique across all courses, forever.
- `targeting.priority` breaks ties when several paths match a user; the highest
  wins. A `default-path` with priority `0` must always exist.
- `interview_weight` drives the "asked in interviews" badge in the UI.
- `source: curated` requires an attributed external link in the body — we credit
  everyone we curate.
