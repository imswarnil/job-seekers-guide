---
title: The toolchain — how real projects are run
description: Everything so far has been loose files opened in a browser. Every professional project has a package manager, a build tool and a linter, and not knowing them is the fastest way to look self-taught in a bad way.
code: JSG-07
duration: 1 week
stage: applied
icon: i-lucide-package
outcomes:
  - Explain what `package.json`, `node_modules` and a lock file each do
  - Run a project with a dev server, and produce a build somebody could deploy
  - Set up formatting and linting so an argument about style never happens again
  - Read somebody else's project and know where to start
prerequisites:
  - data-visualisation
---

One file, eleven hundred lines, three copies of the same chart. That is not a
discipline problem, it is a missing toolchain — and this is the shortest track in
the course.

## Why this track exists

Because the first thing that happens on a real project is `npm install`, and the
first thing that happens on your machine is that it fails. Knowing what these
tools are for takes an afternoon and removes an entire category of humiliation.

::feature-list{columns="2"}
  :::feature{icon="i-lucide-box" title="Node and npm"}
  JavaScript outside the browser, dependencies versus dev dependencies, and why
  `node_modules` is never committed.
  :::
  :::feature{icon="i-lucide-zap" title="A bundler and a dev server"}
  Why bundling exists, hot reload, and what actually ends up in the output folder.
  :::
  :::feature{icon="i-lucide-sparkle" title="Formatting and linting"}
  A formatter ends the style argument. A linter catches the bug before the review
  does.
  :::
  :::feature{icon="i-lucide-folder-git-2" title="Project structure"}
  Where things go, and why every project you open looks broadly the same.
  :::
::

## What UniversityOS becomes

A real project. Installed, built, linted, formatted, and runnable by somebody
else with two commands.

::callout{icon="i-lucide-arrow-right"}
The build is clean and the linter is happy. Then a typo in a property name ships
to production, and neither of them said a word about it.
::
