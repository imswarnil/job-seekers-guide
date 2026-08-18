# java-content.md — the complete build spec for "Java for Job Seekers"

**Read this once, then build the whole course.** This is the single,
self-contained brief for producing the course end to end. You (Claude Code) will
not be handed any other project files — everything you need is in here: the
promise, the voice, the rules, the full lesson map in order, the capstone project,
the interactive HTML component system, the drawing/diagram rules, the external-
reference rules, the interview material, and the exact order to build things in.

Your job: **produce the entire course as interactive HTML lessons** (plus a shared
kit, an interview vault, and one final Java program), section by section, in order,
following the sequence in §16. Do not ask which lesson to write — write them all.

---

## 0. The promise (the north star — reread this before every lesson)

This course exists for **one person: someone who has never written a line of code.**
By the end they must be able to:

1. **Read and write Java without syntax errors** — because we make the shape of the
   language second nature and we name every common syntax trap before they hit it.
2. **Build logic by reasoning, not by memorising** — they think a problem through
   in plain steps first, then translate it to code.
3. **Do all of that by picturing a university** — every idea is anchored to a
   real-world **university admissions** scenario, so nothing is abstract. When the
   reader wonders "why would I ever use this?", the answer is already on the page,
   in the admissions world they now know well.

Every decision in this spec serves that promise. If something you're about to write
doesn't help a total beginner write correct code and build logic through a
university example, cut it or rewrite it.

---

## 1. What this course is

- **One course → sections → lessons.** A section is a chapter; a lesson is one
  interactive blog post — the unit the reader consumes.
- **One theme, forever:** **university admissions.** Every example lives in this
  world. This is a hard rule (see §8).
- **One capstone project:** **AdmitDesk** — a console university-admissions manager
  that starts as ten lines printing a grade and grows, lesson by lesson, into one
  complete, heavily-commented Java program by the final lesson. We **never** start a
  second project. Full spec in §17.
- **Scope:** pure Java (JDK) + JUnit 5 only. No frameworks, web, databases, or third-
  party libraries — see §7. (The lessons are *delivered* as HTML; HTML is the
  classroom, Java is the subject. Never teach HTML/JS as if it were the course.)

---

## 2. The voice (lock this in every lesson)

Write as **a job seeker sharing the notes that got them hired** — the person one
step ahead, turning around to say "here's the bit that confused me, and here's what
made it click." Not a professor. Not a textbook.

- **First person, talking to one reader.** "you," not "students." "Here's what
  finally clicked for me." "Watch what happens when I run this."
- **Beginner-first.** Assume zero prior programming knowledge, always.
- **Warm, confident, honest — never fake praise.** If a topic is genuinely hard
  (objects, generics, concurrency), **say so before it arrives**, so the reader
  never blames themselves.
- **Backstory for credibility** (use when natural, don't repeat every lesson):
  *"I joined JSpiders on 16 August 2017 to learn Java with one goal — a job. I
  wasn't a genius or from a fancy college. I learned it one small idea at a time.
  These are the notes I wish someone had handed me."*
- **The thesis, proven again and again:** *"You don't know nothing, and Java is
  easy — because it's just small ideas stacked in order, and once you know one
  language well you can answer almost any interview question, because the ideas
  repeat."*
- **No jargon without a definition.** The first time a word appears, define it in
  plain English in the same breath: *"a method — a named block of steps you can run
  whenever you want — …"*
- **Avoid** "simply," "just," "as we all know," "it is important to note." Don't
  pad. Don't over-caveat. Short sentences at the hard parts.

---

## 3. The rule that matters most: the cliffhanger chain

**Every lesson ends on a cliffhanger. The very first line of the next lesson answers
it — before anything else.** This is a **chain, not a list.** No lesson is a fresh
start. Today's lesson is the thing yesterday's lesson made the reader *need*. Think:
letters → words → phrases → sentences → paragraphs.

The exact hook each lesson ends on is given in the map (§10). Use it as the closing
beat, unresolved. Open the next lesson by resolving exactly that hook, directly, in
one or two sentences. **The open (resolve) and the close (hook) appear on every
single lesson**, without exception.

---

## 4. One topic per lesson — never mingle (important, new rule)

A beginner drowns when two new ideas arrive at once. So:

- **One lesson teaches one idea.** A methods lesson teaches methods — not objects,
  not recursion, not "a bit of OOP while we're here." An OOP lesson teaches its one
  OOP idea and nothing from another chapter. Do not smuggle a future topic in "just
  to show it." If the reader needs a word we haven't taught yet, either it's not
  time for this lesson, or that word gets its own lesson first.
- **Closely-related sub-parts are allowed, in sequence, if they're the same theme.**
  "Scope, static and final" are one coherent theme about *where names live*, so they
  can share a lesson — taught one after another, each fully, not blended into mush.
  But "methods" and "polymorphism" are different chapters and never share a lesson.
- **You may split any mapped lesson into focused sub-lessons** when one idea per
  lesson demands it — e.g. deliver lesson 16 as **16a StringBuilder → 16b formatting
  → 16c text blocks**. Rules for splitting:
  - Each sub-lesson is one idea, fully taught.
  - Sub-lessons are chained by their own small **micro-hooks** (16a ends on a little
    hook that 16b opens by answering), exactly like the main chain.
  - The **last** sub-lesson ends on the **mapped cliffhanger** for that number, so
    the main chain is preserved and the next numbered lesson still opens correctly.
  - Keep the numbering readable (`L16a`, `L16b`); the section still flows in order.
- **Sections can grow too.** If a chapter genuinely needs more lessons to stay
  one-idea-at-a-time, add them — but keep the chain intact and every added lesson
  inside the university world. More lessons is fine; mingled lessons is not.

> The test: if a confused reader asked "what is *this* lesson about?", they must be
> able to answer in **one short phrase**. If the honest answer is "two things,"
> split it.

---

## 5. The lesson shape (11 beats)

Write every lesson in this order. Keep headings light — it's a blog post. Map each
beat to the interactive components in §12.

1. **Resolve last lesson's cliffhanger** — first thing, directly. One or two
   sentences answering exactly what was left hanging.
2. **Where that leaves us** — one line bridging into today.
3. **Why we're learning this** — the reason Java needs this thing at all; what
   breaks without it. *Never introduce syntax before the reason for it.*
4. **The idea, analogy first** — an everyday analogy, then the technical version.
   (A variable is a labelled box. An interface is a job description. A generic is a
   form with a blank to fill in later.) **If a drawing explains it faster than
   words, draw it here (§13).**
5. **A small runnable example** — 10–20 commented lines, in the university world.
   Where it helps, show the **broken version first (the real error), then the fix.**
   No walls of code. Every sample must **compile and run** exactly as shown.
6. **What confuses people here** — name the trap before the reader falls in it,
   including the **syntax trap** if there is one (§6).
7. **How you'd use this in real life** — one concrete non-toy situation, **inside
   university admissions.**
8. **What you get out of it** — what the reader can now *do* that they couldn't
   before, plus the **actual printed output** so they can check their result.
9. **Your turn** — the exercise / problems / broken code to fix / next AdmitDesk
   step. Follow the practice rule (§9).
10. **Check yourself** — exactly **2 questions**, and **one is the kind an
    interviewer actually asks.**
11. **The cliffhanger** — the exact hook from the map, left hanging. Last thing on
    the page.

Not every beat is heavy in every lesson (a `[P]` lesson leans on beat 9; a `[Q]`
lesson is mostly interview Q&A), but beat 1 (resolve) and beat 11 (hook) are always
present.

**The ten teaching habits:** idea before syntax · analogy then technical · define
every new word the first time · one idea per lesson · short code · broken-first
where it helps · warn before hard parts · say what happens in memory when it matters
(stack/heap, references, what `new` does) · be honest about wrong answers · never
break the chain.

---

## 6. Writing code with zero syntax errors (a teaching duty, not an afterthought)

Because the promise (§0) is that a beginner writes correct code, treat syntax safety
as a first-class teaching job:

- **Every code sample compiles and runs.** No pseudo-code passed off as real code.
- **Name the common syntax mistakes as they become relevant**, using a dedicated
  `syntax-trap` callout (§12): the missing semicolon, mismatched `{ }` braces, `=`
  vs `==`, calling a method without `()`, wrong case (`String` vs `string`), missing
  `return`, comparing strings with `==`, forgetting `new`, off-by-one in a loop.
- **Show the exact compiler/runtime error text** in the broken→fixed component so the
  reader learns to *read errors*, not fear them.
- **Teach a habit of reasoning first, coding second:** in most lessons, write the
  logic in plain English steps (an admissions decision) *before* the Java, so the
  reader sees code as a translation of thought, not magic.

---

## 7. Scope — pure Java only (hard boundary)

**Allowed in taught content:** the **JDK** and **JUnit 5**. Target **Java 21**
(name the version only when a modern feature actually appears: records, sealed
classes, switch expressions, virtual threads). Everything runs from a `main` method
or a JUnit test.

**Never taught as course content:** Spring or any framework, web, HTML/CSS/JS/React
*as a subject*, SQL/databases, REST/APIs, build tools beyond `javac`/`java` (and
later a plain runnable JAR), deployment, cloud, or any third-party library. If a
lesson seems to need one, **teach the Java idea without it and say in one line that
the rest is deliberately out of scope.** Persistence is plain files
(`java.nio.file`), never a database.

The **only** place the wider ecosystem appears is the real-world closing section
(§14), explicitly framed as "what to learn *after* this course."

---

## 8. The university rule (strict — enforce it everywhere)

**Every example, variable, class, exercise and diagram lives in the university-
admissions world. No exceptions.** This is what makes a hundred lessons feel like one
coherent story and lets a beginner reason about code by picturing something real.

- **Recurring cast (use these names):** `Applicant` (a person applying),
  `Application` (one applicant's submission to one program), `Program` (a degree with
  a name, seats, a cutoff score), `ExamScore` (a subject and a mark), `Status`
  (`RECEIVED`, `UNDER_REVIEW`, `SHORTLISTED`, `REJECTED`, `ACCEPTED`), `Reviewer` /
  `StaffUser` (staff who review applications), and the app itself, **AdmitDesk**.
- **Stray values still belong to the world:** use `score`, `applicantName`,
  `deadline`, `cutoff`, `seats`, `programName` — never a throwaway `x`, `temp`, or
  `foo` unless the lesson is literally about naming.
- **Banned, always:** `Animal`, `Dog`, `Cat`, `Shape`, `Circle`, `Car`, `Vehicle`,
  `Employee`/`Person` generic demos, `Foo`, `Bar`, `Baz`. If you catch yourself
  reaching for one of these, replace it with the admissions equivalent
  (`Applicant`, `Program`, `Reviewer`, `Status`).
- **Put the rule on the page where it counts:** the university framing should be
  visible in the example itself, so the reader is always reasoning about admissions,
  not abstract shapes.

> Enforcement line to keep in mind on every lesson: **"If this example isn't about a
> university admission, rewrite it until it is."**

---

## 9. The practice rule

Lessons are tagged concept `[C]`, practice `[P]`, debugging `[D]`, app-building
`[A]`, or interview `[Q]`.

- **Practice `[P]`:** give the problems and hints. **Let the reader attempt before
  revealing any solution** (solutions live behind the reveal component, §12). When
  you show a solution, show **two versions**: the *beginner* version (correct but
  clumsy) and the version an *experienced developer* writes, with a plain-words note
  on why the second is better (no jargon dump).
- **Debugging `[D]`:** show the broken program, the wrong output or error, and walk
  the reader through *finding* it — print debugging first, then the real debugger
  (breakpoints, step over/into, watches). Draw the state when it helps (§13).
- **App-building `[A]`:** give the **plan** — class names, method names, and the
  **acceptance criteria** — **not the finished code** (the final capstone lesson is
  the one exception; it presents the complete program). The reader writes it; you
  describe what "done" looks like.

Never give a practice answer before the reader has attempted it. Never write
AdmitDesk's code for the reader (until the capstone lesson). Never praise code that
doesn't deserve it; name wrong or half-right answers as such, with the reason.

---

## 10. The full lesson map (in order)

Build in this order. Each entry: number, title, `[type]`, one-line focus, and the
**cliffhanger** it ends on (the next lesson opens by answering it). You may split any
entry into focused sub-lessons per §4; the last sub-lesson keeps the mapped
cliffhanger. `[DIAGRAM]` marks a lesson that needs a drawn diagram (§13) — but draw
elsewhere too, whenever a picture teaches faster.

### Section 0 — Before you write a line of code (1–7)
*What & why: a job seeker who understands what's happening under the hood debugs
faster and interviews better. Earn the right to Java by first knowing what a
computer, a program and a language even are.*

1. **What a computer actually does** `[C]` — instructions, memory, input/output; a
   machine unimaginably fast and completely literal.
   *Hook:* a computer only understands electricity on or off — so how does anything
   you type ever become something it can run?
2. **What a program is** `[C]` — idea vs algorithm vs code; the steps of a decision
   in plain English first.
   *Hook:* if a program is just a list of steps, why can't you write those steps in
   English and hand them to the machine?
3. **What a programming language is** `[C]` — syntax vs meaning, keywords, grammar,
   why human language fails at this.
   *Hook:* you can write something that *looks* like code; the machine still can't
   read a word of it. What stands between the two?
4. **How code becomes something that runs** `[C]` — compilers, interpreters, machine
   code, the translation step.
   *Hook:* Java doesn't compile to machine code — it compiles to bytecode. Why add a
   middle step nobody asked for?
5. **Why Java exists** `[C]` — the problem Java solved, "write once, run anywhere,"
   where Java runs today, honest comparison with Python and JavaScript. *(Origin-
   story video hook here — §15.)*
   *Hook:* bytecode needs something to run it — the JVM — and it's not the same as the
   JDK or JRE, though everyone mixes them up.
6. **JDK, JRE and JVM** `[C]` — what each is, what the JVM does while your program
   runs, why we use Java 21.
   *Hook:* enough theory — what actually happens on your machine the moment you type
   `javac`?
7. **Compiling and running by hand** `[C]` — installing Java, `javac`, `java`,
   `.class` files, the classpath, before an IDE hides it.
   *Hook:* it ran — but only once you wrapped everything in a class and a method
   called `main`. Who calls `main`, and why that exact shape?

### Section 1 — Your first words of Java (8–13)
*What & why: letters before words. The smallest pieces every later idea is built
from.*

8. **The anatomy of a Java program** `[C]` — classes, `main`, statements, setting up
   IntelliJ, reading your first error message.
   *Hook:* your program prints the same line forever; to do anything useful it must
   remember things. Where does a remembered value actually live?
9. **Variables and the eight primitive types** `[C]` — declaration, assignment,
   literals, the eight primitives, `var`.
   *Hook:* copy a number into another variable and change it — the original is
   untouched. Do the same with an object and the original changes too. Same syntax,
   opposite result.
10. **Stack, heap and references** `[C]` `[DIAGRAM]` — what a variable really holds,
    what a reference is, what `null` means.
    *Hook:* now you can store values — time to calculate, except `7 / 2` gives `3`
    and `0.1 + 0.2` isn't `0.3`.
11. **Operators** `[C]` — arithmetic, comparison, logical, precedence; integer
    division; why floating point lies.
    *Hook:* putting an `int` into a `double` works silently; putting a `double` into
    an `int` refuses to compile. Something is deciding what's allowed.
12. **Type conversion and casting** `[C]` — widening, narrowing, overflow, when Java
    refuses to guess.
    *Hook:* every value so far has been typed in by you. A program nobody can talk to
    isn't much of a program.
13. **Reading input with Scanner** `[C]` — reading numbers and text, and what happens
    when the user types something unexpected.
    *Hook:* input, output, arithmetic, storage — that's genuinely enough to write real
    programs. Can you write ten?

### Section 2 — Warm-ups and text (14–17)
*What & why: the first "I can build things," then the first real object — the
String.*

14. **Ten warm-up programs** `[P]` — swap two numbers, odd/even, largest of three,
    simple interest, area of shapes (framed as room/seat area), temperature
    conversion, percentage/grade, leap year, sum of first N, multiplication table.
    *Hook:* several needed text — and comparing two identical-looking applicant names
    with `==` said they were different.
15. **Strings** `[C]` — text as objects, immutability, the string pool, `==` vs
    `equals`, the methods you'll actually use.
    *Hook:* if a String can never change, what's happening when you build one inside a
    loop that runs ten thousand times?
16. **StringBuilder, formatting and text blocks** `[C]` — why concatenating in a loop
    is slow, `printf`, multi-line strings. *(Good candidate to split 16a/16b/16c per
    §4.)*
    *Hook:* you can take text apart and rebuild it — reverse it, check if it reads the
    same backwards, find repeated letters. The classics.
17. **Classic string programs** `[P]` — reverse, palindrome, count vowels/consonants,
    character frequency, remove duplicates, anagram check, capitalise each word,
    longest word — all over applicant names and program codes.
    *Hook:* every one made a decision — small ones so far. What happens when there are
    ten possible answers instead of two?

### Section 3 — Decisions and loops (18–24)
*What & why: real programs choose and repeat; the reader starts writing logic and the
first real AdmitDesk piece.*

18. **Conditionals** `[C]` — `if`/`else`, nesting, ternary, conditions a human can
    read.
    *Hook:* ten branches means ten `else if` lines and it reads terribly. For a fixed
    known set, Java has something built for exactly this.
19. **switch** `[C]` — the classic form, fall-through, modern switch expressions.
    *Hook:* you now have input, decisions and output — a real program's worth of tools.
    Time to build one.
20. **Your first real program: the grade calculator** `[A]` — read an applicant's
    marks → percentage → grade → formatted result. *(The seed of AdmitDesk.)*
    *Hook:* it handles one applicant perfectly; for thirty you'd paste the block thirty
    times — then the rules change and you edit thirty copies.
21. **Loops** `[C]` — `for`, `while`, `do-while`, enhanced `for`, how to choose.
    *Hook:* your loop ran one time too many and printed a blank row; a different one
    never stopped at all.
22. **Loop control and the off-by-one error** `[C]` `[DIAGRAM]` — `break`,
    `continue`, nested loops, why the boundary is where the bug lives.
    *Hook:* with loops you can write every program schools and interviewers have asked
    for decades — factorials, primes, Fibonacci.
23. **Classic number programs** `[P]` — factorial, Fibonacci, prime check, list of
    primes, Armstrong, palindrome number, reverse digits, sum of digits, GCD/LCM,
    perfect number.
    *Hook:* each used a single loop. What can you do with a loop *inside* a loop? Every
    printed pyramid you've ever seen, for a start.
24. **Pattern printing** `[P]` — triangles, pyramids, inverted, diamonds, number and
    letter patterns; the traditional nested-loop test.
    *Hook:* everything so far handles one value at a time. Thirty applicants would mean
    thirty variables named `applicant1` through `applicant30`.

### Section 4 — Many values, and finding bugs (25–30)
*What & why: you can't hold a cohort in thirty variables. Arrays hold many — and bugs
hide among them, so you learn to debug, then build the first multi-applicant
AdmitDesk.*

25. **Arrays** `[C]` `[DIAGRAM]` — fixed size, zero-indexing, iteration,
    `ArrayIndexOutOfBoundsException`.
    *Hook:* an array is one row; a timetable is a grid. Can an array hold other arrays?
26. **Two-dimensional arrays and the Arrays utility** `[C]` — arrays of arrays; `sort`,
    `copyOf`, `toString`, `equals` from `java.util.Arrays`.
    *Hook:* arrays plus loops is the single most-tested combination there is. Second
    largest, duplicates, the missing number.
27. **Classic array programs** `[P]` — largest/smallest, second largest, reverse,
    rotate, sum/average, find duplicates, missing number, frequency, merge two, matrix
    add and transpose — over score arrays.
    *Hook:* you found values by checking every element. If the array is already sorted,
    there's a way to find anything in a handful of steps.
28. **Searching and sorting by hand** `[C]` `[DIAGRAM]` — linear/binary search,
    bubble/selection/insertion sort, and why you'd never write these in real code.
    *Hook:* your sort produced the wrong order; prints everywhere and you still can't
    see it. There's a way to freeze a running program and look inside.
29. **Finding a bug** `[D]` — syntax vs runtime vs logic errors; print debugging vs a
    real debugger; breakpoints, step over/into, watches — on a loop with a wrong total.
    *Hook:* you can find bugs now. Time to build something big enough to have some.
30. **Building the applicant marks manager** `[A]` — menu-driven console app: add an
    applicant, list everyone, cohort average, topper, search by name — arrays and loops
    together. *(AdmitDesk grows up.)*
    *Hook:* your `main` is two hundred lines and the same block appears three times.
    *(Recall round #1 inside this lesson's "Check yourself.")*

### Section 5 — Methods and recursion (31–36)
*What & why: a 200-line `main` is unmaintainable. Methods name and reuse steps. Then
the strangest method — one that calls itself. **Teach methods here; no OOP yet.***

31. **Methods** `[C]` — signatures, parameters, return values; splitting code into
    named pieces is the whole job.
    *Hook:* you need the same calculation for whole numbers and decimals; two methods
    means two names, and `calculateAverageDouble` is ugly.
32. **Overloading, and what "pass by value" really means** `[C]` `[DIAGRAM]` — same
    name, different parameters; what Java actually hands to a method (surprises
    everyone).
    *Hook:* a method can call another method. Nothing stops a method calling itself.
    What would that even do?
33. **Recursion** `[C]` `[DIAGRAM]` — a method that calls itself, base cases, the call
    stack growing, `StackOverflowError`.
    *Hook:* elegant, and dangerous. The classics show both sides.
34. **Recursion programs** `[P]` — factorial, Fibonacci, sum of digits, reverse a
    string, recursive binary search, Towers of Hanoi, and when recursion is wrong.
    *Hook:* you've typed `static` before every method since lesson 8 without knowing
    what it does — and a variable declared in a loop vanishes when the loop ends.
35. **Scope, static and final** `[C]` — where a name is visible, class vs instance
    members, constants, when `static` is a smell. *(One coherent theme; teach each part
    fully, in sequence.)*
    *Hook:* that `StackOverflowError` — you don't have to imagine it. You can watch it
    happen, one frame at a time.
36. **Watching the call stack** `[D]` `[DIAGRAM]` — stepping through recursion in the
    debugger, frames stacking and unwinding, reading an overflow as it forms.
    *Hook:* your marks manager keeps names in one array and scores in another, matched
    by position. Sort one and forget the other, and everyone gets someone else's marks.

### Section 6 — Objects: the big turn (37–44)
*What & why: **warn the reader — the hardest conceptual jump in the course.** Parallel
arrays don't scale; the fix is bundling data and behaviour into objects. This is
where Java becomes Java. Teach OOP one pillar at a time — never mix a second chapter
in.*

37. **Classes and objects** `[C]` `[DIAGRAM]` — blueprint vs instance, what `new`
    really does.
    *Hook:* you made an object and its fields were all zeros, nulls and falses. Nothing
    you wanted. How does an object get born already correct?
38. **Constructors and `this`** `[C]` — how an object is created and initialised,
    constructor chaining, the order things happen in.
    *Hook:* your constructor guarantees a valid applicant — then any line anywhere can
    set the score to minus fifty and nothing stops it.
39. **Encapsulation** `[C]` — private fields, getters/setters done properly, why public
    fields are a bug, what an invariant is.
    *Hook:* you have a real `Applicant` class now — but your program still stores
    applicants in three parallel arrays like it's lesson 25.
40. **Rebuilding the app with objects** `[A]` — replace parallel arrays with a list of
    `Applicant` objects; where OOP stops being an abstraction. *(AdmitDesk's first real
    rewrite.)*
    *Hook:* staff need a name, email and phone too. You could copy those three fields
    into a second class — or tell Java "the same as that one, plus a few extras."
41. **Inheritance** `[C]` `[DIAGRAM]` — `extends`, what a subclass gets free,
    `protected`, what inheritance quietly costs.
    *Hook:* your subclass constructor can't reach the parent's private fields. So who
    sets them, and when?
42. **super and constructor chaining** `[C]` `[DIAGRAM]` — how an object is built
    parent-down.
    *Hook:* `Applicant` and `StaffUser` both have `describe()`. Put both in a
    parent-typed variable and call it — which one runs?
43. **Polymorphism** `[C]` `[DIAGRAM]` — overriding, dynamic dispatch, one call
    behaving differently depending on what's really there.
    *Hook:* through the parent-typed variable you can't reach the child's own methods.
    Getting back down is possible — and it's where `ClassCastException` comes from.
44. **Casting and instanceof** `[C]` — upcasting, downcasting, `ClassCastException`,
    pattern matching for `instanceof`.
    *Hook:* a plain `User` isn't a real thing — every user is an applicant or a staff
    member. Can you stop anyone writing `new User()` at all?

### Section 7 — Abstraction, contracts and clean design (45–52)
*What & why: with objects in hand, learn how professionals *shape* them — abstract
classes, interfaces, composition — and meet the object-equality trap before the first
big OOP interview round and the AdmitDesk v1 rewrite.*

45. **Abstract classes** `[C]` — abstract methods, partial implementations, when one is
    right.
    *Hook:* a class can only extend one parent. What about something that must be both
    notifiable and scoreable, when those share nothing else?
46. **Interfaces** `[C]` `[DIAGRAM]` — contracts, implementing several at once, default
    and static methods.
    *Hook:* an interface with default methods looks a lot like an abstract class. So
    which do you use, and why does it matter?
47. **Interface or abstract class** `[C]` — the decision, stated plainly, an example
    each way.
    *Hook:* even with the right choice, five levels of inheritance becomes a codebase
    nobody can change. There's an alternative most experienced developers reach for
    first.
48. **Composition over inheritance** `[C]` `[DIAGRAM]` — "has a" vs "is a", delegation,
    why deep hierarchies rot.
    *Hook:* you put two `Applicant` objects with identical details in a list, asked Java
    if they're equal, and it said no.
49. **toString, equals and hashCode** `[C]` — what `Object` gives every class, the
    `equals`/`hashCode` contract, exactly what breaks when you honour one not the other.
    *Hook:* everything from lesson 37 on is what interviewers actually ask about. Can you
    defend it?
50. **The object-oriented questions you'll be asked** `[Q]` — overloading vs overriding,
    overriding a static method, why Java forbids multiple inheritance, override `equals`
    but not `hashCode`, abstract class vs interface — each answered with code that proves
    it.
    *Hook:* you called a method on something and Java threw the most common error in the
    language at you.
51. **NullPointerException** `[C]` `[DIAGRAM]` — why it happens, the detailed messages
    Java now gives, finding the actual null in a chain, fixes that aren't just another
    null check.
    *Hook:* you now have every tool for a genuinely object-oriented program. The old one
    deserves a rewrite.
52. **The admissions app, version one** `[A]` — add applicants, list, search, shortlist
    by score; classes, encapsulation and polymorphism together. *(AdmitDesk v1.)*
    *Hook:* status is a String, so `"SHORTLSTED"` compiles perfectly and silently breaks
    everything. The compiler could have caught that.

### Section 8 — Better-shaped data and where memory goes (53–59)
*What & why: strings-as-status is a bug factory. Java has purpose-built shapes — enums,
records, sealed types — and it's time to understand where all those objects go.*

53. **Enums** `[C]` — fixed sets of values (the `Status` set), fields and methods on
    constants, switching over them, why they beat string constants.
    *Hook:* an enum is a fixed set of constants. What about a small bundle of data that
    should never change once created — is that really sixty lines of constructor,
    getters, `equals` and `toString`?
54. **Records and immutability** `[C]` — what a record writes for you (`ExamScore` as a
    record), compact constructors, why immutable objects cause fewer bugs.
    *Hook:* some small helper classes are only ever used inside one other class. Does
    each really need its own file?
55. **Nested, inner and anonymous classes** `[C]` — the four kinds and what each is
    genuinely for.
    *Hook:* you can control who creates a class. Can you control who is allowed to
    *extend* it?
56. **Sealed classes and pattern matching** `[C]` — restricting the `User` hierarchy,
    exhaustive switches, record patterns.
    *Hook:* your project is dozens of files in one folder. And if you wanted to hand this
    program to someone else, what exactly would you send them?
57. **Packages, access modifiers and JARs** `[C]` — organising a codebase, the four
    access levels, packaging AdmitDesk into a runnable JAR.
    *Hook:* you've created thousands of objects and deleted none. In some languages
    that's a catastrophe. Where did they all go?
58. **Garbage collection** `[C]` `[DIAGRAM]` — how Java frees memory, what "unreachable"
    means, why there's no `free`, what a Java memory leak looks like anyway.
    *Hook:* stack and heap, the string pool, `==` vs `equals`, copying objects. This is
    exactly the ground interviewers dig into.
59. **The memory and object questions you'll be asked** `[Q]` `[DIAGRAM]` — stack vs
    heap, the string pool, shallow vs deep copy, what `finalize` was and why it's gone.
    *Hook:* somewhere in forty steps of your program, one field became wrong. Stepping
    through forty times is unbearable; printing everything drowns you.

### Section 9 — When things go wrong (60–65)
*What & why: users type garbage, files vanish, nulls appear. A hireable developer
writes programs that survive all of it. This section makes AdmitDesk unbreakable.*

60. **Conditional breakpoints and watches** `[D]` — stopping only when a condition is
    true, watching a field, evaluating expressions mid-run.
    *Hook:* you've fixed your own mistakes. But when a user types a letter into a number
    field, that isn't your bug — and the program still dies.
61. **Exceptions** `[C]` `[DIAGRAM]` — the `Throwable` hierarchy, checked vs unchecked,
    `try`, `catch`, `finally`.
    *Hook:* Java answered with two hundred lines of red text. Somewhere in there is one
    line that matters.
62. **Reading a stack trace** `[D]` — reading bottom-up, finding your own code in someone
    else's, what `Caused by` means.
    *Hook:* Java's built-in exceptions describe Java's problems, not yours. "Applicant
    already applied" isn't in the JDK — and a file you opened is still open.
63. **Custom exceptions and try-with-resources** `[C]` — designing an exception worth
    throwing (`DuplicateApplicationException`), `AutoCloseable`, why lone
    `catch (Exception e)` is dangerous.
    *Hook:* does `finally` always run? What if there's a `return` inside it? These have
    trapped people for twenty years.
64. **The exception questions you'll be asked** `[Q]` — checked vs unchecked, whether
    `finally` always runs, `return` inside `finally`, catching an `Error`, exception
    chaining — each with code that settles it.
    *Hook:* your app still dies when someone types nonsense into it. Time to make that
    impossible.
65. **Making the app unbreakable** `[A]` — validation and exception handling throughout,
    so no input can kill the program. *(AdmitDesk hardened.)*
    *Hook:* your storage class holds applicants. You need one for programs too. Copy the
    file and change the type? There's a way to write it once.

### Section 10 — Generics (66–69)
*What & why: **warn the reader — abstract and slippery.** Copy-pasting a container per
type is madness; generics let you write it once, type-safe.*

66. **Why generics exist** `[C]` — life before generics, compile-time type safety, the
    `ClassCastException` you no longer get.
    *Hook:* you can now write a container that holds anything — which means you can't do
    anything with what's inside, because it could be anything.
67. **Generic classes and methods** `[C]` — type parameters, writing your own generic
    container properly (a `Repository<T>` for AdmitDesk).
    *Hook:* an `Applicant` is a `User`, so a `List<Applicant>` should be a `List<User>` —
    except Java flatly refuses.
68. **Bounded types and wildcards** `[C]` `[DIAGRAM]` — `extends`, `super`, `?`, a rule
    for deciding which you need.
    *Hook:* at runtime, ask a list what type it holds and it can't tell you. The
    information you carefully wrote is gone.
69. **Type erasure** `[C]` — what the compiler throws away, why, and the strange
    limitations that result.
    *Hook:* you've written your own container. Java ships an entire library of them,
    refined over twenty-five years.

### Section 11 — The collections framework (70–80)
*What & why: this is where most coding-round questions actually live. Learn the tools
professionals reach for daily, how they work inside, and rebuild AdmitDesk on them.*

70. **The collections framework** `[C]` `[DIAGRAM]` — the hierarchy, interfaces vs
    implementations, why it's built this way.
    *Hook:* two list implementations, identical methods, wildly different performance.
    Picking the wrong one has made real systems unusable.
71. **Lists** `[C]` `[DIAGRAM]` — `ArrayList` vs `LinkedList`, how `ArrayList` grows,
    when each wins.
    *Hook:* a list happily stores the same applicant twice. What if duplicates are what
    you're trying to prevent — and how would Java check "is this already here" without
    comparing against every item?
72. **Sets and hashing** `[C]` `[DIAGRAM]` — how hashing works, `HashSet`,
    `LinkedHashSet`, `TreeSet`, why lesson 49's `hashCode` suddenly matters.
    *Hook:* a set answers "is this here?" What you often need is "which applicant belongs
    to this email address?"
73. **Maps** `[C]` `[DIAGRAM]` — `HashMap` internals, `LinkedHashMap`, `TreeMap`,
    `computeIfAbsent`, `getOrDefault`.
    *Hook:* sometimes the order of processing is the whole point — first come first
    served, or always the highest score next.
74. **Queues and deques** `[C]` `[DIAGRAM]` — `Queue`, `Deque`, `PriorityQueue`, the real
    situations each models (a review queue, a highest-score-next shortlist).
    *Hook:* you removed an item while looping over the collection and Java threw
    `ConcurrentModificationException` — with only one thread running.
75. **Iterating safely** `[C]` — iterators, fail-fast behaviour, removing elements
    correctly while you loop.
    *Hook:* sorting numbers is obvious. Sorting applicants is not — by what? And what
    happens when two have the same score?
76. **Sorting objects** `[C]` — `Comparable` vs `Comparator`, chaining comparators, what
    a stable sort guarantees.
    *Hook:* you now have five collection types that all look interchangeable. Choosing
    badly can make the same program a thousand times slower.
77. **Choosing the right collection** `[C]` `[DIAGRAM]` — Big-O in plain words, the real
    cost of each operation, what it looks like when you get it wrong.
    *Hook:* collections are where most coding questions live. Here they come.
78. **Collection programs** `[P]` — word frequency, find duplicates, first non-repeating
    character, group applicants by program, top N by score, remove duplicates keeping
    order, merge two maps.
    *Hook:* interviewers won't stop at using a `HashMap` — they'll ask what happens inside
    one when two keys collide.
79. **The collections questions you'll be asked** `[Q]` `[DIAGRAM]` — how `HashMap` works
    internally, load factor and resizing, fail-fast vs fail-safe, `HashSet` vs `TreeSet`,
    `ArrayList` vs `LinkedList` in practice, why `String` makes a good key.
    *Hook:* your app still stores everything in arrays from lesson 30. It deserves better.
80. **The admissions app, version two** `[A]` — lists and maps replace the arrays; add
    filtering, multi-field sorting, duplicate detection, grouped counts. *(AdmitDesk v2.)*
    *Hook:* filtering, sorting and grouping took forty lines of loops that all look the
    same — each differs only in the *rule* applied. What if you could pass the rule itself
    into a method?

### Section 12 — Functional Java and streams (81–87)
*What & why: **warn the reader — a new way of thinking, not just new syntax.** Passing
behaviour as a value collapses repetitive loops into readable pipelines. Modern
interviews expect it.*

81. **Lambdas** `[C]` — functions as values, functional interfaces, closures, what
    "effectively final" means.
    *Hook:* half your lambdas do nothing but call a method that already exists. Writing
    `a -> a.getScore()` to mean `getScore` feels like typing for its own sake.
82. **Method references and the built-in functional interfaces** `[C]` — `Function`,
    `Predicate`, `Consumer`, `Supplier`, the `::` shorthand.
    *Hook:* one lambda at a time is fine. What you really do is filter, then transform,
    then sort, then collect — and each step means another loop.
83. **Streams** `[C]` `[DIAGRAM]` — pipelines, laziness, `filter`, `map`, `sorted`,
    `collect`.
    *Hook:* `collect(toList())` is where most people stop. But the question you get asked
    is "how many applications per program?"
84. **Collectors and grouping** `[C]` `[DIAGRAM]` — `groupingBy`, `partitioningBy`,
    `joining`, `reduce`, summary statistics.
    *Hook:* `findFirst` didn't return an applicant, and it didn't return null either. It
    returned something wrapping the answer.
85. **Optional** `[C]` — why nulls hurt, `map`/`flatMap`, `orElse` vs `orElseGet` vs
    `orElseThrow`, the anti-patterns people write anyway.
    *Hook:* go back to the loops in lessons 27 and 78 and write them again as streams.
    Then decide which version you'd actually defend in a review.
86. **Streams in practice** `[P]` — rewriting old loops, comparing versions honestly, the
    stream questions interviewers ask, and the cases where a plain loop is better.
    *Hook:* your app records the application date as a String. Now sort by it, find
    everyone who applied in the last week, and count the days left until the deadline.
87. **Dates and times** `[C]` — `LocalDate`, `LocalDateTime`, `Instant`, `Duration`,
    `Period`, formatting, time zones, why you store UTC (application dates, deadlines).
    *Hook:* close the program and every applicant you entered is gone. The computer has a
    disk. How does a program actually use it?

### Section 13 — Files and persistence (88–89)
*What & why: a program that forgets everything on exit isn't a tool. No database allowed,
so persistence is plain files — and that's enough.*

88. **Files and input/output** `[C]` `[DIAGRAM]` — streams vs readers, `Path` and
    `Files`, reading and writing text, why buffering matters.
    *Hook:* time to give your program a memory that survives being switched off.
89. **Saving and loading** `[A]` — write applicants to a file, read them back on startup;
    persistence with no database anywhere. *(AdmitDesk remembers.)*
    *Hook:* loading a large file froze the whole program — nothing responded until it
    finished. Can a program do two things at once?

### Section 14 — Concurrency (90–93)
*What & why: **the hardest stretch of the course — say so plainly, up front.** It
defeats careful people. Go slow, show the bugs live, build the mental model interviews
probe hardest.*

90. **Threads** `[C]` `[DIAGRAM]` — process vs thread, `Runnable`, the thread lifecycle,
    a race condition shown live. (Fair warning first.)
    *Hook:* two threads each added one to the same counter a thousand times. The total
    should be two thousand. It wasn't — and it was different every run.
91. **Synchronisation** `[C]` `[DIAGRAM]` — `synchronized`, `volatile`, atomic types,
    deadlock, why shared mutable state is so hard to reason about.
    *Hook:* creating a thread by hand for every task is wasteful, and there's no obvious
    way to get a result back out of one.
92. **Executors and modern concurrency** `[C]` — thread pools, `Callable` and `Future`,
    `CompletableFuture`, virtual threads.
    *Hook:* you put a breakpoint in your threaded code to find the race condition, and the
    bug disappeared. Every time.
93. **Debugging concurrent code** `[D]` — why breakpoints change the outcome, the threads
    view, reading a thread dump, why logging often beats stepping.
    *Hook:* frameworks read your classes, find your methods and call them without you
    wiring anything up. How can code inspect code?

### Section 15 — The professional finish (94–100)
*What & why: the difference between "can write Java" and "hireable" is testing, patterns,
clean code and knowing what the JVM does. This section closes the chain and completes
AdmitDesk.*

94. **Reflection and annotations** `[C]` — inspecting and invoking code at runtime,
    writing your own annotation, how frameworks use both (so you know what they do, even
    though we never use one).
    *Hook:* your app is large now. Every change risks breaking something you fixed three
    weeks ago, and you'd never know until a user found it.
95. **Testing with JUnit 5** `[C]` — why tests exist, assertions, arrange-act-assert,
    parameterised tests, what's genuinely worth testing.
    *Hook:* claiming your shortlisting logic works is easy. Proving it is the point.
96. **Testing the app** `[A]` — tests for the eligibility and shortlisting rules, then
    deliberately break the code and watch a test catch it before you do. *(AdmitDesk,
    verified.)*
    *Hook:* your quota rules are one enormous `if`/`else` chain that grows every time the
    university changes a policy. This exact problem has a name.
97. **Design patterns** `[C]` `[DIAGRAM]` — Strategy, Factory, Builder, Singleton,
    Observer — the admissions problem each solves, and when a pattern makes things worse.
    *Hook:* patterns fix the shape of a system. What makes a single method something
    another person can read without wincing?
98. **Clean code and SOLID** `[C]` — naming, small methods, the five principles, each with
    a Java example of the violation and the fix.
    *Hook:* your code is clean and correct. Is it fast? And what has the JVM been doing
    this whole time while you weren't looking?
99. **Inside the JVM** `[C]` `[DIAGRAM]` — class loading, JIT compilation, how the heap is
    structured, `-Xmx`, what a profiler shows, reasoning about performance instead of
    guessing.
    *Hook:* ninety-nine lessons of separate pieces. There's one thing left to do with them.
100. **The finished program, and everything you know** `[A]` `[Q]` — the complete AdmitDesk
     (the §17 capstone) in one console application; a full recall round across all hundred
     lessons; the interview questions this material produces; an honest map of what to learn
     next. *(AdmitDesk, complete and commented.)*
     *Hook:* none — this is the end. Close the loop and point the reader at their next
     mountain.

### Recall rounds
Run a short recall round (3–4 pull-the-threads questions) *inside the "Check yourself"*
of lessons **15, 30, 45, 60, 75, 90**, and the full recall in **100**. These don't break
the chain — the cliffhanger still follows.

### AdmitDesk build order (quick reference)
| Lesson | What the app becomes |
|--------|----------------------|
| 20 | One applicant: read marks → percentage → grade → print. |
| 30 | Menu-driven marks manager over parallel arrays. |
| 40 | Rewritten with a list of `Applicant` objects. |
| 52 | v1: applicants, programs, search, shortlist — full OOP. |
| 65 | Hardened: validation + exceptions, unbreakable input. |
| 80 | v2: lists and maps, filtering, multi-field sort, grouped counts. |
| 89 | Persistence: save to a file, load on startup. |
| 96 | Tested: JUnit around eligibility and shortlisting rules. |
| 100 | Final: everything together, fully commented (the capstone). |

Keep a running mental model of what classes and methods exist in AdmitDesk so far, and
**never contradict an earlier lesson.** Every "app" lesson edits the *same* codebase and
hands over the plan + acceptance criteria, not the finished code — except lesson 100,
which presents the complete program.

---

## 11. Interview questions (baked in, not bolted on)

- **Every lesson's "Check yourself"** includes **one interviewer-style question**.
- **Every section ends with a section interview Q&A block** (§18.D).
- The **dedicated `[Q]` lessons** (50 OOP, 59 memory, 64 exceptions, 79 collections, and
  the stream questions in 86) answer each classic **with code that proves it**, not just
  a definition.
- **Framing is reassurance:** *"You already know this — you wrote it a few lessons ago.
  Here's how to say it out loud in ten seconds."*
- At the end, **all Q&A is aggregated into one Interview Vault, grouped by section** (§19).

---

## 12. The interactive component library (build this first)

The reader must never face a wall of plain text. Before writing lessons, build a shared
front-end kit: one `assets/styles.css`, one `assets/components.js`, and copy-paste HTML
blocks. Everything is **vanilla HTML/CSS/JS — no framework, no build step, no external
library** (in-page state only, no `localStorage`). It just opens in a browser.

### 12.1 Design tokens (near-monochrome, one rationed accent)
```
--ink-0:#ffffff; --ink-500:#76768a; --ink-900:#191922; --ink-1000:#08080c;
--signal-500:#f04e2e;   /* the ONE accent. Means: the hook / the error / "wait, what?" */
--amber-500:#c1872a;    /* the course/craft colour: wordmark, "you built this", AdmitDesk brand */
--font-display:'Space Grotesk',sans-serif;   /* titles */
--font-body:'Inter',sans-serif;              /* body */
--font-slate:'IBM Plex Mono',monospace;      /* code, labels, lesson numbers, kickers */
--radius-card:14px; --radius-media:10px;
```
The world is ink (grays + near-black). **Signal-red appears exactly once per view** and
always means the live wire — the cliffhanger, the error, the thing that just broke. Amber
is only course branding. Never paint half a diagram red, or red stops meaning anything.

### 12.2 The components (build every one)
1. **`code-window`** — styled code panel: mono font, filename tab, line numbers, syntax
   roles (`.tok-key`, `.tok-str`, `.tok-num`, `.tok-comment`, `.tok-type`); one line
   highlightable with `.ln-hl` (signal-red) for "this is the line that matters."
2. **Broken → Fixed toggle** — two tabs over one code-window: **Broken** (shows the real
   compiler/runtime error in a red terminal strip) and **Fixed**. Default to Broken.
3. **Run-output block** — terminal-styled panel showing the *actual printed output*
   (beat 8); optional "Run ▸" that reveals pre-computed output.
4. **Diagram (inline SVG)** — for anything spatial (§13). Monochrome ink, one red element =
   the idea, every part labelled in mono.
5. **Reveal-answer** — collapsed "Show my solution"; for `[P]` it opens to the two-version
   layout (beginner vs experienced dev, §9).
6. **Interview accordion** — question rows that expand to a short confident answer *with a
   code-window proving it*. Used in section Q&A, `[Q]` lessons, and the vault.
7. **Check-yourself quiz** — the 2-question end block; each option reveals an explanation;
   one question tagged "Interviewers ask this."
8. **Callout / aside** — variants: `note`, `trap` (beat 6), `syntax-trap` (§6),
   `warn-hard` (before objects/generics/concurrency), `real-world` (beat 7), `go-deeper`
   (external references, §14bis below).
9. **Glossary chip** — inline first-use term with a dotted underline and a one-line
   definition on hover/tap; the section glossary is a grid of these.
10. **Cliffhanger bar** — the closing beat: full-width, one red signal dot, hook text in
    display font. Always the last thing on the page.
11. **Video-hook slot** and **Photo slot** — framed placeholders the owner fills (§15).
12. **Progress/section rail** — `SECTION N · JAVA`, lesson number `L34`, prev/next that
    literally follow the chain.

### 12.3 Per-lesson page
Each lesson is one self-contained `.html` file linking the shared kit, laying out beats
1–11 with the components. A component every screen or two, so it never reads like a
textbook.

---

## 13. Draw it — use SVG/art whenever a picture teaches faster than a paragraph

This is a standing habit, not only for `[DIAGRAM]` lessons. Whenever an idea is spatial or
a beginner would "get it" faster from a drawing, **make an inline-SVG teaching diagram**
right there in the lesson. Rules: monochrome ink for all structure, **one signal-red
element = the single thing being taught**, **every part labelled** in mono, **one idea per
diagram** (two ideas = two diagrams). Faint blueprint-grid backdrop.

**Draw it at least whenever you're explaining:**
- memory: stack vs heap, a reference pointing at a heap object, what `null` is (L10, L59);
- what `new` does — blueprint → allocated object with fields filled (L37);
- array indexing (boxes numbered from 0) and an off-by-one boundary (L25, L22);
- a loop's iterations as a small trace table (L21–24);
- binary search halving the range; a sort swapping two cells (L28);
- the call stack growing and unwinding; a recursion tree (L33, L36);
- inheritance/interface hierarchy as a tree with dashed contracts (L41–48);
- `HashMap`: key → hash → bucket index → bucket, with a collision as a short red chain
  (L72, L73, L79);
- a stream pipeline as labelled stages on a conveyor, one item flowing through (L83);
- two threads reading/writing one shared red cell (L90, L91);
- exception propagation climbing the call stack (L61, L62).

Keep these **teaching diagrams** distinct from the **mascot illustration** (§20), which is
the emotional/spot image. A lesson can have both: the mascot sets the mood, the diagram
does the explaining.

---

## 14bis. Point to the best of the internet (references and videos)

A good notes-sharer says "don't just take my word — here's the canonical source, and a
great video." So, where it genuinely helps, add a **`go-deeper` callout** with one or two
high-quality references. Rules:

- **Prefer stable, official sources for facts:** the Oracle/OpenJDK Java documentation and
  API docs (`docs.oracle.com`, `openjdk.org`), and the JEP pages for modern features
  (records, sealed classes, switch expressions, virtual threads). These are safe to link
  by name; verify the exact deep-link at build time.
- **For videos, don't invent URLs.** Drop a **video-hook slot** (§15) naming what the video
  should be plus search terms, so the owner pastes the real link. Examples: the James
  Gosling / Sun Microsystems "Oak → Java, write once run anywhere" origin story at L5; a
  visual HashMap-collision explainer at L72–73; a race-condition animation at L90.
- **One or two references per lesson, maximum.** This is a course, not a link farm. A
  reference earns its place only if it deepens the *one idea* of the lesson.
- Keep the reader in the lesson: references are "when you want more," never a substitute
  for teaching the idea in full here.

---

## 15. Media hooks (videos and photos the owner supplies)

The owner adds real images/videos; you place the slots and say exactly what each should be.
Never hardcode a URL — leave a filled-in-later slot with suggested search terms:
```
[VIDEO HOOK: <what it should show> | suggested: <search terms> | fill: data-src=""]
[PHOTO: <what it should show> | suggested: <search terms> | fill: src="" credit=""]
```
Place them at: the course/Section-0 open and **L5** (origin story); each section overview
(set the stakes); concept anchors where a real photo makes an analogy land (pigeonholes for
hashing, a blueprint for classes, a bank queue for `Queue`); and AdmitDesk milestones (52,
65, 80, 89, 96, 100) for a screenshot of the running console.

---

## 16. Build order (how you actually produce all this)

1. **The shared kit first:** `assets/styles.css` + `assets/components.js`, the Sig model-
   sheet prompt (§20), and a course home page / table of contents.
2. **Section by section, 0 → 15.** For each section produce, in order: the **overview**
   (§18.A) → the **lessons** (each a full HTML file, beats 1–11, one idea per lesson §4,
   university-only §8) → the **glossary** (§18.C) → the **interview Q&A** (§18.D) → the
   **fun exercise set** (§18.E). Keep AdmitDesk's running state consistent.
3. **Per lesson as you go:** the `[ILLUSTRATION PROMPT]`, any teaching diagrams (§13), a
   thumbnail spec, `go-deeper` references (§14bis), and media slots (§15).
4. **The capstone program (§17)** as part of lesson 100.
5. **The real-world closing section (§14).**
6. **The Interview Vault (§19),** grouped by section.
7. Final pass: verify the chain across all lessons (every open resolves the prior hook;
   every close sets the next).

Suggested layout:
```
/index.html
/assets/styles.css
/assets/components.js
/section-00/index.html, l01.html … l07.html, glossary.html, interview.html, exercises.html
/section-01/…  … through …  /section-15/…
/capstone/AdmitDesk.java, /capstone/AdmitDeskTests.java
/real-world/index.html
/interview-vault/index.html
```

**Per-lesson checklist** (run before moving on):
- [ ] First line resolves the previous cliffhanger; last line sets the next.
- [ ] Exactly one idea (§4); nothing from another chapter smuggled in.
- [ ] Every example is university-admissions; no banned generic names (§8).
- [ ] Reads like a person sharing notes, not a textbook.
- [ ] Every new word defined the first time (glossary chip).
- [ ] Broken-first shown where it helps; the sample compiles; actual output given.
- [ ] "What confuses people here" + any `syntax-trap` named.
- [ ] A drawing/SVG wherever a picture teaches faster (§13).
- [ ] Exactly 2 "Check yourself" questions, one interviewer-style.
- [ ] Components used so it's never a plain-text wall; one red element per view.
- [ ] Illustration prompt + diagrams + media slots + (if useful) one `go-deeper` reference.
- [ ] AdmitDesk state consistent; app lessons give plan + criteria, not code (except 100).

---

## 17. The capstone project — AdmitDesk (full spec)

**Name:** **AdmitDesk — the University Admissions Console.**
**Tagline:** *one console, the whole admissions season.*

AdmitDesk is the single project the whole course builds. It begins in lesson 20 as ten
lines that print one grade and ends in lesson 100 as one complete, heavily-commented Java
program that uses **every fundamental the course taught**. Lesson 100 presents it in full;
every earlier `[A]` lesson adds exactly one piece to the *same* codebase (build order in
§10). We never start a second project.

### 17.1 What AdmitDesk does (the finished behaviour)
A menu-driven console app for running a university's admissions:
- add and list **applicants**; record each applicant's **exam scores**;
- define **programs** (name, seats, cutoff score);
- record an **application** of an applicant to a program, with a **status**
  (`RECEIVED → UNDER_REVIEW → SHORTLISTED / REJECTED → ACCEPTED`);
- **search** applicants by name/email, **filter** by program or status, **sort** by score
  or date (multi-field), and detect **duplicate** applications;
- **shortlist** applicants for a program using a **pluggable rule** (top-N by score,
  above-cutoff, etc.);
- show **grouped counts** (applications per program, applicants per status);
- **save** everything to a plain text file and **load** it on startup (no database);
- refuse to crash on bad input (validation + exceptions everywhere);
- ship with **JUnit tests** around the eligibility and shortlisting rules.

### 17.2 The classes (the shape by lesson 100)
Keep names stable across the whole course. Indicative structure (pure JDK only):
- `Applicant` — id, name, email, `List<ExamScore>`; validated fields (encapsulation).
- `StaffUser` / `Reviewer` — extends a common `User` (a **sealed** parent so only these
  two exist); demonstrates inheritance + polymorphism (`describe()`).
- `Program` — name, seats, cutoff.
- `Application` — links an `Applicant` to a `Program`, holds a `Status` and an application
  date (`LocalDate`).
- `ExamScore` — a **record** (subject, mark).
- `Status` — an **enum** (`RECEIVED`, `UNDER_REVIEW`, `SHORTLISTED`, `REJECTED`,
  `ACCEPTED`).
- `Repository<T>` — a **generic** in-memory store, reused for applicants and programs.
- `ShortlistRule` — a functional interface (**Strategy** pattern) with implementations like
  `TopNByScore` and `AboveCutoff`; selected at runtime.
- `AdmitDeskException` (+ `DuplicateApplicationException`) — **custom exceptions**.
- `Storage` — file **save/load** with `java.nio.file`, try-with-resources.
- `AdmitDesk` — the `main` menu loop wiring it together.
- `AdmitDeskTests` — **JUnit 5** tests for eligibility + shortlisting.

### 17.3 The concept map (comment every concept to its lesson)
The final file must **comment each concept with the idea and the lesson it came from**, so
the artifact reads as a guided tour. For example:
```java
// ENCAPSULATION (L39): score is private; only a validating setter may change it.
private int score;

// STRATEGY PATTERN (L97): the shortlisting rule is passed in, not hard-coded.
List<Applicant> shortlist(Program p, ShortlistRule rule) { ... }
```
Across the file, deliberately exercise: variables & types; control flow; methods & one
recursion; arrays **and** collections (List/Map/Set); enums; records; a small sealed
hierarchy with an interface (polymorphism); generics (`Repository<T>`); exceptions & custom
exceptions; streams & `Optional`; dates; file save/load; and a couple of design-pattern
touches (Strategy, maybe Builder). Every concept carries its lesson tag in a comment.

### 17.4 Acceptance criteria (what "done" means)
- Runs from `main`, pure JDK, no framework/database/third-party library.
- Menu works end to end: add applicants/programs, record scores and applications, search/
  filter/sort, shortlist by a chosen rule, show grouped counts, save, quit; relaunch and the
  data is loaded back from the file.
- No input can crash it (validation + exceptions handled).
- `AdmitDeskTests` runs green; deliberately breaking a rule turns a test red.
- The sample console session is shown in a run-output block so the reader can check their
  result.
- The whole thing stays inside the university-admissions world — no stray `Foo`/`Animal`.

Present it via the code-window component with a downloadable `AdmitDesk.java` (and
`AdmitDeskTests.java`).

---

## 18. The section wrapper (every section is bookended this way)

On top of the per-lesson shape, each section is its own folder with a **section index page**
plus these five parts, in this order:

- **A. Overview — "What you'll study & why."** Short interactive page: what this section
  covers, *why it exists* (what the reader couldn't do before and can after), how it hangs
  off the previous section's last hook, and the lesson list with one-line promises. Open with
  a media hook (§15).
- **B. The lessons**, in order (§10), one idea each (§4), university-only (§8).
- **C. Glossary.** Every new term the section introduced, each a one-line plain-English
  glossary chip. Short — just this section's words.
- **D. Interview Q&A.** The interviewer questions this section's material produces, each a
  short confident answer **with code that proves it**, in the interview accordion. Framing:
  *"You already know this — here's how to say it out loud."*
- **E. Exercises — "Your turn: the fun set."** **Exciting, challenge-flavoured** problems,
  never a boring drill — give them stakes and personality (a leaderboard framing, "beat this
  in fewer lines", a broken program to rescue, a small new AdmitDesk feature). Always inside
  the university world. Reader attempts first; solutions behind a reveal, shown as the two
  versions (beginner vs experienced dev) with a plain-words "why the second is better."

So every section reads: **overview → lessons → glossary → interview Q&A → exercises**, and
it still obeys the chain — the overview picks up the previous section's final hook, and the
section's last lesson ends on the hook that launches the next section.

---

## 19. The Interview Vault (all Q&A, grouped by section)

After the course is written, produce one **Interview Vault** page collecting **every
interview question in the course, grouped by section**, in the interview accordion. For each:
the question, a short confident answer, and a code-window that proves it. Pull from the one
interviewer question in each lesson's "Check yourself," each section's Q&A block (§18.D), the
`[Q]` lessons (50, 59, 64, 79, streams in 86), and lesson 100's round.

Group headers mirror the 16 sections. Intro in the notes-sharing voice: *"Every one of these
you've already met. This page is just them, lined up, so you can hear yourself answer."* End
with a **"how to talk about your project"** guide — walk an interviewer through AdmitDesk in
two minutes — and a simple mock-interview plan (warm-up basics → OOP round → collections/
streams → one debugging story → "tell me about a bug you fixed"). This vault, plus lesson 100
and the real-world section, is the **"Prepare yourself for the interview"** finale.

---

## 20. The mascot (Sig), illustrations and thumbnails

Every lesson gets a cartoon **illustration** (you write the prompt; an image generator draws
it) and a **thumbnail**. These are the emotional layer — separate from the teaching diagrams
of §13. All obey the palette: monochrome ink, **exactly one signal-red element = the lesson's
idea**, amber only for course branding.

### 20.1 The character — "Sig" (locked design, changing expression)
Sig is an **original graphic mascot** built from the design system itself — **not a human,
not based on anyone.** Keep his design identical every image; change only expression, pose and
signal state.
- **Head = a rounded frame/screen** (a touch wider than tall) with a thin ink border and a
  light face panel; **two dot eyes + a small mouth** (brows only when an emotion needs them; no
  nose).
- **The signal:** **one red (`#f04e2e`) live-dot in the top-left corner of the screen** — his
  defining mark; concentric rings when "live."
- **Body = a simple deep-ink rounded shape** with short stub arms, mitten hands, little feet —
  so he can point, hold, wave, thumbs-up.
- **Flat solid fills only** — no gradients, shading, realism or 3D.
- **Never:** hair, skin, beard, a human face, a second saturated colour on his body, or more
  than one red element in a scene. If the scene's hook is the red element, turn his dot **off**
  (ink) so total red stays at one.
- **Signal-state = expression:** lit+rings = the big moment (cliffhanger, aha); lit = engaged;
  off = calm / red is spent on the hook.

### 20.2 Illustration prompt (output one per lesson, verbatim scaffold, four blanks)
```
[ILLUSTRATION PROMPT:
Original flat mascot, built from a few clean geometric shapes, flat solid fills ONLY, no
gradients, no shading, no realism, no 3D. NOT a human — a graphic creature. Same character and
style as the reference sheet.

CHARACTER (identical every image) — "Sig": HEAD is a rounded frame/screen with a thin ink
border and a light face panel showing two dot eyes and a small mouth; ONE red (#f04e2e)
live-dot in the TOP-LEFT corner of the screen. BODY is a simple deep-ink rounded shape with
short stub arms, mitten hands, little feet. No hair, no skin, no beard, nothing human.

SIGNAL STATE: <lit + rings / lit / off>.
EMOTION: <curious / aha / surprised / confident / thinking / playfully stuck / proud /
careful-warning>.
POSE / ACTION: <Sig DOING the concept — an admissions prop where possible>.
CAMERA: <bust / 3-4 / full body / over-shoulder / low angle>.
SCENE: <the concept in the university world, naming the ONE red element — his dot OR an
external hook>.

COLOUR (strict): Sig's body ink, screen light, ONE red dot; background + props + diagram
monochrome ink; EXACTLY ONE red (#f04e2e) total; amber only for course branding, never on Sig;
faint blueprint-grid background; no other colours. 16:9.
]
```
**Build the model sheet first** (front + 3/4 poses, six expressions, the dot lit/lit+rings/off,
a few arm poses) and reference "same character and style as the reference sheet" on every later
prompt. Match emotion to the beat: curious at the open, surprised at the hook, proud at the
payoff.

### 20.3 Thumbnails
One per lesson and per section: a 16:9 that **reads in half a second.** Ink everywhere, **one
signal-red element = the hook**, Sig on-model, lesson number in mono in a corner, title = the
tension not the topic ("It said they're different" beats "String equality"). Recipe by type:
`[C]` big idea + one red mark on the concept that bites; `[A]` a code window with one line
highlighted red + kicker `ADMITDESK`; `[D]` an urgent red stack-trace/wrong-output, Sig
reacting; `[P]` the challenge as title + kicker `PRACTICE · N PROGRAMS`; `[Q]` the question in
quotes as the hero + a red question mark + kicker `INTERVIEW`.

---

## 21. The real-world closing section — "Java today, and where you go next"

After lesson 100's recall, add a closing orientation page, clearly framed as **"beyond this
course"** (so it doesn't violate the pure-Java scope of the lessons). Keep the honest,
notes-sharing voice. Cover:
- **Where Java is today** — mainstream on the server side and in large enterprises; a huge,
  stable ecosystem; a language that keeps shipping modern features. *Verify specifics against a
  fresh source at build time; don't ship stale claims.*
- **Who uses it** — a short, honest, recognisable list (major banks, well-known tech companies,
  Android on the JVM). *Check current examples when you write it; avoid overclaiming.*
- **What you can build with Java** — backend services and APIs, Android apps, big-data tooling
  (Hadoop/Spark on the JVM), desktop apps, embedded/enterprise systems, high-throughput
  payments/trading.
- **What Java is *not* the usual pick for** (be honest) — the browser front-end (HTML/CSS/JS),
  quick data-science/ML scripting (Python), tiny shell glue, iOS apps. *"The right tool changes
  with the job — knowing Java well makes the next language easy."*
- **Career paths** — backend/Java developer, Android developer, big-data engineer, then the
  senior/staff ladders or a move into DevOps/platform.
- **The ecosystem you'll meet next** (named, not taught) — Spring / Spring Boot, Hibernate/JPA,
  Maven/Gradle, JUnit (already yours), CI tools. "These are your next mountains — you're now
  equipped to climb them."
- **Where you can host/run Java** — any machine with a JVM: a Linux server, a container, a
  managed cloud platform. Name the shapes, don't write a deployment tutorial.
- **Projects worth building to get hired** — AdmitDesk (extend it), a CLI inventory/library
  manager, a bank-account simulator, a text-based game, a file-based expense tracker; then, as
  you learn the ecosystem, a REST API and a small full-stack app. Point at a bigger staged
  roadmap as "your next six months."

---

## 22. Do / Don't (guardrails)

**Do**
- Keep the chain unbroken: resolve the last hook first, set the next hook last, every lesson.
- One idea per lesson; split into focused sub-lessons rather than mingle (§4).
- Keep every example in the university-admissions world; use the cast names; never a banned
  generic name (§8).
- Make every code sample compile; name the syntax traps (§6).
- Draw it with SVG whenever a picture teaches faster (§13).
- Add one strong external reference where it deepens the idea (§14bis).
- Let readers attempt practice before revealing solutions; show beginner vs experienced-dev
  versions.
- Verify real-world names/figures at build time (§21).

**Don't**
- Don't teach any framework, library or tool beyond the JDK and JUnit; don't bring in web,
  databases, APIs or a frontend *as course content* (HTML is only the delivery medium).
- Don't mingle topics; don't merge or skip lessons; don't open a lesson without answering the
  previous cliffhanger.
- Don't give practice solutions first; don't write AdmitDesk's code for the reader (except
  lesson 100).
- Don't dump long code; don't use a word you haven't defined; don't fake praise.
- Don't invent media URLs — leave filled-in-later slots with suggested search terms.
- Don't let more than one red element appear in any single view.

Build it all. Start with the shared kit, then Section 0, and go — one idea, one lesson, one
university example at a time.