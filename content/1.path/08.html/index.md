---
title: HTML — give the system a body
description: Everything you have built so far is invisible to everyone but you. HTML is the structure a browser can render — and the first version of it you write will be the university's actual enquiry form.
code: JSG-03
duration: 3 weeks
stage: applied
icon: i-simple-icons-html5
outcomes:
  - Explain what happens between typing an address and seeing a page
  - Write markup that means something, rather than markup that merely looks right
  - Build a real form that a real person could submit
  - Make a page usable by somebody who never sees it
prerequisites:
  - sql
---

You can store the university's data and ask it anything, and nobody but you can
reach a single row of it. A browser can — if you give it something it knows how
to render.

## Why this track exists

HTML is small. You can learn the tags in a weekend, which is exactly why most
people learn it badly: they learn the tags and never learn what the tags *mean*.
The difference shows up in interviews within about ninety seconds, and it shows
up in production the first time somebody uses your page with a keyboard.

::real-life{title="The form that lost a term's admissions" source="A university IT office"}
An enquiry form built entirely from `div` elements and click handlers worked
perfectly in testing. It could not be submitted with a keyboard, screen readers
announced nothing, and the browser's autofill never triggered — so on mobile,
where most enquiries came from, people abandoned it halfway. Nothing was broken.
Everything was meaningless.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="How the web actually works" icon="i-lucide-globe"}
  The browser, the address, the request, the response, the render. Client and
  server, HTTP, status codes, and what "the front end" honestly means.
  :::
  :::flow-step{label="Structure and meaning" icon="i-lucide-code"}
  Elements, attributes, text, links, images, lists, tables — and forms, properly:
  labels, input types, validation, and what the browser gives you for free.
  :::
  :::flow-step{label="Semantics and accessibility" icon="i-lucide-accessibility" highlight}
  The document outline, landmarks, headings that mean something, keyboard order,
  and the shape of a real page a stranger can use.
  :::
::

The web-skills roadmap at `andreasbm.github.io/web-skills` is the map this track
and the next three cover. Look at it once, then close it — a map is not a plan.

## What the University Management App becomes

Real pages: the public enquiry form, the student directory, and the shells of the
student, teacher and admin portals. Unstyled, ugly, and for the first time,
something you can send to another human being.

::callout{icon="i-lucide-arrow-right"}
You send the link to someone. They open it on a phone, and it is unreadable —
black text, times new roman, a form running off the side of the screen.
::
