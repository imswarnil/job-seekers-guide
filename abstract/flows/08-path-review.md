# Step 8 — Your path

The payoff screen. Everything the user answered becomes one visible plan.

## The screen

> ### Aarav, here's your path.
> Built from your answers. Roughly **28 weeks** at your chosen pace. Free, all
> of it. Change anything, any time.

A vertical milestone timeline, each module expandable:

```
●  0  How the IT Industry Works              2 wk   ▸ 6 lessons
│     Locked in for everyone. Start here.
│
●  1  Foundations — Semester 1: The Machine   4 wk   ▸ OS · Networks
│  2  Foundations — Semester 2: The Data      3 wk   ▸ DBMS · SQL
│  3  Foundations — Semester 3: The Code      6 wk   ▸ OOP · DSA
│  4  Foundations — Semester 4: The Craft     4 wk   ▸ SE · Web Tech
│  5  Foundations — Semester 5: The Scale     3 wk   ▸ System Design · AI
│
●  ⟲  Java — running alongside from week 3   12 wk   ▸ 9 courses
●  ⟲  English & Communication — throughout      —    ▸ weekly
│
●  6  Projects 1 → 4                          6 wk   ▸ web track
│
●  7  Your Resume                             1 wk
●  8  Job Search & Using AI Properly          2 wk
●  9  Interviews — HR & Technical             2 wk
● 10  Offer, Joining & Your First Switch      1 wk
```

## Controls on this screen

- **Adjust pace** — relaxed (fewer hours/week) / regular / intense → recomputes
  the estimated finish date
- **Reorder or remove** any optional module
- **Edit any earlier answer** — inline, without restarting the wizard
- **[ Start learning → ]** — the primary action, big and unmissable

## Sidebar copy

> **This isn't a contract.** Nearly everyone changes their path — they discover
> they like data more than web, or that they need to slow down, or that they're
> ready to start applying at month four. The path adapts. What matters is that
> you start Module 0 today, not that you got every downstream choice right.
>
> **28 weeks looks long.** It's about six months. If you'd walked into a
> training institute in month one instead, you'd be six months in, ₹80,000
> lighter, and on somebody else's syllabus. And the six months pass either way.

## Path assembly rules

Pseudo-rules the assembler follows (implemented as data in the admin console —
see [10-admin-path-builder.md](10-admin-path-builder.md)):

```
path = []
path += MODULE_0[level]                         # always, variant by level
path += foundations(selected_subjects, level, target_role)
path += language_track(language)                # parallel, starts week 3
path += applied(track)                          # web / data / devops / ...
path += projects(track, 4)
path += story_lesson(persona)                   # branch-switch or career-change
path += resume(goal)                            # fresher or experienced variant
path += job_search + ai_for_job_search
path += interviews(goal)
path += offer_and_switch(goal)
if selected(english): path += english           # parallel throughout
```

Weighting rules:

| Condition | Effect |
|---|---|
| `level == 1` | Full Module 0, foundations at full depth, gentlest pacing |
| `level >= 3` | Module 0 condensed, foundations marked "revision", System Design promoted earlier |
| `target_role == data engineer` | DBMS + OS + Networks weighted up; DSA weighted down |
| `target_role == frontend` | Web Tech + OOP up; ToC/Compilers optional |
| `goal == switch jobs` | Offer & Switch expanded; fresher resume variant swapped out |
| `branch != CS` | Branch-switch story lesson inserted before Resume |
| `age band / working` | Pace defaults to relaxed |

## After [ Start learning ]

The path is persisted as a `learning_path` assignment on the user, the user is
enrolled in module 0's first course, and they land on the dashboard with lesson
1 already queued. **Zero further decisions required to begin.**
