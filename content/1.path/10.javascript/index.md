---
title: JavaScript — make it react
description: HTML is the body, CSS is the face, JavaScript is the nervous system. You already know how to program, so this track moves fast on the basics and slow on the two things that genuinely bite.
code: JSG-10
duration: 8 weeks
stage: web
icon: i-simple-icons-javascript
outcomes:
  - Read and write modern JavaScript without guessing at what `this` refers to
  - Change a live page in response to what somebody does
  - Fetch data over the network and handle it going wrong
  - Explain the event loop well enough to debug something asynchronous
prerequisites:
  - css
---

The page looks like a product and clicking anything does nothing. Nothing is
listening. That is the job of the third language the browser speaks.

## Why this track exists

React, Next.js and everything after them are JavaScript with extra rules. Learn
the language properly here and the frameworks stop being magic; skip it and you
will spend two years pattern-matching other people's React and never quite
knowing why something re-ran.

You already know how to program from the Java track, so variables and loops go
quickly. Two things get the time they deserve, because they are what actually
catch people out.

::feature-list{columns="2"}
  :::feature{icon="i-lucide-mouse-pointer-click" title="The DOM"}
  The page is a tree of objects your code can reach into. Events travel through
  that tree, and understanding how is the difference between one listener and
  fifty.
  :::
  :::feature{icon="i-lucide-clock" title="Asynchronous code"}
  The single most common interview trap in the language: a function that returns a
  Promise instead of a value, and a loop that finishes before the thing it was
  waiting for.
  :::
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="The language" icon="i-lucide-code-2"}
  Variables and the loose type system, functions, arrays and objects, array
  methods, destructuring, modules, and the ways JavaScript is not Java despite the
  name.
  :::
  :::flow-step{label="The DOM" icon="i-lucide-mouse-pointer-click"}
  Selecting elements, changing them, events, bubbling and delegation, forms and
  live validation, and rendering a list of students from data.
  :::
  :::flow-step{label="Asynchronous JavaScript" icon="i-lucide-timer" highlight}
  Callbacks, promises, `async`/`await`, `fetch`, error and loading states, and the
  event loop drawn rather than described.
  :::
::

## What the University Management App becomes

Interactive. Live validation on the enquiry form, instant search over the student
directory, filters that work without a page reload, and data fetched and
rendered rather than typed into the HTML by hand.

::callout{icon="i-lucide-arrow-right"}
The registrar opens the page you built, looks at twenty-three thousand
attendance rows, and asks the question you cannot answer with a table: is it
getting better or worse?
::
