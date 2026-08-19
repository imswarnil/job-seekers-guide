---
title: Next.js — the production framework
description: React gives you components. A real product also needs routing, server rendering, an API, data fetching that does not waterfall, and a build that deploys.
code: JSG-10
duration: 6 weeks
stage: applied
icon: i-simple-icons-nextdotjs
outcomes:
  - Explain what runs on the server and what runs in the browser, and why it matters
  - Build routes, nested layouts and dynamic segments from folders
  - Load and mutate data without hand-rolling an API for every form
  - Assemble the real UniversityOS application shell
prerequisites:
  - react
---

Routing, rendering, data loading and the build were all yours to hand-roll. This
is the track where you stop hand-rolling them, and understand exactly what you
handed over.

## Why this track exists

"React developer" in a job posting usually means this. It is also where the
single most-asked modern front-end question lives: what is the difference between
a server component and a client one, and how do you know which you are writing?

::real-life{title="The bug that only happened for real users" source="Any team's first month on the app router"}
A component read `window.localStorage` to remember a filter. It worked on every
developer's machine, because they always arrived by clicking a link. The first
user to open the page directly got a server render, `window` did not exist, and
the whole route five-hundredted. The fix is one line. Knowing which side your
code is on is the entire skill.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="Rendering and routing" icon="i-lucide-route"}
  Why a framework at all, file-based routing, layouts, dynamic segments, and
  server components versus client components with the boundary drawn.
  :::
  :::flow-step{label="Writing, layout and the app shell" icon="i-lucide-layout-dashboard" highlight}
  Data fetching, server actions, forms that work without JavaScript, loading and
  error states, metadata, images, and the UniversityOS shell.
  :::
::

## What UniversityOS becomes

An application: the public site, the CRM, the three portals and the dashboards,
all inside one routed, server-rendered project with real navigation and real
forms.

::callout{icon="i-lucide-arrow-right"}
It is a proper application, and every row of data it shows still lives in a
database on your laptop, which is switched off right now.
::
