# Admin — Course library and path builder

The requirement in one line:

> **An admin must be able to build a complete new learning path from existing
> courses, without a developer.**

## Course library

Every piece of content is a reusable unit:

| Type | Description |
|---|---|
| **Course** | A container of modules → lessons. Has a code (e.g. `CS-OS-101`), category, description, card art |
| **Lesson** | Article / video (with clip range) / quiz. Belongs to a course |
| **Standalone item** | An article, video, or quiz attached directly to a path without a parent course |
| **Learning Path** | An ordered sequence of milestones, each being a course or standalone item |

Already implemented — see `TODO.md` §3: CPTs, modules taxonomy, visual course
builder, quiz engine, video clip ranges, course codes, categories.

## The path builder

```
┌─── LIBRARY ──────────┐   ┌─── PATH: "Non-CS Graduate → Web Developer" ───┐
│ 🔍 search            │   │                                               │
│                      │   │  1  ▸ CS-000 How the IT Industry Works    ⠿ ✕ │
│ CS-000 How IT Works  │   │  2  ▸ CS-OS-101 Operating Systems         ⠿ ✕ │
│ CS-OS-101 OS         │   │  3  ▸ CS-NET-101 Computer Networks        ⠿ ✕ │
│ CS-NET-101 Networks  │──▶│  4  ▸ CS-DB-101 DBMS                      ⠿ ✕ │
│ CS-DB-101 DBMS       │   │  5  ▸ LANG-JAVA-101 Java from Zero        ⠿ ✕ │
│ CS-OOP-101 OOP       │   │  6  ▪ [video] Why your branch doesn't     ⠿ ✕ │
│ LANG-JAVA-101 Java   │   │        matter                                 │
│ …                    │   │  7  ▸ WEB-101 Web Technologies            ⠿ ✕ │
│                      │   │  …                                            │
│ [+ standalone item]  │   │                          [ Save ]  [ Publish ]│
└──────────────────────┘   └───────────────────────────────────────────────┘
```

Drag from library → path. Reorder by drag. Inline rename. Mixed courses and
standalone items. Already built for course→module→lesson; the path-level builder
exists too (`TODO.md` §3, "Visual learning-path builder").

## Targeting rules — the piece that closes the loop

For onboarding to *select* a path automatically, each path needs targeting
metadata, editable in the console:

| Rule field | Example |
|---|---|
| `level` | 1, 2 |
| `branch` | non-CS |
| `goal` | first job |
| `target_role` | web developer, any |
| `language` | java |
| `track` | web |
| `priority` | 10 (higher wins when several match) |

Matching: score each published path against the user's onboarding answers, take
the highest scorer, fall back to the **Default Path** if nothing matches. Every
path must be reachable; the default must always exist.

## Dummy / seed content

The platform ships with a **complete dummy curriculum**, start to end, so the
whole experience is demonstrable before real content is written:

> teaching computer science → teaching a programming language → databases →
> web development → projects 1–4 → resume → job search → AI for job search →
> HR interview → technical interview → offer & first switch

Seed data lives in [../markdown/](../markdown/) and is loaded with
`wp jsl seed --fresh` (already exists for a smaller set; extend it to cover the
full curriculum in this folder).

## Admin capabilities checklist

- [x] Create/edit/publish courses, lessons, quizzes
- [x] Visual course builder (modules + lessons, drag-reorder)
- [x] Visual path builder (courses + standalone items)
- [x] Course codes and categories
- [x] Analytics — learners, enrollments, completions, per-course performance
- [ ] Path targeting rules bound to onboarding answers
- [ ] Onboarding wizard itself (steps 1–8) and its user-meta storage
- [ ] Full seed curriculum matching [../curriculum/](../curriculum/)
- [ ] Path preview "as persona X" — see what a given user would be assigned
