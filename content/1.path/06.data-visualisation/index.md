---
title: Data visualisation — make the numbers speak
description: A genuinely rare, genuinely hireable skill, and the thing that makes the University Management App feel like a product rather than a form with a database behind it.
code: JSG-06
duration: 3 weeks
stage: applied
icon: i-lucide-chart-column
outcomes:
  - Choose the chart that answers the question being asked
  - Draw with SVG by hand, so no charting library is ever magic
  - Build dashboards from real queries, with numbers you can defend
  - Spot a chart that is lying, including your own
prerequisites:
  - javascript
---

Nobody reads twenty-three thousand attendance rows. They look at one picture and
decide something — and if the picture is wrong, they decide the wrong thing
confidently.

## Why this track exists

Because the last mile of every data job is a chart, and almost nobody teaches it
as a skill with rules. Most self-taught developers reach for a library, accept
its defaults, and produce something that is technically a chart and practically a
decoration.

::real-life{title="The chart that hid a dropout problem" source="A registrar's monthly report"}
Attendance was drawn as a pie chart with nine slices, sorted alphabetically by
department. Two departments were quietly collapsing and nobody could see it,
because a pie chart makes nine similar numbers look identical. The same data as a
sorted bar chart made it obvious in under a second.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="Drawing on the web" icon="i-lucide-pen-tool"}
  Which chart answers which question, the charts that lie, SVG from scratch, and
  scales — turning a number into a position on a screen.
  :::
  :::flow-step{label="Charts that ship" icon="i-lucide-chart-line" highlight}
  A charting library used deliberately, axes and labels that mean something,
  interaction, accessibility, and the University Management App dashboards.
  :::
::

## What the University Management App becomes

The dashboards: the admissions funnel, fee collection by month with a running
total, placements per department, attendance against performance, and the
at-risk list — each one reading a view you wrote in the SQL track.

::callout{icon="i-lucide-arrow-right"}
The dashboards work. They are also one HTML file of eleven hundred lines, with
three copies of the same chart code and a script tag you are frightened to move.
::
