---
title: React — build interfaces out of pieces
description: Hand-writing DOM updates does not scale past one screen. React is how most of the industry builds interfaces, and it is the framework most job postings name.
code: JSG-14
duration: 6 weeks
stage: applied
icon: i-simple-icons-react
outcomes:
  - Break an interface into components without over-engineering it
  - Explain what state is, when it updates, and what re-renders as a result
  - Lift state, pass data down and send events up, deliberately
  - Rebuild the University Management App portals and the admissions board out of pieces
prerequisites:
  - typescript
---

One file, rebuilding the entire student list because one row changed. There is a
way to describe what the screen should look like and let something else work out
the difference.

## Why this track exists

Because the industry settled on it, and because the idea underneath it — describe
the result, not the steps — is worth having even if you never write another line
of React.

::warning{icon="i-lucide-alert-triangle"}
The first two lessons feel like a step backwards. Everyone finds the component
mental model strange until it clicks, usually somewhere in the second week, and
then nothing else feels sane. If you are annoyed, you are on schedule.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="Thinking in components" icon="i-lucide-component"}
  Why components exist, JSX, props, rendering lists and keys, conditional
  rendering, state, events, and forms.
  :::
  :::flow-step{label="Effects, structure and the real app" icon="i-lucide-refresh-cw" highlight}
  Lifting state, effects and when you do not need one, fetching data, custom
  hooks, context, and the shape of a real application.
  :::
::

## What the University Management App becomes

Component-based portals and the admissions CRM board: an applicant card written
once and used everywhere, a pipeline you can move a candidate through, and a
dashboard assembled from pieces rather than pasted together.

::callout{icon="i-lucide-arrow-right"}
It is a real interface now — and routing, server rendering, data loading and the
build are all still yours to hand-roll.
::
