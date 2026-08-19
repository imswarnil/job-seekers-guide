# Writing the curriculum

This file governs **the course** — everything under `content/1.path/`. It does not
govern `content/6.story/` (the book), `content/5.series/` (the episodes) or the
marketing pages; those have their own voice and are left alone.

The full specification is `content-plan.md` in the repository root. That file is
the source of truth for **what** gets taught, in **what order**, and in **whose
voice**. This file is the working translation of it onto this platform: the
folders, the front matter, the components, and the checks to run before calling a
lesson done. When the two disagree about substance, `content-plan.md` wins. When
they disagree about mechanics — a component name, a front-matter field — this
file wins, because it describes what actually renders.

---

## 1. The narrator

Every lesson is written by **one person: a job seeker, writing in 2027, looking
back at how he got hired.** Not a professor, not a documentation team. He is one
step ahead of the reader on the same path, turning around to say *"this is the
part that confused me, and here's what finally made it click."*

- **First person, to one reader.** "I", and "you". Never "students", never "we"
  meaning the reader, never "one".
- **Assume nothing.** Not "assume little" — the reader has never opened a
  terminal.
- **Name your own confusion.** "I thought a variable was a box for three months.
  It isn't, quite, and here's where that model breaks."
- **Warn before the hard parts, out loud**, so the reader never thinks the
  problem is them. Objects, generics, joins, window functions, async, the
  component model, server versus client, row-level security.
- **Define every new word the first time**, in the same breath, in plain English.
- **Say the number.** "Four weeks", "about 40%", "nine seconds".
- **Say what to skip**, and what skipping costs.
- **Be honest.** Wrong code gets called wrong, with the reason. No fake praise.
  Never claim outcomes — no placement percentages, no guaranteed packages.
- **British spelling**, to match the rest of the site.

**Banned words and habits:** "simply", "just", "obviously", "as we all know",
"it is important to note", exclamation marks, hype, and the word **"capstone"**
(say "the finished system", "putting it all together", or "the assembly lesson").

The thesis, proved over and over rather than asserted:

> None of this is hard. It's a lot of small ideas stacked in the right order.

---

## 2. The chain — the rule that outranks everything

**Every lesson ends on a cliffhanger. The first line of the next lesson answers
it, before anything else.**

This is a chain, not a list. Today's lesson is the thing yesterday's lesson made
the reader *need*. The exact hook for every lesson is written in
`content-plan.md`, Part Three. Use it.

- **First beat, always:** one or two sentences resolving the previous hook. No
  heading, no throat-clearing — it is the first paragraph on the page.
- **Last beat, always:** the next hook, unresolved, in the `## Next` block
  (§5 below).
- **Prev/next spans the whole path**, not the subject. The last lesson of a
  chapter hooks into the first lesson of the next chapter; the last lesson of a
  track hooks into the first lesson of the next track.

If you add, split or reorder a lesson, you have taken on the job of re-welding
both ends of the chain. Do it in the same change.

---

## 3. One idea per lesson

A beginner drowns when two new ideas arrive at once.

- One lesson teaches one idea. A Flexbox lesson teaches Flexbox — not Grid, not
  JavaScript "while we're here".
- Related sub-parts may share a lesson if they are one theme, taught in
  sequence. "AND, OR, NOT and precedence" is one theme. "Filtering and joining"
  is two chapters.
- **Split rather than mingle.** Splitting a mapped lesson into two files is
  always allowed; the second one carries the mapped cliffhanger so the chain
  survives.

> The test: if a confused reader asked "what is *this* lesson about?" they must
> answer in one short phrase. If the honest answer is "two things", split it.

---

## 4. The university rule

**Every example, variable, table, component, page, chart and exercise belongs to
the university world, and once we can build, to UniversityOS.**

The cast, used everywhere: `Applicant`, `Application`, `Program`, `Department`,
`Student`, `Faculty`, `Subject`, `ExamResult`, `Attendance`, `FeePayment`,
`Placement`, `Alumni`, `Enquiry`, and the status ladder
`RECEIVED` → `UNDER_REVIEW` → `SHORTLISTED` / `REJECTED` → `ACCEPTED`.

Even throwaway values belong to the world: `score`, `applicantName`, `cutoff`,
`seats`, `deadline`, `attendancePct`. Never `x`, `temp`, `data`, `foo`.

**Banned, always:** `Animal`/`Dog`/`Shape`/`Car`/`Person` demos, `foo`/`bar`,
the generic tutorial schemas (`employees`, `customers`, `orders`, `products`),
and to-do apps, blogs, e-commerce stores and weather apps as project examples.

UniversityOS — the one system the whole course builds — is specified in
`content-plan.md` §26. There is never a second project and never a throwaway
app. Before writing a build lesson, restate to yourself what already exists in
UniversityOS and do not contradict it.

---

## 5. The shape of a lesson on this platform

`content-plan.md` §7 lists eleven beats. On this site they land as these
headings, in this order. Beats 1 and 11 are not optional. The rest flex — a
practice lesson leans on "Your turn", an interview lesson is mostly Q&A.

```md
(no heading — one or two sentences resolving the previous cliffhanger, then a
line bridging into today)

## Why this matters in a job          ← beats 2-3: what breaks without this

## <the idea, named plainly>          ← beat 4: analogy first, then the
                                        technical version. Draw it.

## <watch it happen>                  ← beat 5: a small runnable example, broken
                                        version first where that teaches

## What confuses people here          ← beat 6: name the trap before they fall in

::real-life                           ← beat 7: a registrar, a dean, a lecturer

## What you can do now                ← beat 8: plus the actual output

## Your turn                          ← beat 9: the exercise; solutions behind
                                        an ::accordion, never before the attempt

## Check yourself                     ← beat 10: exactly two questions, one of
                                        them the kind an interviewer asks

## The interview question this answers ← the house block: the question phrased
                                        the way it is really asked, then what a
                                        good answer contains

## Next                               ← beat 11: the cliffhanger, last thing on
                                        the page
```

The two middle headings are named for their content, not for the beat — "The
idea, analogy first" is a scaffold, not a title a reader should ever see.

The `## Next` block is written as:

```md
## Next

::callout{icon="i-lucide-arrow-right"}
The hook, stated as the reader's own problem, in one or two sentences. Left
unresolved.
::
```

### Lesson kinds

Tag every lesson with `kind` in the front matter, and write to the tag:

| `kind`     | Spec tag   | What it means |
| ---------- | ---------- | ------------- |
| `lesson`   | `[C]`      | A concept. Idea before syntax, always. |
| `practice` | `[P]` `[T]`| Problems and hints first. **Let the reader attempt before revealing anything.** Solutions go inside `::accordion`, and show two versions: the beginner one (correct but clumsy) and the one an experienced developer writes, with a plain-words note on why. |
| `practice` | `[D]`      | Debugging. Show the broken thing and the real error, then walk through *finding* it: read the message, shrink the problem, print, then reach for the real tool. |
| `project`  | `[A]`      | Advances UniversityOS. Give the **plan and the acceptance criteria** — files, functions, routes, columns, what "done" looks like — **not the finished code.** The only exceptions are the assembly lesson at the end of a track and the finished-system lesson at the very end. |
| `quiz`     | `[Q]`      | Interview. Every answer proved with code, not just defined. |
| `reading`  | —          | Glossaries, maps, orientation. |

### Terminal-first

The reader lives in the terminal from the first week. Every install, run, build
and deploy is a real command with its real output, in a fenced ```bash block —
never "install it and continue". Explain the command, not just print it. **When
a command fails, teach the failure** — permission denied, command not found,
port already in use, `git push` rejected. Those are the moments beginners quit.

### Correct, not just runnable

A thing that runs is not a thing that's right, and this is taught as its own
discipline (`content-plan.md` §12). Where a lesson has a silently-wrong version,
show it with `::compare` — `verdict="wrong"` on the left, `verdict="right"` on
the right, and one line on *why the first is silently wrong*.

### Safety

Anywhere data can be destroyed — `rm -rf`, `git reset --hard`, `push --force`,
`UPDATE` with no `WHERE`, a leaked key — the `::warning` or `::caution` block
comes **before** the reader is in a position to do the damage, never after.

---

## 6. The components

The full author-facing reference, with a runnable example of each, is
`.studio/components.md`, and the rendered version is the lesson
`/terminal/how-this-course-works/how-to-read-these-lessons`. Nuxt UI's own blocks
are available too: `::callout`, `::note`, `::tip`, `::warning`, `::caution`,
`::tabs`, `::steps`, `::accordion`, `::card-group`, `::field`.

**A component every screen or two. A lesson that is a wall of prose has failed,
however good the prose is.** Reach for these by beat:

| Beat | Reach for |
| --- | --- |
| The idea, drawn | `::flow` for anything with an order · `::memory` for boxes and pointers · `::feature-list` for parallel small points · `::timeline` for things in time |
| Walking through code | `::code-trace` — never write "on line three" in prose |
| Broken → fixed, wrong → right | `::compare` with `verdict="wrong"` / `verdict="right"` |
| The honest trade-off | `::pros-cons` — never assert a technology choice, argue it |
| Where it shows up in a job | `::real-life` — the single highest-value block on the platform |
| Somebody's actual situation | `::persona` |
| Run it yourself | `::runner` — `html`, `css`, `javascript`, `python`, `sql`, `java` |
| The trap, the gotcha, the danger | `::warning`, `::caution`, `::tip`, `::note` |
| Solutions, interview answers | `::accordion` |
| A prompt worth having | `::ai-prompt` — always fill in `#why` |
| A video | `::youtube` — **never invent an ID**; leave the block out and note what it should show |

Rules that hold across all of them:

- **One accent per diagram.** One `highlight` on a `::flow`, one `active` cell in
  a `::memory`. If two things are important, that is two diagrams.
- **Every code sample is paired with its result** — the printed output, the
  rendered page, the returned rows. If the result is illustrative rather than
  from a real run, say so.
- **Never invent a URL** for a video or an image.
- `::runner` for anything the reader can usefully run in the tab. Nothing
  downloads on a lesson with no runner in it.

---

## 7. Files, folders and front matter

The folder tree **is** the curriculum, and the numeric prefixes **are** the
order. There is no manifest. Reordering the course is `git mv`.

```
content/1.path/
  0.terminal/                     → /terminal                 a track (subject)
    .navigation.yml               title, icon, description
    index.md                      the track overview
    2.the-terminal/               → /terminal/the-terminal     a chapter (module)
      .navigation.yml             title, icon, description
      1.your-first-session.md     → /terminal/the-terminal/your-first-session
```

Three levels, and depth alone decides the view: subject → module → lesson. A
module folder does not need an `index.md`; its `.navigation.yml` supplies the
title, icon and description the module page renders.

Slugs are the URL. Keep them short and human — `/java/collections/generics`, not
`/java/1-collections/1-generics`. Check `modules/reserved-slugs.ts` before naming
a track: a handful of top-level slugs are taken by the site itself.

**Track (subject) front matter:**

```yaml
---
title: SQL — learn to store and ask
description: One sentence for a stranger. It appears on cards, in search and on the social image.
code: JSG-SQL-201          # badge on the card
duration: 10 weeks
stage: language            # orientation | foundation | language | applied | projects | job-search
icon: i-lucide-database
outcomes:
  - Written as "you can …" — what the reader can do that they could not before
prerequisites:
  - terminal               # slugs of earlier tracks
---
```

`stage` only groups the cards on `/start`; it never orders them. Because the
groups render in a fixed order, **a track's stage must not run backwards
relative to its folder number** or the path will display out of sequence.

**Lesson front matter:**

```yaml
---
title: What a computer actually does
description: One sentence, for a stranger.
minutes: 12                # 200 words a minute, plus a minute per diagram
kind: lesson               # lesson | practice | project | quiz | reading
---
```

`draft: true` exists in the schema but nothing filters on it yet — an unfinished
lesson still appears in the rail. Do not rely on it to hide work in progress.

---

## 8. Bookending a chapter

Every chapter reads: **overview → lessons → glossary → interview Q&A →
exercises** (`content-plan.md` §24).

- **The overview** is the module's `.navigation.yml` description plus the track's
  `index.md`. Say what this chapter covers, why it exists, and how it hangs off
  the previous chapter's final hook.
- **The glossary** is the last-but-two file, `kind: reading`. Only this chapter's
  new words, one plain-English line each.
- **The interview Q&A** is the last-but-one file, `kind: quiz`. The questions
  this chapter's material produces, in an `::accordion`, each answer short,
  confident, and **proved with code**.
- **The exercises** are the last file, `kind: practice`. Challenge-flavoured, with
  stakes — "can you do this in one query with no subquery?", "this runs but the
  number is wrong, find out why". Never a boring drill. Always the university.
  The reader attempts first; solutions sit inside an `::accordion`.

All three still obey the chain: they resolve the hook before them and set the
hook after them.

Terminal-heavy chapters end their exercises file with a command cheat-sheet — a
table, one line per command, everything the chapter used.

---

## 9. AI in the course

Stated once in the reader's voice and then held: the value moved from typing the
code to knowing whether the code is right, and you cannot supervise what you do
not understand.

- **Java and SQL tracks: no AI at all.** Fundamentals only, on purpose.
- **Web tracks:** AI as a working tool for the reader — scaffolding, boilerplate,
  a first pass at CSS — always paired with "read every line, run it, and fix what
  it got wrong", especially accessibility, security and edge cases.
- **The AI track:** AI as something the reader builds, and only there.

Never move an AI feature earlier. Every time you praise its usefulness, name a
limit: it hallucinates APIs, it forgets your schema, it produces confident wrong
numbers, it cannot know your business rules.

---

## 10. Before you call a lesson done

- [ ] The first line resolves the previous cliffhanger; the `## Next` block sets
      the following one.
- [ ] Exactly one idea. Nothing from a later chapter smuggled in.
- [ ] Every example is the university. No banned generic names, no demo apps.
- [ ] It reads like the narrator sharing notes, not like documentation.
- [ ] Every new word is defined the first time it appears.
- [ ] The code or query actually runs, and its real result is shown.
- [ ] Broken-first or wrong-first is used where it teaches something.
- [ ] The trap is named — syntax, correctness, gotcha or safety.
- [ ] Terminal commands are real, explained, and their failure modes covered.
- [ ] A diagram wherever a picture teaches faster than a paragraph.
- [ ] Exactly two "Check yourself" questions; one is interviewer-style.
- [ ] Components throughout. It is never a wall of text.
- [ ] `minutes` is an honest estimate, and `kind` matches how it is written.
- [ ] No "simply", no "just", no exclamation marks, no "capstone".
- [ ] `pnpm lint` passes and the page renders.
