---
title: CSS — make it look like something
description: The page works and nobody would use it. This is the track where the system gets a face, on every screen size, and where most self-taught developers quietly stay weak.
code: JSG-04
duration: 5 weeks
stage: applied
icon: i-simple-icons-css
outcomes:
  - Style a page deliberately, instead of adding rules until it looks right
  - Explain the cascade, specificity and the box model without hedging
  - Lay out a real interface with Flexbox and Grid, and know which one to reach for
  - Make one page work from a 360px phone to a desktop, in light and dark
prerequisites:
  - html
---

They opened it on a phone and it was unreadable. Not broken — the markup was
right. It just had no face, and a browser's default styling is a punishment.

## Why this track is longer than you expect

Because layout is the part almost everyone half-learns. People who are otherwise
strong will centre something by trial and error and hope it is not asked about.
It is asked about. "How would you centre this and keep it centred at 360 pixels?"
is one of the fastest ways an interviewer finds out whether the front-end line on
your CV is real.

::compare{caption="Two ways to arrive at the same-looking page."}
  :::compare-side{label="Adding rules until it looks right" verdict="wrong"}
  Fourteen `!important` declarations, three fixed pixel heights, and a layout that
  falls apart the moment a student's name is longer than expected.
  :::
  :::compare-side{label="Knowing what the browser is doing" verdict="right"}
  A box model you can picture, a cascade you can predict, and a layout that bends
  because you chose the axis it bends on.
  :::
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="Applying style" icon="i-lucide-paintbrush"}
  How CSS attaches to HTML, selectors, specificity, inheritance, and the cascade
  in a sentence you can repeat under pressure.
  :::
  :::flow-step{label="Type, colour and units" icon="i-lucide-type"}
  Fonts and readable text, colour and contrast, and the units that scale versus
  the ones that trap you.
  :::
  :::flow-step{label="Layout" icon="i-lucide-layout-grid" highlight}
  The box model, display and flow, positioning, then Flexbox and Grid — the two
  tools that make every layout question answerable.
  :::
  :::flow-step{label="Responsive, themed and alive" icon="i-lucide-smartphone"}
  Media queries, mobile-first, custom properties, dark mode, transitions, and
  keeping motion out of the way of people who do not want it.
  :::
::

## What the University Management App becomes

Styled, responsive and themeable. The enquiry form is usable on a phone, the
portals look like software rather than a document, and the whole thing survives
being turned sideways.

::callout{icon="i-lucide-arrow-right"}
It looks like a product now. Click anything — the search box, the filter, the
"apply" button — and absolutely nothing happens.
::
