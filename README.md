# Job Seekers Guide

A structured-learning platform for people trying to get their first job in
software. The thinking behind it — the problem, the personas, the curriculum and
the product flow — lives in [`abstract/`](./abstract/).

Built with **Nuxt 4**, **Nuxt UI** and **Nuxt Content**. Fully front-end: no
database, no server-side state, no account required. Every course, lesson and
page is markdown in this repository.

## Stack

| Layer | Choice |
| --- | --- |
| Framework | Nuxt 4 |
| UI | Nuxt UI 4 (Tailwind CSS v4, Reka UI) |
| Content | Nuxt Content 3 — file-based, SQLite at build time |
| Icons | Nuxt Icon with Lucide + Simple Icons |
| Fonts | Nuxt Fonts — Plus Jakarta Sans, Inter, JetBrains Mono |
| Images | Nuxt Image |
| Social images | nuxt-og-image |
| Progress | `localStorage`, in the browser only |

Auth and persistence are deliberately absent. `app/pages/login.vue` and
`signup.vue` are unwired scaffolding for the day progress needs to sync across
devices — Supabase is the intended candidate.

## Repository layout

```
.
├── app/
│   ├── assets/css/main.css   # the design language, as Tailwind v4 tokens
│   ├── components/           # AppHeader, course player pieces, OG image
│   ├── composables/          # useCourses, useCourseProgress
│   ├── layouts/              # default, docs, course (the player), auth
│   ├── pages/                # index, courses, docs, blog, changelog
│   └── utils/courses.ts      # folder tree → course / module / lesson
├── content/
│   ├── 0.index.yml           # the landing page, as data
│   ├── 1.docs/               # documentation, nested folders
│   ├── 2.courses/            # course folder → module folder → lesson.md
│   ├── 3.blog/
│   └── 4.changelog/
├── abstract/                 # product thinking: problem, personas, curriculum
└── content.config.ts         # collections and front-matter schemas
```

## Content is folders

A course is a folder, a module is a folder inside it, and a lesson is a markdown
file. The tree on disk *is* the curriculum — reordering a module is renaming a
directory, and the numeric prefixes control order only, never the URL.

```
content/2.courses/0.how-the-industry-works/
├── .navigation.yml            # sidebar title + icon
├── index.md                   # /courses/how-the-industry-works
└── 1.what-is-software/
    ├── .navigation.yml
    └── 1.what-is-software.md  # /courses/how-the-industry-works/what-is-software/what-is-software
```

The documentation sidebar and the course player read the same navigation tree,
so a syllabus cannot drift out of sync with the lessons it lists.

Full authoring guide: [`/docs/authoring`](./content/1.docs/3.authoring/), and the
schemas are in [`content.config.ts`](./content.config.ts).

## Design language

Documented in the comments of
[`app/assets/css/main.css`](./app/assets/css/main.css), and unchanged from the
previous incarnation of this platform:

- **Indigo** carries everything structural — navigation, progress, primary actions.
- **Teal** is the accent, chosen over the amber every other course platform uses.
- **Amber** survives for one job only: streaks and milestones. Rationed on purpose.
- **Neutrals** are tinted toward the indigo hue so greys never look bolted on.
- Type is sized for long-form reading first. Lesson prose is the product.

## Development

```bash
pnpm install
pnpm dev          # http://localhost:3000
pnpm build        # prerenders every page
pnpm lint         # eslint
pnpm typecheck    # vue-tsc
```

Editing content in the browser is supported through
[Nuxt Studio](https://nuxt.studio) — it writes to the same markdown files, so a
lesson edited in an editor and a lesson edited in a pull request are the same
change.

## Licence

Private project — all rights reserved unless stated otherwise.
