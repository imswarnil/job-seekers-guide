# The components you can use in a lesson

Every one of these is written in markdown. None of them need code. They are all
built to still make sense with JavaScript switched off, so use them freely —
nothing here is decoration that leaves a hole if it fails to load.

Nuxt UI's own blocks are also available and are usually the right answer for
simple things: `::callout`, `::note`, `::tip`, `::warning`, `::caution`,
`::tabs`, `::steps`, `::accordion`, `::card-group`, `::field`.

---

## `::flow` — a sequence with arrows

For "how a request travels", "what happens when you push", "text becomes a
running process". Reads left to right on a wide screen and folds to a column on
a phone by itself.

```md
::flow{numbered caption="From text on disk to a running process"}
  :::flow-step{label="You write text" icon="i-lucide-file-code"}
  `Admissions.java` is characters in a file. Nothing more.
  :::
  :::flow-step{label="javac compiles" icon="i-lucide-cog"}
  Produces `Admissions.class` — bytecode, not machine code.
  :::
  :::flow-step{label="The JVM runs it" icon="i-lucide-play" highlight}
  This is the step people skip, and it is the one interviews ask about.
  :::
::
```

`direction="vertical"` for a long sequence. `highlight` on the step the lesson is
actually about. `note="..."` for a short aside under a box.

## `::code-trace` — walk down a code block

The code sits still and the explanation moves through it, one highlighted region
at a time. Use it wherever you would otherwise write "on line three...".

````md
::code-trace{caption="Why this prints 70 and not 70.0"}
```java [Admissions.java]
int seats = 60;
int applicants = 4200;
double ratio = applicants / seats;
```

  :::code-trace-step{lines="1-2" caption="Both are ints. Nothing wrong yet."}
  :::
  :::code-trace-step{lines="3" caption="The division happens between two ints, so the fraction is thrown away before the double ever sees it."}
  :::
::
````

`lines` is 1-based and takes ranges and lists: `"3"`, `"1-2"`, `"3,7-9"`.

## `::memory` — boxes and pointers

Arrays, stacks, queues, linked lists, tables. The whiteboard drawing.

```md
::memory{kind="stack" caption="The call stack during fact(3)"}
---
cells:
  - { value: "fact(1)", label: "top", state: "active" }
  - { value: "fact(2)" }
  - { value: "fact(3)", label: "bottom" }
---
::
```

Several `frames` instead of `cells` turns it into a stepper:

```md
::memory{kind="array" caption="Insertion sort, first two passes"}
---
frames:
  - label: "Start"
    cells: [{ value: 5 }, { value: 2 }, { value: 9 }]
  - label: "After pass 1"
    cells: [{ value: 2, state: "active" }, { value: 5, state: "active" }, { value: 9 }]
---
::
```

`kind` is `array`, `stack`, `queue`, `list` or `table`. There is no `heap` — a
wrong tree diagram is worse than a table, and tree layout is a project of its own.

## `::diagram` — a drawing of your own

For the shapes the other blocks do not cover: a hash collision, a packet's
journey, a graph traversal, the order a query is evaluated in. Puts your SVG in
the same frame as every other diagram, so it gets the label, the caption and the
scroll container that stops a wide drawing scrolling the whole page on a phone.

```md
::diagram{label="Two keys, one bucket" caption="The second name queues behind the first."}
  <svg viewBox="0 0 448 152" role="img" aria-label="Two names hash to the same bucket.">
    <rect x="1" y="10" width="120" height="32" rx="4" fill="none" stroke="currentColor" opacity="0.45" />
    <text x="61" y="30" text-anchor="middle">"Anita Rao"</text>
    <rect x="286" y="10" width="146" height="128" rx="4" data-accent fill="none" stroke-width="1.5" />
    <text x="359" y="32" text-anchor="middle">bucket 3</text>
  </svg>
::
```

Draw with `currentColor` and mark the **one** thing being taught with
`data-accent` — that way the drawing follows the reader's theme instead of
fighting it. Always give the `<svg>` a `viewBox` and an `aria-label`: without the
first it will not scale, without the second a screen reader sees nothing.

Sketch the layout with `graphviz` or `mermaid` if that is faster, but restyle the
output before it ships — neither looks like this site — and run
`pnpm svg:opt <file>` first, because inline SVG ships on every page view.

## `::side-note` — what kind of concern this is

`::note`, `::tip`, `::warning` and `::caution` say *how alarmed to be*. These
four say *what kind of thing* is being raised, which is a different axis.

```md
::side-note{kind="security"}
Permissions belong in the database, not in the screen. If the interface is the
only thing stopping a student reading another student's marks, anyone who skips
the interface reads them.
::
```

`kind` is `accessibility`, `performance`, `security` or `go-deeper`. `title`
overrides the default label.

They are monochrome on purpose. Red means the live wire and amber means a
milestone, so an aside that is only *about* danger does not get to spend either.

## `::compare` — the wrong way and the right way

```md
::compare{caption="Both take six months. Only one is employable."}
  :::compare-side{label="What most people do" verdict="wrong"}
  Learn four languages badly.
  :::
  :::compare-side{label="What works" verdict="right"}
  One language, taken all the way.
  :::
::
```

## `::pros-cons` — the honest trade-off

Every technology choice here is argued for rather than asserted. This is the
shape that argument takes.

```md
::pros-cons
---
title: Java as your first language
pros:
  - The most entry-level roles in this market
  - Verbose enough that the machine model stays visible
cons:
  - Slower to a first running program than Python
  - The build tooling is genuinely unpleasant at first
---
::
```

## `::real-life` — where this shows up in a job

The most important component on the platform. The subjects are not secret; what
is missing everywhere else is somebody connecting them to the work.

```md
::real-life{title="Why the deploy failed at 2am" source="A payments team"}
The service had a connection pool of ten and a thread pool of two hundred. Every
request past the tenth queued, the queue grew, and the health check timed out —
so the orchestrator killed a service that was working correctly and slowly.
::
```

## `::persona` — a named person

Turns an abstract point into somebody's actual situation.

```md
::persona
---
name: Priya
role: Mechanical engineering, 2023, no offers
background: Third-tier college, campus placements came and went
goal: A first software job inside a year
blocker: Believes she needs to be good at maths
---
I kept starting tutorials and stopping, because I could never tell whether the
thing I was learning was the thing I was supposed to be learning.
::
```

## `::feature-list` — a grid of small points

```md
::feature-list{columns="2"}
  :::feature{icon="i-lucide-cpu" title="Its own memory"}
  One process cannot read another's address space.
  :::
  :::feature{icon="i-lucide-file" title="Its own file handles"}
  Closing a file in one does not close it in another.
  :::
::
```

## `::timeline` — moments in order

```md
::timeline{label="A hiring loop"}
  :::timeline-item{title="Application" state="done" date="Week 0"}
  A recruiter reads it for nine seconds.
  :::
  :::timeline-item{title="Technical screen" state="current" date="Week 1"}
  One problem, forty-five minutes, thinking out loud.
  :::
  :::timeline-item{title="Onsite" state="todo" date="Week 3"}
  :::
::
```

`state` is `done`, `current` or `todo`.

## `::ai-prompt` — a prompt to hand the reader

The job-search subjects teach AI as a tool rather than a shortcut, which means
handing over the actual words. Always fill in `#why` — a prompt with no
explanation teaches copying, not judgement.

```md
::ai-prompt{title="Critique your resume bullets" model="Any"}
You are a hiring manager at a mid-size product company. Below are six bullets
from my resume. For each one, tell me what a reader learns about my impact, and
what they cannot tell. Do not rewrite them.

  #why
  It asks for a critique, not a rewrite. A rewrite gives you somebody else's
  words; a critique tells you what yours were missing.
::
```

## `::youtube` — a video, with a start and an end

Costs nothing until somebody presses play — no third-party script loads before
that. `start` and `end` are in seconds.

```md
::youtube{id="dQw4w9WgXcQ" start="42" end="940" title="What a process is"}
The three minutes that explain scheduling better than the textbook does.
::
```

## `::runner` — code the reader can run

````md
::runner{lang="java" stdin="4200"}
```java
public class Main {
  public static void main(String[] args) {
    System.out.println(4200 / 60);
  }
}
```
::
````

`lang` is `html`, `css`, `javascript`, `python`, `sql` or `java`.

HTML, CSS and JavaScript run in the reader's tab instantly. Python and SQL
download a runtime the first time somebody presses Run — the component says so
before they do. Java runs on a server, and says that too.

Nothing downloads on a lesson that has no `::runner` in it, and nothing
downloads until Run is pressed.

## `::ad` — an ad slot

```md
::ad{placement="in-article"}
::
```

Ads are off site-wide by default. The switch is `ads.enabled` in the site
settings, and turning it off removes these too.
