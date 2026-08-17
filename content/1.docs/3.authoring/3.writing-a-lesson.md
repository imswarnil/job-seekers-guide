---
title: Writing a lesson
description: The house style. Why before what, one running example, permission to skip, and the interview question at the end.
---

## The five rules

::steps{level="3"}

### Why before what

Open with where this shows up in a real job. Not "a semaphore is a synchronisation
primitive" — "two requests updated the same row and one of the updates vanished;
here is the thing that stops it."

### One running example, everywhere

The whole curriculum shares a single example: a university admissions system. The
table introduced in DBMS is the same table used in the Java course and the same
table queried in the web module. A learner should never have to learn a new
fictional domain to learn a new subject.

### Say what to skip

If part of the topic is not load-bearing for a first job, say so in the lesson,
in plain words, and say what is lost by skipping it. This is the product.

### Show the smallest thing that runs

Every code block should be runnable as written. No `...` in the middle, no
imports left to the imagination.

### End with the interview question

Close with the question this lesson answers in an interview, and a two-sentence
answer. That is what converts study into an offer.

::

## Length and shape

| | |
| --- | --- |
| Target length | 800–1,500 words |
| Reading time | 8–15 minutes — set `minutes` honestly |
| Headings | Every 200–300 words. People skim before they read. |
| Code blocks | At least one, unless the lesson is genuinely conceptual |

## Tone

Honest, structured, warm, anti-hype. Somebody may be opening this after their
thirty-third rejection. The writing should read as *somebody has thought this
through and is on your side* — not as a sales page and not as a gamified toy.

::warning{icon="i-lucide-ban"}
Never write "easy", "simple", "just", or "obviously". If it were obvious the
reader would not be here, and the word only tells them they are stupid.
::

## Before you commit

- [ ] The first paragraph says why this matters in a real job
- [ ] Every code block runs as written
- [ ] Anything skippable is marked skippable
- [ ] `minutes` is a real estimate, not a guess
- [ ] The interview question is at the end
