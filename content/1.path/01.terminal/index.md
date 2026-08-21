---
title: The machine and the terminal
description: Before a single line of code — what a computer actually does, how to command one without touching a mouse, and how to keep a permanent, reversible history of everything you write.
code: JSG-01
duration: 3 weeks
stage: introduction
icon: i-lucide-terminal
outcomes:
  - Explain what happens inside a machine between pressing a key and seeing a result
  - Move around a computer, read files and find things in them, without a graphical interface
  - Understand permissions, processes and why "permission denied" is not a bug
  - Reach a machine that is not yours, over the network, from a prompt
  - Keep every version of your work with Git, and get back anything you lose
prerequisites:
  - orientation
---

You know what the work is and you have decided you want it. What you do not have
is any idea how the machine in front of you works — and everything after this
rests on that.

I started here because I had to. Not out of purity — out of embarrassment.

My first week of trying to learn this, someone sent me a project and said "clone
it and run it". I did not know what either word meant in that sentence. I spent
two days on something a person who had spent two hours in a terminal would have
done in ninety seconds. That was the moment I understood that the thing standing
between me and a job was not intelligence. It was a set of small, cheap,
completely learnable habits that nobody had ever shown me.

This track is those habits.

## Why this comes first

You cannot build well on a machine you think is magic. And you cannot work like a
professional through a graphical interface alone — not because the mouse is
shameful, but because every real instruction you will ever be given assumes a
prompt. Install this. Run that. Push your branch. Check the logs on the server.

::compare{caption="The same first six months, two different starting points."}
  :::compare-side{label="Starting with a framework" verdict="wrong"}
  You can make a page appear. When the build fails with `EACCES: permission
  denied`, you have no model of what a permission is, so you copy the command
  from the third search result and hope. Half your bugs are unknowable.
  :::
  :::compare-side{label="Starting here" verdict="right"}
  You are slower for three weeks. Then every error message afterwards is
  addressed to somebody who knows what a process, a path and a permission are —
  and error messages stop being weather and start being information.
  :::
::

::real-life{title="What the first day of a job actually looks like" source="Every junior developer, everywhere"}
Nobody hands you a feature on day one. They hand you a repository URL and a
README, and you are expected to clone it, install its dependencies, run it
locally, and have it working before lunch. Every step of that is this track. The
people who struggle on day one are almost never the ones who knew less
JavaScript.
::

## What you will actually be able to do

::feature-list{columns="2"}
  :::feature{icon="i-lucide-cpu" title="Explain the machine"}
  Bits, gates, the fetch–execute cycle, memory versus disk, and what an
  operating system is really for. Enough to reason, not enough to design a chip.
  :::
  :::feature{icon="i-lucide-folder-tree" title="Live at a prompt"}
  Navigate, create, move, delete, read, search, and chain small tools together
  into something none of them could do alone.
  :::
  :::feature{icon="i-lucide-shield" title="Understand refusal"}
  Permissions, ownership, processes, and what `sudo` really means — so "permission
  denied" becomes a sentence rather than a wall.
  :::
  :::feature{icon="i-lucide-git-branch" title="Never lose work again"}
  Git: commits, branches, merges, remotes, and how to recover the thing you were
  certain you had destroyed.
  :::
::

## The three chapters, and the thread through them

::flow{numbered direction="vertical" caption="Each chapter exists because the one before it ran out of road."}
  :::flow-step{label="What is actually happening in there" icon="i-lucide-cpu"}
  Five lessons on the machine itself — from electricity being on or off, up to the
  operating system deciding which program runs next. It ends with you knowing
  there is a faster way into the machine than clicking.
  :::
  :::flow-step{label="The terminal" icon="i-lucide-terminal"}
  That faster way. Moving around, making and destroying things, reading and
  searching, permissions and processes, and reaching a machine on the other side
  of the world. It ends with you having typed a hundred commands and kept none of
  them.
  :::
  :::flow-step{label="Git and GitHub" icon="i-lucide-git-branch" highlight}
  A permanent, reversible history of every change you ever make, on your machine
  and off it. It ends with your workshop ready — and nothing left to do but learn
  to speak to the machine, which is the next track.
  :::
::

## How long this takes

About three weeks at an hour a day, and the hour a day matters more than the
three weeks. There are 26 lessons. The five machine lessons are reading; the
eleven terminal and Git lessons are typing, and if you read them without typing
them you will have learned nothing, pleasantly.

::warning{icon="i-lucide-alert-triangle"}
Two lessons in this track can destroy your own files: `rm -rf` in the terminal
chapter, and `git reset --hard` in the Git chapter. Both arrive with a warning
before the command, not after it. Read those two twice.
::

## One system, the whole way through

Everything in this course — every example, every exercise, every table and page —
belongs to one system called the **University Management App**: the software a university would
actually run, from a prospective student's first enquiry through admission,
fees, attendance, results and placement.

You start it in this track, and the start is deliberately unglamorous: a folder,
a repository, and a first commit. By the end of the course it is a real system on
a real domain that anybody can visit. There is never a second project.

::callout{icon="i-lucide-arrow-right"}
The first lesson is not about a computer. It is about what you are signing up
for, and what you get at the end of it — because I nearly quit twice, and both
times it was because I had lost sight of that.
::
