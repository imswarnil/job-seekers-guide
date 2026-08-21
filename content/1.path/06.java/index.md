---
title: "Java: learn to program"
description: You learn to program once. Everything after this is a dialect. Java is strict enough to show you what is really happening, and it is what a very large number of entry-level jobs still run on.
code: JSG-06
duration: 16 weeks
stage: language
icon: i-simple-icons-openjdk
outcomes:
  - Write, compile and run a real program from the terminal, with no IDE hiding the steps
  - Reason about logic in plain English before writing a line of code
  - Explain what your program is doing to memory while it runs
  - Model a problem with objects, and defend the design in an interview
  - Build the console engine room of the University Management App, with tests that catch you breaking it
prerequisites:
  - data-structures
---

You can describe, precisely, the steps a machine should take, and you have no
way of making one take them. That is the gap this track closes, and it closes
it for good, because you learn to program exactly once.

## Why this track, and why it is this long

A person who learned React first can build a page. A person who learned to
program first can build anything. This course is written for the second person,
and this is the track where that happens.

::pros-cons
---
title: Java as the language you learn properly
pros:
  - The most entry-level roles in this market, by a wide margin
  - Verbose enough that the machine model stays visible while you learn it
  - Strict enough to refuse the mistakes a looser language lets you ship
  - The habits it teaches are the ones the rest of the industry assumes you have
cons:
  - Slower to a first running program than Python
  - The tooling is genuinely unpleasant for the first fortnight
  - Nobody writes a weekend script in it
---
::

::warning{icon="i-lucide-alert-triangle"}
This is the longest track in the course, and the objects chapter is the hardest
conceptual jump anywhere in it. It took me two weeks and I nearly stopped. That
is normal, it is not a verdict on you, and the chapter opens by saying so.
::

## The chapters

::flow{numbered direction="vertical" caption="Fourteen chapters. Each one exists because the one before it hit a wall."}
  :::flow-step{label="Before you write a line of Java" icon="i-lucide-map"}
  What a program is, what a language is, how code becomes something that runs,
  and what the JVM actually is. Compiled and run by hand, before an editor hides
  any of it.
  :::
  :::flow-step{label="Your first words, then text" icon="i-lucide-type"}
  Variables, the eight primitive types, the stack and the heap, operators,
  casting, input. Then strings, and why comparing two identical names with `==`
  says they are different.
  :::
  :::flow-step{label="Decisions, loops and many values" icon="i-lucide-repeat"}
  Conditionals, switch, loops, the off-by-one error, arrays, searching and
  sorting by hand, and your first real debugging session.
  :::
  :::flow-step{label="Methods, recursion and objects" icon="i-lucide-boxes" highlight}
  Splitting code into named pieces, a method that calls itself, and then the big
  turn: classes, constructors, encapsulation, inheritance, polymorphism.
  :::
  :::flow-step{label="Contracts, memory and failure" icon="i-lucide-shield-alert"}
  Abstract classes and interfaces, enums and records, garbage collection,
  exceptions, and reading a stack trace without panic.
  :::
  :::flow-step{label="Generics, collections and streams" icon="i-lucide-layers"}
  Where most coding-round questions actually live: lists, sets, maps, how a hash
  map works inside, sorting objects, lambdas and stream pipelines.
  :::
  :::flow-step{label="Files, tests and the professional finish" icon="i-lucide-check-check"}
  Persistence with no database in sight, JUnit 5, design patterns, clean code,
  what the JVM has been doing all along, and an honest chapter on concurrency.
  :::
::

## What the University Management App becomes

A console tool that turns one applicant's marks into a grade, then a menu-driven
marks manager, then a genuinely object-oriented admissions system with search,
filtering, multi-field sorting, duplicate detection, file persistence and tests
around the shortlisting rules.

::real-life{title="Why the console tool is not a toy" source="Every back office you have ever queued in"}
Batch jobs that grade, shortlist, reconcile and export are running right now
inside universities, banks and insurers, on exactly this shape of code, with no
web interface anywhere near them. The engine room is real work. It is also the
part of your project an interviewer can read in five minutes.
::

::callout{icon="i-lucide-arrow-right"}
By the end of it your program is genuinely good. And everything it knows still
disappears into a text file the moment you close it.
::
