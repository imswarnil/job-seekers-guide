---
title: "Computer networks: how two machines talk"
description: "One machine you understand. Every real system is several, and the wire between them is where the interesting failures live: timeouts, ports, certificates, and requests that vanish."
code: JSG-03
duration: 2 weeks
stage: foundation
icon: i-lucide-network
outcomes:
  - Trace a request from an address typed in a browser to a reply on the screen
  - Say what each layer is responsible for, and what breaks when one of them does
  - Explain IP, ports, DNS, TCP and TLS in plain English, in that order
  - Read the network tab of a browser and know what you are looking at
  - Answer "what happens when you type a URL and press enter" properly
prerequisites:
  - operating-systems
---

You understand one machine, completely. Now count the machines involved in
anything you have ever used, and the number is never one. The wire between them
is a subject of its own, and it is the one every "it works on my laptop" story
ends up being about.

## Why this track exists, and why it is short

Networks is a huge subject and most of it is not yours. You are not building a
router. What you need is the chain (address, port, connection, request, reply)
in enough detail to read an error and know which link broke. That is two weeks,
not a semester.

It is also the single most-asked opening question in an interview, in a form so
standard it has a name: "what happens when you type a URL and press enter". A
candidate who can walk it end to end without hedging has answered four questions
at once.

::callout{icon="i-lucide-arrow-right"}
So data arrives, and it arrives constantly. Nobody has yet said where any of it
is kept, or how a system finds one row in ten million without reading them all.
::
