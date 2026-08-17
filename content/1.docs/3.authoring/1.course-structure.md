---
title: Course structure
description: How a folder of markdown on disk becomes a course, a syllabus and a player. The tree is the curriculum.
---

There is no database and no admin screen. A course is a **folder**, a module is a
**folder inside it**, and a lesson is a **markdown file**. Reordering a module is
renaming a directory; publishing a lesson is committing a file.

## The shape

```bash
content/2.courses/
└── 0.how-the-industry-works/     # the course — a folder
    ├── .navigation.yml           # title + icon for the sidebar
    ├── index.md                  # the syllabus page at /courses/how-the-industry-works
    ├── 1.what-is-software/       # a module — a folder
    │   ├── .navigation.yml
    │   ├── 1.what-is-software.md # a lesson — a file
    │   └── 2.how-a-program-runs.md
    └── 2.inside-a-company/
        ├── .navigation.yml
        └── 1.the-teams.md
```

That produces:

| File | URL |
| --- | --- |
| `0.how-the-industry-works/index.md` | `/courses/how-the-industry-works` |
| `1.what-is-software/1.what-is-software.md` | `/courses/how-the-industry-works/what-is-software/what-is-software` |

The numeric prefixes control **order only** — they are stripped from the URL. Put
a new lesson between two others by renumbering, and nothing else has to change.

## The course index

`index.md` at the root of a course folder is its syllabus page. Its front matter
carries everything the catalogue and the player need:

```yaml
---
title: How the IT Industry Works
description: What software is, what the industry is, and the vocabulary…
code: CS-000
duration: 2 weeks
level: orientation
icon: i-lucide-building-2
outcomes:
  - Name every team in a software company and what it does all day
  - Explain the difference between a product and a service company
prerequisites: []
---
```

| Field | Meaning |
| --- | --- |
| `code` | Course code, shown as a badge |
| `duration` | Human-readable, e.g. `3 weeks` |
| `level` | `orientation` · `foundation` · `applied` · `job-search` |
| `icon` | Any Iconify name, e.g. `i-lucide-database` |
| `outcomes` | What you can do afterwards — written as verbs |
| `prerequisites` | Course slugs that should come first |

## A lesson

```yaml
---
title: What is software, really
description: Instructions, hardware, and everything in between.
minutes: 12
kind: lesson
---
```

`minutes` drives the reading estimate and the module time totals. `kind` is one of
`lesson`, `reading`, `practice`, `project` or `quiz`, and changes the icon in the
player. `draft: true` keeps a lesson out of the navigation until it is ready.

## A module

`.navigation.yml` inside a module folder names it:

```yaml
title: Inside an IT company
icon: i-lucide-users
```

::callout{icon="i-lucide-git-branch" color="primary"}
Everything above is plain text in git. A lesson edited in the browser and a
lesson edited in a pull request land in exactly the same file.
::
