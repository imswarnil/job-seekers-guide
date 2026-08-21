---
title: "Operating systems: the program that says no"
description: You spent three weeks talking to something. This is what was listening. It hands out memory, shares one processor between everything, and is the real author of every "permission denied" you have seen.
code: JSG-02
duration: 3 weeks
stage: foundation
icon: i-lucide-cpu
outcomes:
  - Say what an operating system actually does, without reciting a definition
  - Explain what a process is, and what is different about a thread
  - Describe how one processor runs forty things at once, and what that costs
  - Read a "permission denied" or an out-of-memory error and know which layer produced it
  - Answer the operating-systems round of an interview from things you have seen happen
prerequisites:
  - terminal
---

Every command you typed last month went to the same program, and it is the one
that said no. It was not the terminal refusing you, and it was not the file.
There is a layer between you and the hardware that owns the machine, and this is
the track where you meet it.

## Why this track exists, and why it is here

Three weeks in the terminal gave you a pile of facts with no frame around them:
permissions, processes, a prompt that came back when a program finished. Every
one of those is an operating system decision, and none of them make sense until
you know that. Learning this now means the rest of the course has somewhere to
put things: a memory leak, a port already in use, a container, a deployment
that dies at 3am, all land in the same picture.

It is also one of the four subjects a computer science graduate is assumed to
have, and it is asked about directly. "What is the difference between a process
and a thread" is not a trick question. It is a check that you have this frame.

::callout{icon="i-lucide-arrow-right"}
By the end of it you understand one machine, completely. And every job you will
ever have involves at least two of them, talking.
::
