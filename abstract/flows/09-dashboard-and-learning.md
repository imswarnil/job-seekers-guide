# After onboarding — Dashboard and learning

## The dashboard

The screen a returning learner sees. Its job is to answer one question in under
two seconds: **what do I do right now?**

```
┌────────────────────────────────────────────────────────────┐
│  Welcome back, Aarav.            🔥 12-day streak           │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  CONTINUE                                             │  │
│  │  Module 1 · Operating Systems                         │  │
│  │  Lesson 7 of 14 — "What actually happens on a         │  │
│  │  context switch"                          [ Resume → ]│  │
│  └──────────────────────────────────────────────────────┘  │
│                                                            │
│  Your path            ████████░░░░░░░░░░░░  34%            │
│  Est. finish: 12 Mar 2027 · regular pace                   │
│                                                            │
│  Activity  ▁▃▅▂▇▆▃▁▄▅▇▆▂▃   (last 14 days)                 │
│                                                            │
│  Up next:  OS lesson 8 · Java: Collections · Quiz: DBMS 1   │
└────────────────────────────────────────────────────────────┘
```

Much of this already exists — see `TODO.md` §2, "My Learning".

## The sidebar, post-onboarding

The same sidebar that explained the wizard becomes permanent navigation:

```
  🏠  Dashboard
  🗺️  My Path
  📚  All Courses
  🧪  Projects
  💼  Job Toolkit          resume · tracker · interview prep
  🏆  Wall of Success
  ⚙️  Settings             edit onboarding answers, pace, path
```

## The learning loop

```
Dashboard → Course → Lesson (video / article / quiz) →
Mark complete → Auto-advance to next → Progress updates →
Streak increments → Dashboard
```

Already implemented: lesson player with sidebar progress, mark-complete,
prev/next, auto-complete on Next, server-graded quizzes.

## Accountability features

These are the parts a training institute actually delivered, rebuilt as software:

| Institute provided | Platform equivalent |
|---|---|
| A daily schedule | Path with paced milestones and an estimated finish date |
| A trainer noticing you missed a day | Streaks, and a nudge after 3 days idle |
| Classmates | Wall of Success, and cohort features later |
| "You're on unit 4 of 12" | Live progress bars per course, module, and path |
| A certificate at the end | Certificates (planned — `TODO.md` §3) |
| Someone to ask | Lesson-level Q&A (planned) |

## Re-deciding, safely

From **Settings → My Path**, the learner can:

- change level, goal, target role, subjects, language, or track
- see a **diff** before confirming — "this adds 3 courses, removes 2, and moves
  your finish date by +4 weeks"
- **keep all completed progress** — completed lessons stay completed even if the
  module leaves the path

Nothing a learner has already earned is ever destroyed by changing direction.
That's the whole point of building on shared foundations.

## The end of the path

Completing module 10 does not end the relationship. The final screen offers:

1. **Submit your success story** → the Wall of Success
2. **Set a 6-month reminder** for the first switch conversation
3. **Give back** — mentor, or answer questions, for the people behind you
