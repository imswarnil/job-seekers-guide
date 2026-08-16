# Sample Lessons

One worked example per lesson format, showing the frontmatter contract from
[schema.md](schema.md) and the house teaching style from
[../curriculum/README.md](../curriculum/README.md).

Use these as the template when authoring or seeding.

---

## Article — original

```yaml
---
type: lesson
slug: what-is-a-process
title: "What is a process, really"
course: CS-OS-101
format: article
duration_min: 12
free_preview: true
source: original
interview_link: "'Process vs thread' is asked in almost every fresher round."
---
```

> **Why you're reading this**
> Six months from now, an application you built will stop responding, and your
> team lead will ask "is the process still running, or is it a thread that's
> stuck?" This lesson is so you have an answer.

**The short version.** A program is a file on disk. A process is that file
*running* — with its own memory, its own open files, its own slice of CPU time.
Double-clicking Chrome turns a program into a process. Opening a second Chrome
window may or may not create a second one, and *why* is the interesting part.

**Show it.** [Walkthrough: open your task manager / run `ps aux`, find one
process, read every column…]

**Now the definition, which will make sense.** …

**Skip for now:** process control blocks in kernel-level detail, and the
five-state vs seven-state model argument. Neither will be asked, and neither
will help you debug anything this year.

**Check yourself:** In one sentence — why does opening two Chrome windows not
double your memory usage the way opening two different applications does?

---

## Video — curated

```yaml
---
type: lesson
slug: how-tcp-handshake-works
title: "The TCP handshake, watched packet by packet"
course: CS-NET-101
format: video
duration_min: 14
source: curated
video:
  url: https://www.youtube.com/watch?v=EXAMPLEID
  start: 128
  end: 968
interview_link: "Follows directly from 'what happens when you type google.com'."
---
```

> **Why this video, and what to ignore.**
> This is the clearest explanation of the three-way handshake available for
> free, and we've clipped it to the 14 minutes that matter. The presenter spends
> the first two minutes on history — skipped. After 16:08 they go into
> congestion-control algorithms, which you do not need yet — also skipped.
>
> **Watch for:** SYN, SYN-ACK, ACK, and *why* three messages rather than two.
> That "why" is the interview question.
>
> Full credit and the complete video: [link]. If you find it useful, subscribe
> to them — we curate, we don't take credit.

**After watching:** open your browser's dev tools, Network tab, load any site,
and find the connection timing. That's this lesson, happening.

---

## Quiz

```yaml
---
type: lesson
slug: os-checkpoint-1
title: "Operating Systems — checkpoint 1"
course: CS-OS-101
format: quiz
duration_min: 8
quiz: os-quiz-1
---
```

See [quizzes-sample.md](quizzes-sample.md).

---

## Exercise

```yaml
---
type: lesson
slug: build-a-deadlock
title: "Exercise — build a deadlock, then break it"
course: CS-OS-101
format: exercise
duration_min: 45
source: practice
---
```

> **The task.** Write a program with two threads and two locks that reliably
> deadlocks. Run it. Watch it hang. Then fix it three different ways: lock
> ordering, a timeout, and by removing the second lock entirely.

**Why this exercise:** every candidate can recite the four Coffman conditions.
Almost none have watched their own program freeze. The one who has answers the
follow-up question differently, and the interviewer notices.

**Deliverable:** the deadlocking version and the three fixes, in your practice
repo, with a paragraph in the README on which fix you'd choose in production and
why.

---

## Career lesson — with a personal note

```yaml
---
type: lesson
slug: the-first-offer-is-a-door
title: "The first offer is a door, not a destination"
format: article
duration_min: 9
source: original
status: real
---
```

> My first offer was **₹13,000 a month — ₹1.8 LPA** — after 33 rejections.
>
> It was less than my friends were making and less than the training institute
> had implied. I took it anyway, and it was the correct decision, for one
> reason: **the fresher tag is a door you only need opened once.**
>
> Once you have a company name, a joining date, and real work on your resume,
> every conversation afterwards is different. You are no longer someone asking
> to be believed in. Three months after that job I was at Accenture on a
> Salesforce track, at a multiple of the salary — and none of that was reachable
> from my bedroom at home.
>
> **Take the door.** Then read the next lesson, which is about what to do the
> day after you walk through it.

**The two cases where you shouldn't:** a bond over 18 months with a large
penalty, or a company asking *you* to pay *them*. Both are covered in
`JOB-201`. Everything else — take it.

---

## House style checklist

Every lesson, regardless of format:

- [ ] Opens with **why this matters in a real job**, not a definition
- [ ] Shows a concrete example **before** the abstraction
- [ ] Explicitly names **what to skip**, and why
- [ ] Ends with a **check** — a question, an exercise, or a thing to build
- [ ] States the **interview link** where one exists
- [ ] **Credits the source** if curated, with a link and a nudge to support them
- [ ] Written as if to one person who is capable and behind — never
      condescending, never inflated
