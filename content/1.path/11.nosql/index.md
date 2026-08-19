---
title: NoSQL, and the other shape of data
description: You know relational databases deeply, which is exactly the right moment to meet the alternative — because now you can judge it instead of following a trend.
code: JSG-11
duration: 1 week
stage: applied
icon: i-simple-icons-mongodb
outcomes:
  - Name the four families of NoSQL and the problem each was invented for
  - Model the same applicant as rows and as a document, and argue for one
  - Say when a document database genuinely wins, and when it quietly costs you
  - Answer "SQL or NoSQL?" without repeating a blog post
prerequisites:
  - nextjs
---

Before the university's data goes onto the internet, there is one honest detour:
the other shape data comes in, and why half the industry spent a decade arguing
about it.

## Why this track is short, and why it is here

Because you already know the relational model properly. Meeting NoSQL now means
meeting it as a set of trade-offs rather than as a trend — which is exactly the
position an interviewer is testing for when they ask.

::compare{caption="The same question, asked of two different candidates."}
  :::compare-side{label="Learned NoSQL first" verdict="wrong"}
  "It's faster and it scales." Cannot say what it is faster at, or what is given
  up to get there.
  :::
  :::compare-side{label="Learned the relational model first" verdict="right"}
  "It removes the join at write time by duplicating data. That is a win when you
  read one shape constantly and a problem the first time the duplicated field has
  to change."
  :::
::

## What this track covers

::flow{numbered direction="vertical"}
  :::flow-step{label="What NoSQL means" icon="i-lucide-boxes"}
  Document, key-value, wide-column and graph — four unrelated things sharing a
  bad name.
  :::
  :::flow-step{label="Documents versus rows" icon="i-lucide-file-json" highlight}
  The same applicant modelled both ways, embedding versus referencing, and what
  each one costs.
  :::
  :::flow-step{label="Choosing, honestly" icon="i-lucide-scale"}
  Where the University Management App would use one, where it would be a mistake, and how to answer
  the interview question without picking a side you cannot defend.
  :::
::

## What the University Management App becomes

Unchanged, deliberately. This track ends with a written decision — which parts of
the system would benefit from a document store and which absolutely would not —
and that written decision is worth more in an interview than a migration.

::callout{icon="i-lucide-arrow-right"}
The choice is made and the data still sits on your laptop. Time to put it
somewhere the internet can reach, with real logins and permissions the database
itself enforces.
::
