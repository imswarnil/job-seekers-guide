---
title: Replace with the debugging title
description: One sentence for a stranger. Name the broken thing.
minutes: 15
kind: practice
---

<!--
  Kind: practice, spec tag [D]. Debugging. Show the broken thing and the REAL
  error, then walk through FINDING it: read the message, shrink the problem,
  print, then reach for the real tool (debugger, dev tools, the query plan,
  the network tab). Never hand over the fix before the hunt.
  Beat 1 goes right here, with no heading: resolve the previous cliffhanger in
  one or two sentences, then set the scene for what is broken today.
  Reminder: the em dash is banned in prose. Full stop, comma, colon or brackets.
-->

## Why this matters in a job

<!--
  Most professional hours are spent on code that already exists and does not
  work. Say so, with a number where you have one. A ::real-life block about a
  production bug fits here.
-->

## The broken thing

<!--
  Show the failing code or query, in the university world, and the REAL error
  text or the wrong output, exactly as the machine prints it. If it is
  silently wrong (runs, but the number is off), show the wrong number and say
  what the right one should have been. ::compare with verdict="wrong" /
  verdict="right" comes later, after the hunt, not here.
-->

## How to find it

<!--
  The method, in order, out loud:
  1. Read the message, all of it, and say what each part means.
  2. Shrink the problem until the failure is small enough to stare at.
  3. Print what you believe, and let the machine disagree.
  4. Only then reach for the real tool.
  ::code-trace to walk the failing lines, ::flow for the method itself.
  Name the trap: what people assume that sends them hunting in the wrong place.
-->

## Your turn

<!--
  Now the reader hunts. One or two more broken things, hints only. The fixes
  live in an ::accordion, never in the open, and each one explains why the bug
  happened, not only what to change.
-->

::accordion
  :::accordion-item{label="The fix, and why it broke"}
  <!-- The cause, then the fix, then how to have found it faster. -->
  :::
::

## Check yourself

<!--
  Exactly two questions. One is the kind an interviewer actually asks, and for
  debugging that is usually "here is an error, what do you do first?".
-->

## The interview question this answers

<!--
  Debugging questions in interviews test method, not memory. Phrase it the way
  it is really asked, then the shape of a good answer, in an ::accordion.
-->

## Next

<!--
  The cliffhanger, last thing on the page. Replace the example with the mapped
  hook from content-plan.md.
-->

::callout{icon="i-lucide-arrow-right"}
You found the bug by printing five variables and reading the output like tea
leaves. There is a tool that pauses the program mid-run and shows you every
variable at once. Time to meet it.
::
