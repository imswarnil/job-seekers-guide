---
title: Supabase — the real backend
description: The University Management App needs a database on the internet, real logins, file storage, and permissions the database itself enforces — not permissions your interface politely suggests.
code: JSG-17
duration: 4 weeks
stage: applied
icon: i-simple-icons-supabase
outcomes:
  - Run the university schema on hosted Postgres, and name what differs from MySQL
  - Add real sign-up, sign-in and sessions without hand-rolling authentication
  - Enforce who-can-see-what in the database, not in the interface
  - Handle file uploads and live updates, and keep secrets out of the browser
prerequisites:
  - nosql
---

The application is real and the database is on your laptop, switched off. This is
the track where the university's data moves onto the internet and starts refusing
the wrong people.

## Why this track exists

Because authentication written by hand is how beginners leak data, and because
"the interface hides the button" is not security. The database can enforce the
rules itself, and learning that once changes how you think about every system
afterwards.

::warning{icon="i-lucide-shield-alert"}
This is the track where real keys appear. A service key in browser code hands a
stranger the whole university. The lesson that introduces keys introduces the
rules first: never commit `.env`, never put a service key in client code, and
rotate anything you leak.
::

## The chapters

::flow{numbered direction="vertical"}
  :::flow-step{label="A real database on the internet" icon="i-lucide-cloud"}
  What Supabase is, Postgres versus MySQL honestly, migrating the university
  schema, and querying it from the app.
  :::
  :::flow-step{label="Identity and permissions" icon="i-lucide-lock" highlight}
  Sign-up, sign-in, sessions, roles — then row-level security, where a student
  sees only their own results because the database says so.
  :::
  :::flow-step{label="Files, realtime and the finished backend" icon="i-lucide-hard-drive"}
  Document uploads for applications, live updates on the CRM board, and the
  backend assembled end to end.
  :::
::

## What the University Management App becomes

A genuine backend: hosted data, three real roles, storage for transcripts, live
updates on the admissions board, and a student who cannot read another student's
marks even with the API open in front of them.

::callout{icon="i-lucide-arrow-right"}
Everything works. It works at `localhost:3000`, which is a place only you can
go.
::
