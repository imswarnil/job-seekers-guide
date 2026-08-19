---
title: Vercel, DNS and hosting — go live
description: Software nobody can visit is not software. DNS is the piece almost every self-taught developer waves their hands at, which makes understanding it a quiet advantage.
code: JSG-13
duration: 2 weeks
stage: applied
icon: i-lucide-globe
outcomes:
  - Trace a request from a name typed in a browser to your running code
  - Buy a domain, point it correctly, and explain every record you created
  - Deploy from a Git push, with previews before production
  - Keep environments and secrets separate, and roll back when it goes wrong
prerequisites:
  - supabase
---

It all works at `localhost:3000` — a place only you can go. This is the track
that turns it into a link you can send to a stranger, and later, put at the top
of a CV.

## Why DNS gets a whole chapter

Because it is the one piece almost everybody skips, and it is exactly the thing
that breaks at nine on a Monday. Somebody who can say "the A record still points
at the old host and the TTL is an hour, so give it an hour" is visibly different
from somebody who says "the internet is being weird".

::real-life{title="The launch that was live for everyone except the client" source="A first freelance project"}
The site was deployed and the domain was pointed correctly. The client still saw
the old page for a day and a half, because their machine had cached the previous
answer and nobody involved knew what a TTL was. Nothing was broken. Everybody
spent a day certain something was.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="What live actually means" icon="i-lucide-radio"}
  How a website reaches a stranger, DNS properly — registrars, nameservers, A,
  CNAME, TXT and MX records, TTLs — plus `dig` and `nslookup` at the terminal, and
  what HTTPS is doing.
  :::
  :::flow-step{label="Shipping" icon="i-lucide-rocket" highlight}
  Deploying from a Git push, preview versus production, environment variables per
  environment, custom domains, rollbacks, and what to check the first hour after
  going live.
  :::
::

## What UniversityOS becomes

Live. On a real domain, over HTTPS, deployed from a push, with a preview URL for
every branch and a production environment you can roll back.

::callout{icon="i-lucide-arrow-right"}
Anyone in the world can now use it. It is also completely literal: it answers
exactly what it was asked, and a dean does not want to write SQL to find out how
this intake is going.
::
