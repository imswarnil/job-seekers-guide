---
title: "DBMS: how data is really stored"
description: "The theory before the language. Normalisation, keys, transactions, isolation and indexes: the reasons SQL looks the way it does, learned before you write a line of it."
code: JSG-04
duration: 2 weeks
stage: foundation
icon: i-lucide-database
outcomes:
  - Design a set of tables that does not store the same fact twice
  - Explain primary, foreign and candidate keys, and what each one prevents
  - Say what a transaction guarantees, and what it does not
  - Describe how an index works well enough to know when it will not help
  - Answer the database-theory round without reciting normal forms you cannot apply
prerequisites:
  - computer-networks
---

Data arrives constantly and nothing has said where it is kept. A file works
until two people write to it at once, or until somebody asks a question that
needs half of it. Then you need the thing that was designed for exactly
this, and the ideas it was built on.

## Why this is separate from the SQL track

Because they are two different skills and mixing them is why people end up
writing queries they cannot defend. This track is the theory: what a relation
is, why a fact should live in exactly one place, what happens when two updates
collide, and how a lookup that should read ten million rows reads eleven
instead. The SQL track, later, is the language you say all of it in.

Learning the theory first means SQL arrives as something obvious rather than as
syntax to memorise. It also means the design questions ("how would you model
this?") stop being frightening, because they are this subject and not that one.

::callout{icon="i-lucide-arrow-right"}
An index turns ten million rows into eleven. Ask how, and you are no longer
asking about databases. You are asking how data is arranged so it can be
searched at all.
::
