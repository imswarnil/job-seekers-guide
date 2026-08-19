# content-plan.md — the complete build spec
## *"Notes from a job seeker, 2027"* — foundation first, core computer science next, technologies after that, and the real project last

**Read this file once, end to end, then build the whole course.** This is the single,
self-contained brief. You (Claude Code) will not be handed any other project file.
Everything is here: who narrates and in what voice, the rules that never bend, the full
part → track → chapter → lesson map, the component kit, the diagrams, the AI policy, the
project specification, and the exact order to build in.

**The shape of this course, and why it is in this order:**

> **Part A — Foundation.** What a computer is, how it works, and how to command it. No
> code yet. Nothing is allowed to be magic.
>
> **Part B — Core computer science engineering.** Programming, data structures,
> algorithms, the mathematics, computer architecture, operating systems, computer
> networks, database theory and SQL, software engineering, and security. This is the
> degree. This is what makes an engineer instead of someone who copies code, and it is
> what interviews are actually built on.
>
> **Part C — The technologies of the industry.** HTML, CSS, JavaScript, data
> visualization, the toolchain, TypeScript, React, Next.js, NoSQL, Supabase, hosting and
> DNS, and AI. Each one earned by a wall the reader has already hit.
>
> **Part D — The project, last.** With the foundation solid and every technology in hand,
> the reader builds the **University Management App** — a complete university management system — end to
> end, and deploys it on a real domain.
>
> **Part E — The interview.** Turning all of it into a job.

Nothing in Part C or D is taught before the foundation that makes it understandable. AI
comes near the end, on purpose. The project comes last, on purpose.

---

# PART ONE — THE RULES

## §1. Who is narrating

Every word is written by **one person: a job seeker, writing in 2027, looking back at how
he got hired.**

He is not a professor. He is the guy who was exactly where the reader is now — no famous
college, no connections, no genius — who sat down, learned this one small idea at a time,
built one real system, and walked into interviews able to answer the questions. He turns
around on the path and says: *"Here. This is the part that confused me. This is what
finally made it click. Don't waste the six weeks I wasted."*

**His story, used naturally, never mechanically:**

> "I started with nothing. I'd never opened a terminal. By 2027 I was answering questions
> in interviews I once thought were impossible — not because I got smart, but because I
> learned the fundamentals properly and then built one real thing I understood every line
> of. These are my notes. They're not clean. They're what actually worked."

**How he writes:**
- **First person, to one reader.** "you," never "students." "Watch what happens when I run
  this." "This had me stuck for two days."
- **Beginner-first.** Assume the reader has never programmed. Not "assume little" — assume
  *nothing*.
- **Warm, confident, honest.** Never flatters. If code is bad, he says so and why. If a
  topic is genuinely brutal — objects, pointers, concurrency, joins, async, row-level
  security — he **says so before it arrives**, so the reader never thinks the problem is
  them.
- **He names his own confusion.** "I thought a variable was a box for three months. It
  isn't, quite, and here's where that model breaks."
- **Every new word defined the first time**, in the same breath, in plain English: *"a
  process — one running program, with its own private slice of memory —"*
- **Banned:** "simply," "just," "obviously," "as we all know," "it is important to note."
  No padding. No corporate voice. Short sentences at the hard parts.

**The thesis, proven over and over:**

> "None of this is hard. It's a lot of small ideas stacked in the right order. Learn the
> fundamentals once and every language, every framework, every tool after that is a
> dialect. That's why this course does the boring theory first — it's the thing that makes
> everything else easy."

## §2. The promise

By the end, someone who started at zero can:

1. **Live in the terminal** — navigate, run, install, version, deploy, without a graphical
   interface holding their hand.
2. **Write code that runs, and that is correct** — correctness taught as a discipline
   separate from syntax.
3. **Reason like an engineer** — pick the right data structure, estimate a cost, explain
   what the operating system and the network are doing underneath, and design a schema
   that won't rot.
4. **Build and ship a real system** — the University Management App, front to back, on a real domain.
5. **Speak the whole language of the industry** — web, data, cloud, hosting, DNS, AI.
6. **Pass the interview**, because every lesson quietly trained for it.

If something you are about to write doesn't move a beginner toward those six, cut it.

## §3. The chain (the rule that outranks everything)

**Every lesson ends on a cliffhanger. The very first line of the next lesson answers it,
before anything else.** A chain, not a list. Today's lesson is the thing yesterday's made
the reader *need*.

The whole course as one chain:

> the machine only understands on and off → so you learn how it's built → you can't command
> it by clicking → so you learn the terminal → you lose your work → so you learn Git → the
> machine needs instructions → so you learn to program → your program is correct but
> unusably slow → so you learn data structures → the right structure still isn't the right
> method → so you learn algorithms → you can't prove any of it → so you learn the
> mathematics → your code runs on hardware you don't understand → so you learn architecture
> → a hundred programs share one processor → so you learn operating systems → your program
> is alone on one machine → so you learn networks → your data dies when the program stops →
> so you learn databases → nobody but you can run any of it → so you learn the web → it's
> ugly → CSS → it doesn't react → JavaScript → the numbers are unreadable → charts → the
> project is a swamp → tooling and types → the interface doesn't scale → React → rendering
> and routing are yours to hand-roll → Next.js → the data is on your laptop → Supabase →
> only you can reach it → hosting and DNS → it's live but not smart → AI → and now you have
> everything you need to build the real thing → the University Management App → and then explain it in an
> interview.

Each lesson's exact hook is in the map. Use it as the closing beat, unresolved. Open the
next lesson by resolving exactly that hook in one or two sentences. **The open (resolve)
and the close (hook) appear on every lesson, without exception.**

## §4. One idea per lesson — never mingle

- **One lesson, one idea.** A linked-list lesson teaches linked lists — not trees, not
  recursion "while we're here." A Flexbox lesson doesn't touch Grid.
- **Related sub-parts may share a lesson if they're one theme**, taught fully, in sequence.
  "Scope, static and final" is one theme. "Methods and polymorphism" is two chapters.
- **You may split any mapped lesson into sub-lessons** (`L84a`, `L84b`) when one idea per
  lesson demands it. Sub-lessons chain with their own micro-hooks; the **last** one carries
  the mapped cliffhanger so the main chain survives.
- **Chapters may grow.** More lessons is fine; mingled lessons is not.

> The test: if a confused reader asked "what is *this* lesson about?", they must answer in
> **one short phrase**. If the honest answer is "two things," split it.

## §5. The eleven beats of a lesson

1. **Resolve last lesson's cliffhanger.** First thing on the page, one or two sentences.
2. **Where that leaves us.** One line into today.
3. **Why we're learning this.** What breaks without it; what you *cannot* do yet. Never
   introduce syntax before the reason for it.
4. **The idea, analogy first.** Everyday analogy, then the technical version. (A variable
   is a labelled box. A stack is a pile of plates. A process is one running program with
   its own private desk. A join stitches two sheets on a shared column. A DNS record is a
   line in the internet's address book.) **If a drawing teaches faster, draw it here (§10).**
5. **A small runnable example.** Short, in the university world, and it actually runs. Show
   the **broken version first** — the real error — then the fix, where it helps.
6. **What confuses people here.** Name the trap before they fall in it.
7. **How you'd use this for real.** One concrete, non-toy situation inside a university.
8. **What you get out of it.** What the reader can now do, plus the **actual result** —
   printed output, the rendered page, the returned rows — so they can check their own run.
9. **Your turn.** The exercise, the problems, the broken code to fix.
10. **Check yourself.** Exactly **two questions**; **one is the kind an interviewer asks.**
11. **The cliffhanger.** The exact hook from the map. The last thing on the page.

**The ten teaching habits:** idea before syntax · analogy then technical · define every new
word the first time · one idea per lesson · short code · broken-first where it helps · warn
before the hard parts · say what the machine is actually doing when it matters · be honest
about wrong answers · never break the chain.

## §6. Lesson types

`[C]` concept · `[P]` practice · `[D]` debugging · `[T]` terminal · `[Q]` interview ·
`[A]` builds a piece of the project (Part D only) · `[DIAGRAM]` needs a drawn diagram.

- **Practice `[P]`** — problems and hints, and **the reader attempts before anything is
  revealed.** Solutions sit behind a reveal, shown as **two versions**: the beginner one
  (correct but clumsy) and the one an experienced developer writes, with a plain-words note
  on why the second is better.
- **Debugging `[D]`** — show the broken thing, the real error or wrong output, and walk
  through *finding* it: read the message, shrink the problem, print, then reach for the real
  tool (debugger, dev tools, `EXPLAIN`, the network tab).
- **Terminal `[T]`** — every command real, explained flag by flag, with its real output and
  its failure modes.
- **Build `[A]`** — give the **plan and the acceptance criteria**, never the finished code,
  except in the final assembly lessons.

## §7. Terminal-first (a standing discipline)

The reader lives in the terminal from the first week and never leaves it. Every install,
run, build and deploy is a real command with real output. **When a command fails, teach the
failure** — permission denied, command not found, port already in use, wrong version,
rejected push. These are the moments beginners quit; catch them first. End every
terminal-heavy chapter with a one-line-per-command cheat sheet.

Commands the reader must own by the end: `cd ls pwd mkdir mv cp rm cat less head tail grep
find chmod ps kill top ssh curl dig` · `git init add commit branch checkout merge rebase
push pull clone log diff stash reset revert` · `javac java jar` · `mysql psql` · `npm npx`
· `next dev build start` · `docker` basics · `vercel` · `supabase`.

## §8. The university rule (strict)

**Every example, variable, table, page, chart and exercise belongs to the university
world.** Even in the theory tracks: a stack is the call stack of the grade calculator, a
queue is the reviewer's pending pile, a graph is the prerequisite chart of a degree
program, a deadlock is two clerks each holding one file the other needs.

**The cast:** `Applicant`, `Application`, `Program`, `Department`, `Student`, `Faculty`,
`Reviewer`, `Subject`, `ExamResult`, `Attendance`, `FeePayment`, `Placement`, `Alumni`,
`Enquiry`, `Status` (`RECEIVED → UNDER_REVIEW → SHORTLISTED / REJECTED → ACCEPTED`).

**Banned, always:** `Animal`, `Dog`, `Shape`, `Car`, `Person` demos, `foo`, `bar`, `baz`,
and the generic tutorial schemas `employees`, `customers`, `orders`, `products`. Banned as
project examples: to-do apps, blogs, e-commerce stores, weather apps.

> Enforcement line on every lesson: **"If this isn't about the university, rewrite it until
> it is."**

## §9. Correct, not just runnable — and safe

**Correctness is a taught discipline.** A thing that runs is not a thing that's right. Use
the **wrong → right toggle** (§11) and default it to *wrong*, so the reader feels the bug
first: the loop that runs one time too many; `==` on two identical names returning false;
the missing `WHERE` that hits every row; the join that fans out and doubles a count;
`= NULL` returning nothing; the async function that returns a Promise instead of the value;
the state that updates but the screen doesn't; the query that works for you and returns
nothing for a real user because security is doing its job. Teach the habit out loud: *does
this number make sense — or did I just see green?*

**Safety comes before the reader can do the damage**, in a red callout: know which database
you're in; select before you write; never write without a `WHERE`; wrap risky writes in a
transaction; back up first; practise destructive commands on a copy; know what
`rm -rf`, `git reset --hard` and `push --force` actually destroy; never commit a secret;
preview before production.

## §10. Draw it — SVG whenever a picture teaches faster

A standing habit, not just for `[DIAGRAM]` lessons. Monochrome ink for structure, **one
signal-red element = the single thing being taught**, every part labelled in mono, **one
idea per diagram**, faint blueprint grid behind.

**Draw at minimum:** logic gates building an adder · the fetch-execute cycle · stack versus
heap with a reference pointing into the heap · what `new` does · array indexing from zero
and the off-by-one boundary · a linked list's pointers · a stack and a queue in motion · a
tree and its traversals · a hash table with a collision chain · a graph traversal frontier ·
the call stack growing and unwinding · recursion as a tree · a sort in progress · binary
search halving the range · Big-O curves compared · a finite automaton · the memory hierarchy
· pipelining · the process lifecycle · a context switch · virtual memory and page tables · a
deadlock as a cycle · the OSI/TCP-IP layers · a packet's journey · the TCP handshake · DNS
resolution · primary key to foreign key · inner versus left join · join fan-out multiplying
rows · `GROUP BY` collapsing rows · the logical order of query evaluation · a B-tree index
versus a full scan · a transaction committing or rolling back · the DOM tree and event
bubbling · the box model · Flexbox axes · Grid tracks · the event loop · a fetch timeline ·
the React component tree and what re-renders · the server/client component boundary ·
row-level security filtering rows per user · the deploy pipeline · and the AI assistant's
pipeline with the verification step marked red.

## §11. The component kit (build this before any lesson)

One `assets/styles.css`, one `assets/components.js`, and copy-paste HTML blocks. **Vanilla
HTML, CSS and JavaScript — no framework, no build step, in-page state only.**

**Design tokens:**
```
--ink-0:#ffffff; --ink-500:#76768a; --ink-900:#191922; --ink-1000:#08080c;
--signal-500:#f04e2e;   /* the ONE accent: the hook, the error, the wrong result, the danger */
--amber-500:#c1872a;    /* branding only: wordmark, "you built this", the University Management App mark */
--font-display:'Space Grotesk',sans-serif;  --font-body:'Inter',sans-serif;
--font-slate:'IBM Plex Mono',monospace;     --radius-card:14px; --radius-media:10px;
```
The world is ink. **Signal-red appears exactly once per view** and always means the live
wire. Amber is only branding. Break this and red stops meaning anything.

**The components:**
1. **`code-window`** — any language: filename tab, line numbers, syntax roles, one line
   highlightable in red for "this is the line that matters."
2. **`terminal-window`** — prompt, typed command, real output; failures render red.
3. **`broken → fixed`** — two tabs; Broken shows the real compiler/runtime/browser error.
   Defaults to Broken.
4. **`wrong → right`** — the correctness component (§9). Defaults to Wrong.
5. **`run-output`** — the actual printed output of a program.
6. **`result-grid`** — a query result as a database-style table, tagged "illustrative — run
   it yourself."
7. **`live-preview`** — for the web tracks: the rendered result beside the code, resizable
   so the reader can *see* responsive behaviour.
8. **`chart-demo`** — a small live chart built with the library the lesson teaches.
9. **`diagram`** — inline SVG (§10).
10. **`visualiser`** — a step-through animation for data structures and algorithms: push and
    pop a stack, insert into a list, walk a tree, watch a sort swap, watch binary search
    halve. Step forward and back. **This is the single best teaching device in Part B; build
    it well.**
11. **`query-builder` stepper** — build a query clause by clause, the result grid changing
    at each step.
12. **`explain-panel`** — a query plan as a grid, the full scan marked red.
13. **`complexity-table`** — operations versus cost for a structure, with the winning cell
    marked.
14. **`reveal-answer`** — "show my solution", opening to the two-version layout.
15. **`interview-accordion`** — questions expanding to short answers *with code that proves
    it*.
16. **`check-yourself` quiz** — two questions, explanations on click, one tagged
    "Interviewers ask this."
17. **`callout`** — `note`, `trap`, `syntax-trap`, `gotcha`, `correctness`, `safety` (red),
    `warn-hard`, `real-world`, `a11y`, `perf`, `security`, `ai-note`, `go-deeper`.
18. **`glossary-chip`** — inline first-use term with a one-line definition on hover.
19. **`cliffhanger-bar`** — full width, one red dot, the hook in display type. Always last.
20. **`video-slot` / `photo-slot`** — framed placeholders the owner fills.
21. **`progress-rail`** — part, track, chapter, lesson number, and prev/next that follow the
    chain.
22. **`command-cheatsheet`** — the end-of-chapter terminal reference.

Each lesson is one self-contained `.html` file linking the kit, laying out beats 1–11. A
component every screen or two, so it never reads like a textbook.

## §12. The mascot: Sig

An **original graphic mascot — not a human.** Identical design every image; only expression,
pose and signal state change.

- **Head:** a rounded frame/screen, slightly wider than tall, thin ink border, light face
  panel, two dot eyes and a small mouth. Brows only when needed. No nose.
- **The signal:** one red (`#f04e2e`) live-dot in the **top-left of his screen**. Concentric
  rings when "live."
- **Body:** a simple deep-ink rounded shape, short stub arms, mitten hands, little feet.
- **Flat solid fills only.** No gradients, shading, realism or 3D.
- **Never:** hair, skin, a beard, a human face, a second saturated colour on his body, or
  more than one red element in a scene. If the scene's hook is the red element, his dot goes
  **off**.
- **Signal state as expression:** lit plus rings = the big moment; lit = engaged; off = calm.

**Build the model sheet first**, then reference it. Output one prompt per lesson:

```
[ILLUSTRATION PROMPT:
Original flat mascot, built from a few clean geometric shapes, flat solid fills ONLY, no
gradients, no shading, no realism, no 3D. NOT a human — a graphic creature. Same character
and style as the reference sheet.

CHARACTER (identical every image) — "Sig": HEAD is a rounded frame/screen with a thin ink
border and a light face panel showing two dot eyes and a small mouth; ONE red (#f04e2e)
live-dot in the TOP-LEFT corner of the screen. BODY is a simple deep-ink rounded shape with
short stub arms, mitten hands, little feet. No hair, no skin, no beard, nothing human.

SIGNAL STATE: <lit + rings / lit / off>.
EMOTION: <curious / aha / surprised / confident / thinking / playfully stuck / proud /
careful-warning>.
POSE / ACTION: <Sig DOING the concept, with a university prop where possible>.
CAMERA: <bust / three-quarter / full body / over-shoulder / low angle>.
SCENE: <the concept in the university world, naming the ONE red element>.

COLOUR (strict): Sig's body ink, screen light, ONE red dot; background, props and any
diagram in monochrome ink; EXACTLY ONE red (#f04e2e) in the whole image; amber only for
branding, never on Sig; faint blueprint-grid background; no other colours. 16:9.
]
```

**Calibration:** variables — Sig holding one ink box tagged red `score = 87` (dot off).
Stack — Sig adding a red plate to an ink pile (dot off). Hash collision — two ink keys
landing in one red bucket (dot off). Deadlock — two Sigs each gripping one ink folder, the
cycle marked red (both dots off). Off-by-one — an ink row of boxes with the red one just
past the end (dot off). NULL — Sig peering into an empty ink cell tagged red (dot off). Join
fan-out — Sig startled as one row multiplies, the doubled count red (dot off). Async — Sig
at an ink counter holding a red ticket labelled `Promise` (dot off). DNS — Sig at an ink
switchboard plugging one red cable (dot off). The finished system — Sig, low angle, dot lit
plus rings, beside an ink tower of labelled blocks.

**Thumbnails:** 16:9, readable in half a second, one red element, Sig on-model, lesson number
in mono, and a title that is **the tension, not the topic** — "Where did 230 students go?"
beats "LEFT JOIN"; "It said they're different" beats "String equality"; "Two clerks, one
file, forever" beats "Deadlock."

## §13. AI policy (a helper early, a subject late)

State it once, in his voice:

> "AI writes code faster than I ever will. That didn't make learning this pointless — it
> made it worth more. The value moved from *typing the code* to *knowing whether the code is
> right*. You cannot supervise what you don't understand. I learned the fundamentals so I
> could tell when the machine was confidently wrong — and it is, often."

- **Part A and early Part B: AI is used to explain errors and generate practice problems
  only**, always with "and here's how to check it." The reader implements every data
  structure and writes every query themselves. No exceptions — this is where the
  understanding is built.
- **Part C: AI as a working tool** — scaffolding, boilerplate, first-pass CSS, test cases —
  paired always with reading every line and fixing what it missed, especially accessibility,
  security and edge cases.
- **Part C's AI track and Part D: AI as something you build** — real features, real
  guardrails.

**Honest limits, taught plainly:** it invents APIs; it writes insecure queries; it doesn't
know your schema; it produces confident wrong numbers; it can't know your rules. The
reader's advantage in an interview is being the person who can tell.

## §14. The chapter wrapper

Each chapter is a folder with an index page plus five parts, in order:
**A. Overview** — what you'll learn, why it exists, how it follows the previous chapter's
final hook, the lesson list with one-line promises, and a media hook.
**B. The lessons**, in order, one idea each.
**C. Glossary** — this chapter's new terms as chips.
**D. Interview Q&A** — the questions this material produces, answered short and confident,
**with code that proves it**.
**E. Exercises** — challenge-flavoured, with stakes and personality: "do this in one query
with no subquery," "this runs but the number's wrong — find out why," a broken page to
rescue, a real dean's question. Never a boring drill. Reader attempts first; solutions behind
a reveal, in two versions.

## §15. References and media

One or two `go-deeper` references per lesson, maximum. Facts go to official docs cited by
name: the Java documentation, the MySQL and PostgreSQL manuals, MDN, the React and Next.js
docs, the TypeScript handbook, the Supabase and Vercel docs. Name the web-skills roadmap
(`andreasbm.github.io/web-skills`) once, in the Part C overview, as the map Part C covers.

**Never invent a URL.** Leave a slot:
```
[VIDEO HOOK: <what it should show> | suggested: <search terms> | fill: data-src=""]
[PHOTO: <what it should show> | suggested: <search terms> | fill: src="" credit=""]
```
Place them at the course opening, each track overview, the history lessons, the analogy
anchors (a phone book for indexes, a switchboard for DNS, a plate stack for a stack), and
every project milestone.

---

# PART A — FOUNDATION
### *Nothing is allowed to be magic*

*Why this part exists: you cannot build well on a machine you think is magic, and you cannot
work like a professional through a graphical interface alone. No programming yet. First,
understand the machine and learn to command it.*

## TRACK A1 — THE MACHINE

### Chapter A1.1 — What is actually happening in there

1. **The story of computing** `[C]` — from mechanical calculators and Ada Lovelace's first
   program, through Turing and the idea of computation, the transistor, the chip, the
   personal computer, the internet, and where AI arrives at the end of that story. *(History
   video hook.)*
   → *Every one of those machines did the same fundamental thing. What is a computer
   actually doing, underneath all of it?*
2. **What a computer actually does** `[C]` `[DIAGRAM]` — instructions, memory, input and
   output; a machine unimaginably fast and completely literal.
   → *A computer only understands electricity on or off. So how does anything you type ever
   become something it can run?*
3. **Bits, bytes and binary** `[C]` `[DIAGRAM]` — counting in twos, hexadecimal, how numbers
   are stored, how text becomes numbers.
   → *Numbers are one thing. How do a few billion on-off switches ever add two of them
   together?*
4. **Logic gates** `[C]` `[DIAGRAM]` — AND, OR, NOT, XOR; truth tables; building an adder out
   of gates.
   → *So the machine can compute. What tells it which computation to do, and in what order?*
5. **The CPU and the fetch-execute cycle** `[C]` `[DIAGRAM]` — registers, the instruction
   cycle, the clock, what one instruction really is.
   → *Instructions have to come from somewhere and results have to go somewhere. Where does a
   running program actually live?*
6. **Memory and storage** `[C]` `[DIAGRAM]` — RAM versus disk, volatile versus persistent,
   why one forgets and one doesn't, and roughly how much slower disk is.
   → *You have a machine that can run instructions. Who decides which of the hundred programs
   on it goes next?*
7. **What an operating system is for** `[C]` — the manager between your program and the
   hardware, and a first look at processes and files. *(A survey; the full track comes later.)*
   → *Everything you've ever done with a computer was through pictures and clicks. Underneath
   there's a faster door, and every professional lives behind it.*

### Chapter A1.2 — The terminal

8. **Your first terminal session** `[T]` — opening it, the prompt, `pwd`, `ls`, `cd`; what a
   path is; absolute versus relative.
   → *You can look around. How do you make, move and destroy things — and how do you avoid
   destroying the wrong thing?*
9. **Creating, moving, deleting** `[T]` `[SAFETY]` — `mkdir`, `touch`, `cp`, `mv`, `rm`, and
   the honest warning about `rm -rf`.
   → *You can manage files. How do you read what's inside one without opening an editor?*
10. **Reading, searching, piping** `[T]` — `cat`, `less`, `head`, `tail`, `grep`, `find`,
    pipes and redirection, and the idea that small tools chain together.
    → *Some commands refuse to run and say "permission denied." Who is stopping you, and why?*
11. **Permissions, users and processes** `[T]` — `chmod`, ownership, `ps`, `top`, `kill`, and
    what `sudo` really means.
    → *Everything so far happened on your own machine. Real code runs on someone else's. How
    do you get there?*
12. **Remote machines from the terminal** `[T]` — `ssh`, `curl`, `ping`, and your first look
    at a server that isn't yours.
    → *You've been typing commands and losing them, and editing files with no way back. Every
    professional keeps a permanent, reversible history of every change they have ever made.*

### Chapter A1.3 — Git and GitHub

13. **Why version control exists** `[C]` — the folder full of `final_v2_FINAL.zip`, and what a
    commit actually is.
    → *So a commit is a save point with a message. How do you make one?*
14. **Your first repository** `[T]` — `git init`, `status`, `add`, `commit`, `log`; the staging
    area and why it exists.
    → *You have a history on your laptop. What happens the day the laptop dies?*
15. **GitHub and remotes** `[T]` — `remote add`, `push`, `pull`, `clone`, SSH keys; your public
    portfolio starts here.
    → *You want to try something risky without breaking what already works. Can you have two
    versions at once?*
16. **Branching and merging** `[T]` `[DIAGRAM]` — `branch`, `checkout`, `merge`, and what a
    merge conflict looks like and how to resolve it without panic.
    → *You committed something you shouldn't have, or lost work you needed. Is it gone?*
17. **Undoing things safely** `[T]` `[SAFETY]` — `restore`, `revert`, `reset` and exactly what
    each one destroys; `stash`; why `push --force` ends friendships.
    → *Your workshop is ready and your work is safe. Time to make the machine do something —
    which means learning to speak to it.*

### Chapter A1.4 — How software gets made

18. **What a program is** `[C]` — idea versus algorithm versus code; an admissions decision
    written as plain-English steps before a single line of code.
    → *If a program is just a list of steps, why can't you write those steps in English and
    hand them over?*
19. **What a programming language is** `[C]` — syntax versus meaning, keywords, grammar, and
    why human language fails at this. *(History: from machine code to the modern era.)*
    → *You can write something that looks like code. The machine still can't read a word of
    it. What stands between the two?*
20. **How code becomes something that runs** `[C]` `[DIAGRAM]` `[T]` — compilers,
    interpreters, machine code, bytecode, and a virtual machine; what actually happens when
    you compile.
    → *Enough theory about languages in general. You need one — and the one you learn first
    should be the one that shows you the machine instead of hiding it.*

**Part A close:** a command cheat sheet, a glossary, an interview Q&A block (what is a
process, what does a commit contain, compiled versus interpreted, how do you resolve a
conflict), and an exercise set — set up the `university-management-app` repository you'll use for the rest
of the course, make a mess in it, and recover.

---

# PART B — CORE COMPUTER SCIENCE ENGINEERING
### *The degree. This is what makes an engineer.*

*Why this part exists: frameworks churn every two years; none of this does. Data structures,
algorithms, operating systems, networks, architecture and database theory are what let you
reason about any system, debug what nobody else can, and answer the questions interviews are
actually built on. Everything in Part C rests on this. Do not skip it, and do not let the
reader skip it.*

**Language note:** Part B is taught in **Java** — strict enough to make types, memory and
objects visible, and still one of the most-hired languages on the server side. Every data
structure is implemented **by hand** before any library is used. No frameworks, no web, no
third-party libraries anywhere in Part B; persistence is plain files. Say so in one line when
it comes up.

## TRACK B1 — PROGRAMMING (in Java)

### Chapter B1.1 — First words

21. **Why Java, and the pieces of it** `[C]` `[T]` — the problem Java solved, where it runs
    today, an honest comparison with Python and JavaScript; JDK versus JRE versus JVM;
    installing it; `javac` and `java` by hand before any editor hides them. *(History hook.)*
    → *It ran, but only once you wrapped everything in a class and a method called `main`. Who
    calls `main`, and why that exact shape?*
22. **The anatomy of a program** `[C]` — classes, `main`, statements, setting up the editor,
    and reading your first error message without fear.
    → *Your program prints the same line forever. To do anything useful it has to remember
    things. Where does a remembered value actually live?*
23. **Variables and the primitive types** `[C]` — declaration, assignment, literals, the eight
    primitives, `var`.
    → *Copy a number into another variable and change it — the original is untouched. Do the
    same with an object and the original changes too. Same syntax, opposite result.*
24. **Stack, heap and references** `[C]` `[DIAGRAM]` — what a variable really holds, what a
    reference is, what `null` means. **This diagram is used for the rest of the course.**
    → *Now you can store values. Time to calculate — except `7 / 2` gives `3`, and `0.1 + 0.2`
    isn't `0.3`.*
25. **Operators** `[C]` — arithmetic, comparison, logical, precedence; integer division; why
    floating point lies and what you use for money.
    → *Putting an `int` into a `double` works silently. The reverse refuses to compile.
    Something is deciding what's allowed.*
26. **Types, conversion and casting** `[C]` — widening, narrowing, overflow, and when the
    compiler refuses to guess for you.
    → *Every value so far was typed in by you. A program nobody can talk to isn't much of a
    program.*
27. **Input and output** `[C]` — reading numbers and text, formatting output, and what happens
    when a user types something you didn't expect.
    → *Input, arithmetic, output, storage. That's genuinely enough to write real programs. Can
    you write ten?*
28. **Ten warm-up programs** `[P]` — swap two scores, odd/even, largest of three marks, simple
    interest on a fee instalment, room area, temperature, percentage and grade, leap year, sum
    to N, a multiplication table.
    → *Several of them needed text — and comparing two identical-looking applicant names with
    `==` said they were different.*

### Chapter B1.2 — Text, decisions and repetition

29. **Strings** `[C]` `[DIAGRAM]` — text as objects, immutability, the string pool, `==` versus
    `equals`, and the methods you'll actually use.
    → *If a string can never change, what is happening when you build one inside a loop that
    runs ten thousand times?*
30. **Building text efficiently** `[C]` — why concatenating in a loop is slow, the builder,
    formatted printing, multi-line text.
    → *You can take text apart and rebuild it. Reverse it, check if it reads the same
    backwards, count the repeats. The classics.*
31. **Classic string programs** `[P]` — reverse, palindrome, vowel and consonant counts,
    character frequency, remove duplicates, anagram check, capitalise each word, longest word —
    over applicant names and program codes.
    → *Every one made a decision, and small ones so far. What happens when there are ten
    possible answers instead of two?*
32. **Conditionals** `[C]` — `if`/`else`, nesting, the ternary, and writing conditions a human
    can read.
    → *Ten branches means ten `else if` lines and it reads terribly. For a fixed, known set,
    there's something built for exactly this.*
33. **switch** `[C]` — the classic form, fall-through, and modern switch expressions.
    → *You have input, decisions and output. But everything runs once and stops.*
34. **Loops** `[C]` — `for`, `while`, `do-while`, the enhanced form, and how to choose.
    → *Your loop ran one time too many and printed a blank row. A different one never stopped
    at all.*
35. **Loop control and the off-by-one** `[C]` `[DIAGRAM]` — `break`, `continue`, nested loops,
    and why the boundary is where the bug always lives.
    → *With loops you can write every program schools and interviewers have asked for over
    decades — factorials, primes, Fibonacci.*
36. **Classic number programs** `[P]` — factorial, Fibonacci, prime check, list of primes,
    Armstrong, palindrome number, reverse digits, digit sum, GCD and LCM, perfect number.
    → *Each used a single loop. What can you do with a loop inside a loop? Every printed
    pyramid you've ever seen, for a start.*
37. **Pattern printing** `[P]` — triangles, pyramids, inverted, diamonds, number and letter
    patterns; the traditional nested-loop test.
    → *Everything so far handles one value at a time. Thirty applicants would mean thirty
    variables named `applicant1` through `applicant30`.*

### Chapter B1.3 — Methods and recursion

38. **Arrays** `[C]` `[DIAGRAM]` — fixed size, zero-indexing, iteration, and the out-of-bounds
    exception.
    → *An array is one row. A timetable is a grid. Can an array hold other arrays?*
39. **Two-dimensional arrays** `[C]` — arrays of arrays, and the utility methods that come free.
    → *Your `main` is two hundred lines and the same block of code appears three times.*
40. **Methods** `[C]` — signatures, parameters, return values; splitting code into named pieces
    is most of the job.
    → *You need the same calculation for whole numbers and decimals. Two methods means two
    names, and `calculateAverageDouble` is ugly.*
41. **Overloading, and what "pass by value" really means** `[C]` `[DIAGRAM]` — same name,
    different parameters; what Java actually hands a method. This surprises everyone.
    → *A method can call another method. Nothing stops a method calling itself. What would that
    even do?*
42. **Recursion** `[C]` `[DIAGRAM]` — a method that calls itself, the base case, the call stack
    growing, and the stack overflow. *(Use the step-through visualiser.)*
    → *Elegant, and dangerous. The classics show both sides.*
43. **Recursion programs** `[P]` — factorial, Fibonacci, digit sum, reverse a string, Towers of
    Hanoi, and an honest note on when recursion is the wrong tool.
    → *You've typed `static` before every method without knowing what it does — and a variable
    declared inside a loop vanishes when the loop ends.*
44. **Scope, static and final** `[C]` — where a name is visible, class versus instance members,
    constants, and when `static` is a smell.
    → *That stack overflow — you don't have to imagine it. You can watch it happen, one frame
    at a time.*
45. **Finding a bug** `[D]` `[T]` — syntax versus runtime versus logic errors; print debugging;
    then the real debugger — breakpoints, step over and into, watches — on a loop with a wrong
    total and on a runaway recursion.
    → *Your program keeps names in one array and scores in another, matched by position. Sort
    one and forget the other, and everyone gets someone else's marks.*

### Chapter B1.4 — Objects
*Open with the honest warning: this is the hardest conceptual jump so far. It took the
narrator weeks. Finding it hard is normal.*

46. **Classes and objects** `[C]` `[DIAGRAM]` — blueprint versus instance, and what `new` really
    does in memory.
    → *You made an object and its fields were all zeros, nulls and falses. Nothing you wanted.
    How does an object get born already correct?*
47. **Constructors and `this`** `[C]` — creating and initialising an object, chaining, and the
    order things happen in.
    → *Your constructor guarantees a valid applicant — and then any line anywhere can set the
    score to minus fifty, and nothing stops it.*
48. **Encapsulation** `[C]` — private fields, getters and setters done properly, why a public
    field is a bug, and what an invariant is.
    → *Staff need a name, an email and a phone too. Copy those fields into a second class — or
    tell Java "the same as that one, plus a few extras."*
49. **Inheritance** `[C]` `[DIAGRAM]` — `extends`, what a subclass gets free, `protected`, and
    what inheritance quietly costs.
    → *Your subclass constructor can't reach the parent's private fields. So who sets them, and
    when?*
50. **super and construction order** `[C]` `[DIAGRAM]` — how an object is built from the parent
    down.
    → *`Applicant` and `Reviewer` both have `describe()`. Put both in a parent-typed variable and
    call it. Which one runs?*
51. **Polymorphism** `[C]` `[DIAGRAM]` — overriding, dynamic dispatch, one call behaving
    differently depending on what's really there.
    → *Through that parent-typed variable you can't reach the child's own methods. Getting back
    down is possible — and it's where the class-cast exception comes from.*
52. **Casting and instanceof** `[C]` — upcasting, downcasting, the exception, and pattern
    matching.
    → *A plain `User` isn't a real thing — every user is an applicant or a staff member. Can you
    stop anyone creating one at all?*
53. **Abstract classes** `[C]` — abstract methods, partial implementations, when one is right.
    → *A class can extend only one parent. What about something that must be both notifiable and
    scoreable, when those two share nothing else?*
54. **Interfaces** `[C]` `[DIAGRAM]` — contracts, implementing several at once, default and
    static methods.
    → *An interface with default methods looks a lot like an abstract class. Which do you use,
    and why does it matter?*
55. **Interface or abstract class, and composition** `[C]` `[DIAGRAM]` — the decision stated
    plainly; "has a" versus "is a"; delegation; why deep hierarchies rot.
    → *You put two applicant objects with identical details in a list, asked Java whether
    they're equal, and it said no.*
56. **toString, equals and hashCode** `[C]` — what every class inherits, the contract between
    equals and hashCode, and exactly what breaks when you honour one and not the other.
    → *Everything since classes is what interviewers actually ask about. Can you defend it?*
57. **The object-oriented questions you'll be asked** `[Q]` — overloading versus overriding;
    overriding a static method; why Java forbids multiple inheritance; equals without hashCode;
    abstract class versus interface — each answered with code that proves it.
    → *You called a method on something and Java threw the most common error in the language at
    you.*

### Chapter B1.5 — Robust programs

58. **NullPointerException** `[C]` `[DIAGRAM]` — why it happens, reading the detailed message,
    finding the actual null in a chain, and fixes that aren't just another null check.
    → *A user typed a letter into a number field. That isn't your bug — and the program still
    died.*
59. **Exceptions** `[C]` `[DIAGRAM]` — the hierarchy, checked versus unchecked, `try`, `catch`,
    `finally`.
    → *Java answered with two hundred lines of red text. Somewhere in there is the one line that
    matters.*
60. **Reading a stack trace** `[D]` — bottom-up, finding your own code inside someone else's,
    what "caused by" means.
    → *Java's built-in exceptions describe Java's problems, not yours. "Applicant already
    applied" isn't in the JDK — and a file you opened is still open.*
61. **Custom exceptions and try-with-resources** `[C]` — designing an exception worth throwing,
    auto-closing, and why a lone catch-everything is dangerous.
    → *Does `finally` always run? What if there's a `return` inside it? These have trapped people
    for twenty years.*
62. **The exception questions you'll be asked** `[Q]` — checked versus unchecked, whether
    `finally` always runs, `return` inside `finally`, catching an `Error`, exception chaining.
    → *Your storage class holds applicants. You need one for programs too. Copy the file and
    change the type?*
63. **Generics** `[C]` `[DIAGRAM]` — why they exist, type parameters, writing a reusable
    container, bounded types and wildcards, and what type erasure throws away. *(Warn: abstract
    and slippery. Split freely.)*
    → *You've written your own container. Java ships an entire library of them — but before you
    use someone else's, you should know how they're built.*

### Chapter B1.6 — Enums, records, files and tests

64. **Enums** `[C]` — fixed sets of values (the `Status` set), fields and methods on constants,
    and why they beat string constants.
    → *An enum is a fixed set. What about a small bundle of data that should never change once
    created — is that really sixty lines of boilerplate?*
65. **Records and immutability** `[C]` — what a record writes for you, and why immutable objects
    cause fewer bugs.
    → *Your project is dozens of files in one folder. And what exactly would you send someone who
    wanted to run it?*
66. **Packages, access and a runnable JAR** `[C]` `[T]` — organising a codebase, the four access
    levels, and packaging it so a stranger can run it.
    → *You've created thousands of objects and deleted none. In some languages that's a
    catastrophe.*
67. **Garbage collection** `[C]` `[DIAGRAM]` — how Java frees memory, what "unreachable" means,
    why there's no manual free, and what a memory leak still looks like here.
    → *Close the program and everything it knew is gone. The computer has a disk.*
68. **Files** `[C]` `[DIAGRAM]` — reading and writing text, paths, buffering, and doing it
    safely.
    → *You claim your shortlisting logic works. How would you prove it — to a reviewer, or to
    yourself three weeks from now?*
69. **Testing** `[C]` `[T]` — why tests exist, assertions, arrange-act-assert, parameterised
    tests, and running them from the terminal.
    → *A test that passes on code you wrote yesterday proves nothing until you watch one fail.*
70. **Testing properly** `[D]` `[P]` — write tests for the eligibility rules, then deliberately
    break the code and watch a test catch it before you do.
    → *Your program works, it's tested, and it stores five hundred applicants in an array. Add
    one more and everything has to be copied. Search it and you check every single entry.*
71. **The memory questions you'll be asked** `[Q]` `[DIAGRAM]` — stack versus heap, the string
    pool, shallow versus deep copy, how garbage collection decides.
    → *You can program now. But "it works" and "it works on five hundred thousand records" are
    completely different claims — and the second one is what you get paid for.*
72. **Concurrency, honestly** `[C]` `[DIAGRAM]` — process versus thread, a race condition shown
    live, synchronisation, thread pools, and a clear statement of what a beginner needs versus
    what interviews ask. *(Revisited properly in the operating systems track.)*
    → *Everything you've written stores data in whatever way was convenient. There's a whole
    science about which way is right — and it's the single most-tested subject in this field.*

**Track close:** glossary, the Java interview Q&A, and the fun set — the console grade tool,
built properly, with tests.

---

## TRACK B2 — DATA STRUCTURES
*Why this track exists: the difference between a program that works and a program that works
on real data is almost always the structure you chose. This is the heart of the degree and the
core of every coding interview. **Every structure is implemented by hand first**, then compared
with the library version. Use the step-through visualiser in every lesson.*

### Chapter B2.1 — Cost, and the simplest structures

73. **How to measure a program** `[C]` `[DIAGRAM]` — counting operations, not seconds; how a
    program behaves as the data doubles; best, average and worst case.
    → *You have a way to measure. Now you need the notation everyone uses to write it down —
    and the interview question that follows within thirty seconds.*
74. **Big-O notation** `[C]` `[DIAGRAM]` — constant, logarithmic, linear, linearithmic,
    quadratic, exponential; dropping constants; the curves compared on one chart.
    → *You can name a cost. What about the memory a program uses while it runs?*
75. **Space complexity** `[C]` — the other half of the trade-off, and why an extra array is
    sometimes exactly the right answer.
    → *You already know arrays. Now measure one honestly: what does it cost to read, to search,
    to insert in the middle, to grow?*
76. **Arrays, measured** `[C]` `[DIAGRAM]` — contiguous memory, constant-time access, and the
    expensive middle-insert; how a growable array doubles.
    → *Inserting an applicant in the middle of an array shuffles everything after it. What if
    each record just pointed at the next one?*
77. **Linked lists** `[C]` `[DIAGRAM]` — nodes and pointers, building one by hand, insert and
    delete, and the price you pay: no random access.
    → *You can walk forward through the list. A reviewer wants to step back one.*
78. **Doubly and circular linked lists** `[C]` `[DIAGRAM]` — two pointers per node, and the
    round-robin reviewer queue.
    → *Two classic interview questions live here, and they both look impossible until you see
    the trick.*
79. **Linked list problems** `[P]` — reverse a list, find the middle in one pass, detect a
    cycle, merge two sorted lists, remove the Nth from the end.
    → *Sometimes you don't want the middle at all. Sometimes the only thing that matters is the
    most recent thing you added.*
80. **Stacks** `[C]` `[DIAGRAM]` — last in, first out; implement one on an array and on a list;
    the call stack you already met is exactly this.
    → *Where would you actually use one? Every undo button, every balanced-bracket check, and
    every expression a compiler ever read.*
81. **Stack problems** `[P]` — balanced brackets in a form definition, undo in the marks editor,
    evaluate an expression, the next greater element.
    → *Last in, first out is wrong for a queue of applications. The first one to arrive should be
    reviewed first.*
82. **Queues** `[C]` `[DIAGRAM]` — first in, first out; the naive array version and why it
    wastes space; the circular buffer that fixes it.
    → *A queue takes from the front and adds at the back. What if you need both ends?*
83. **Deques and priority queues** `[C]` `[DIAGRAM]` — the double-ended queue, and the queue
    that always hands you the highest score next.
    → *Everything so far is a line. A university is not a line — a department contains programs
    which contain subjects.*

### Chapter B2.2 — Trees

84. **Trees** `[C]` `[DIAGRAM]` — nodes, root, children, leaves, height and depth; the
    department-program-subject hierarchy as a tree.
    → *You have a tree. How do you visit every node in it — and why are there four different
    answers?*
85. **Tree traversals** `[C]` `[DIAGRAM]` `[P]` — in-order, pre-order, post-order and
    level-order, recursively and iteratively, and what each one is actually good for.
    → *Walking every node to find one student is no better than a list. Unless the tree is
    arranged so half of it can be ignored at every step.*
86. **Binary search trees** `[C]` `[DIAGRAM]` — the ordering property, insert, search, delete;
    logarithmic lookup, and building one by hand.
    → *You inserted the applicants in order of registration number and your tree became a
    straight line. Every advantage, gone.*
87. **Balance, and why it matters** `[C]` `[DIAGRAM]` — the degenerate tree, rotations, and the
    idea of self-balancing (AVL and red-black at the level you'll actually be asked about).
    → *Databases don't use these trees. They use one with hundreds of children per node, and the
    reason is the disk you learned about in Part A.*
88. **B-trees, and why databases use them** `[C]` `[DIAGRAM]` — wide, shallow trees, disk pages,
    and the direct line to the index chapter later.
    → *A different kind of tree doesn't sort at all — it just guarantees the biggest thing is
    always on top.*
89. **Heaps** `[C]` `[DIAGRAM]` — the heap property, an array-backed tree, insert and extract,
    and how the priority queue you used earlier really works.
    → *Every structure so far had to search. What if you could compute where something is,
    instead of looking for it?*

### Chapter B2.3 — Hashing, graphs and choosing

90. **Hash tables** `[C]` `[DIAGRAM]` — the hash function, buckets, and constant-time lookup;
    building one by hand.
    → *Two different applicant emails produced the same bucket. Now what?*
91. **Collisions** `[C]` `[DIAGRAM]` — chaining and open addressing, the load factor, resizing,
    and why a bad hash function turns your table back into a list.
    → *This is exactly why `equals` and `hashCode` had to agree, back in the objects chapter.*
92. **Sets and maps built on hashing** `[C]` — the two structures you'll reach for most, what
    each guarantees, and what ordering you do and don't get.
    → *A university has students who take subjects taught by faculty in departments — and none
    of that is a tree. Things point at each other in every direction.*
93. **Graphs** `[C]` `[DIAGRAM]` — vertices and edges, directed and undirected, weighted;
    adjacency list versus matrix and the memory trade-off; the prerequisite graph of a degree.
    → *You can store the connections. How do you actually walk them — and why are the two ways
    of walking so different in what they find?*
94. **Breadth-first search** `[C]` `[DIAGRAM]` `[P]` — the queue-based frontier, shortest path in
    an unweighted graph, level by level.
    → *Breadth-first fans out. What if you follow one path all the way to the end first?*
95. **Depth-first search** `[C]` `[DIAGRAM]` `[P]` — the stack (or recursion), cycle detection,
    and connected components.
    → *Your prerequisite graph has a cycle: subject A requires B, which requires A. Nobody can
    ever graduate. How do you order a graph that has no cycles?*
96. **Topological sort** `[C]` `[DIAGRAM]` — ordering the prerequisite graph, and detecting the
    impossible curriculum.
    → *Nine structures now, and they all promise to store your applicants. Choosing wrong is how
    a system that worked in testing dies in production.*
97. **Choosing the right structure** `[C]` `[DIAGRAM]` — the operations table: access, search,
    insert, delete for every structure you've built, with the real-world question each one
    answers.
    → *You can choose the right container. You still can't choose the right *procedure* — and
    that's a whole subject of its own.*
98. **Data structure problems** `[P]` — build a frequency map of grades, find the first
    non-repeating character in a name, detect duplicate applications, the top N by score, group
    students by program, an LRU cache for the dashboard.
    → *Every one of those got easier once the data was in the right shape. The next question is
    what you do with it — starting with the most fundamental operation in computing.*

### Chapter B2.4 — Implementations and interviews

99. **Implementing a list, a stack and a queue** `[P]` — hand-built, tested, and compared against
    the library versions for speed.
    → *Your hand-built map is correct and three times slower than the library's. Why?*
100. **Implementing a hash map** `[P]` `[D]` — buckets, chaining, resizing, and profiling it
     against the real one.
     → *You built it. Now open the library's source and read what the professionals did
     differently.*
101. **Reading the library's implementation** `[C]` — the growable list, the hash map, the tree
     map; what the source teaches that no tutorial can.
     → *You know how they're built. Interviewers won't ask you to build one — they'll ask you
     which one, and why.*
102. **The data structure questions you'll be asked** `[Q]` `[DIAGRAM]` — array versus linked
     list; how a hash map works internally, and what happens on a collision and a resize; hash
     set versus tree set; when a heap beats a sorted list; why the equals/hashCode contract
     exists; what a balanced tree buys you.
     → *That's how data is held. Now — how is it processed?*
103. **Iterators and safe iteration** `[C]` — walking any structure the same way, and the error
     you get when you modify a collection while looping over it.
     → *You can walk anything. But walking things in the right order is a subject with a hundred
     years of history behind it.*
104. **Sorting things that aren't numbers** `[C]` — comparable and comparator, sorting applicants
     by score then name, chaining comparisons, and what a stable sort guarantees.
     → *Every sort you've used was written by someone else. To reason about cost — and to survive
     the interview — you have to know how they work.*

**Track close:** glossary, the data structures interview Q&A, and the fun set — implement one
structure from scratch, prove it works with tests, and race it against the library version.

---

## TRACK B3 — ALGORITHMS
*Why this track exists: the right structure with the wrong procedure is still slow. This is the
single most-tested subject in technical interviews, and it's also how you learn to reason about
a problem before writing a line.*

105. **What an algorithm is** `[C]` — a procedure with a proof; correctness and cost; and why
     "it works on my test case" is not an argument.
     → *The simplest algorithm anyone writes is looking for something. There are two ways, and
     the difference between them is enormous.*
106. **Linear and binary search** `[C]` `[DIAGRAM]` `[P]` — checking every element versus halving
     the range; the precondition everyone forgets; the off-by-one that makes it loop forever.
     → *Binary search needs sorted data. So how does data get sorted?*
107. **The simple sorts** `[C]` `[DIAGRAM]` `[P]` — bubble, selection and insertion; why they're
     quadratic; and the one situation where insertion sort actually wins.
     → *Quadratic on five hundred applicants is fine. On five hundred thousand it's a coffee
     break. There's a better idea, and it comes from splitting the problem in half.*
108. **Divide and conquer, and merge sort** `[C]` `[DIAGRAM]` — the recursive split, the merge
     step, guaranteed linearithmic time, and the extra memory it costs.
     → *Merge sort is reliable and hungry for memory. There's a sort that usually beats it and
     occasionally falls apart.*
109. **Quick sort** `[C]` `[DIAGRAM]` — partitioning, the pivot, average versus worst case, and
     why your library probably uses a hybrid.
     → *Sorting rearranges everything. What if you only need the top ten applicants and don't
     care about the rest?*
110. **Selection, and sorting without comparisons** `[C]` — finding the Nth largest efficiently;
     counting sort when the range is small; and the theoretical floor on comparison sorting.
     → *You can search and sort. Now the class of problems where you make the obvious choice at
     every step — and it's sometimes exactly right, and sometimes catastrophically wrong.*
111. **Greedy algorithms** `[C]` `[P]` — the interval-scheduling of exam rooms, coin change, and
     the honest part: proving a greedy choice is safe, and seeing one fail.
     → *Greedy fails when today's best choice ruins tomorrow. What if you could try every path
     without paying for it twice?*
112. **Recursion, revisited as a technique** `[C]` `[DIAGRAM]` — the recurrence, the tree of
     calls, and how to see the repeated work.
     → *Your recursive Fibonacci computed the same value thousands of times. If you'd written the
     answers down, it would have been instant.*
113. **Memoisation and dynamic programming** `[C]` `[DIAGRAM]` — top-down with a cache,
     bottom-up with a table, and the two conditions a problem needs before this works. **Warn:
     this is the topic people find hardest. Go slow.**
     → *You've seen the technique on one problem. It's a way of thinking, and it takes about six
     problems before it clicks.*
114. **Dynamic programming problems** `[P]` — climbing stairs, coin change, the knapsack of exam
     slots, longest common subsequence of two transcripts, edit distance on applicant names.
     → *Some problems can't be built up from smaller answers. Sometimes you have to try
     everything — carefully.*
115. **Backtracking** `[C]` `[DIAGRAM]` `[P]` — building candidates and abandoning them early;
     permutations, subsets, N-queens, and the timetable-assignment problem.
     → *Backtracking searches a space of choices. Graphs are a space of choices too — and there
     are famous algorithms for walking them well.*
116. **Shortest paths** `[C]` `[DIAGRAM]` — Dijkstra explained properly with the campus map;
     why negative weights break it; and where you'd actually meet this.
     → *Shortest path connects two points. What if you need to connect everything as cheaply as
     possible?*
117. **Minimum spanning trees and union-find** `[C]` `[DIAGRAM]` — the campus network cabling
     problem; the disjoint-set structure that makes it fast.
     → *You know a dozen named algorithms. In an interview nobody asks for the name — they
     describe a problem and wait.*
118. **Recognising the pattern** `[C]` — the handful of shapes that most problems collapse into:
     two pointers, sliding window, fast and slow pointers, hashing for lookup, sort-then-scan,
     binary search on the answer, breadth-first for shortest, dynamic programming for optimal.
     → *A pattern is a hypothesis. The way you turn it into a solution has its own method.*
119. **How to solve a problem you've never seen** `[C]` — restate it, find the simplest example,
     brute force first, find the repeated work, then optimise; and how to do all of it out loud.
     → *You have a method. Now use it on the problems that come up most.*
120. **Array and string patterns** `[P]` — two pointers, sliding window, prefix sums, in-place
     reversal, over score arrays and applicant names.
     → *Those were linear scans. The next set need you to remember what you've already seen.*
121. **Hashing patterns** `[P]` — two-sum, group anagrams of subject codes, longest substring
     without repeats, duplicate detection across intakes.
     → *Those all had one right answer. The hard ones have a whole space of answers.*
122. **Tree and graph patterns** `[P]` — tree depth and diameter, validate a search tree, level
     order, number of islands, course prerequisites, shortest path in the campus map.
     → *You can solve them. Can you solve them in forty minutes, on a whiteboard, while
     explaining yourself?*
123. **Practising like it's an interview** `[P]` `[Q]` — timed practice, thinking aloud, handling
     being stuck, and what an interviewer is actually scoring (it is not "did you finish").
     → *Algorithms are proved with mathematics. You've been taking that mathematics on trust.*
124. **The algorithm questions you'll be asked** `[Q]` — complexity of every sort and search;
     when quick sort degrades; recursion versus iteration; how you'd find a duplicate in a
     million records; how you'd design a cache; what dynamic programming actually is.
     → *Every claim in this track rested on a proof you haven't seen. Time to see them.*

**Track close:** glossary, the algorithms interview Q&A, and the fun set — a timed set of six
problems, then a written explanation of your approach to each, in plain English.

---

## TRACK B4 — THE MATHEMATICS AND THE THEORY
*Why this track exists: this is the language every algorithm and every proof is written in. It
is also the shortest track, because the goal is fluency in what you'll actually use, not a
mathematics degree. Keep it concrete and keep it in the university.*

125. **Why a programmer needs mathematics** `[C]` — not the mathematics you were taught at
     school; the parts that describe computation, stated plainly, with the promise that every
     one of them has already appeared in this course.
     → *You've been writing conditions since the first `if`. There's a formal system behind
     them, and it makes complicated conditions provably right.*
126. **Logic** `[C]` `[DIAGRAM]` — propositions, and/or/not, implication, truth tables,
     De Morgan's laws; simplifying a monstrous eligibility condition without changing what it
     means.
     → *Logic tells you whether a statement is true. Sets tell you what you're talking about.*
127. **Sets and relations** `[C]` `[DIAGRAM]` — membership, union, intersection, difference,
     subsets; the applicants who applied to both programs; and how this is exactly what a
     database join and a `UNION` will turn out to be.
     → *A relation connects two sets. A function is a relation with a rule — and it's the thing
     you've been writing all along.*
128. **Functions, and counting** `[C]` — domain, range, mapping; then permutations and
     combinations: how many ways to seat an exam hall, how many possible timetables.
     → *Counting tells you how big a problem is. Sometimes it tells you the problem is too big
     to brute force — and that's an argument you'll make in interviews.*
129. **Proof techniques** `[C]` — direct proof, contradiction, and induction; proving a loop
     does what you claim; the connection between induction and recursion.
     → *You can prove a program correct. Can you prove one is fast — or that a hash function
     spreads keys evenly?*
130. **Probability and statistics for engineers** `[C]` `[DIAGRAM]` — average, spread,
     distributions; expected value; why the average case of a hash table is what it is; and the
     honest statistics behind "attendance correlates with results."
     → *You've drawn graphs of prerequisites and campus maps. There's a whole branch of
     mathematics about exactly those, and it names everything you did by instinct.*
131. **Graph theory** `[C]` `[DIAGRAM]` — degree, paths, cycles, connectivity, trees as a
     special case, bipartite graphs; and the vocabulary that makes graph algorithms readable.
     → *Mathematics describes what computers do. There's an older question: what can a computer
     do at all?*
132. **Finite automata** `[C]` `[DIAGRAM]` — states and transitions; modelling the application
     status flow as a machine; deterministic and non-deterministic, in plain words.
     → *A state machine that recognises patterns — you use one every time you validate an email
     address, and you've never noticed.*
133. **Regular expressions, and what they are underneath** `[C]` `[T]` — the syntax you'll
     actually use, why they're the same thing as a finite automaton, and the honest warning
     about the ones that hang your server.
     → *Some patterns no state machine can recognise — like matching brackets, which needs
     memory. So there must be more powerful machines.*
134. **Grammars and the hierarchy of languages** `[C]` `[DIAGRAM]` — context-free grammars; how
     a compiler parses your code; why the language hierarchy exists.
     → *If there are more powerful machines, is there a most powerful one — and can it solve
     everything?*
135. **Turing machines and computability** `[C]` `[DIAGRAM]` — the model, what "computable"
     means, and the halting problem stated without hand-waving: some problems are provably
     impossible, and that is not a failure of engineering.
     → *Some problems can't be solved at all. Others can be solved, but not quickly — and
     knowing the difference has made people very rich.*
136. **Tractability, P and NP, in plain words** `[C]` — what "hard" means formally, the
     timetable-scheduling problem as the university's own NP-hard problem, why approximation and
     heuristics are respectable answers, and where cryptography gets its confidence.
     → *That's the theory of what can run. Time to look at what it runs on — the actual silicon
     under everything you've written.*

**Track close:** glossary, interview Q&A (what induction proves, why average and worst case
differ, what a regular expression can't match, what the halting problem means, what NP-hard
means when a recruiter says it), and the fun set — model the admissions status flow as a state
machine and prove no applicant can reach two states at once.

---

## TRACK B5 — COMPUTER ARCHITECTURE
*Why this track exists: you now write programs whose speed you can calculate on paper. Real
speed happens on real hardware, and the gap between the two is where senior engineers live.
This track is why some correct code is a hundred times slower than other correct code.*

137. **From gates to a processor** `[C]` `[DIAGRAM]` — a quick rebuild from Part A: gates,
     adders, registers, the datapath; what a machine instruction really is.
     → *You know what one instruction is. Your Java program is not instructions — so something
     translated it, and you should see what it produced.*
138. **Instruction sets and a little assembly** `[C]` `[T]` — reading a few lines of assembly
     for a loop you wrote; what the compiler did with your code; and why you never need to write
     it but should be able to read it.
     → *Each instruction takes a tick of the clock. So a faster clock means a faster computer —
     except that stopped being true two decades ago.*
139. **The clock, pipelining and parallelism** `[C]` `[DIAGRAM]` — instruction pipelining, why
     branches hurt, superscalar execution, and why the industry went sideways into multiple
     cores instead of faster clocks.
     → *The processor is fast. Memory is not — and the difference is bigger than you think.*
140. **The memory hierarchy** `[C]` `[DIAGRAM]` — registers, L1, L2, L3, RAM, disk; the real
     latency numbers written as human time so the scale lands.
     → *If memory is that slow, how does any program run fast? Something is quietly hiding the
     wait.*
141. **Caches, and why your array was faster than your linked list** `[C]` `[DIAGRAM]`
     `[CORRECTNESS]` — cache lines, locality of reference, hits and misses; and the experiment
     that proves it: two structures with identical Big-O, one many times faster.
     → *That experiment just undermined half of what the algorithms track told you. Both things
     are true, and knowing when each one matters is the point.*
142. **When Big-O lies** `[C]` — constants, cache behaviour, and small inputs; how to reason
     about real performance instead of theoretical performance; and how to talk about this in an
     interview without sounding like you're contradicting yourself.
     → *You can reason about one program on one core. Modern machines have eight, and using them
     is a different problem entirely.*
143. **Multiple cores, and what that costs** `[C]` `[DIAGRAM]` — true parallelism versus
     concurrency, shared memory, why two cores rarely mean twice the speed, and the limit that
     puts a ceiling on it.
     → *Numbers behave strangely at the edges. You saw it with floating point long ago, and it's
     time to understand why.*
144. **How numbers really work** `[C]` `[DIAGRAM]` — two's complement, integer overflow,
     floating point representation, and exactly why a fee total should never be a float.
     → *The processor is one part. The machine is a bus, memory, disks and devices, and a program
     that ignores them is a program that waits.*
145. **Storage, buses and I/O** `[C]` `[DIAGRAM]` — how data actually gets from a disk to your
     variable; solid state versus spinning disk; why an operation that touches the disk is a
     different category of slow.
     → *That's the hardware. Every one of those resources is shared by a hundred programs at once
     — and something has to referee.*
146. **The architecture questions you'll be asked** `[Q]` — what a cache miss costs; why
     sequential access beats random; integer overflow and why money isn't a float; concurrency
     versus parallelism; roughly how long a memory read, a disk read and a network call take.
     → *The referee has a name, and it is the largest piece of software on your machine.*

**Track close:** glossary, interview Q&A, and the fun set — write two programs with identical
complexity, measure the difference, and explain it.

---

## TRACK B6 — OPERATING SYSTEMS
*Why this track exists: every program you will ever write runs on top of one. When something is
mysteriously slow, hangs, leaks, or fails only in production, the answer is almost always here.
This is also the subject that separates a junior from an engineer in interviews.*

### Chapter B6.1 — Processes and scheduling

147. **What an operating system actually does** `[C]` `[DIAGRAM]` — the manager of the machine;
     kernel versus user space; the system call as the door between them; what happens when your
     program asks to read a file.
     → *Your program is one of hundreds running. What is a "running program" from the operating
     system's point of view?*
148. **Processes** `[C]` `[DIAGRAM]` `[T]` — the process, its private memory layout (code, data,
     heap, stack — the same picture from the Java track, now explained), the process table, and
     watching real ones with `ps` and `top`.
     → *Two hundred processes, eight cores. They cannot all be running. So what are they doing?*
149. **The process lifecycle and context switching** `[C]` `[DIAGRAM]` — new, ready, running,
     waiting, terminated; what a context switch saves and restores; and why switching isn't free.
     → *Something has to choose who runs next, thousands of times a second. How does it decide?*
150. **Scheduling** `[C]` `[DIAGRAM]` — first-come, shortest-job, round robin, priority; the
     trade-off between throughput and responsiveness; starvation; and why your machine feels
     smooth.
     → *A process is expensive to create and completely isolated. What if one program needs to do
     two things at once?*
151. **Threads** `[C]` `[DIAGRAM]` — threads inside a process, what they share and what they
     don't, and the direct link back to the concurrency you saw in Java.
     → *Two threads updating the same total gave a different wrong answer every run.*

### Chapter B6.2 — Concurrency, properly

152. **Race conditions** `[C]` `[DIAGRAM]` `[CORRECTNESS]` — the critical section; why
     `total = total + 1` is three operations, not one; the bug reproduced live.
     → *You need to make those three operations indivisible. What guarantees that?*
153. **Locks, mutexes and semaphores** `[C]` `[DIAGRAM]` — mutual exclusion, the semaphore, the
     classic producer-consumer of the application queue, and what "atomic" means.
     → *Two clerks each holding one file the other needs, both waiting forever. Your program just
     stopped, and nothing is broken.*
154. **Deadlock** `[C]` `[DIAGRAM]` — the four conditions, the dining-philosophers problem told
     as a faculty-room story, prevention and detection.
     → *You avoided deadlock and now one thread never gets a turn at all.*
155. **Starvation, livelock and fairness** `[C]` — the subtler failures, and why concurrency bugs
     are the hardest to reproduce.
     → *Locks are one answer. There's another that avoids shared state entirely, and modern code
     leans on it hard.*
156. **Message passing and modern concurrency** `[C]` — not sharing state at all; thread pools;
     asynchronous work; and how this connects to the async JavaScript you'll meet in Part C.
     → *You understand who runs. Now — where does a running program's memory actually come from,
     when the machine doesn't have enough?*

### Chapter B6.3 — Memory, files and the outside world

157. **Memory management** `[C]` `[DIAGRAM]` — allocation, fragmentation, the heap the operating
     system gives your process, and where Java's garbage collector fits into this picture.
     → *Every process thinks it owns the whole memory space. They can't all be right.*
158. **Virtual memory** `[C]` `[DIAGRAM]` — the illusion, page tables, address translation, and
     why it makes isolation and security possible.
     → *If every process thinks it has more memory than exists, what happens when they all try to
     use it?*
159. **Paging and thrashing** `[C]` `[DIAGRAM]` — page faults, swapping to disk, replacement
     policies (and the LRU cache you built in the data structures track, showing up in the
     kernel), and what thrashing feels like from the outside.
     → *Memory disappears when the power goes. Files don't. How does the operating system turn a
     disk into folders and names?*
160. **File systems** `[C]` `[DIAGRAM]` `[T]` — files, directories, inodes, permissions (the ones
     you used in Part A, now explained), links, and journaling.
     → *Reading a file one byte at a time was unbearably slow, and reading it in chunks was fast.
     Why?*
161. **Buffering, caching and I/O** `[C]` `[DIAGRAM]` — the page cache, buffered versus
     unbuffered, blocking versus non-blocking, and why the operating system lies to you about
     when a write finished.
     → *One machine, many programs, one set of files. What stops a student's process reading the
     admin's data?*
162. **Users, permissions and isolation** `[C]` `[SECURITY]` — users and groups, privilege
     levels, why the kernel/user split exists, and the security guarantee underneath it.
     → *Isolation on one machine is one thing. Running a whole second machine inside your machine
     is another — and it's how all modern software is shipped.*
163. **Virtual machines and containers** `[C]` `[DIAGRAM]` `[T]` — the difference explained
     properly, why containers are light, and a first hands-on container, so "it works on my
     machine" stops being an excuse.
     → *You know how one machine runs many programs. Every one of those programs is alone on that
     machine — and alone is useless.*
164. **The operating system questions you'll be asked** `[Q]` `[DIAGRAM]` — process versus
     thread; what a context switch costs; how virtual memory works; deadlock and its four
     conditions; what a system call is; the difference between concurrency and parallelism; what
     a container actually is.
     → *A program alone on a machine can't do much. Everything interesting happens when two
     machines talk.*

**Track close:** glossary, the operating systems interview Q&A, and the fun set — reproduce a
race condition, fix it three different ways, then cause a deadlock on purpose and diagnose it.

---

## TRACK B7 — COMPUTER NETWORKS
*Why this track exists: everything you build from Part C onward is a conversation between
machines. When a page is slow, a request fails, or something works locally and dies in
production, this is the knowledge that finds it. It's also what makes DNS and hosting obvious
later instead of magic.*

165. **Why networks exist, and the layer idea** `[C]` `[DIAGRAM]` — the problem of two machines
     agreeing on anything; layering as the trick that made it possible; the TCP/IP model with the
     OSI model named honestly as the teaching version. *(History hook.)*
     → *Layers are an idea. What actually moves between two machines?*
166. **Packets, and the physical reality** `[C]` `[DIAGRAM]` — data split into packets, frames on
     the wire, bandwidth versus latency, and why distance costs milliseconds nobody can beat.
     → *A packet has to be addressed to somebody. Who?*
167. **Addressing: IP** `[C]` `[DIAGRAM]` `[T]` — IPv4 and IPv6, subnets and masks, public versus
     private addresses, the local network, and looking at your own with the terminal.
     → *You can address a machine. But a machine runs a hundred programs — which one gets the
     packet?*
168. **Ports and sockets** `[C]` `[DIAGRAM]` — the port as the door number, well-known ports, the
     socket as the endpoint of a conversation, and why "port already in use" happens.
     → *Packets can get lost, arrive out of order, or arrive twice. Something has to make a
     reliable conversation out of that mess.*
169. **TCP** `[C]` `[DIAGRAM]` — the handshake, sequence numbers, acknowledgements,
     retransmission, flow and congestion control; reliability, and what it costs in round trips.
     → *All that reliability costs time. Sometimes you'd rather lose a packet than wait for it.*
170. **UDP, and choosing** `[C]` — unreliable and fast, what actually uses it, and the honest
     trade-off table.
     → *You can move bytes reliably between two programs. But nobody types an IP address into a
     browser.*
171. **DNS** `[C]` `[DIAGRAM]` `[T]` — the internet's address book; the resolution chain from
     resolver to root to authoritative; record types (A, AAAA, CNAME, MX, TXT); caching and TTL;
     and looking it all up yourself with `dig`. **This lesson pays for the entire hosting track
     later.**
     → *You have a name and an address and a reliable connection. What do the two machines
     actually say to each other?*
172. **HTTP** `[C]` `[DIAGRAM]` `[T]` — request and response; methods; status codes that mean
     something; headers; statelessness; and making raw requests with `curl` before any browser is
     involved.
     → *HTTP is stateless — every request is a stranger. So how does a website remember you're
     logged in?*
173. **Cookies, sessions and tokens** `[C]` `[DIAGRAM]` — how state is faked over a stateless
     protocol, and the direct line to the authentication you'll build later.
     → *Everything you just sent went across the internet in plain text, readable by anyone in
     between.*
174. **TLS and HTTPS** `[C]` `[DIAGRAM]` `[SECURITY]` — encryption in transit, certificates and
     who vouches for them, the handshake in plain words, and what the padlock does and does not
     promise.
     → *Your request is encrypted and addressed. It still has to cross a dozen machines to get
     there.*
175. **Routing, and what's between you and the server** `[C]` `[DIAGRAM]` `[T]` — routers, hops,
     NAT, firewalls, proxies, load balancers and CDNs — the cast of characters you'll meet in
     production; tracing a real route.
     → *The whole path is fast except one hop, and you can't tell which. How do you actually look
     at network traffic?*
176. **Diagnosing a network problem** `[D]` `[T]` — `ping`, `dig`, `curl -v`, the browser's
     network tab; reading a slow request; distinguishing DNS from connection from server from
     payload.
     → *That's the client asking. What is the machine on the other end actually doing?*
177. **Client and server, and the shapes of an API** `[C]` `[DIAGRAM]` — what a server is; the
     request-response model; REST explained from first principles; and websockets for when the
     server needs to speak first.
     → *You understand every layer between a browser and a server. There's one thing left that
     the whole conversation exists to move: data — and it needs somewhere to live.*
178. **The networking questions you'll be asked** `[Q]` `[DIAGRAM]` — what happens when you type
     a URL and press enter (the whole chain, end to end); TCP versus UDP; what DNS does; HTTP
     status codes; how HTTPS works; what a load balancer is for; stateless versus stateful.
     → *Every one of those requests was fetching data that has to be stored somewhere, reliably,
     forever, and answerable in milliseconds. That's a subject of its own.*

**Track close:** command cheat sheet, glossary, the networking interview Q&A, and the fun set —
trace a single request from your browser to a server and write down every layer it crossed.

---

## TRACK B8 — DATABASES AND SQL
*Why this track exists: your programs forget everything when they stop, and a flat file cannot
answer a question. SQL is the most universal, longest-lived skill in this entire course — the
language has barely changed in decades, which is exactly why it is worth learning properly. This
track teaches the theory **and** the practice, against the real university data.*

**The two disciplines of this track, stated in its first lesson:** (1) **correct, not just
runnable** — a query that executes can still be silently, expensively wrong; (2) **safe** —
there is no undo, so know where you are, select before you write, and never write without a
`WHERE`.

### Chapter B8.1 — Why databases exist

179. **The problem with files** `[C]` `[DIAGRAM]` — your Java program's text file: no structure,
     no concurrency, no integrity, no way to ask a question; what a database management system
     adds and what it costs.
     → *A database has structure. What structure — and why did one particular idea beat every
     alternative?*
180. **The relational model** `[C]` `[DIAGRAM]` — relations, tuples, attributes, keys; the
     mathematics from the sets lesson showing up as tables. *(History hook.)*
     → *If the data is split across tables, something has to connect a student to their exam
     results.*
181. **Keys and relationships** `[C]` `[DIAGRAM]` — primary, candidate, composite and foreign
     keys; one-to-one, one-to-many, many-to-many; referential integrity.
     → *You can describe the shape in English. The database answers only one language.*
182. **What SQL is** `[C]` `[T]` — declarative "what, not how"; the engine plans the how;
     installing a database, connecting from the terminal, and your first query. *(MySQL first;
     the differences from PostgreSQL are named honestly when Supabase arrives.)*
     → *It returned every column of every row and drowned you.*

### Chapter B8.2 — Asking questions

183. **SELECT, FROM and DISTINCT** `[C]` — choosing columns, the result set, collapsing
     duplicates.
     → *Your column header is an unreadable expression, and the rows came back in whatever order
     the database felt like.*
184. **Aliases, expressions, ORDER BY and LIMIT** `[C]` — naming output, computing columns,
     sorting, and taking the top ten.
     → *"Top ten by CGPA" is easy. "The ten with the lowest attendance" needs you to keep only
     the matching rows first.*
185. **WHERE and the operators** `[C]` — comparison on numbers, text and dates; `AND`, `OR`,
     `NOT` and precedence; `IN` and `BETWEEN`.
     → *You want every applicant whose name starts with a letter, or whose email ends in a
     domain. Exact match can't do it.*
186. **LIKE and pattern matching** `[C]` — wildcards, and how case is handled.
     → *You filtered on a salary and rows with no salary vanished — including ones you expected.*
187. **NULL and three-valued logic** `[C]` `[DIAGRAM]` `[CORRECTNESS]` — the absence of a value;
     `IS NULL`; why `= NULL` always fails; NULL in comparisons and in aggregates.
     → *You keep writing five lines to turn an empty value into "Unknown."*
188. **Functions and CASE** `[C]` — text, date and numeric functions; bucketing a timestamp by
     month; `CASE` for grade bands; `COALESCE` for defaults.
     → *You can shape one row beautifully. "How many" and "what's the average" need you to squash
     many rows into one number.*
189. **Aggregates and GROUP BY** `[C]` `[DIAGRAM]` — count, sum, average, min, max; one row per
     group; and the diagram of rows collapsing into buckets.
     → *Counting all rows said 550, but counting one column said 549 on the same table.*
190. **COUNT(\*) versus COUNT(column), and the strict grouping rule** `[C]` `[CORRECTNESS]`
     `[GOTCHA]` — what each ignores; why every non-aggregated column must be grouped.
     → *You can filter rows. Now you want only the groups with more than fifty students — and
     `WHERE` can't see a group's count.*
191. **HAVING, and the order of evaluation** `[C]` `[DIAGRAM]` — filtering groups versus rows,
     and **the single most useful diagram in this track**: `FROM → WHERE → GROUP BY → HAVING →
     SELECT → ORDER BY → LIMIT`.
     → *"Departments with the most backlogs" needs the counts and the department names — and they
     live in two different tables.*
192. **Query practice** `[P]` — twelve real questions a registrar would ask, over one table each.
     → *You've hit the wall. Nearly every interesting question needs two or more tables.*

### Chapter B8.3 — Joins
*Open with the warning: this is what separates "can write a SELECT" from "can query."*

193. **Why the data is split, and INNER JOIN** `[C]` `[DIAGRAM]` — the cost of normalisation, and
     matching rows on a key, drawn row by row.
     → *You joined students to placements and hundreds vanished — the ones never placed.*
194. **LEFT JOIN** `[C]` `[DIAGRAM]` — keeping every row from the left, with empties where there
     is no match.
     → *The unplaced students came back with an empty company — which is exactly how you find
     "never placed."*
195. **Anti-joins** `[C]` — the left-join-where-null pattern: enquiries that never enrolled.
     → *You joined three tables and the row count exploded. You added no data, so where did the
     extra rows come from?*
196. **Join fan-out** `[C]` `[DIAGRAM]` `[CORRECTNESS]` — one row matching many, why your counts
     and sums suddenly lie, and how to spot it before a dean does.
     → *A warning row points at both a student and the faculty member who issued it.*
197. **Self-joins and multi-table joins** `[C]` — a table joined to itself; aliasing; sensible
     join order; the full transcript query; and when `RIGHT` and `CROSS` are genuinely right.
     → *Joins combine tables. But "students who scored above their own department's average"
     needs a value you must compute first.*
198. **Join practice** `[P]` — transcripts, the at-risk shortlist, department scorecards.
     → *"Above the department average" means running one query to get the average and using it
     inside another. Can a query contain a query?*

### Chapter B8.4 — Subqueries and windows

199. **Subqueries** `[C]` `[DIAGRAM]` — scalar and correlated; the inner query that changes for
     every row.
     → *These get slow and dense, and often you only care whether a match exists at all.*
200. **EXISTS, IN and derived tables** `[C]` — existence checks, the three routes to the same
     answer and when each wins, and naming a result set in the `FROM` clause.
     → *This intake and last intake need to be stacked into one list. A join puts tables side by
     side, not on top of each other.*
201. **UNION and set operations** `[C]` — stacking results, deduplicating versus keeping
     everything, and the speed difference.
     → *You've been computing rankings by hand with subqueries. There's a feature built for
     exactly "rank within a group."*
202. **Window functions** `[C]` `[DIAGRAM]` — summary beside detail without collapsing rows;
     `OVER` and `PARTITION BY`. **Warn: a new way of thinking.**
     → *You can show a group's average per row. Ranking within each department needs numbered
     ordering.*
203. **Ranking** `[C]` — row number, rank and dense rank, exactly how each treats ties, and the
     top-N-per-group and latest-per-group patterns.
     → *You can rank. Now you want a running total of fees, month over month.*
204. **Running totals, frames, LAG and LEAD** `[C]` `[DIAGRAM]` — cumulative sums, moving
     averages, and letting a row peek at its neighbours for month-over-month change.
     → *Your best queries now nest three deep. They run, and nobody could read them — including
     you next week.*
205. **CTEs** `[C]` — naming steps with `WITH`, reading top-down, refactoring a nested monster
     into a pipeline; and recursive CTEs for hierarchies.
     → *You've written the perfect at-risk query. You'll want it again tomorrow.*
206. **Views** `[C]` — saving a query as a virtual table; what a view costs; and the honest note
     on materialising results.
     → *You're reading this database expertly. To answer design questions — and to build your own
     — you need to know how it was built.*

### Chapter B8.5 — Designing a database

207. **Data types** `[C]` — integers, decimals, text, dates, timestamps, enums, JSON; why money
     is never a floating-point number (straight from the architecture track).
     → *A column that must be unique, never empty, or auto-numbered — how does the database
     enforce that?*
208. **Constraints** `[C]` — primary and foreign keys, unique, not-null, defaults, checks; the
     database defending its own integrity.
     → *The data is spread across two dozen tables on purpose. What principle decides what goes
     where?*
209. **Normalisation** `[C]` `[DIAGRAM]` — the anomalies that motivate it; first, second and
     third normal form in plain words, using the university schema; and when to denormalise
     deliberately.
     → *You understand the shape. Could you design one from a blank page?*
210. **Designing a schema from requirements** `[C]` `[P]` `[DIAGRAM]` — from a page of plain
     English to an entity-relationship diagram to tables; the full university ER map.
     → *Design is a one-time job. The daily job is inserting, updating and deleting — and one
     line there can destroy a university's records.*
211. **Creating and altering, safely** `[C]` `[T]` `[SAFETY]` — DDL on a copy, migrations, and
     why you never click a change into production without a record of it.
     → *Time to write data — exactly where a missing `WHERE` has ended real careers.*

### Chapter B8.6 — Writing, transactions and speed

212. **INSERT, UPDATE, DELETE** `[C]` `[SAFETY]` — writing data respecting foreign keys; the
     sacred `WHERE`; select-first; upserts; delete versus truncate; and why a refused delete is
     protection, not a bug.
     → *You ran an update, then realised it was wrong. Is it too late?*
213. **Transactions** `[C]` `[DIAGRAM]` — begin, commit, rollback; atomicity; a fee payment and
     its registration succeeding or failing together.
     → *Two staff update the same row at the same moment. Who wins, and what stops chaos?*
214. **ACID and isolation** `[C]` `[DIAGRAM]` — the four guarantees in plain words; the isolation
     levels and the anomalies each one allows; optimistic versus pessimistic locking; and the
     direct line back to the operating systems track.
     → *Your reporting queries feel slow on the big tables. What actually makes a query fast?*
215. **How a query finds rows** `[C]` `[DIAGRAM]` — a full scan versus a lookup, and the phone
     book analogy.
     → *A phone book is fast because it's sorted. What's the database's version?*
216. **Indexes** `[C]` `[DIAGRAM]` — the B-tree from the data structures track, now doing its real
     job; primary versus secondary; composite indexes and the left-most-prefix rule; what your
     keys already gave you free.
     → *You added an index and reads got faster — and writes got slower.*
217. **The cost of indexes, and reading a plan** `[C]` `[T]` `[DIAGRAM]` — write overhead,
     selectivity; then `EXPLAIN`: the access type, the key, the row estimate, and spotting a full
     scan on the biggest table.
     → *The plan shows what happened. How do you rewrite a slow query so it's fast?*
218. **Optimisation** `[C]` `[D]` — filter early, join lean, name your columns, keep conditions
     index-friendly; and fixing three real slow queries.
     → *One machine holds this database. What happens when one machine isn't enough — or when it
     catches fire?*
219. **Scaling, replication and backups** `[C]` `[DIAGRAM]` `[SAFETY]` — vertical and horizontal
     scaling; replicas; sharding; connection pools; and the backup and restore you should have
     tested before you needed it.
     → *Everything here is relational. There's an entire family of databases that isn't — and now
     you know enough to judge them instead of following a trend.*
220. **NoSQL, honestly** `[C]` `[DIAGRAM]` — the four families (document, key-value, wide-column,
     graph); the same applicant modelled as a document and as rows; embedding versus referencing;
     what you gain and what you lose.
     → *"It scales better" is what everyone says. What does that actually mean, and what does it
     cost?*
221. **Consistency, and choosing** `[C]` `[DIAGRAM]` — eventual consistency, the CAP idea without
     the jargon, and the honest cases for each: caching and sessions and event logs on one side;
     money, marks and relationships you must trust on the other. Why the university is relational.
     → *You can design, query, protect and scale a database. Can you argue for your choices?*
222. **The SQL questions you'll be asked** `[Q]` — joins and what changes a row count; `WHERE`
     versus `HAVING`; the second-highest value three ways; finding and removing duplicates; NULL
     behaviour; counting variants; window functions — each answered with a query that proves it.
     → *Those were the query questions. The design and performance questions are where people who
     "know SQL" quietly fall apart.*
223. **The database design questions you'll be asked** `[Q]` — normalisation to third normal
     form; what an index costs; how to read a plan; what ACID means; isolation levels; SQL versus
     NoSQL and when you'd choose each; how you'd design this schema from scratch.
     → *You can store the university's data and ask it anything. Building the system around it is
     a discipline of its own — one that decides whether a codebase survives its second year.*

### Chapter B8.7 — Practice

224. **Query challenges: the basics** `[P]` — fifteen real questions using filtering, functions
     and grouping.
     → *Those all lived in one table. The interesting ones never do.*
225. **Query challenges: joins and subqueries** `[P]` — twelve questions across the whole schema.
     → *You can answer any question about the past. What about ranking, trends and running
     totals?*
226. **Query challenges: windows and CTEs** `[P]` — top-N per department, running fee totals,
     month-over-month change, dropout curves.
     → *Every one of those queries is worth keeping. Right now they're scattered across files with
     no names.*
227. **The reporting layer** `[P]` `[T]` — turn your best queries into named, commented, versioned
     views: the funnel, fee health, the department scorecard, backlogs, and the at-risk list.
     → *You have a reporting layer nobody can run but you, from a terminal.*
228. **Debugging a wrong number** `[D]` `[CORRECTNESS]` — a dashboard figure that's double what it
     should be; find the fan-out; then a report missing rows; find the inner join that should have
     been a left join.
     → *Your data is safe, correct, fast and documented. And the way you wrote the program around
     it — one enormous file, no tests, no structure — is the thing that will actually get you
     rejected in a code review.*

**Track close:** glossary, the SQL interview Q&A, and the fun set — answer five questions a real
registrar would ask, one query each, then optimise the slowest one.

---

## TRACK B9 — SOFTWARE ENGINEERING
*Why this track exists: companies don't hire people who write code. They hire people who write
code other people can change, six months later, without fear. This is the difference between a
hobbyist and a professional, and it's the part self-taught developers most often skip.*

229. **What software engineering is** `[C]` — programming versus engineering; why most of the cost
     of software is everything after it first works; and the honest lifecycle.
     → *You've been writing code alone. What changes the moment a second person touches it?*
230. **Working in a codebase with other people** `[C]` `[T]` — branching strategies, pull
     requests, code review as a culture rather than a gate, and writing a commit message someone
     will thank you for.
     → *Your reviewer asked "why is this a hundred-line method?" and you didn't have an answer.*
231. **Clean code** `[C]` — naming things properly, small functions that do one thing, comments
     that explain why and not what, and deleting code as a skill.
     → *Clean methods, and the classes around them are still a tangle that resists every change.*
232. **SOLID, in plain words** `[C]` — the five principles, each with a university example of the
     violation and its fix, and an honest note on when they're over-applied.
     → *Your eligibility rules are one enormous if-else chain that grows every time a policy
     changes. This exact problem has a name — several, in fact.*
233. **Design patterns** `[C]` `[DIAGRAM]` — Strategy, Factory, Builder, Singleton, Observer,
     Adapter: the admissions problem each one solves, and when a pattern makes things worse.
     → *Patterns shape a system. What shapes the whole application — where the database, the rules
     and the interface each belong?*
234. **Architecture, at your level** `[C]` `[DIAGRAM]` — layers, separation of concerns, the
     dependency direction, monolith versus services stated honestly (and why you should start with
     a monolith).
     → *Good structure means nothing if a change quietly breaks something three files away and
     nobody notices for a month.*
235. **Testing, properly** `[C]` `[T]` — the pyramid; unit, integration and end-to-end; what's
     worth testing and what isn't; test doubles; and writing tests you'll actually maintain.
     → *You wrote the code and then the tests, and the tests only ever pass. Try it the other way
     around.*
236. **Test-driven development, honestly** `[C]` `[P]` — red, green, refactor on the shortlisting
     rules; where it genuinely helps and where it's cargo cult.
     → *Tests pass on your machine. Do they pass on everyone's, every time?*
237. **Automation: builds and pipelines** `[C]` `[T]` — a build tool, a script that runs
     everything, and a pipeline that runs your linter, types and tests on every push and blocks a
     broken merge.
     → *Everything is automated, and the moment something breaks in production you'll find out
     from a user.*
238. **Logging, errors and observability** `[C]` `[T]` `[D]` — what to log and what never to log
     (credentials, personal data), log levels, structured errors, and looking before someone
     complains.
     → *Your system is correct, tested, automated and observable. Is any of it written down?*
239. **Documentation that people read** `[C]` — a README that gets a stranger running in five
     minutes, architecture notes, decision records, and API documentation.
     → *You can build software properly. The one thing left is the one thing that ends careers.*
240. **The engineering questions you'll be asked** `[Q]` — what makes code maintainable; how you'd
     review this; unit versus integration; what would you refactor and why; how you'd add a feature
     to a system you didn't write.
     → *There's one discipline underneath all of it. Get it wrong and none of the rest matters —
     because the university's data will be on the internet with someone else's name on it.*

**Track close:** glossary, interview Q&A, and the fun set — review a deliberately awful piece of
code and write the comments you'd leave on the pull request.

---

## TRACK B10 — SECURITY FUNDAMENTALS
*Why this track exists: you are about to hold real people's data. Security is not a chapter at
the end of a web course — it's a way of thinking, and it's asked about at every level of
interview.*

241. **How to think about security** `[C]` — assets, threats, and trust boundaries; the attacker's
     mindset; why "nobody would bother attacking us" is always wrong; and defence in depth.
     → *The first door into any system is the login. How is a password actually stored?*
242. **Passwords, hashing and authentication** `[C]` `[DIAGRAM]` — hashing versus encryption
     versus encoding (three things constantly confused); salts; slow hashes; multi-factor; and
     never, ever storing a password in plain text.
     → *You know who the user is. That's a completely different question from what they're allowed
     to do.*
243. **Authorization and least privilege** `[C]` `[DIAGRAM]` — roles and permissions; the student,
     the lecturer and the admin; why hiding a button is not security; and the principle that the
     server decides.
     → *Your login is solid and your database still trusts whatever text arrives with a query.*
244. **Injection** `[C]` `[DIAGRAM]` `[SECURITY]` — SQL injection demonstrated on your own
     university database, then defeated with parameterised queries; and why string concatenation
     into a query is the original sin.
     → *You've protected the database. The browser is a second, completely separate attack
     surface.*
245. **The classic web attacks** `[C]` `[SECURITY]` — cross-site scripting, cross-site request
     forgery, insecure direct object references (changing the id in a URL to read another
     student's marks), and clickjacking — each with the university version and the fix.
     → *Attacks come in patterns, and someone has written the patterns down.*
246. **The common weakness list, and how to use it** `[C]` — the well-known top-ten style lists as
     a checklist rather than trivia, and how to review your own work against one.
     → *Data at rest, data in transit, data in a log file, data in a screenshot. Where is the
     university's information right now?*
247. **Protecting data** `[C]` `[SECURITY]` — encryption at rest and in transit, secrets and
     environment variables, what belongs in a browser and what never does, personal data and
     privacy obligations, and what to do the day you leak a key.
     → *You can build it securely. Interviewers will ask you to prove you think this way.*
248. **The security questions you'll be asked** `[Q]` — how do you store a password; what is SQL
     injection and how do you stop it; XSS versus CSRF; authentication versus authorization; what
     HTTPS protects; how would you secure an API; what do you do about a leaked secret.
     → *You now have the whole foundation: the machine, the code, the structures, the algorithms,
     the mathematics, the hardware, the operating system, the network, the database, the
     engineering and the security. Everything after this is a tool — and tools are easy once you
     know what they're for.*

**Part B close:** a full recall round across every track in the foundation, a combined interview
Q&A, and an honest checkpoint: *"If you can answer the questions in this section, you already
know more computer science than most people who apply for these jobs. Everything from here is
building."*

---

# PART C — THE TECHNOLOGIES
### *Now the tools — and every one is earned by a wall you've already hit*

*Why this part exists: the foundation tells you what to build and why it works. This part is
what the industry actually types. Because Part B is done, none of it is magic: React is objects
and state, Next.js is client and server, Supabase is your database with permissions, hosting is
DNS and networks, and AI is a function call you have to verify.*

*Part C covers the whole modern web-skills map — name the roadmap here, once. Everything in this
part is still taught with university examples, but the **project itself is built in Part D**;
here you're learning the tools on small, real pieces.*

## TRACK C1 — HTML

249. **What the browser receives** `[C]` `[DIAGRAM]` `[T]` — the request you already understand
     from the networks track, and what comes back: a text document the browser turns into a page.
     *(History hook: the birth of the web.)*
     → *You wrote text and it appeared. But every heading and paragraph looks identical to the
     browser. How does it know what anything means?*
250. **Elements, tags and attributes** `[C]` — the anatomy of a tag, nesting, self-closing
     elements, and the document skeleton.
     → *Your page is a wall of paragraphs. How do you show what's a heading, a list, a link?*
251. **Text, headings and lists** `[C]` — the heading hierarchy and why it isn't about size.
     → *Your admissions page needs to reach the programs page. How do pages connect?*
252. **Links and navigation** `[C]` — anchors, relative and absolute paths, in-page links.
     → *A university site with no photographs is a strange thing.*
253. **Images and media** `[C]` `[A11Y]` — the image element, alternative text and why it isn't
     optional, sizing, modern formats.
     → *You have pages, links and pictures. The whole point of an admissions site is that a
     stranger can send you their details.*
254. **Forms** `[C]` — inputs, labels, every input type you'll use, required fields, select,
     radio, checkbox, textarea, and submit.
     → *You built the form. Press submit and the page reloads and loses everything.*
255. **How a form submits** `[C]` `[DIAGRAM]` — the method, the action, what the browser sends,
     and why nothing catches it yet.
     → *The student directory is a list of people with identical fields. A stack of paragraphs is
     the wrong shape for that.*
256. **Tables** `[C]` — headers, rows, cells, captions, and when a table is right (data) and
     wrong (layout).
     → *Your markup works and every element is a `div`. A screen reader — and a search engine —
     sees nothing but boxes.*
257. **Semantic HTML** `[C]` `[A11Y]` — header, nav, main, section, article, aside, footer; the
     right element as a free accessibility win.
     → *Semantics help the machine. A person using only a keyboard still can't get through your
     form.*
258. **Accessibility from the start** `[C]` `[A11Y]` — labels tied to inputs, focus order,
     keyboard navigation, landmarks, and how a screen reader actually reads your page.
     → *Your page is structured and accessible. How would a search engine even find it?*
259. **The head, metadata and SEO basics** `[C]` — title, description, character set, viewport,
     favicons, social previews.
     → *You can build a real page. Time to prove it on something harder than a demo.*
260. **Building real pages** `[P]` — the admissions page, the enquiry form and the student
     directory, semantic and accessible, from a written specification.
     → *They work, and they are genuinely ugly — black text on white, one column, unreadable on a
     phone.*

## TRACK C2 — CSS

261. **How CSS attaches, and selectors** `[C]` — inline, internal, external; element, class, id,
     descendant, child and attribute selectors.
     → *Two rules apply to the same element and one wins. Who decided?*
262. **Specificity, inheritance and the cascade** `[C]` `[DIAGRAM]` — how conflicts resolve, what
     inherits, and why `!important` is a confession.
     → *You set a width and the element came out wider. Something is being added you didn't ask
     for.*
263. **The box model** `[C]` `[DIAGRAM]` — content, padding, border, margin, and the border-box
     fix everyone applies once and never removes.
     → *Two margins met and instead of adding up, one swallowed the other.*
264. **Margin collapse and spacing** `[C]` `[TRAP]` — the rule that confuses everyone, and how to
     space things predictably.
     → *Your text is legible and lifeless. Type is most of what a page actually is.*
265. **Typography** `[C]` — families, sizes, weights, line height, web fonts, and a readable
     scale.
     → *Everything is black on white. How do colours work here — and which ones can a person with
     low vision actually read?*
266. **Colour and contrast** `[C]` `[A11Y]` — hex, rgb, hsl, transparency, and contrast ratios
     that pass an audit.
     → *You've written pixel values everywhere. On a phone they're too big; on a large screen
     they're too small.*
267. **Units** `[C]` — pixels, ems, rems, percentages, viewport units, and when each is right.
     → *Your colours and sizes are copied into forty places.*
268. **Custom properties** `[C]` — design tokens, scoping, and theming.
     → *You have a design system. Now put two things side by side and watch everything stack in
     one stubborn column.*
269. **Display and flow** `[C]` `[DIAGRAM]` — block, inline, inline-block, and normal flow.
     → *Normal flow stacks. A navigation bar needs a row, evenly spaced.*
270. **Flexbox: the main axis** `[C]` `[DIAGRAM]` — container and items, direction, justification.
     → *They're in a row and they're not aligned vertically. There's a second axis.*
271. **Flexbox: the cross axis and sizing** `[C]` `[DIAGRAM]` — alignment, grow, shrink, basis,
     gap, wrapping.
     → *Flexbox is brilliant in one direction. A dashboard needs rows and columns at once.*
272. **Grid: tracks and placement** `[C]` `[DIAGRAM]` — defining rows and columns, fractional
     units, placing items, gaps.
     → *Your grid is exact, rigid, and a disaster on a narrow screen.*
273. **Grid: responsive by design** `[C]` — auto-fit, minmax, named areas, and layouts that reflow
     with no media query at all.
     → *Sometimes an element must sit on top of another — a badge on a card, a header that stays.*
274. **Positioning and stacking** `[C]` `[DIAGRAM]` — static, relative, absolute, fixed, sticky,
     z-index, and why your dropdown hides behind something.
     → *It looks right on your laptop and it's unusable on a phone.*
275. **Responsive design** `[C]` `[DIAGRAM]` — mobile-first thinking, breakpoints that come from
     the content, and testing at real sizes.
     → *It fits every screen and it's completely static — nothing responds when you touch it.*
276. **Transitions and states** `[C]` `[A11Y]` — hover, focus (which keyboard users need),
     active; what changes, over how long, with what easing.
     → *A transition moves between two states. What about something that moves on its own?*
277. **Animations** `[C]` — keyframes, transforms, and respecting a user who asked for reduced
     motion.
     → *Your stylesheet is a thousand lines and you're afraid to change any of it.*
278. **Organising CSS** `[C]` `[P]` — naming conventions, component-scoped thinking, file
     structure, and an honest note on preprocessors and utility frameworks (named, not taught);
     then style the pages you built.
     → *It's beautiful. Press the button and absolutely nothing happens.*

## TRACK C3 — JAVASCRIPT

279. **JavaScript, and why it's everywhere** `[C]` — the language of the browser, honestly
     different from Java despite the name, and where it runs today. *(History hook.)*
     → *You know variables from Java. Here there are three ways to declare one and two of them
     will hurt you.*
280. **Variables and the loose type system** `[C]` `[TRAP]` — `let`, `const`, the old `var`,
     dynamic typing, null versus undefined, truthy and falsy.
     → *`"5" + 3` gives `"53"` and `"5" - 3` gives `2`. The language is guessing, and sometimes
     guessing wrong.*
281. **Coercion, and `==` versus `===`** `[C]` `[TRAP]` — why you always use the strict one.
     → *You can compute. Now decide and repeat — the shapes you know, with a few new ones.*
282. **Control flow and iteration** `[C]` — the familiar ones plus `for…of`, `for…in`, and the
     modern shortcuts.
     → *Java made you name a class before you could write a function. Here a function is just a
     value — and that changes everything.*
283. **Functions as values** `[C]` — declarations, expressions, arrow functions, defaults, and why
     passing a function around matters.
     → *You wrote a function inside another and the inner one still remembered a variable that
     should have been gone.*
284. **Scope and closures** `[C]` `[DIAGRAM]` — block scope, hoisting, the closure, and the
     interview question that always follows.
     → *An applicant is a name, an email, three scores and a status. Five loose variables is
     madness.*
285. **Objects and `this`** `[C]` — properties and methods, dot versus bracket, nesting, and the
     one rule about `this` that saves you.
     → *One applicant is an object. Five hundred need something that holds a list.*
286. **Arrays and the methods that matter** `[C]` — map, filter, find, reduce, sort, some, every —
     the same thinking as Java streams in a smaller package.
     → *You keep writing the same property access on separate lines. Modern code has a shorthand
     for all of it.*
287. **Modern JavaScript essentials** `[C]` — destructuring, spread and rest, template literals,
     optional chaining, defaults.
     → *Your file is four hundred lines. How do professionals split JavaScript up?*
288. **Modules** `[C]` `[T]` — import and export, module scope, and telling the browser.
     → *All of this runs and prints to a console nobody sees. How does JavaScript touch the page?*
289. **The DOM** `[C]` `[DIAGRAM]` — the page as a tree of objects, and the crucial difference
     between your HTML file and the live document.
     → *You can see the tree. How do you grab one node out of it?*
290. **Selecting and changing elements** `[C]` — query selectors, text versus HTML, attributes,
     classes, styles.
     → *You changed the page from the console. Now make it change when a student clicks
     something.*
291. **Events** `[C]` `[DIAGRAM]` — listeners, the event object, preventing the default, and the
     click that reloads your form away.
     → *You added a listener to every row, and when new rows arrive they're dead.*
292. **Bubbling and delegation** `[C]` `[DIAGRAM]` — how an event travels up the tree, and one
     listener for a whole table.
     → *You can react to a click. Now catch a bad email before the form ever submits.*
293. **Live form validation** `[C]` `[A11Y]` — validating as the user types, showing errors
     accessibly, and never trusting the browser alone.
     → *You built rows by pasting HTML strings together. It works — until a name contains a
     character that breaks everything, or worse.*
294. **Building elements safely** `[C]` `[SECURITY]` — creating nodes properly, and the cross-site
     scripting you met in the security track, now in your own hands.
     → *Everything on your page is hard-coded. The real data is in a database on another machine.*
295. **Why asynchronous code exists** `[C]` `[DIAGRAM]` — one thread, a slow network, a frozen
     page. **Warn: this is the concept that breaks people.**
     → *So the browser doesn't wait. Then how does your code know when the data arrives?*
296. **Callbacks and promises** `[C]` `[DIAGRAM]` — the original answer and its pyramid; then
     pending, fulfilled, rejected, and chaining instead of nesting.
     → *Chained promises still don't read like the steps in your head.*
297. **async and await** `[C]` `[TRAP]` — the modern way, error handling, and the classic mistake
     of forgetting `await` and getting a Promise instead of your data.
     → *You understand awaiting. What is actually happening in the engine while it waits?*
298. **The event loop** `[C]` `[DIAGRAM]` — the call stack, the queue, the callback; and why a
     slow loop still freezes the page even with async everywhere.
     → *Now you can wait properly. Time to actually ask a server for the applicant list.*
299. **fetch and APIs** `[C]` `[T]` — the request, JSON, status codes, headers, error and empty
     states, and watching all of it in the network tab.
     → *The data arrives and the page is still blank, because your code rendered before the
     response came back.*
300. **Debugging JavaScript** `[D]` `[T]` `[P]` — the console, breakpoints in dev tools, the
     network tab, reading a real error; then make the student directory search, sort and filter
     instantly.
     → *The dashboard now fetches the university's numbers and prints twenty rows of digits.
     Nobody can see anything in that.*

## TRACK C4 — DATA VISUALIZATION

301. **Which chart answers which question** `[C]` — comparison, trend, composition, distribution,
     relationship; and the charts that lie (truncated axes, nine-slice pies, dual axes).
     → *You know the chart you want. What can a browser actually draw?*
302. **SVG from scratch** `[C]` `[DIAGRAM]` — shapes, coordinates, paths, text; a tiny bar chart
     drawn by hand so nothing later is magic.
     → *You drew five bars by hand. Now draw five hundred, and let them move.*
303. **Canvas, and choosing between them** `[C]` — pixels versus shapes, and the honest rule of
     thumb.
     → *Hand-drawing every chart is a lifetime's work. There are libraries — and picking one is
     its own decision.*
304. **A chart library, end to end** `[C]` `[T]` — installing it, the anatomy of a config, data in
     and chart out; the admissions funnel as the first real one.
     → *Your chart looks right and the numbers are wrong, because you fed it raw rows instead of
     the aggregate.*
305. **Shaping data for a chart** `[C]` `[CORRECTNESS]` — grouping, ordering, filling gaps in a
     time series; and why the SQL from Part B does most of the work.
     → *One chart is a picture. A dashboard is a set of them that must agree with each other.*
306. **Scales, axes and honest labels** `[C]` — ranges, tick formatting, units, and titles that
     state the finding rather than the topic.
     → *It's perfect on your monitor and a smear on a phone.*
307. **Responsive and accessible charts** `[C]` `[A11Y]` — resizing, simplifying at small sizes,
     colour that survives colour-blindness, and giving every chart a text alternative and a data
     table.
     → *You can draw any standard chart. The at-risk view needs something no library's defaults
     will give you.*
308. **Going deeper with D3** `[C]` — what D3 actually is (data joined to elements), when it's
     worth the effort, and one custom visual built with it.
     → *You have the tools and the taste. Build the set of them.*
309. **A dashboard, built** `[P]` — the funnel, fee collection with a running total, placements
     per department, attendance versus performance, and the at-risk list — from your Part B
     reporting views.
     → *It's one enormous HTML file, three script tags and a folder of loose files you copy
     between pages. This is not how anyone builds software.*

## TRACK C5 — THE TOOLCHAIN

310. **Node and npm** `[C]` `[T]` — JavaScript outside the browser, `package.json`, dependencies
     versus dev dependencies, `node_modules` and why it's never committed, and lockfiles.
     → *You installed a package. How does the browser get it, when the browser has never heard of
     `node_modules`?*
311. **Bundlers and a dev server** `[C]` `[T]` — why bundling exists, creating a project, the dev
     server, hot reload, the build command, and what lands in the output folder.
     → *Your project runs, and every file is formatted differently because you wrote them over
     three months.*
312. **Linting and formatting** `[C]` `[T]` — the two tools, what each is for, configuring them,
     and running them from the terminal and the editor.
     → *It's clean and consistent. Now put it in Git properly, with the right things ignored.*
313. **Project hygiene** `[C]` `[T]` — `.gitignore`, environment files that never get committed,
     npm scripts, a README a stranger can follow, and semantic commits.
     → *You renamed a field in one file, everything still built, and the page broke in front of a
     user. Nothing warned you.*

## TRACK C6 — TYPESCRIPT

314. **Why types** `[C]` — the bug JavaScript cannot catch, what a compiler can, and the honest
     cost; and why this feels like coming home after Java.
     → *You've been told to add types. Where do they even go?*
315. **Basic types and inference** `[C]` `[T]` — installing it, annotating variables and
     functions, and letting inference do most of the work.
     → *An applicant isn't a string or a number. It's a shape.*
316. **Interfaces, types and objects** `[C]` — describing your data models, optional properties,
     and the two keywords and when to use each.
     → *A status can only be one of five words, but your type says "string" — so a typo compiles
     perfectly. The same bug you fixed in Java with an enum.*
317. **Unions, literals and narrowing** `[C]` — modelling a status exactly, narrowing with checks,
     exhaustive switches.
     → *Your repository function works for applicants. You need the same one for programs.*
318. **Generics** `[C]` — type parameters and constraints; the same idea you already built in
     Java, now in the browser.
     → *Data comes back from a fetch and TypeScript calls it `any`. Every guarantee evaporates at
     the boundary.*
319. **Typing external data** `[C]` `[CORRECTNESS]` — typing an API response, why `any` is a hole
     in the hull, `unknown`, and validating what actually arrived at runtime.
     → *Your types are strong. Now read a compiler error that's forty lines long without
     panicking.*
320. **Reading TypeScript errors, and converting a project** `[D]` `[P]` `[T]` — the config file,
     strictness, converting a JavaScript file, and what to do when the error makes no sense.
     → *Your typed code still builds the interface one element at a time. Fifty student cards
     means fifty rounds of create, set, append.*

## TRACK C7 — REACT
*Open with the warning: the first two lessons feel like a step backwards. Everyone finds the
component model strange until it clicks — and then nothing else feels sane.*

321. **Why components** `[C]` `[DIAGRAM]` — the student card written fifty times, the rubber-stamp
     idea, and what React does with the DOM so you don't have to.
     → *A component is a function that returns markup — markup sitting inside your JavaScript,
     which shouldn't even be legal.*
322. **JSX** `[C]` — markup in JavaScript, expressions in braces, the small differences, and what
     it compiles to.
     → *Your card is hard-coded to one student. It needs to work for five hundred.*
323. **Props** `[C]` `[DIAGRAM]` — passing data in, typing props, children, and why props are
     read-only.
     → *You render one card per applicant from an array, and React printed a warning about a
     missing key.*
324. **Lists, keys and conditional rendering** `[C]` `[TRAP]` — mapping over data, why keys matter
     (with the bug that proves it), and rendering the empty state.
     → *You built a filter box. You type in it and nothing happens, because the component has no
     memory.*
325. **State** `[C]` `[DIAGRAM]` — what a re-render is, why you never mutate state directly, and
     the classic "I changed it and nothing updated."
     → *Two components need the same value and each has its own copy that disagree.*
326. **Lifting state up** `[C]` `[DIAGRAM]` — where state should live, passing setters down, events
     flowing up.
     → *Your form has eight fields and eight pieces of state.*
327. **Forms in React** `[C]` — controlled inputs, one state object, validation, submitting without
     a reload.
     → *You need data from the server the moment the page opens. Where does a fetch even go?*
328. **Effects** `[C]` `[DIAGRAM]` `[TRAP]` — the dependency array, cleanup, the infinite loop
     everyone writes once, and the honest modern advice about when you don't need one at all.
     → *Every component that needs data repeats the same fetch, loading and error code.*
329. **Custom hooks** `[C]` — extracting reusable logic into something the whole app shares.
     → *The logged-in user is needed by nine components four levels deep, and you're threading it
     through every one.*
330. **Context** `[C]` `[DIAGRAM]` — sharing state without prop-drilling, and an honest note on
     when it's the wrong tool.
     → *Your app is one screen. A real system has a directory, a detail page, a dashboard and a
     login — and the back button should work.*
331. **Routing, as a concept** `[C]` — multiple views, links, URL parameters, and the layout shell.
     *(The framework does it for real in the next track.)*
     → *You're rendering five hundred applicants and typing in the search box feels sticky.*
332. **Performance in React** `[C]` `[PERF]` — what causes a re-render, memoisation, and measuring
     before optimising.
     → *You can build any screen. Can you build a set of them that look like one product?*
333. **Building a component library** `[P]` — card, table, field, badge, chart wrapper, modal:
     typed, reusable, accessible, and documented.
     → *It's a beautiful application that lives entirely in the browser. View the page source and
     there's nothing there — no content for a search engine, and a blank screen on a slow phone.*

## TRACK C8 — NEXT.JS

334. **Why a framework** `[C]` `[DIAGRAM]` `[T]` — everything you'd hand-roll (routing, rendering,
     bundling, data loading); creating a project and reading the folder you didn't write.
     → *You made a page and it rendered on the server, which means the code never touched a
     browser — and half your React instincts are now illegal.*
335. **File-based routing and layouts** `[C]` — folders as routes, pages, nested layouts, dynamic
     segments, and the detail-page URL.
     → *Routing is solved. The bigger question is where your code actually runs.*
336. **Server and client components** `[C]` `[DIAGRAM]` `[TRAP]` — the boundary, what runs where,
     when you need the client directive, and the error everyone hits first.
     → *Server rendering is fast and search-friendly. So when does anything render in the browser
     at all?*
337. **Rendering strategies, honestly** `[C]` `[DIAGRAM]` — static, server-rendered and
     client-rendered; which page should be which and why.
     → *Your page renders on the server. Where does it get data — you can't fetch from a browser
     that isn't there.*
338. **Data fetching on the server** `[C]` — fetching in a server component, caching and
     revalidation in plain words, and the loading and error files.
     → *Reading is one thing. A form has to send data — and you have no backend.*
339. **Route handlers: your first API** `[C]` `[T]` — building an endpoint, request and response,
     status codes, and testing it with `curl` before any interface exists.
     → *You wrote an endpoint and then a fetch to call it. There's a way to skip the round trip.*
340. **Server actions** `[C]` — submitting a form straight to the server, validating on the server
     because the client can always be bypassed, and revalidating after a write.
     → *The form submits and the page sits there until the server answers.*
341. **Loading, streaming and optimistic updates** `[C]` — suspense boundaries, skeletons, and
     making a write feel instant without lying to the user.
     → *Your images are enormous, your fonts flash, and the whole thing feels heavy.*
342. **Images, fonts and metadata** `[C]` `[PERF]` — the built-in optimisations and per-page
     metadata for search and sharing.
     → *The app is growing and you need the parts nobody mentioned: middleware, environment
     variables, error boundaries.*
343. **The rest of the framework you'll use** `[C]` — middleware, environment variables, route
     groups, redirects, and error boundaries.
     → *You have a real application shell. Every piece of data in it is still hard-coded, because
     the database you built in Part B is on a machine the internet cannot see.*

## TRACK C9 — SUPABASE: A REAL BACKEND

344. **What Supabase is** `[C]` — hosted PostgreSQL plus authentication, storage, realtime and
     generated APIs; what it saves you and what it doesn't; and the reassurance that you're using
     the SQL you already know.
     → *You learned MySQL. This is PostgreSQL. How different is it, really?*
345. **PostgreSQL versus MySQL, honestly** `[C]` — what transfers unchanged (nearly everything)
     and the real differences: quoting, identity columns, case-insensitive matching, stricter
     grouping, better JSON, returning rows from a write, and arrays.
     → *You have an empty database in the cloud. Time to give it the university's shape.*
346. **Creating the schema for real** `[C]` `[T]` — your Part B design, created as migrations, and
     why you never click a change into production without a record of it.
     → *There's a table with no rows. How does data get in, and how does your app read it out?*
347. **The client, and reading data** `[C]` `[T]` — installing it, querying from a server
     component, and the generated API versus writing SQL yourself.
     → *You rendered real data from a hosted database. Then you opened dev tools and found a
     database key sitting in plain sight.*
348. **Keys and secrets** `[C]` `[SECURITY]` `[SAFETY]` — the publishable key versus the secret
     key, environment variables, what may live in browser code and what absolutely may not, and
     what to do the day you leak one.
     → *If a key in the browser can read your database, what stops a student reading every other
     student's marks?*
349. **Row-level security** `[C]` `[DIAGRAM]` `[SECURITY]` — the database itself enforcing who
     sees which rows; enabling it and watching your app return zero rows, which is the system
     working; your first policy. **Warn: this is where a mistake is a real breach — and where an
     interviewer will be genuinely impressed.**
     → *Policies decide based on who is asking. How does the database know who's asking?*
350. **Authentication** `[C]` — sign-up, sign-in, sessions, and how a logged-in user becomes an
     identity the database can check; everything from the security track, made concrete.
     → *A student, a lecturer and an admin all log in the same way and must see completely
     different things.*
351. **Roles and authorization** `[C]` `[DIAGRAM]` — modelling the three roles, writing policies
     per table, and testing by logging in as each one.
     → *Your policies are right and your interface still shows an admin button to a student. Which
     one is the real security?*
352. **Client checks versus server truth** `[C]` `[SECURITY]` `[CORRECTNESS]` — hiding a button is
     convenience; the database is the guarantee — proved by calling the endpoint directly with
     `curl`.
     → *Logins work and permissions hold. Now an applicant needs to upload a transcript, and a
     column is the wrong place for a PDF.*
353. **File storage** `[C]` — buckets, uploading, public versus private files, signed URLs, and
     access rules on files too.
     → *A reviewer changes an applicant's stage and the second reviewer's screen still shows the
     old one.*
354. **Realtime** `[C]` `[DIAGRAM]` — subscribing to changes, live updates, and when realtime is
     genuinely worth the complexity.
     → *Some logic shouldn't live in the browser at all — recalculating standing the moment marks
     are entered, for instance.*
355. **Database functions and edge functions** `[C]` — logic in the database, serverless functions
     for the rest, and how to choose.
     → *Your backend is real, and every dashboard query goes over the network for data that barely
     changes.*
356. **Indexes, caching and performance** `[C]` `[PERF]` `[T]` — indexing what your app actually
     filters by, reading a PostgreSQL plan, and caching the expensive query — all of it the Part B
     material, applied.
     → *You have a real database, real logins, real permissions, real files and live updates — at
     an address only you can visit.*

## TRACK C10 — HOSTING, DNS AND DEPLOYMENT

357. **How a website reaches a stranger** `[C]` `[DIAGRAM]` — the whole path from a typed name to a
     rendered page, assembled from the networks track, with each piece owned by someone different.
     → *That name resolved to a number. You know how. Now you have to own one.*
358. **Domains and registrars** `[C]` `[T]` — what you're actually renting, nameservers versus
     records, and pointing a domain somewhere without breaking your email.
     → *A name needs something to point at. What kinds of hosting exist, and which one do you
     need?*
359. **The hosting map** `[C]` `[DIAGRAM]` — static hosting, a server you manage yourself, a
     managed platform, containers, and serverless; what each costs in money and in your time.
     → *You've picked a platform. Getting your code onto it turns out to be one command — and the
     first time, it fails.*
360. **Your first deploy** `[T]` `[D]` — connecting the repository, the build step, reading the log,
     and fixing a failed build (a missing environment variable, a type error, a version mismatch).
     → *It's live at a random address, and it can't reach your database, because production has
     never heard of your local environment file.*
361. **Environments and variables** `[C]` `[SECURITY]` `[T]` — development, preview and production;
     which variables are public and which are secret; rotating a key without downtime.
     → *Production works. Now change something without risking the people using it.*
362. **Preview deploys and the real workflow** `[C]` `[T]` — a URL per branch, reviewing before
     merging, and rolling back a bad deploy in seconds.
     → *Time to put your own domain on it — and to understand the padlock properly.*
363. **Custom domains and HTTPS** `[C]` `[T]` — adding the domain, the records the platform asks
     for, certificates, `www` versus the bare domain, and redirects.
     → *It's live on your domain over HTTPS. Is it fast?*
364. **Performance in production** `[C]` `[PERF]` `[T]` — the metrics that matter, running an audit,
     images and fonts, caching, code splitting, and measuring before and after.
     → *It's fast, and invisible to every search engine on earth.*
365. **SEO for a real site** `[C]` — titles and descriptions, structured data, sitemap and robots
     files, and why performance is itself a ranking factor.
     → *People can find it and use it. What happens the first time it breaks at two in the morning?*
366. **Logs, errors and monitoring** `[C]` `[T]` `[D]` — reading production logs, catching errors,
     uptime checks, and looking before a user complains.
     → *You deploy by pushing and test by hoping. Every professional team automates the bit in
     between.*
367. **Continuous integration and delivery** `[C]` `[T]` — a pipeline that lints, type-checks and
     tests every push and blocks a broken merge; and containers as the honest alternative.
     → *You can ship anything. There's one capability left, and in 2027 it's the one every job
     posting mentions.*

## TRACK C11 — AI
*Why this comes last: you can now program, model data, query it, build an interface, secure a
backend and ship it. That is exactly the foundation needed to build AI features that are useful
instead of dangerous — and to be the person who can tell the difference.*

368. **What AI actually is, in plain words** `[C]` `[DIAGRAM]` — model, training, parameters,
     neural network, large language model, token, context window, embedding, inference. No
     mysticism, no hype. *(History hook: the early nets, the winters, deep learning, today.)*
     → *It predicts the next token. So why does it state a completely false thing with total
     confidence?*
369. **Why models get things wrong** `[C]` `[CORRECTNESS]` — hallucination explained mechanically,
     training cut-offs, the fact that it has never seen your schema, and why "it sounded right" is
     the most expensive phrase in this field.
     → *If it can't be trusted alone, how does anyone build a product on it?*
370. **Machine learning fundamentals** `[C]` `[DIAGRAM]` — supervised learning, features, labels,
     training and test data, evaluation and overfitting — resting on the probability from Part B.
     → *You understand the ideas. How do you call one from your own code?*
371. **Calling a model from your app** `[C]` `[T]` — an API call from a server route (never the
     browser), the request shape, streaming to the interface, handling failure and timeouts, and
     what it costs per call.
     → *It answered — with a paragraph of prose when you needed a number your app could use.*
372. **Prompting as engineering** `[C]` — instructions, context, examples, structured output you
     can parse, temperature, and testing a prompt like code instead of guessing.
     → *It gives good answers about the world and knows nothing about your university.*
373. **Giving a model your context** `[C]` `[DIAGRAM]` — why the schema and data must be handed to
     it; retrieval; embeddings and vector search in plain words; and the honest limits of a context
     window.
     → *Time to build the thing a dean actually wants: ask a question in English, get the real
     number back.*
374. **English to SQL, safely** `[C]` `[DIAGRAM]` `[CORRECTNESS]` — the full pipeline: question →
     schema context → generated SQL → **read-only** execution → answer shown *with the query it
     ran*; and why your Part B SQL is what makes this safe.
     → *It answers most questions and occasionally invents a column. How do you stop a wrong answer
     reaching a human as if it were fact?*
375. **Guardrails and the human in the loop** `[C]` `[SECURITY]` — validating generated SQL against
     the real schema, read-only credentials, refusing rather than guessing, showing sources, and
     never letting a model write to the database unattended.
     → *The assistant answers questions. Now make search understand what someone meant.*
376. **Semantic search** `[C]` — embeddings over records, combining them with plain filters, and
     being honest about when a simple query beats a vector.
     → *Search helps. What would help more is somebody noticing a student is in trouble before
     they fail.*
377. **Prediction, and surfacing it honestly** `[C]` `[DIAGRAM]` — combining attendance, backlogs,
     fees and warnings into a ranked signal with its contributing factors shown; and the framing
     that this prompts a human conversation and never an automatic penalty.
     → *You've built something that makes judgements about real students. That deserves more than a
     shrug.*
378. **Responsible AI, as a build requirement** `[C]` `[SECURITY]` — privacy (what leaves your
     servers), consent, bias in the signals you chose, explainability, an audit trail, and the
     features you should decline to build.
     → *Every feature works. The harder question: which of them actually earned its place?*
379. **Knowing when not to use AI** `[C]` `[Q]` — the cases where a query, a rule or a form is
     better, faster, cheaper and more reliable; cost and latency reality; and how to defend that
     judgement in an interview.
     → *You now have the whole foundation and every tool. There's only one thing left to do with
     them — the thing this entire course has been building toward.*

**Part C close:** glossary, a combined interview Q&A across every technology, and the honest
checkpoint: *"You can now build anything on this list. Which means it's finally time to build the
real thing."*

---

# PART D — THE PROJECT: THE UNIVERSITY MANAGEMENT APP
### *Last, on purpose — because now you can actually build it*

*Why the project comes last: a project built while you're still learning teaches you to copy. A
project built after the foundation teaches you to engineer. Everything in Part D is a decision
you are now equipped to make, and you'll make each one on paper before you write a line.*

**Name:** **The University Management App** — the software a university actually runs on.
**Tagline:** *one system, the whole campus — from the first enquiry to the alumni page.*

**How Part D is taught, and this is a hard rule:** every lesson gives the **plan and the
acceptance criteria** — the requirement, the data involved, the files and functions, the routes,
and what "done" looks like — and **never the finished code**, except in the two assembly lessons
at the end, which present the completed system as a guided tour. The reader builds it. That is
the whole point of having done Part B and Part C first.

## §D.1 What the University Management App is

- **The public site** — the university's website: home, programs, admissions, and the enquiry
  form. Fast, findable, accessible; the front door where a stranger first lands.
- **The admission CRM** — the pipeline that turns an enquiry into an admitted student:
  `RECEIVED → UNDER_REVIEW → SHORTLISTED / REJECTED → ACCEPTED`, with notes, follow-ups,
  assigned reviewers, source tracking, duplicate detection and conversion metrics.
- **The student portal** — profile, exam results, semester registration, attendance, fee status
  and history, library books, and placement offers.
- **The teacher portal** — class lists, marking attendance, entering marks, subject-level
  performance, and flagging a student for follow-up.
- **The admin console** — departments, programs, seats and cutoffs, faculty, fee structures,
  placements and companies.
- **The ERP modules** — fees, library, faculty records, and placements, all on shared data.
- **The dashboards** — the funnel, fee collection with a running total, placements per
  department, attendance versus performance, backlog trends, and the at-risk list.
- **The AI layer** — the staff assistant, semantic search, sourced application summaries, the
  at-risk signal, and a draft-reply helper.
- **The engine room** — the Java console tools and the SQL reporting views from Part B, still
  running, still tested, underneath all of it.

## §D.2 The build

### Chapter D.1 — Decide before you type

380. **Requirements** `[A]` — turning "run our admissions" into written requirements: the actors
     (applicant, student, lecturer, admin), the jobs each needs done, what's in scope and what is
     explicitly not, and the acceptance criteria for the whole system.
     → *You know what it must do. Nothing yet says what shape it should be.*
381. **Architecture on paper** `[A]` `[DIAGRAM]` — the decision record: why a monolith, why
     server-rendered, why relational, where each responsibility lives, and the diagram of the
     whole system on one page.
     → *You have a shape. It has to hold real data, and a bad schema will haunt every feature you
     ever add.*
382. **The data model** `[A]` `[DIAGRAM]` — the entity-relationship diagram; every table, key,
     constraint and relationship, normalised deliberately, with the denormalisations you chose on
     purpose and why.
     → *The data is designed. Who's allowed to see which parts of it?*
383. **The access model** `[A]` `[DIAGRAM]` `[SECURITY]` — the three roles written as a matrix:
     every table, every operation, who may do it; and the row-level policies that will enforce it
     in the database rather than the interface.
     → *You know the shape and the rules. What does it actually look like?*
384. **The interface plan** `[A]` — every screen listed, the user's path through each one, the
     shared components, the loading and empty and error states, and the mobile behaviour.
     → *You have a complete plan and an empty repository.*
385. **Setting up the project** `[A]` `[T]` — the repository, the framework, TypeScript, the
     linter, the folder structure, the environment files, the README, and the first commit.
     → *An empty project. The first thing a stranger will ever see is the public site.*

### Chapter D.2 — Build it

386. **The public site** `[A]` — home, programs, admissions pages: server-rendered, semantic,
     responsive, accessible, with metadata and a sitemap.
     *Done when:* it scores well on an audit, works on a phone, and reads correctly with a keyboard
     alone.
     → *A beautiful front door with no letterbox.*
387. **The enquiry form** `[A]` — the form, client-side validation for kindness, **server-side
     validation for truth**, writing to the database, the confirmation state, and rate limiting.
     *Done when:* a stranger can submit an enquiry and it appears in the database, and no malformed
     or hostile input gets through.
     → *Enquiries are arriving into a table nobody can read.*
388. **Authentication** `[A]` `[SECURITY]` — sign-up and sign-in, sessions, the three roles, and
     protected routes.
     *Done when:* each role lands on their own home screen and an unauthenticated request to a
     protected route is refused by the server.
     → *Everyone can log in and everyone can see everything.*
389. **Locking down the data** `[A]` `[SECURITY]` `[DIAGRAM]` — row-level policies implementing the
     access matrix from lesson 383, table by table.
     *Done when:* a student calling the API directly with `curl` gets their own rows and nothing
     else — proved, not assumed.
     → *The data is safe. Now make it useful to the people who process it every day.*
390. **The admission CRM** `[A]` — the pipeline board, the applicant detail view, stage
     transitions, notes and follow-ups, assignment, and duplicate detection.
     *Done when:* a reviewer can take an enquiry all the way to admitted, and every transition is
     recorded.
     → *Applicants become students, and a student's life is marks, attendance, fees and books.*
391. **The student portal** `[A]` — profile, results and grades, attendance, fee status and
     history, library, and offers.
     *Done when:* a student sees a complete and correct picture of their own record, and nobody
     else's.
     → *Those marks and that attendance have to come from somewhere.*
392. **The teacher portal** `[A]` — class lists, marking attendance, entering marks with
     validation, and subject performance.
     *Done when:* a lecturer can mark a full class in under a minute and a wrong mark is impossible
     to save.
     → *The staff can run the university day to day. Nobody can see how it's actually doing.*
393. **The ERP modules** `[A]` — fees (structure, instalments, status, overdue), library (issue,
     return, fines), faculty records, and placements.
     *Done when:* each module does its job on shared data with no duplicated records.
     → *Every number the university needs is now in the database, and completely invisible.*
394. **The reporting layer, connected** `[A]` — wiring the Part B views into the application:
     funnel, fee health, scorecards, backlogs, at-risk.
     *Done when:* every figure on the dashboard traces back to a named, documented query.
     → *Numbers in a table. A dean will not read a table.*
395. **The dashboards** `[A]` — the charts, responsive and accessible, each one answering a stated
     question; the at-risk list with its contributing factors visible.
     *Done when:* someone who has never seen the system can answer "how are admissions going?" in
     five seconds.
     → *It shows what happened. It can't answer a question nobody anticipated.*
396. **The AI assistant** `[A]` `[SECURITY]` — English to SQL, read-only, with the generated query
     shown; guardrails against invented columns and any attempt to write.
     *Done when:* a dean gets a correct answer with its query visible, and a hostile question is
     refused rather than guessed at.
     → *It answers questions. A reviewer still reads six screens to make one decision.*
397. **The remaining AI features** `[A]` — semantic search, sourced application summaries, the
     at-risk signal surfaced on the dashboard, and the draft-reply helper with a human always in
     the loop.
     *Done when:* every generated claim links to the field it came from, and nothing is saved
     without a human confirming it.
     → *It's built. Is it right?*

### Chapter D.3 — Make it real

398. **Testing the University Management App** `[A]` `[T]` — unit tests on the rules (eligibility, shortlisting, fee
     calculation), integration tests on the API, and one end-to-end test that walks an enquiry all
     the way to admitted.
     *Done when:* deliberately breaking a rule turns a test red before a human notices.
     → *The tests pass on your machine.*
399. **Accessibility and quality pass** `[A]` `[A11Y]` `[D]` — a keyboard-only walk through every
     screen, an audit, contrast, focus management, form errors, and accessible charts.
     *Done when:* the whole system is usable without a mouse and passes the audit.
     → *It's correct and usable and it takes six seconds to load the dashboard on a phone.*
400. **Performance pass** `[A]` `[PERF]` `[D]` — measure first; then indexes on the columns you
     actually filter by, caching the expensive aggregates, image and font handling, code splitting;
     measure again.
     *Done when:* you can show a before and after, and explain each change.
     → *Fast, correct, accessible — and holding real student data with your own security review
     never done.*
401. **The security review** `[A]` `[SECURITY]` — your own audit against the Part B checklist:
     injection, cross-site scripting and request forgery, direct object references, secrets, logs,
     and the access matrix re-tested role by role.
     *Done when:* you have tried to break your own system in five specific ways and failed.
     → *It's ready. It exists only on your laptop.*
402. **Deploying it** `[A]` `[T]` — production environment and variables, the database, the build,
     the first deploy, and the smoke test.
     *Done when:* it runs at a URL that isn't `localhost`, with production data separate from your
     development data.
     → *It's on the internet at an address that looks like a random string.*
403. **The domain, HTTPS and the pipeline** `[A]` `[T]` — your own domain, the records, the
     certificate, preview deploys per branch, and a pipeline that lints, type-checks and tests
     every push.
     *Done when:* pushing to a branch gives you a preview URL, and merging ships to your domain
     automatically.
     → *It's live and nobody knows if it breaks.*
404. **Monitoring and operating it** `[A]` `[T]` `[D]` — logs, error tracking, an uptime check, a
     tested backup and restore, and a short runbook for the three things most likely to go wrong.
     *Done when:* you find out about a failure before a user tells you, and you have restored the
     database from a backup at least once.
     → *It runs. Could anyone but you run it?*
405. **Documentation** `[A]` — a README that gets a stranger running locally in ten minutes,
     architecture notes, the decision records from lesson 381, the query catalogue, and the API
     documentation.
     *Done when:* someone else clones it and has it running without asking you a single question.
     → *It's documented and complete. Now the question that decides whether it was worth building.*

### Chapter D.4 — The finished system

406. **The University Management App, assembled** `[A]` — the complete system presented end to end as a guided tour:
     every module, every layer, every decision, with each part tagged to the lesson that taught it.
     The one place in this course where the finished code is shown in full.
     → *You built it, and it's live. Which parts of it are you proud of, and which parts would you
     do differently?*
407. **An honest review of your own system** `[C]` `[Q]` — the trade-offs you accepted, the
     shortcuts you took and why, what you'd change with another month, and the one bug that took
     you longest. **This lesson is the raw material for every interview answer you will give.**
     → *There's one thing left to do with a system this good, and it isn't technical.*
408. **Making it a portfolio piece** `[C]` `[A]` — the public README, a short demo video, seeded
     demo accounts for each role so a stranger can log in and look around, screenshots, and a
     one-page write-up that leads with the problem rather than the tech stack.
     *Done when:* a recruiter can understand what it does in thirty seconds and try it in two
     minutes.
     → *Somebody is about to ask you to explain all of it, out loud, in ten minutes, while
     judging you.*

**Part D close:** the project glossary, an interview Q&A entirely about the system you built
("why did you choose that?", "how would you scale it?", "what would break first?"), and the
exercise set — add one feature nobody asked for, end to end, and document why it was worth it.

---

# PART E — THE INTERVIEW
### *Turning all of it into a job*

409. **How technical hiring actually works** `[C]` — the stages you'll meet (screen, coding round,
     technical deep-dive, system design, behavioural, offer), what each is really testing, and an
     honest word about rejection volume.
     → *The first filter isn't a person. It's six seconds on a document.*
410. **Resume, portfolio and profile** `[C]` — a resume that leads with what you built and what it
     does; a portfolio that opens with a live link; a maintained profile; and the honest truth that
     a referral beats a hundred applications.
     → *You got the call. The first thing they'll say is "so, tell me about yourself."*
411. **Talking about yourself, and about what you built** `[C]` — the sixty-second introduction; the
     two-minute University Management App walkthrough that leads with the problem; the decisions, the trade-offs,
     and the one bug story that proves you were really there.
     → *They believe the project. Now they want to watch you think, with nothing to look at.*
412. **The coding round** `[Q]` `[P]` — thinking out loud, restating the problem, brute force first
     then improving; the patterns that recur; what's actually being scored; and what to do when you
     are completely stuck.
     → *You solved it. The follow-up is always "and how would you make that faster?"*
413. **The complexity and fundamentals round** `[Q]` — explaining cost in plain words; the data
     structure choice questions; recursion versus iteration; and the memory, process and thread
     questions from Part B.
     → *That's the code. The database round is where people who "know SQL" quietly fall apart.*
414. **The database round** `[Q]` — joins and row counts, `WHERE` versus `HAVING`, window
     functions, the second-highest value, duplicates, NULL behaviour, indexes and plans,
     normalisation, ACID, SQL versus NoSQL — each answered with a query.
     → *Then the systems round, which is really the operating systems and networks tracks wearing a
     suit.*
415. **The systems round** `[Q]` — what happens when you type a URL and press enter, end to end;
     process versus thread; what a context switch costs; TCP versus UDP; how DNS and HTTPS work;
     caching; what a load balancer does.
     → *And the front-end round, where they'll ask you why something re-rendered.*
416. **The front-end round** `[Q]` — the box model, specificity, Flexbox versus Grid, `==` versus
     `===`, closures, the event loop, `async`/`await`, the virtual DOM, keys, props versus state,
     effects, server versus client components, and accessibility.
     → *Then the round with no right answer at all.*
417. **The system design round** `[Q]` `[DIAGRAM]` — designing an admissions system out loud:
     requirements, data model, API, storage, caching, scale, failure modes — using the system you
     actually built as the worked example you know cold.
     → *You can answer everything technical. Then they ask about a time you disagreed with someone
     and you freeze.*
418. **The behavioural round** `[Q]` — telling a real story with a structure; "why should we hire
     you," "your biggest weakness," "a time you failed," "a time you disagreed"; and how a
     self-taught background is a strength when you tell it right.
     → *You're ready. Being ready is not the same as getting interviews.*
419. **The hunt** `[C]` — where to apply, reaching out to humans without being annoying, tracking
     applications, handling rejection at volume, and the numbers nobody warns you about.
     → *An offer arrives, and most people give away money in the next ten minutes out of relief.*
420. **The offer** `[C]` — what's negotiable, how to ask without risk, evaluating an offer beyond
     salary, and leaving a good impression either way.
     → *You've got the job. What you learn in the first ninety days decides the next five years.*
421. **Everything you know** `[Q]` — the full recall round across the entire course: foundation,
     core computer science, technologies, and the system you built.
     → *You can answer all of it. So what's still missing?*
422. **What you didn't learn, honestly** `[C]` — the map of what's beyond this course and where it
     lives: other languages, mobile, cloud at scale and infrastructure as code, data engineering,
     security as a career, machine learning proper, and distributed systems.
     → *That's a lot of mountains. Which one, and how do you climb without drowning?*
423. **How to keep learning** `[C]` — one thing at a time, always attached to something you're
     building; reading official documentation rather than the fifth blog post; building in public;
     and reading other people's code.
     → *One last thing.*
424. **The last word** `[C]` — the narrator closes the loop: he started never having opened a
     terminal, learned the foundation properly, built one real system, and got hired. So can the
     reader. What to build next, and why the domain never mattered.
     → *No hook. This is the end.*

**Part E close:** the complete Interview Vault (§E.1), the real-world map (§E.2), and the final
word.

## §E.1 The Interview Vault

One page collecting **every interview question in the course, grouped by track**, in the accordion
component. For each: the question, a short confident answer in the narrator's voice, and a
code-window, query-window or diagram that proves it.

**Groups:** Terminal & Git · Programming & Java · Data structures · Algorithms · Mathematics &
theory · Computer architecture · Operating systems · Networks · Databases & SQL · Software
engineering · Security · HTML & accessibility · CSS & layout · JavaScript & async · Data
visualization · TypeScript · React · Next.js · Supabase & backend · Hosting, DNS & DevOps · AI ·
Your project · Behavioural.

Open it in his voice: *"Every one of these you already answered somewhere in this course. This
page is just them, lined up, so you can hear yourself say it."*

Close it with: the two-minute University Management App walkthrough; a mock-interview plan (warm-up →
coding → fundamentals → database → systems → front-end → design → behavioural); and a one-page
cheat sheet of the answers most likely to come up.

## §E.2 The real-world map

A closing orientation page, framed as *beyond this course*, in his voice. **Verify any current
specifics at build time rather than shipping a stale claim.** Cover: where these skills sit in the
industry now; that the fundamentals in Part B transfer no matter what the frameworks do; what you
can build (internal tools, dashboards, CRMs and ERPs, SaaS, public sites, APIs — the University Management App is a
template for most business software); what this stack is *not* the usual choice for (native
high-performance mobile, systems programming, machine-learning research, heavy data engineering);
the roles this opens and the ladders from each; hosting and cost realities; and what to build next
— extend the University Management App, then build something that isn't a university, to prove to yourself the
skills were never about the domain.

---

# PART FIVE — HOW TO BUILD THIS COURSE

## §16. Build order

1. **The component kit first** — `assets/styles.css` and `assets/components.js` with everything in
   §11 (especially the **visualiser**, the **live-preview**, and the **wrong → right** toggle), the
   Sig model sheet, and the course home page showing the A → E structure and the chain.
2. **Part by part, A → E, in order.** For each track: the track overview → each chapter in order →
   within each chapter, the chapter overview, the lessons in order, the glossary, the interview
   Q&A, and the exercise set.
3. **Per lesson:** the eleven beats, one idea, the components, the illustration prompt, any
   diagrams, the thumbnail spec, a `go-deeper` reference where it earns its place, and media slots.
4. **Part D only:** restate what already exists in the University Management App before each build lesson, and never
   contradict an earlier one. Give the plan and the acceptance criteria, not the code.
5. **The Interview Vault**, then **the real-world map**.
6. **A final pass over the whole chain:** walk every lesson in order and verify each one's first
   line answers the previous hook and its last line sets the next.

**Layout:**
```
/index.html                     the map, and the chain made visible
/assets/styles.css  /assets/components.js
/part-a-foundation/         a1-machine/  a2-terminal/  a3-git/  a4-software/
/part-b-core/               b1-programming/  b2-data-structures/  b3-algorithms/
                            b4-mathematics/  b5-architecture/  b6-operating-systems/
                            b7-networks/  b8-databases/  b9-engineering/  b10-security/
/part-c-technologies/       c1-html/ c2-css/ c3-javascript/ c4-dataviz/ c5-toolchain/
                            c6-typescript/ c7-react/ c8-nextjs/ c9-supabase/
                            c10-hosting/ c11-ai/
/part-d-university-management-app/       d1-design/ d2-build/ d3-real/ d4-finished/
/part-e-interview/
/university-management-app/              the system itself: /java-tools /sql /web /ai /README.md
/interview-vault/index.html
/real-world/index.html
```

**Per-lesson checklist:**
- [ ] First line resolves the previous cliffhanger; last line sets the next.
- [ ] Exactly one idea; nothing from a later chapter smuggled in.
- [ ] Every example is the university; no banned generic names or demo projects.
- [ ] It reads like the narrator sharing notes, not documentation.
- [ ] Every new word defined the first time, with a glossary chip.
- [ ] The code, query or command actually runs, and its real result is shown.
- [ ] Broken-first or wrong-first used where it teaches something.
- [ ] The trap is named — syntax, correctness, gotcha, or safety.
- [ ] Terminal commands are real, explained, and their failure modes covered.
- [ ] A diagram or visualiser wherever a picture teaches faster than a paragraph.
- [ ] Exactly two "Check yourself" questions; one is interviewer-style.
- [ ] Components throughout; never a wall of text; exactly one red element per view.
- [ ] Illustration prompt, thumbnail spec and any media slots present.
- [ ] In Part D: the plan and acceptance criteria, never the finished code (except 406).
- [ ] The word "capstone" appears nowhere.

## §17. Recall rounds

Run a short recall round — three or four questions pulling threads from the last stretch — inside
the "Check yourself" of the final lesson of each chapter, and a full recall at the end of each
part. These never break the chain; they sit inside beat 10, and the cliffhanger still closes the
page. Lesson 421 runs the full-course recall.

## §18. Do and don't

**Do**
- Teach in order: foundation → core computer science → technologies → the project → the interview.
- Keep the chain unbroken, every lesson, without exception.
- One idea per lesson; split rather than mingle.
- Keep everything in the university world.
- Write as the job seeker of 2027 sharing the notes that got him hired.
- Live in the terminal: real commands, real output, real failures.
- Implement every data structure by hand before using the library one.
- Teach correct, not just runnable; default the toggle to wrong.
- Put the red safety callout before the reader can do the damage, never after.
- Draw it, or build a step-through visualiser, whenever a picture teaches faster.
- Pair every code sample with its visible result.
- Let the reader attempt before revealing; show the beginner and the experienced version.
- Warn before the hard chapters: objects, generics, dynamic programming, concurrency, joins,
  window functions, async, the component model, server versus client, row-level security.
- Name the AI limits every time you praise its usefulness.
- Verify current real-world claims at build time.

**Don't**
- **Never use the word "capstone."** Say "the finished system" or "putting it all together."
- Don't front-load technologies, frameworks or AI before the foundation that explains them.
- Don't let the reader use AI to generate the data structures, algorithms or queries in Part B.
  That is where the understanding is built.
- Don't mingle topics, merge lessons, or skip one; don't open a lesson without answering the
  previous cliffhanger.
- Don't hand over practice answers first, and don't write the University Management App for the reader.
- Don't use a generic demo — no to-do apps, no blogs, no `foo`, no `Animal`, no `employees` table.
- Don't dump long code or long queries.
- Don't use a word you haven't defined.
- Don't fake praise, and don't call something easy that isn't.
- Don't imply a shown result came from a live run when it's illustrative.
- Don't invent a URL for a video or an image. Leave a slot with search terms.
- Don't let more than one red element appear in a single view.
- Don't teach a second framework, dialect or cloud as a subject. Name it and move on.

---

# THE LAST WORD (put this on the course home page, in his voice)

> I want to be honest with you about what this is.
>
> This is not a shortcut. There isn't one. It's a long walk, and some of it is genuinely hard —
> the day objects finally made sense took me two weeks, and I nearly quit twice before then.
>
> And I'm going to do something that will annoy you at first: I'm going to make you learn the
> foundation before you build anything impressive. No websites for a while. No frameworks. Just
> the machine, the code, the structures, the algorithms, the operating system, the network and
> the database.
>
> Here's why. I spent my first months building things I didn't understand, from tutorials, and
> the moment anything broke I was helpless — because I'd learned the shapes and not the reasons.
> The people who got hired around me weren't better at React. They understood what was
> underneath it.
>
> So we do the foundation first. Then the tools, and every single one will make sense
> immediately, because you'll already know what problem it exists to solve. And then — last, when
> you can actually do it justice — you build the real thing. One system, the University Management App, live on a
> domain you own, that anyone in the world can visit.
>
> That system is what got me hired. Not my marks. Not my college. Not a certificate. A link I
> could send someone, and the ability to explain every line behind it.
>
> Start at lesson one. Don't skip the boring parts — they're the parts that make the interesting
> parts easy.
>
> I'll see you at the end of it.