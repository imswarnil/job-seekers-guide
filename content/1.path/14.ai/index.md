---
title: AI — use it, then build with it
description: You can now program, model data, query it, build an interface, secure a backend and ship it. That is exactly the foundation you need to build AI features that are useful instead of dangerous.
code: JSG-14
duration: 4 weeks
stage: applied
icon: i-lucide-sparkles
outcomes:
  - Explain what a model, a token, a context window and an embedding actually are
  - Build a feature that turns a plain-English question into a query and shows its working
  - Put a human in the loop everywhere a mistake would matter
  - Say plainly where these systems fail, and design around it
prerequisites:
  - hosting
---

It is live, and it is completely literal. A dean should not have to write SQL to
find out how this intake is going — and now you know enough to build the thing
that answers them without handing the university's records to a stranger.

## Why this track is last

Because the value moved from typing code to knowing whether the code is right,
and you cannot supervise what you do not understand. Every feature here is only
buildable because you already know SQL, schemas, permissions and row-level
security. That is not an accident of ordering; it is the argument of the whole
course.

::warning{icon="i-lucide-alert-triangle"}
Three rules ship with every feature in this track, and they are part of the
build, not an essay at the end: the model never writes to the database
unattended; the human always sees the source — the query, the fields, the
factors; and student data is handled with real access control and a clear note on
what is sent where.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="Understanding the thing" icon="i-lucide-brain"}
  What these systems actually are in plain words, why they hallucinate, prompting
  as a skill, embeddings and meaning-based search, and the honest limits.
  :::
  :::flow-step{label="The features" icon="i-lucide-wand-sparkles" highlight}
  The staff assistant that turns a question into SQL and shows the query it ran;
  smart search over applicants; application summaries linked to every source
  field; the at-risk signal with its contributing factors visible; a draft-reply
  helper; and document extraction with a confirmation step.
  :::
::

## What the University Management App becomes

The system a university in 2027 would actually run: a dean types "how many CSE
applicants scored above 80 this intake?" and gets a number, the query that
produced it, and a link to the rows.

::callout{icon="i-lucide-arrow-right"}
It is built, it is live, and it is smart. Now somebody who has never seen it is
going to ask you to explain it in two minutes, and then decide whether to pay
you.
::
