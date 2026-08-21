---
title: "Data structures and algorithms: how to think about work"
description: Arrays, lists, stacks, queues, trees, hash tables and graphs, and the vocabulary for saying how expensive something is. Taught as ideas first, in plain English and pictures, before any language is in the way.
code: JSG-05
duration: 6 weeks
stage: foundation
icon: i-lucide-binary
outcomes:
  - Name the common data structures and say what each one is good and bad at
  - Read and write big-O notation without treating it as an incantation
  - Choose a structure for a problem and defend the choice out loud
  - Describe searching, sorting and traversal as steps before writing them as code
  - Walk an interviewer through your thinking instead of freezing
prerequisites:
  - dbms
---

An index turns ten million rows into eleven, and the reason is not a database
trick. It is the way the data was arranged before anybody searched it. That
idea, that arrangement decides cost, is the whole of this subject, and it is the
last one you meet before you start writing programs.

## Why this comes before a language, not after

Most courses teach a language and then bolt algorithms on afterwards, which is
why so many people can write a loop and cannot say what it costs. Taken first,
in plain English and pictures, these are just ideas about arrangement: a queue
is a queue, a tree is a tree, and neither needs a semicolon to be understood.
Then Java arrives and you are implementing something you already have a picture
of, rather than meeting two hard things at once.

::caution
This is the longest track in this section and it is the one people give up on.
Six weeks is honest. Nothing here is beyond you, but it is the first subject in
the course where thinking, rather than typing, is the whole activity, and that
feels like being stuck when it is actually working.
::

::callout{icon="i-lucide-arrow-right"}
You can now describe, precisely, the steps a machine should take. You still have
no way of making one take them.
::
