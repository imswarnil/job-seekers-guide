# Flow Overview

From landing page to a personalised path, in eight screens.

## The whole journey

```
┌──────────────┐
│  Landing     │  the story, the promise, "it's free"
└──────┬───────┘
       │
┌──────▼───────┐
│  Sign up     │  email / password or Google  ── existing user → Sign in
└──────┬───────┘
       │
┌──────▼───────────────────────────────────────────────┐
│  ONBOARDING WIZARD  (sidebar explains every step)     │
│                                                       │
│  1  Who are you        name, photo, DOB, place        │
│  2  Education          college, degree, branch, year  │
│  3  Where are you now  zero / a little / working      │
│  4  Where do you want  goal + target role (optional)  │
│  5  Foundations        pick CS subjects (Module 0     │
│                        pre-selected & locked)         │
│  6  Language           Java (recommended) / C++ /     │
│                        Python / TypeScript            │
│  7  Track              web / data / DevOps / QA / ... │
│  8  Your path          review the assembled plan      │
└──────┬───────────────────────────────────────────────┘
       │
┌──────▼───────┐
│  Dashboard   │  progress, streak, resume, next lesson
└──────┬───────┘
       │
┌──────▼──────────────────────────────────────────────┐
│  THE PATH                                            │
│  0 How IT Works → 1 Foundations → 2 Language →       │
│  3 Database → 4 Web → 5 Projects 1-4 →               │
│  6 Resume → 7 Job Search & AI → 8 Interviews →       │
│  9 Offer, Joining & First Switch                     │
└─────────────────────────────────────────────────────┘
```

## Screen documents

| Step | Document |
|---|---|
| Landing + auth | [01-landing-and-auth.md](01-landing-and-auth.md) |
| 1–2 Profile | [02-profile-setup.md](02-profile-setup.md) |
| 3 Starting point | [03-starting-point.md](03-starting-point.md) |
| 4 Goal | [04-goal-and-destination.md](04-goal-and-destination.md) |
| 5 Foundations | [05-foundation-subjects.md](05-foundation-subjects.md) |
| 6 Language | [06-language-selection.md](06-language-selection.md) |
| 7 Track | [07-track-selection.md](07-track-selection.md) |
| 8 Review | [08-path-review.md](08-path-review.md) |
| After | [09-dashboard-and-learning.md](09-dashboard-and-learning.md) |
| Admin | [10-admin-path-builder.md](10-admin-path-builder.md) |

## Rules that apply to every screen

1. **The sidebar always explains the current step** — what it's for, why we're
   asking, how it changes the path. New users are lost by default; the sidebar
   is the cure.
2. **Every step is skippable except Module 0.** Skipping falls back to a sane
   default rather than blocking.
3. **Progress is saved per step.** A user who closes the tab at step 5 resumes
   at step 5.
4. **Nothing is permanent.** Every choice is editable later from Settings, and
   the path recomposes.
5. **Show the consequence.** As choices are made, a live "your path so far"
   preview updates in the sidebar so the user sees what they're building.

## Data captured

| Field | Step | Used for |
|---|---|---|
| `full_name`, `photo`, `dob`, `place` | 1 | Profile, certificates, community |
| `college`, `degree`, `branch`, `grad_year` | 2 | Persona inference, story lessons |
| `level` | 3 | Starting module, pacing |
| `goal`, `target_role` | 4 | Track, role-weighting of foundations |
| `subjects[]` | 5 | Foundation module composition |
| `language` | 6 | Language course selection |
| `track` | 7 | Applied courses and projects |

Stored as user meta; consumed by the path-assembly rules in
[08-path-review.md](08-path-review.md).
