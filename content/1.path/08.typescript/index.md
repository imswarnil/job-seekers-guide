---
title: TypeScript — stop shipping preventable bugs
description: JavaScript will let you pass a string where a number belongs and find out in production. TypeScript catches it while you type — and nearly every serious job posting for the web now expects it.
code: JSG-08
duration: 2 weeks
stage: applied
icon: i-simple-icons-typescript
outcomes:
  - Describe the shape of your data and let the compiler hold you to it
  - Read a type error and know which of the two sides is actually wrong
  - Type the UniversityOS data models once and use them everywhere
  - Say honestly what TypeScript costs as well as what it gives
prerequisites:
  - toolchain
---

`applicant.programme` instead of `applicant.program`. It ran, it rendered
`undefined`, and nobody noticed for a week. A compiler would have refused before
you saved the file.

## Why this is a homecoming

You spent sixteen weeks in Java, where every value has a declared type and the
compiler argues with you. TypeScript is that argument, added back to a language
that gave it up — and it is the same instinct you already built.

::pros-cons
---
title: TypeScript on a project like this one
pros:
  - Catches a whole class of bug before the code ever runs
  - Editors get genuinely useful: real autocomplete, real rename, real navigation
  - Types are documentation that cannot go stale
cons:
  - A build step, and friction on the days you want to move fast
  - Somebody will eventually write `any` and undo all of it
  - Type errors in library code can be brutal to read at first
---
::

## What this track covers

::flow{numbered direction="vertical"}
  :::flow-step{label="Why types, and the basics" icon="i-lucide-shield-check"}
  The bug JavaScript cannot catch, installing it, annotating variables and
  functions, and letting inference do most of the work.
  :::
  :::flow-step{label="Describing shapes" icon="i-lucide-shapes" highlight}
  Interfaces and type aliases for `Applicant`, `Program` and `FeePayment`; optional
  properties; unions; and narrowing.
  :::
  :::flow-step{label="Generics and the real project" icon="i-lucide-braces"}
  Generic functions and types, typing the fetch layer, and converting the
  UniversityOS front end file by file.
  :::
::

## What UniversityOS becomes

Typed. The data models written once, shared between the pages and the fetch
layer, and a build that refuses to complete when the shapes disagree.

::callout{icon="i-lucide-arrow-right"}
Your code is safe and your interface is still one enormous file, rebuilding the
whole student list every time a single row changes.
::
