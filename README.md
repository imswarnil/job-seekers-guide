# Job Seekers Guide

A structured-learning platform for people trying to get their first job in
software. The thinking behind it — the problem, the personas, the curriculum and
the product flow — lives in [`abstract/`](./abstract/).

Built with **Nuxt 4**, **Nuxt UI** and **Nuxt Content**. Static: no database, no
server-side state, no account required. Every lesson and page is markdown in this
repository.

There is exactly one exception to "static", and it is documented rather than
hidden: [`workers/runner/`](./workers/runner/) is a Cloudflare Worker that
compiles and runs Java for the `::runner{lang="java"}` component, because `javac`
does not run in a browser. Everything else on the site works with the origin
offline.

## Stack

| Layer | Choice |
| --- | --- |
| Framework | Nuxt 4 |
| UI | Nuxt UI 4 (Tailwind CSS v4, Reka UI) |
| Content | Nuxt Content 3 — file-based, SQLite at build time |
| Icons | Nuxt Icon with Lucide + Simple Icons |
| Images | Nuxt Image |
| SEO | @nuxtjs/sitemap, @nuxtjs/robots, nuxt-schema-org, nuxt-seo-utils |
| Social images | nuxt-og-image (zero-runtime, rendered at build) |
| Progress | `localStorage`, in the browser only |
| Java runner | Cloudflare Worker in front of Piston |

Auth and persistence are deliberately absent. `app/pages/login.vue` and
`signup.vue` are unwired scaffolding for the day progress needs to sync across
devices.

## One path, at the root

There is no course catalogue and no documentation section — there is one path,
and it lives at the root of the site.

```
/                            the landing page
/start                       every subject, in order
/java                        a subject
/java/collections            a module
/java/collections/generics   a lesson
```

A subject is a folder, a module is a folder inside it, and a lesson is a markdown
file. The numeric prefixes are the **only** place the order of the curriculum is
written down, so reordering it is a `git mv` and there is no manifest to drift.

```
content/1.path/0.how-the-industry-works/
├── .navigation.yml            # title + icon
├── index.md                   # → /how-the-industry-works
└── 1.what-is-software/
    ├── .navigation.yml
    └── 1.what-is-software.md  # → /how-the-industry-works/what-is-software/what-is-software
```

The collection sets `prefix: '/'`, which is what strips `1.path` from the URL
while keeping the curriculum in one directory.

Because subjects sit at the root, a subject named after an existing page would be
shadowed by it and silently never render.
[`modules/reserved-slugs.ts`](./modules/reserved-slugs.ts) fails the build
instead of letting that happen.

## The player, not a doc page

`prev` and `next` index into **every lesson on the platform**, not into the
current subject. The last lesson of Operating Systems goes forward into
Databases, and the first lesson of a subject goes back into the one before it.
That single decision — `app/composables/usePath.ts` — is what makes it a path
rather than a catalogue.

The rail renders server-side, which is not cosmetic: `crawlLinks` only finds
links that are in the HTML, so the rail is how every module and lesson gets
prerendered. Only completion ticks and progress bars are client-only.

## Writing lessons

Every teaching component is markdown. The full list, with examples, is in
[`.studio/components.md`](./.studio/components.md) — `::flow`, `::code-trace`,
`::memory`, `::compare`, `::pros-cons`, `::real-life`, `::persona`,
`::feature-list`, `::timeline`, `::ai-prompt`, `::youtube`, `::runner`, `::ad`.

Two rules they all follow:

- **They work without JavaScript.** The reveal animation is added by script on
  mount; the finished diagram is what is in the HTML. A code walkthrough degrades
  to the code plus a numbered caption list. A runner degrades to a highlighted
  code block.
- **They share one timing scale.** `app/assets/css/diagram.css` defines it, and a
  single `prefers-reduced-motion` block there sets every duration to zero. No
  component branches on reduced motion itself.

House style for lessons is in [`.studio/lesson-style.md`](./.studio/lesson-style.md).

A live example of every component: `/how-the-industry-works/the-vocabulary/the-teaching-components`.

## The code runner

`::runner` supports `html`, `css`, `javascript`, `python`, `sql` and `java`.
Nothing downloads on a lesson without one, and nothing downloads until Run is
pressed.

| Language | Where it runs | Cost |
| --- | --- | --- |
| html, css, javascript | Sandboxed iframe, opaque origin | ~0 |
| python | Pyodide, from a CDN on first Run | ~12 MB, cached |
| sql | sql.js, from a CDN on first Run | ~1.5 MB, cached |
| java | Cloudflare Worker → Piston | a network round trip |

Pyodide and sql.js are deliberately **not** npm dependencies — they are loaded by
URL so they stay out of the dependency graph, the build and the prerender.
Adapters are reached through literal dynamic imports in
`app/composables/useRunner.ts`; a template literal there would merge them into
one chunk and make a JavaScript lesson pay for SQLite.

## Repository layout

```
.
├── app/
│   ├── assets/css/            # main.css (design language) + diagram.css (motion)
│   ├── components/
│   │   ├── content/           # the MDC teaching components
│   │   └── player/            # rail, footer bar, progress, up-next
│   ├── composables/           # usePath, useProgress, useRunner, usePageSeo
│   ├── layouts/               # default, auth
│   ├── pages/                 # index, path, about, faq, changelog, [...slug]
│   └── utils/
│       ├── path.ts            # folder tree → subject / module / lesson
│       ├── runners/           # one adapter per language
│       └── ads.ts             # every ad placement on the site, in one file
├── content/
│   ├── 0.index.yml            # the landing page, as data
│   ├── 1.path/                # subject → module → lesson.md
│   ├── 2.about.md  3.faq.md
│   └── 4.changelog/
├── modules/reserved-slugs.ts  # build guard for root-level subject URLs
├── workers/runner/            # the Java runner (the one server)
├── .studio/                   # authoring context for Nuxt Studio
├── abstract/                  # product thinking: problem, personas, curriculum
└── content.config.ts          # collections and front-matter schemas
```

## Ads

Off. `app/utils/ads.ts` declares every placement and `ads.enabled` in
`app/app.config.ts` is the master switch — in app config rather than nuxt config
so it can be toggled from Nuxt Studio without a deploy. Every slot reserves its
box before anything loads, so enabling them cannot shift layout.

The parallax slot is built and shipped disabled on purpose. Prove it against
Lighthouse on `/start` before turning it on anywhere near a lesson.

## Design language

Documented in the comments of
[`app/assets/css/main.css`](./app/assets/css/main.css):

- **Indigo** carries everything structural — navigation, progress, primary actions.
- **Teal** is the accent, chosen over the amber every other course platform uses.
- **Amber** survives for one job only: streaks and milestones. Rationed on purpose.
- **Neutrals** are tinted toward the indigo hue so greys never look bolted on.
- Type is sized for long-form reading first. Lesson prose is the product.

The logo is the same geometry everywhere — `app/utils/logo.ts` — animated with
CSS in the browser and drawn as a still frame by the OG image renderer, which has
no stylesheet.

## Development

```bash
pnpm install
pnpm dev          # http://localhost:3000
pnpm generate     # prerenders every page to .output/public
pnpm lint
pnpm typecheck
```

Editing content in the browser is supported through
[Nuxt Studio](https://nuxt.studio) — it writes to the same markdown files, so a
lesson edited in an editor and a lesson edited in a pull request are the same
change. The files in `.studio/` are the context it reads.

Old `/courses/**` and `/docs/**` URLs still resolve. The fixed ones are route
rules; the per-lesson rewrite needs a splat and lives in
[`public/_redirects`](./public/_redirects), which Cloudflare honours. Testing
those needs `wrangler pages dev .output/public` — `pnpm preview` will not.

## Licence

Private project — all rights reserved unless stated otherwise.
