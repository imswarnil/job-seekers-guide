# sql-content.md — the complete build spec for "SQL for Job Seekers"

**Read this once, then build the whole course.** This is the single,
self-contained brief for producing a SQL course end to end, taught entirely
against the real **`university`** MySQL database. You (Claude Code) will not be
handed any other project file — the schema, the rules, the full lesson map, the
component system, and the capstone are all here.

Your job: **produce the entire course as interactive HTML lessons** (plus a shared
kit, an interview vault, and one final `.sql` reporting layer), section by section,
in order, following §16. Do not ask which lesson to write — write them all.

This is the sibling course to "Java for Job Seekers": same author, same voice, same
brand, same mascot (Sig), same look. Where the Java course grows one program
(AdmitDesk), this course grows one **reporting layer** (CampusLens, §17) on the
university database.

---

## 0. The promise (the north star — reread before every lesson)

This course exists for **someone who has never written a query** — maybe someone
who has only ever used a spreadsheet. By the end they must be able to:

1. **Write SQL that runs *and returns the right answer*** — because in SQL, a query
   that executes without an error can still be silently wrong (a bad join, a
   forgotten NULL, a missing `WHERE`). We teach correctness, not just syntax.
2. **Reason from a plain-English question to a query** — "which departments have the
   most backlogs?" becomes SQL through clear thinking, not memorised templates.
3. **Do it against a real, messy, production-shaped database — safely** — the
   `university` DB, with intentional NULLs, a funnel that doesn't fully convert,
   backlogs that aren't random, and no cloud "undo." They learn to look before they
   leap.

Every decision in this spec serves that promise. If something you're about to write
doesn't help a total beginner write a *correct, safe* query by reasoning about a
real university question, cut it or rewrite it.

---

## 1. What this course is

- **One course → sections → lessons.** A section is a chapter; a lesson is one
  interactive blog post — the unit the reader consumes.
- **One theme, forever:** the **`university` database.** Every query, example,
  exercise and diagram uses its real tables. This is a hard rule (§8).
- **One capstone:** **CampusLens** — a growing, documented pack of SQL **views and
  queries** that turns the raw `university` DB into decisions (funnel, fees,
  placements, backlogs, and an at-risk early-warning report). It starts as one view
  and grows, lesson by lesson, into one complete, heavily-commented `.sql` file.
  We **never** switch databases. Full spec in §17.
- **Scope:** **MySQL 8.x only**, taught against this one database. No ORM, no BI
  tool, no Python, no other dialect *as a subject* — see §7. (Lessons are
  *delivered* as HTML; HTML is the classroom, SQL is the subject. Never teach
  HTML/JS as if it were the course.)

---

## 2. The voice (lock this in every lesson)

Write as **a job seeker sharing the notes that got them hired** — the person one
step ahead, turning around to say "here's the bit that confused me, and here's what
made it click." Not a professor. Not the MySQL manual.

- **First person, talking to one reader.** "you," not "students." "Watch what this
  query actually returns." "Here's the join that finally made sense to me."
- **Beginner-first.** Assume the reader has only ever seen a spreadsheet, if that.
- **Warm, confident, honest — never fake praise.** If a topic genuinely bites (NULL
  logic, join fan-out, window functions, indexes), **say so before it arrives**, so
  the reader never blames themselves.
- **Backstory for credibility** (use when natural, don't repeat every lesson):
  *"I learned SQL to get a data job — not in a classroom, but by pointing queries at
  a database until the numbers made sense. These are the notes I wish I'd had."*
- **The thesis, proven again and again:** *"SQL is small — a handful of clauses that
  always run in the same order. Once you can picture what the database does with your
  query, you can answer almost any interview question, because the ideas repeat."*
- **No jargon without a definition.** First use gets a plain-English gloss: *"a join —
  stitching two tables together by matching rows on a shared value — …"*
- **Avoid** "simply," "just," "as we all know." Short sentences at the hard parts.

---

## 3. The rule that matters most: the cliffhanger chain

**Every lesson ends on a cliffhanger. The very first line of the next lesson answers
it — before anything else.** This is a **chain, not a list.** No lesson is a fresh
start; today's lesson is the thing yesterday's lesson made the reader *need*. Think:
one table → filtered rows → grouped summaries → joined tables → windows over groups.

The exact hook each lesson ends on is in the map (§10). Use it as the closing beat,
unresolved. Open the next lesson by resolving exactly that hook, directly, in one or
two sentences. **The open (resolve) and the close (hook) appear on every lesson.**

---

## 4. One topic per lesson — never mingle

A beginner drowns when two new ideas arrive at once. So:

- **One lesson teaches one idea.** A `WHERE` lesson teaches filtering — not joins,
  not aggregates "while we're here." Don't smuggle a future clause in early. If the
  reader needs a keyword we haven't taught, either it isn't time for this lesson, or
  that keyword needs its own lesson first.
- **Closely-related sub-parts are allowed, in sequence, if they're one theme.**
  "AND, OR, NOT and precedence" is one theme (combining conditions); teach each part
  fully, in order. But "filtering" and "joining" are different chapters and never
  share a lesson.
- **You may split any mapped lesson into focused sub-lessons** (e.g. 20a string
  functions → 20b more string functions) when one-idea-per-lesson demands it. Chain
  sub-lessons with their own micro-hooks; the **last** sub-lesson carries the mapped
  cliffhanger, so the main chain is preserved.
- **Sections can grow.** More lessons is fine; mingled lessons is not.

> The test: if a confused reader asked "what is *this* lesson about?", they must
> answer in **one short phrase**. If the honest answer is "two things," split it.

---

## 5. The lesson shape (11 beats)

Write every lesson in this order. Keep headings light — it's a blog post. Map each
beat to the components in §12.

1. **Resolve last lesson's cliffhanger** — first thing, directly, one or two
   sentences answering exactly what was left hanging.
2. **Where that leaves us** — one line bridging into today.
3. **Why we're learning this** — the reason SQL needs this thing; what question you
   *can't* answer without it. *Never introduce syntax before the reason for it.*
4. **The idea, analogy first** — everyday analogy, then the technical version.
   (A table is a spreadsheet the database guards. A join is stitching two sheets on a
   shared column. An index is the sorted tab-edge of a phone book.) **If a drawing
   explains it faster, draw it here (§13).**
5. **A small query example** — short, over real `university` tables, with the
   **result shown** (a result-grid, §12). Where it helps, show a query that **errors
   or runs-but-is-wrong first**, then the fix (§6). No 200-line queries.
6. **What confuses people here** — name the trap before they fall in it, including
   the **MySQL gotcha** or the **correctness trap** if there is one (§6, §9).
7. **How you'd use this in real life** — one concrete question a real registrar,
   dean, or placement cell would actually ask, answered on this DB.
8. **What you get out of it** — the question the reader can now answer that they
   couldn't before, plus the **result** so they can check their own run.
9. **Your turn** — the exercise / problems / query to fix / next CampusLens piece.
   Follow the practice rule (§9).
10. **Check yourself** — exactly **2 questions**, and **one is the kind an
    interviewer actually asks** (often "predict the result of this query").
11. **The cliffhanger** — the exact hook from the map, left hanging. Last thing on
    the page.

Not every beat is heavy every time (a `[P]` lesson leans on beat 9; a `[Q]` lesson is
mostly interview Q&A), but beat 1 (resolve) and beat 11 (hook) are always present.

**The ten teaching habits:** idea before syntax · analogy then technical · define
every new word the first time · one idea per lesson · short queries · show the wrong
query first where it helps · warn before hard parts · say what the database actually
*does* with the query when it matters (the logical order of clauses, how a join
matches rows, how an index is searched) · be honest about wrong answers · never break
the chain.

> **A note on running results:** these lessons can't execute against the reader's
> live DB, so every shown result is a **realistic, illustrative** sample consistent
> with the synthetic data — label it as such and always tell the reader to run it
> themselves and compare. Teaching the reader to *check their own result critically*
> is part of the point.

---

## 6. Correct, not just runnable — and safe (two teaching duties)

Because the promise (§0) is correct, safe SQL, treat these as first-class jobs:

**Correctness (a query that runs can still be wrong).**
- Show the **runs-but-wrong** trap with the wrong→right component (§12): a missing
  `WHERE` that hits every row, a join that fans out and doubles a `COUNT`, `= NULL`
  that silently returns nothing, `COUNT(col)` vs `COUNT(*)` disagreeing, an aggregate
  without the right `GROUP BY`.
- Teach the reader to **read results critically**: does the row count make sense? are
  there unexpected NULLs? A green checkmark is not correctness.
- Teach **reason-first**: write the question in plain English, name the tables and the
  join keys, *then* write SQL as a translation of that plan.

**Safety (a self-hosted DB with no undo).** Weave these in from Section 0 and enforce
them every time a lesson touches a write:
- **Know where you are:** `SELECT DATABASE();` / `USE university;` before anything.
- **`SELECT` before `UPDATE`/`DELETE`** — run the exact `WHERE` as a `SELECT` first and
  eyeball the rows.
- **Never `UPDATE`/`DELETE` without a `WHERE`.** A missing `WHERE` hits every row.
- **Safe-update mode** (`SET SQL_SAFE_UPDATES = 1;`) while learning.
- **Wrap risky writes in a transaction** (`START TRANSACTION; … ROLLBACK;/COMMIT;`).
- **Back up first** for bulk/destructive work; the dataset is reproducible, so
  experiment freely and reset if needed.
- **Practice destructive commands on a copy** (`CREATE TABLE students_copy AS SELECT
  * FROM students;`).
Use the dedicated `safety` callout (red) whenever a lesson shows a write.

---

## 7. Scope — MySQL 8 on this one database (hard boundary)

**Allowed in taught content:** **MySQL 8.x** SQL, run against the **`university`**
database, plus the MySQL CLI and **Adminer** as the tools to run it. Everything is a
query, a DDL statement on a copy, or a transactional write done safely.

**Never taught as course content:** any ORM; any BI tool (Power BI/Tableau/Metabase)
*as a subject*; Python/pandas; another SQL dialect *as a subject* (note dialect
differences in a one-liner, don't teach Postgres/BigQuery syntax); stored-procedure-
heavy programming; server administration/replication; cloud provisioning. If a lesson
seems to need one, **teach the SQL idea against this DB and say in one line that the
rest is deliberately out of scope.**

The **only** place the wider data stack appears is the real-world closing (§21),
framed as "what to learn *after* this course."

---

## 8. The university rule (strict — enforce it everywhere)

**Every query, example, exercise and diagram uses the real `university` tables and
columns. No exceptions.** This is what makes the whole course cohere and lets a
beginner reason about SQL by picturing a real campus.

- **Use the real cast:** `students`, `enquiries`, `admissions`, `departments`,
  `programs`, `faculty`, `subjects`, `exam_results`, `student_attendance`,
  `semester_registrations`, `fee_structure`, `fee_payments`, `books`,
  `library_transactions`, `companies`, `placements`, `alumni`, `events`,
  `event_participation`, `cafeteria_transactions`, `hostel_allocations`,
  `complaints`, `warnings`, `faculty_attendance`. (Full schema in §22.)
- **Ask real questions:** funnel conversion, backlog trends, at-risk students, fee
  health, placements per department, attendance vs performance, alumni careers.
- **Banned, always:** the generic tutorial schemas — `employees`/`emp`/`dept` from old
  Oracle courses, `customers`/`orders`/`products`, `foo`/`bar`, invented `sales`
  tables. If you catch yourself reaching for one, replace it with the university
  equivalent (`faculty`, `students`, `exam_results`, `placements`).

> Enforcement line for every lesson: **"If this query isn't against the `university`
> database, rewrite it until it is."**

---

## 9. MySQL & data gotchas to teach as traps (this DB will bite you here)

Surface these exactly where they become relevant, using the `mysql-gotcha` / `trap`
callouts. They are not footnotes — they are lessons' worth of "what confuses people."

- **Reserved words need backticks.** `alumni.current_role` is reserved in MySQL 8 —
  it errors without backticks: `` SELECT `current_role` FROM alumni; ``.
- **`NULL` is the absence of a value, not a value.** The `alumni` table is *full of
  intentional NULLs* (`current_company`, `estimated_salary_lpa`, `linkedin_url`,
  `industry`, `` `current_role` ``). Use `IS NULL`/`IS NOT NULL`, never `= NULL`.
  `COUNT(col)` skips NULLs; `COUNT(*)` counts rows. `COALESCE(col,'Unknown')` for a
  default.
- **`ONLY_FULL_GROUP_BY` is on by default.** Every non-aggregated `SELECT` column must
  be in `GROUP BY`, or MySQL rejects the query. Trips up everyone from older tutorials.
- **Timestamps are full datetime to the second.** Bucket with `DATE_FORMAT(paid_at,
  '%Y-%m')`; grouping by a raw `*_at` makes every row its own group.
- **UUID transaction IDs are strings** (`fee_payments.txn_id`,
  `library_transactions.txn_id`, `cafeteria_transactions.txn_id`) — quote them, no math.
- **Foreign keys are enforced.** Insert parents before children; a blocked delete is
  the DB protecting integrity, not a bug.
- **Data semantics baked in (filter accordingly or your numbers lie):** `exam_results.
  attempt_no > 1` = a backlog re-sit after a fail on attempt 1; low `attendance_pct`
  correlates with failure (results aren't random); `fee_payments.paid_at` is NULL when
  `pending`; only the **2021 & 2022** intakes are `graduated` (and in `alumni`), 2023 is
  in sem 7, 2024 in sem 5, 2025 in sem 3; each active batch has a few `dropped` rows;
  the enquiry funnel is ~990 enquiries → ~550 students.

---

## 10. The practice rule

Lessons are tagged concept `[C]`, practice `[P]`, debugging `[D]`, analytics-build
`[A]` (grows CampusLens), or interview `[Q]`.

- **Practice `[P]`:** give the problems and hints. **Let the reader attempt before
  revealing any solution** (behind the reveal component, §12). When you show a
  solution, show **two versions**: the *beginner* query (correct but clumsy — nested
  subqueries, `SELECT *`) and the version an *experienced analyst* writes (a clean CTE,
  named columns), with a plain-words note on why the second is better.
- **Debugging `[D]`:** show the broken or wrong query, the actual MySQL error *or* the
  wrong result, and walk the reader through *finding* it — read the error, shrink the
  query, check row counts, `EXPLAIN` when it's a performance bug. Draw the state when
  it helps (§13).
- **Analytics-build `[A]`:** give the **plan** — the question, the tables and join
  keys, the expected output columns and row shape (the **acceptance criteria**) — **not
  the finished SQL.** The reader writes the view/query; you describe what "correct"
  looks like. The one exception is the CampusLens-complete lesson, which presents the
  full pack.

Never give a practice answer before the reader has attempted it. Never write
CampusLens for the reader (until the capstone lesson). Never praise a query that's
wrong or clumsy; name it, with the reason.

---

## 10b. The full lesson map (in order)

Build in this order. Each entry: number, title, `[type]`, focus, and the
**cliffhanger** it ends on. You may split any entry into focused sub-lessons per §4.
`[DIAGRAM]` marks a lesson that needs a drawn diagram (§13) — draw elsewhere too,
whenever a picture teaches faster.

### Section 0 — Before you write a query (1–7)
*What & why: a person who pictures what a database *is* writes correct queries and
interviews well. Earn the right to SELECT by first understanding tables, keys, and
what SQL even does — and how to stay safe on a DB with no undo.*

1. **Data, and why not one giant spreadsheet** `[C]` `[DIAGRAM]` — rows, columns,
   tables; the pain of one flat sheet.
   *Hook:* a table looks exactly like a spreadsheet tab — so what makes a *database*
   different, and better?
2. **What a relational database is** `[C]` `[DIAGRAM]` — many tables, no duplication,
   linked by shared values.
   *Hook:* if the data is split across tables, something must connect a student to
   their exam results. What?
3. **Keys and relationships** `[C]` `[DIAGRAM]` — primary keys, foreign keys,
   one-to-many, the shape of `students → exam_results`.
   *Hook:* you can describe the shape in English, but the database answers only one
   language. What is SQL, really?
4. **What SQL is** `[C]` — declarative "what, not how"; the engine plans the how; why
   the same SQL is (mostly) portable.
   *Hook:* SQL runs on many databases, but each has quirks — ours is MySQL 8, and it
   has opinions that will trip you up.
5. **Meet the `university` database** `[C]` `[DIAGRAM]` — the 24 tables, the student
   lifecycle across 2021–2025, what's synthetic. *(Origin-story video hook — §15.)*
   *Hook:* one wrong command with no `WHERE` can wipe a table — and there's no cloud
   undo. How do you stay safe?
6. **Staying safe on a live database** `[C]` — read-only mindset, `SELECT`-before-write,
   safe-update mode, backups, transactions, reset.
   *Hook:* enough caution — how do you actually connect and run your first `SELECT`?
7. **Connecting and your first query** `[C]` — Adminer and the CLI, `SELECT … LIMIT`,
   reading a result grid.
   *Hook:* it returned every column of every row and drowned you. How do you ask for
   only the few columns and rows you actually want?

### Section 1 — Asking for exactly what you want: SELECT (8–13)
*What & why: the single most-used statement. Pull, name, sort and cap what you see —
before any filtering or joining.*

8. **SELECT and FROM** `[C]` — choosing columns, the result set, reading a table.
   *Hook:* two students live in the same city; you want the list of cities *once*. How
   do you collapse duplicates?
9. **DISTINCT** `[C]` — unique values and unique combinations.
   *Hook:* your column header reads `CONCAT(first_name,...)` and is unreadable. Can you
   rename a column in the output?
10. **Aliases and computed columns** `[C]` — `AS`, expressions, building a full name and
    a student label.
    *Hook:* the rows came back in whatever order the DB felt like. How do you sort by
    CGPA, highest first?
11. **ORDER BY** `[C]` — one and multiple columns, `ASC`/`DESC`, sorting by an
    expression.
    *Hook:* sorting gave you all 550 students; you wanted the top 10. How do you cap the
    list?
12. **LIMIT and OFFSET** `[C]` — top-N, paging through results.
    *Hook:* "top 10 by CGPA" is easy; "the 10 students with the *lowest* attendance"
    needs you to keep only matching rows first. How do you filter?
13. **Your first ten SELECT challenges** `[P]` — over `students`, `departments`,
    `programs`, `faculty`.
    *Hook:* every query so far returned rows that already matched. What if you want only
    *active* students, or only the *2021* intake? *(Recall round #1 in "Check yourself".)*

### Section 2 — Filtering rows: WHERE (14–19)
*What & why: real questions want a subset. Filtering is where correctness and NULL
first bite.*

14. **WHERE and comparison operators** `[C]` — `=`, `<>`, `<`, `>` on numbers, strings,
    dates.
    *Hook:* you want 2021 **and** CSE, or 2024 **or** 2025. How do you combine
    conditions?
15. **AND, OR, NOT and precedence** `[C]` — parentheses change the answer.
    *Hook:* `status = 'active' OR 'graduated'` returned nonsense. Why — and what's the
    clean way to say "one of these"?
16. **IN and BETWEEN** `[C]` — set membership and ranges (`intake_year BETWEEN 2023 AND
    2025`).
    *Hook:* you want every student whose name starts with "A", or whose email ends in a
    domain. Exact match can't do it.
17. **LIKE and pattern matching** `[C]` — `%`, `_`, case behaviour.
    *Hook:* you filtered `estimated_salary_lpa > 10` and rows with no salary vanished —
    including some you expected. NULL is doing something strange.
18. **NULL and three-valued logic** `[C]` `[DIAGRAM]` — `IS NULL`, why `= NULL` fails,
    NULL in comparisons; the intentionally-NULL `alumni` table.
    *Hook:* you keep writing the same messy `CASE` to turn NULL into "Unknown". Isn't
    there a shorter way?
19. **Filtering challenges** `[P]` — the funnel, at-risk hints, NULL-aware filters.
    *Hook:* your filters compare raw values, but real questions ask for *derived* things
    — a student's age, the month of a payment, a clean full name.

### Section 3 — Shaping values: functions & CASE (20–25)
*What & why: raw columns aren't the answer; you compute ages, month buckets, labels and
defaults.*

20. **String functions** `[C]` — `CONCAT`, `UPPER`/`LOWER`, `TRIM`, `SUBSTRING`,
    `LENGTH`, `REPLACE`.
    *Hook:* you have a date of birth but need an age; a `paid_at` timestamp but need just
    the month.
21. **Date & time functions** `[C]` — `CURDATE`, `TIMESTAMPDIFF` (age), `YEAR`/`MONTH`,
    `DATE_FORMAT` bucketing.
    *Hook:* you can pull a value out of a row — but to *label* a row "distinction / pass
    / fail" by its marks, a function won't branch. What will?
22. **Numeric functions and division** `[C]` — `ROUND`, `CEIL`, `FLOOR`, `MOD`, integer
    vs decimal division.
    *Hook:* you want to tag each result "distinction / first class / pass / fail". You
    need logic *inside* the SELECT.
23. **CASE expressions** `[C]` — searched vs simple `CASE`, grade and risk buckets.
    *Hook:* `CASE` handles NULL in five lines; for the single job "use a default when
    NULL", there's a one-word tool.
24. **COALESCE and IFNULL** `[C]` — defaults for the `alumni` NULLs, `NULLIF`.
    *Hook:* you can shape single values beautifully — but "how many" and "what's the
    average" need you to squash many rows into one number.
25. **Function & CASE challenges** `[P]` — ages, month buckets, grade labels, cleaned
    alumni profiles.
    *Hook:* labelling each row is one thing; *counting how many* fall in each label is
    the real question. How do you count per group? *(Recall round #2.)*

### Section 4 — Summarizing: GROUP BY & aggregates (26–32)
*What & why: most questions are "how many / how much / on average, per something". This
is where `ONLY_FULL_GROUP_BY` and NULL-in-aggregates bite.*

26. **Aggregate functions** `[C]` — `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` over a table.
    *Hook:* one number for the whole table is rarely the question; you want it *per
    department*, *per intake year*. How do you split rows into groups first?
27. **GROUP BY** `[C]` `[DIAGRAM]` — one row per group, aggregates per group.
    *Hook:* `COUNT(*)` said 550 but `COUNT(email)` said 549 on the same table. Why
    different?
28. **COUNT(\*) vs COUNT(col), and aggregates with NULL** `[C]` — what each ignores.
    *Hook:* you grouped by department and also selected the student's name — MySQL
    rejected the whole query. Why?
29. **ONLY_FULL_GROUP_BY** `[C]` — the rule tutorial-veterans trip on, and how to satisfy
    it.
    *Hook:* you can filter rows with `WHERE`, but now you want only the *groups* with more
    than 50 students. `WHERE` can't see a group's count.
30. **HAVING** `[C]` — filtering groups vs rows; `WHERE` runs before grouping, `HAVING`
    after.
    *Hook:* "departments with the most backlogs" needs the group counts *and* the
    department names, which live in two different tables. So far you've queried one table
    at a time.
31. **Multi-level grouping and combining aggregates** `[C]` — grouping by two columns,
    several aggregates at once.
    *Hook:* "average marks per department" lives in `exam_results` *and* `departments`.
    How do you bring two tables together?
32. **Aggregation challenges** `[P]` — funnel counts, fee health, backlog counts,
    attendance averages.
    *Hook:* you've hit the wall — nearly every interesting question needs two or more
    tables. Time for the heart of SQL.

### Section 5 — The heart of SQL: JOINs (33–41)
*What & why: the data is split on purpose, so you must stitch it back. **Warn the reader
— this is the concept that separates "can SELECT" from "can query".***

33. **Why data is split across tables** `[C]` `[DIAGRAM]` — no duplication, and the cost:
    you must re-join.
    *Hook:* to combine two tables you match rows on a key. What does that matching
    actually look like, row by row?
34. **INNER JOIN** `[C]` `[DIAGRAM]` — matching rows on a key: `students` × `departments`.
    *Hook:* you joined `students` to `placements` and 230 students vanished — the ones
    never placed. Where did they go, and how do you keep them?
35. **LEFT JOIN** `[C]` `[DIAGRAM]` — keep all left rows, NULLs where there's no match.
    *Hook:* the unplaced students came back with a NULL company — which is exactly how you
    *find* "students who were never placed".
36. **Anti-joins** `[C]` — `LEFT JOIN … WHERE right IS NULL`: enquiries that never
    enrolled, students never placed.
    *Hook:* you joined three tables and your row count exploded into the thousands. You
    added no data — so where did the extra rows come from?
37. **Join fan-out and duplicate rows** `[C]` `[DIAGRAM]` — one-to-many multiplication,
    why `COUNT` and `SUM` suddenly lie.
    *Hook:* a `warnings` row points at both a student and the *faculty* who issued it. How
    do you join a table to itself, or two roles from one table set?
38. **Self-joins** `[C]` — a table joined to itself: students in the same city, a faculty
    chain.
    *Hook:* you can join `students → exam_results → subjects → departments` — four tables —
    but it's a tangle. How do the pros keep multi-join queries readable?
39. **Multi-table joins, done cleanly** `[C]` — aliasing, sensible join order, the full
    transcript join.
    *Hook:* `RIGHT JOIN`, `CROSS JOIN` — you've heard of them. When would you ever use
    them?
40. **RIGHT and CROSS JOIN** `[C]` — completeness and the rare real uses (a date × dept
    grid).
    *Hook:* joins answer "combine tables", but "students who scored above their
    *department's* average" needs a value you must compute first.
41. **JOIN challenges** `[P]` `[A]` — transcripts, at-risk shortlist, department scorecards
    *(first CampusLens pieces sketched)*.
    *Hook:* "above the department average" means one query to get the average, then using
    it inside another. Can a query contain a query? *(Recall round #3.)*

### Section 6 — Queries inside queries: subqueries & sets (42–48)
*What & why: some answers need an intermediate result. Subqueries, EXISTS, derived tables,
UNION — and the first taste of why nesting gets unreadable.*

42. **Scalar subqueries** `[C]` — a query returning one value, used in `SELECT`/`WHERE`
    (above the overall average CGPA).
    *Hook:* "above overall average" was easy; "above their *own department's* average"
    needs the inner query to change per row. How?
43. **Correlated subqueries** `[C]` `[DIAGRAM]` — the inner query references the outer row.
    *Hook:* correlated subqueries can be slow and dense; often you only care *whether* a
    match exists, not its value.
44. **EXISTS and NOT EXISTS** `[C]` — existence checks; `IN` vs `EXISTS`.
    *Hook:* you keep writing the same subquery in `FROM` as a "temporary table". Does it
    need a name — and can you reuse it?
45. **Derived tables (subquery in FROM)** `[C]` — naming a result set; the top-3-per-dept
    pattern.
    *Hook:* this year's enrollees and last year's need to be *stacked* into one list.
    `JOIN` puts tables side by side, not on top of each other.
46. **UNION and UNION ALL** `[C]` — stacking result sets; dedup vs keep-all and the speed
    difference.
    *Hook:* derived tables and unions nest deep and read inside-out. There's a cleaner way
    to write the same logic top-down.
47. **IN vs EXISTS vs JOIN** `[C]` — three routes to the same answer, when each wins.
    *Hook:* your subqueries are three levels deep and unreadable. Pros write these flat and
    named. What's the tool?
48. **Subquery challenges** `[P]` — above-average students, never-placed, stacked cohorts.
    *Hook:* you've been computing rankings by hand with subqueries. SQL has a feature built
    exactly for "rank within a group" — the one that separates juniors from seniors.

### Section 7 — The senior-level tool: window functions (49–56)
*What & why: **warn the reader — a new way of thinking.** Aggregate *without* collapsing
rows: keep every row *and* its group's summary. Modern data interviews expect this.*

49. **Why window functions exist** `[C]` `[DIAGRAM]` — `OVER()`, summary beside detail,
    what "window" means.
    *Hook:* `OVER()` gave every row the same grand total; you wanted the total *per
    department* beside each student. How do you window by group?
50. **PARTITION BY** `[C]` `[DIAGRAM]` — per-group windows: the dept average beside each
    student.
    *Hook:* showing a group average per row is one thing; "rank students *within* each
    department" needs numbered ordering.
51. **ROW_NUMBER, RANK, DENSE_RANK** `[C]` — ranking within partitions; how ties differ.
    *Hook:* `ROW_NUMBER` gives one unique number per row — perfect for "the single top
    student per department" *and* "the latest payment per student". How?
52. **Top-N-per-group and latest-per-group** `[C]` — the classic pattern: window then
    filter on the rank.
    *Hook:* you can rank; now you want a *running total* of fees collected, month over
    month, each row adding to the last.
53. **Running totals and frames** `[C]` `[DIAGRAM]` — `SUM() OVER (ORDER BY …)`, `ROWS
    BETWEEN`, moving averages.
    *Hook:* comparing this month's collection to *last* month's needs a row to peek at the
    previous row. Can a row see its neighbours?
54. **LAG and LEAD** `[C]` — previous/next row: month-over-month change, semester-to-
    semester dropout.
    *Hook:* your window definition is copied into three functions in one query. Can you
    name a window once and reuse it?
55. **Named windows and combining them** `[C]` — the `WINDOW` clause, multiple windows in
    one query.
    *Hook:* your best queries now nest derived tables *and* windows three deep. It runs —
    but nobody could read it, including you next week.
56. **Window challenges** `[P]` — top-N per dept, running fee totals, semester dropout.
    *Hook:* every complex query is a pyramid of nested subqueries. There's a way to write
    them as a clean, top-down list of named steps. *(Recall round #4.)*

### Section 8 — Organizing complexity: CTEs & views (57–62)
*What & why: real analytics queries get big. CTEs make them readable; views make them
reusable — and the CampusLens reporting layer begins.*

57. **CTEs (WITH)** `[C]` — naming steps, reading top-down, refactoring a nested query into
    a pipeline.
    *Hook:* a CTE that refers to *itself* — could you walk a hierarchy, like a faculty
    reporting chain or a date spine?
58. **Recursive CTEs** `[C]` `[DIAGRAM]` — hierarchies and generated sequences.
    *Hook:* you've written the perfect at-risk-students query. You'll want it again
    tomorrow — must you paste it every time?
59. **Views** `[C]` `[A]` — saving a query as a virtual table; the first CampusLens view.
    *(Capstone build starts.)*
    *Hook:* your view recomputes on every call, and on 23,000 attendance rows that's slow.
    Can you store the *result*?
60. **Views vs stored results** `[C]` — MySQL has no materialized views; the honest
    workaround (summary tables), and its trade-offs.
    *Hook:* a saved query is great, but the university wants the *same* transcript logic
    per student. A view can't take an argument.
61. **Parameterized reporting patterns** `[C]` — making views flexible; filter at query
    time; when a stored routine is worth it.
    *Hook:* you build reusable views now — but which columns should be *indexed* so they're
    fast? First you must know how the data is stored.
62. **CTE & view challenges** `[P]` `[A]` — refactor nested queries; add reporting views to
    CampusLens.
    *Hook:* you've been *reading* this database. To truly understand it — and to answer
    schema-design questions — you need to know how it was *built*.

### Section 9 — How the database is built: modeling & DDL (63–69)
*What & why: to read a schema well, and to pass design questions, you must understand data
types, constraints and normalization. Do DDL on a copy, safely.*

63. **Data types** `[C]` — `INT`/`DECIMAL`/`VARCHAR`/`DATE`/`DATETIME`/`ENUM`; why money is
    never a float; the right type per column.
    *Hook:* a column that must be unique, never null, or auto-numbered — how does the
    database *enforce* that?
64. **Constraints** `[C]` — `PRIMARY KEY`, `FOREIGN KEY`, `UNIQUE`, `NOT NULL`, `DEFAULT`,
    `AUTO_INCREMENT`.
    *Hook:* the DB spreads data across 24 tables on purpose. What principle decides what
    goes where?
65. **Normalization** `[C]` `[DIAGRAM]` — 1NF/2NF/3NF in plain words, the university schema
    as the example, when to denormalize.
    *Hook:* you understand the shape — could you *create* a new table, say `scholarships`,
    and link it correctly?
66. **CREATE and ALTER TABLE (on a copy)** `[C]` — building and changing tables safely,
    adding a foreign key.
    *Hook:* you built a table; how do you read the shape of one that already exists, like
    `exam_results`?
67. **Inspecting a schema** `[C]` — `SHOW`/`DESCRIBE`, `information_schema`, reading foreign
    keys.
    *Hook:* you can model and inspect — but everything so far only *read* data. How do you
    add rows, change them, and (carefully) remove them?
68. **The ER model of the `university` DB** `[C]` `[DIAGRAM]` — the full relationship map,
    drawn.
    *Hook:* modeling is design-time; the daily job is `INSERT`/`UPDATE`/`DELETE` — and one
    line there can destroy data.
69. **Modeling challenges** `[P]` — design a `scholarships` or `clubs` table with the right
    types and constraints; draw its relationships.
    *Hook:* time to write data — exactly where a missing `WHERE` has ended real careers.
    How do you do it without fear? *(Recall round #5.)*

### Section 10 — Changing data, safely: DML & transactions (70–75)
*What & why: the safety pillar in full. Write, update and delete without ever wrecking the
data.*

70. **INSERT** `[C]` `[A]` — single and multi-row inserts, respecting FKs (parents before
    children), `INSERT … ON DUPLICATE KEY UPDATE`.
    *Hook:* you inserted a duplicate and got an error — or you want "insert, but update if
    it already exists". Which is it?
71. **UPDATE** `[C]` — changing rows, the sacred `WHERE`, `SELECT`-first, safe-update mode.
    *Hook:* you ran an `UPDATE`, then realised it was wrong. Is it too late?
72. **Transactions** `[C]` `[DIAGRAM]` — `START TRANSACTION`, `COMMIT`, `ROLLBACK`;
    atomicity (a fee payment and its registration together).
    *Hook:* two people update the same row at once — who wins, and what stops chaos?
73. **ACID and isolation, in plain words** `[C]` — what a transaction guarantees and why it
    matters.
    *Hook:* `DELETE` removes rows — but the database sometimes *refuses* because a child row
    points at it. Is that a bug?
74. **DELETE and FK protection** `[C]` — `DELETE` vs `TRUNCATE`, `ON DELETE` behaviour, why
    the refusal protects you.
    *Hook:* your reporting queries feel slow on the big tables. What actually makes a query
    fast or slow?
75. **Safe-write challenges** `[P]` — on a copy: upserts, batched updates, a transactional
    enrolment.
    *Hook:* every "slow query" complaint comes back to one idea — how the database *finds*
    rows. What is an index, really?

### Section 11 — Making it fast: indexes & EXPLAIN (76–81)
*What & why: performance is a real interview topic from day one. Understand how rows are
found, and how to prove and fix a slow query.*

76. **How a query finds rows** `[C]` `[DIAGRAM]` — full scan vs lookup; the phone-book
    analogy.
    *Hook:* a phone book is fast because it's *sorted*. What's the database's version of a
    sorted lookup?
77. **Indexes** `[C]` `[DIAGRAM]` — B-tree intuition, primary vs secondary, what PK/FK give
    you free.
    *Hook:* you added an index and reads sped up — but inserts slowed down. Why does an
    index cost anything?
78. **The cost of indexes, and when they help** `[C]` — write overhead, selectivity,
    composite indexes and the left-most-prefix rule.
    *Hook:* how do you even *see* whether MySQL used your index or scanned the whole table?
79. **Reading EXPLAIN** `[C]` `[DIAGRAM]` — `type`, `key`, `rows`; spotting a full scan on
    `exam_results` / `student_attendance`.
    *Hook:* `EXPLAIN` shows the plan — now how do you *rewrite* a slow query to be fast?
80. **Query optimization patterns** `[C]` — filter early, join lean, avoid `SELECT *`,
    keep conditions sargable.
    *Hook:* you can make one query fast. A dashboard runs twenty — how do you think about the
    whole layer's performance?
81. **Performance challenges** `[D]` `[P]` — `EXPLAIN` and speed up real university queries.
    *Hook:* you now have every tool. Time to assemble them into the thing a real university
    would actually use.

### Section 12 — Analytics patterns & the capstone (82–88)
*What & why: assemble everything into CampusLens — the reporting layer a real registrar
could run.*

82. **Funnel analysis** `[C]` `[A]` — enquiry → application → admission conversion, drop-off
    per stage.
    *Hook:* a funnel is a snapshot; the university wants to compare the 2023 cohort to 2024
    as they progress. That's a cohort.
83. **Cohort & retention analysis** `[C]` `[A]` — track intakes across semesters, dropout
    curves.
    *Hook:* you can see *who* dropped; can you flag *who's about to*? The signals are already
    in the data.
84. **The at-risk early-warning report** `[A]` `[DIAGRAM]` — low attendance + backlogs +
    pending fees + warnings, combined into one ranked list. *(CampusLens centrepiece.)*
    *Hook:* one report is useful; a dashboard needs a dozen, all consistent. How do you
    package them?
85. **The dashboard query pack** `[A]` — admissions, fees, placements, backlogs,
    attendance-vs-performance as one coherent view set.
    *Hook:* your pack is a pile of `.sql` files. Which query answers which question, and how
    does someone else run them?
86. **Packaging & documenting the reporting layer** `[C]` `[A]` — naming, comments, a query
    catalogue, versioning in Git.
    *Hook:* a human runs these by hand. Could a *non-technical* dean just ask a question in
    English and get the SQL?
87. **NL-to-SQL and this schema** `[C]` — how AI tools use schema context, how to prompt,
    and how to *verify* the SQL they produce (the correctness pillar again).
    *Hook:* you built a real analytics layer. Time to present it — the complete CampusLens,
    in one file.
88. **CampusLens, complete** `[A]` — the full, commented reporting layer presented end to
    end; run it, read it as a guided tour. *(Capstone artifact.)*
    *Hook:* the only thing between you and the job now is the interview itself.

### Section 13 — Prepare for the interview, and where you go next (89–92)
*What & why: the finale — the questions you'll be asked, how to talk about what you built,
and an honest map of the wider data world.*

89. **The SQL questions you'll be asked** `[Q]` — joins, `GROUP BY` vs `HAVING`, window
    functions, second-highest CGPA/salary, find/remove duplicates, NULL behaviour,
    `COUNT(*)` vs `COUNT(col)` — each answered with a query that proves it.
    *Hook:* knowing the answer is half of it; explaining *how the query runs* is the other
    half.
90. **The performance & design questions** `[Q]` — indexes, `EXPLAIN`, normalization, ACID,
    "optimize this query", "design a table" — each answered against the `university` DB.
    *Hook:* they'll also ask you to talk about something you built. You have exactly the
    thing.
91. **Talking about your reporting layer** `[C]` — the two-minute CampusLens walkthrough,
    the decisions and trade-offs you made.
    *Hook:* you're SQL-interview-ready. But where does SQL actually sit in a real data stack —
    and what's the next mountain?
92. **SQL today, and where you go next** `[C]` `[Q]` — dialects (Postgres/SQLite/BigQuery/
    Snowflake), the modern data stack (dbt, BI tools, pandas, warehouses, AI/NL-to-SQL), what
    SQL can't do, career paths, hosting, projects to build; plus a full recall across the
    course. *(Real-world closing — §21.)*
    *Hook:* none — this is the end. Close the loop and point the reader at their next
    mountain.

### Recall rounds
Short recall rounds (3–4 pull-the-threads questions) inside the "Check yourself" of
**13, 25, 41, 56, 69**, and a full recall in **92**. These don't break the chain — the
cliffhanger still follows.

### CampusLens build order (quick reference)
| Lesson | What the reporting layer becomes |
|--------|----------------------------------|
| 41 | First reporting queries sketched (transcript, dept scorecard). |
| 59 | First saved **view**. |
| 62 | A small set of reporting views. |
| 82 | Admissions **funnel** view. |
| 83 | **Cohort / retention** view. |
| 84 | The **at-risk early-warning** report (centrepiece). |
| 85 | The full **dashboard query pack**. |
| 86 | Packaged, commented, catalogued, versioned. |
| 88 | Complete CampusLens presented end to end (the capstone). |

Keep a running mental model of which views/queries exist so far, and never contradict an
earlier lesson. Every `[A]` lesson adds to the *same* pack and hands over the plan +
acceptance criteria (question, tables, join keys, expected columns) — not the finished SQL —
except lesson 88, which presents the complete pack.

---

## 11. Interview questions (baked in, not bolted on)

- **Every lesson's "Check yourself"** includes **one interviewer-style question** — very
  often a **"predict the result of this query"** question, which is exactly how SQL is
  screened.
- **Every section ends with a section interview Q&A block** (§18.D).
- The **dedicated `[Q]` lessons** (89 core SQL, 90 performance & design, plus the round in
  92) answer each classic **with a query that proves it**, not just a definition.
- **Framing is reassurance:** *"You already wrote this a few lessons ago. Here's how to say
  it out loud in ten seconds."*
- At the end, **all Q&A is aggregated into one Interview Vault, grouped by section** (§19).

Classics to make sure land somewhere: second-highest CGPA/salary (three ways: subquery,
`LIMIT/OFFSET`, window); find & delete duplicate rows; `WHERE` vs `HAVING`; `INNER` vs
`LEFT` and when the count changes; `COUNT(*)` vs `COUNT(col)`; `IN` vs `EXISTS` vs `JOIN`;
`UNION` vs `UNION ALL`; `RANK` vs `DENSE_RANK` vs `ROW_NUMBER`; how an index speeds a query
and what it costs; reading an `EXPLAIN`; normalization to 3NF; what ACID means; why `= NULL`
returns nothing.

---

## 12. The interactive component library (build this first)

The reader must never face a wall of plain text. Before writing lessons, build a shared
kit: one `assets/styles.css`, one `assets/components.js`, and copy-paste HTML blocks —
**vanilla HTML/CSS/JS, no framework, no build step, in-page state only.** It just opens in a
browser. (This kit can match the Java course's kit — same brand.)

### 12.1 Design tokens (near-monochrome, one rationed accent)
```
--ink-0:#ffffff; --ink-500:#76768a; --ink-900:#191922; --ink-1000:#08080c;
--signal-500:#f04e2e;   /* the ONE accent. Means: the hook / the error / the wrong result / "wait, what?" */
--amber-500:#c1872a;    /* the course/craft colour: wordmark, "you built this", CampusLens brand */
--font-display:'Space Grotesk',sans-serif;   /* titles */
--font-body:'Inter',sans-serif;              /* body */
--font-slate:'IBM Plex Mono',monospace;      /* SQL, result grids, labels, lesson numbers */
--radius-card:14px; --radius-media:10px;
```
The world is ink. **Signal-red appears exactly once per view** and always means the live
wire — the cliffhanger, the MySQL error, the wrong result, the destructive command. Amber
is only course branding. Never paint half a diagram red.

### 12.2 The components (build every one)
1. **`query-window`** — a styled SQL panel: mono font, a tab (e.g. `funnel.sql`), line
   numbers, syntax roles (`.tok-key` for `SELECT`/`FROM`/`WHERE`, `.tok-fn`, `.tok-str`,
   `.tok-num`, `.tok-comment`, `.tok-tbl`); one clause highlightable with `.ln-hl`
   (signal-red) for "this is the line that matters".
2. **`result-grid`** — the query's result rendered as a database-style table (header row,
   zebra rows, right-aligned numbers, a small "illustrative — run it yourself" tag). Rows
   must be **plausible and consistent** with the synthetic data.
3. **Error → Fixed toggle** — two tabs over one query-window: **Errors** (shows the real
   MySQL error text in a red strip — e.g. the `ONLY_FULL_GROUP_BY` message, the reserved-
   word error) and **Fixed**.
4. **Wrong → Right toggle** — the correctness component: a query that **runs but returns the
   wrong answer** (missing `WHERE`, fan-out double-count, `= NULL`) beside the correct one,
   with a one-line "why the first is silently wrong". Default to Wrong so the reader feels it.
5. **`explain-panel`** — an `EXPLAIN` output rendered as a small grid with `type`/`key`/
   `rows`, the bad cell (a full `ALL` scan) marked red.
6. **Diagram (inline SVG)** — for anything spatial (§13): row-matching joins, GROUP BY
   buckets, the clause-evaluation pipeline, a B-tree, an ER map.
7. **`query-builder` stepper** — build a query clause by clause (SELECT → FROM → WHERE →
   GROUP BY → HAVING → ORDER BY → LIMIT); each step shows the query growing *and* the
   result-grid changing. The single best tool for teaching how a query is assembled.
8. **Predict-the-result quiz** — the "Check yourself" block: show a query, ask which rows /
   how many / what value comes back; each option reveals an explanation; one question tagged
   "Interviewers ask this".
9. **Reveal-answer** — collapsed "Show my solution"; for `[P]` it opens to the two-version
   layout (beginner vs experienced analyst, §10).
10. **Interview accordion** — question rows expanding to a short answer *with a query-window
    proving it*.
11. **Callout / aside** — variants: `note`, `trap` (beat 6), `mysql-gotcha` (§9), `safety`
    (red — destructive-op warning, §6), `real-world` (beat 7), `go-deeper` (external refs,
    §14bis).
12. **Glossary chip** — inline first-use term with a one-line definition on hover/tap; the
    section glossary is a grid of these.
13. **Cliffhanger bar**, **video-hook / photo slots**, **progress/section rail** — as in the
    Java course (§15).

### 12.3 Per-lesson page
Each lesson is one self-contained `.html` file linking the shared kit, laying out beats 1–11
with the components. Nearly every lesson pairs a `query-window` with a `result-grid`; a
component every screen or two, so it never reads like the manual.

---

## 13. Draw it — use SVG/art whenever a picture teaches faster

A standing habit, not only for `[DIAGRAM]` lessons. Rules: monochrome ink for structure,
**one signal-red element = the single idea**, **every part labelled** in mono, **one idea per
diagram**, faint blueprint-grid backdrop.

**Draw it at least whenever you're explaining:**
- tables/rows/columns and the relational model (L1–L2);
- a **primary key → foreign key** link between two tables (L3);
- an **INNER JOIN** as rows matching on a key, and a **LEFT JOIN** keeping unmatched left
  rows with NULLs (L34–L35);
- **join fan-out**: one left row matching many right rows, multiplying (L37);
- **GROUP BY**: many rows collapsing into one row per bucket (L27);
- the **logical order of evaluation** as a pipeline: `FROM → WHERE → GROUP BY → HAVING →
  SELECT → ORDER BY → LIMIT` (great recurring diagram — it explains `WHERE` vs `HAVING`,
  aliases, and `ONLY_FULL_GROUP_BY` all at once);
- **NULL** three-valued logic (true / false / unknown) (L18);
- a **window function**: partition boxes with a running frame sliding down (L49–L53);
- an **index** as a sorted B-tree lookup vs a full scan (L76–L77);
- the **ER map** of the `university` DB (L5, L68);
- a **transaction**: writes held, then commit vs rollback (L72).

Keep these **teaching diagrams** distinct from the **mascot illustration** (§20). A lesson
can have both.

---

## 14bis. Point to the best of the internet (references and videos)

Where it genuinely deepens the one idea, add a **`go-deeper` callout** with one or two
high-quality references (max two per lesson):
- **Facts → official docs:** the MySQL 8 Reference Manual (`dev.mysql.com/doc`). Safe to
  cite by name; verify the exact deep-link at build time.
- **Videos → don't invent URLs.** Drop a video-hook slot (§15) naming what it should be plus
  search terms, for the owner to paste the real link. Examples: the history of the relational
  model / Edgar Codd at L5; a visual JOINs explainer at L34–35; a window-functions walkthrough
  at L49.
- Keep the reader in the lesson: references are "when you want more," never a substitute for
  teaching the idea here in full.

---

## 15. Media hooks (videos and photos the owner supplies)

The owner adds real images/videos; you place the slots and say exactly what each should be.
Never hardcode a URL:
```
[VIDEO HOOK: <what it should show> | suggested: <search terms> | fill: data-src=""]
[PHOTO: <what it should show> | suggested: <search terms> | fill: src="" credit=""]
```
Place them at: the course / Section-0 open and **L5** (relational-model / SQL history); each
section overview (set the stakes — e.g. a JOIN visual for Section 5, an EXPLAIN screenshot for
Section 11); concept anchors where a real image makes an analogy land (a phone book for
indexes, a filing system for normalization); and CampusLens milestones (84, 85, 88) for a
screenshot of a report's result grid.

---

## 16. Build order (how you actually produce all this)

1. **The shared kit first:** `assets/styles.css` + `assets/components.js`, the Sig model-sheet
   prompt (§20), and a course home page / table of contents.
2. **Section by section, 0 → 13.** For each section produce, in order: the **overview**
   (§18.A) → the **lessons** (each a full HTML file, beats 1–11, one idea per lesson §4,
   `university`-only §8) → the **glossary** (§18.C) → the **interview Q&A** (§18.D) → the
   **fun exercise set** (§18.E). Keep CampusLens's running state consistent.
3. **Per lesson as you go:** the `[ILLUSTRATION PROMPT]`, any teaching diagrams (§13), a
   thumbnail spec, `go-deeper` references (§14bis), media slots (§15). Nearly every lesson
   pairs a query-window with a result-grid.
4. **The capstone (§17)** presented in lesson 88.
5. **The real-world closing (§21)** in lesson 92.
6. **The Interview Vault (§19),** grouped by section.
7. Final pass: verify the chain across all lessons (every open resolves the prior hook; every
   close sets the next).

Suggested layout:
```
/index.html
/assets/styles.css
/assets/components.js
/section-00/index.html, l01.html … l07.html, glossary.html, interview.html, exercises.html
/section-01/…  … through …  /section-13/…
/capstone/campuslens.sql          (the complete reporting layer: views + queries)
/real-world/index.html
/interview-vault/index.html
```

**Per-lesson checklist** (run before moving on):
- [ ] First line resolves the previous cliffhanger; last line sets the next.
- [ ] Exactly one idea (§4); nothing from a later chapter smuggled in.
- [ ] Every query is against the `university` DB; real tables/columns; no banned generic
      schema (§8).
- [ ] Reads like a person sharing notes, not the manual.
- [ ] Every new word defined the first time (glossary chip).
- [ ] A query paired with its result-grid; where useful, a wrong→right or error→fixed toggle.
- [ ] The correctness trap and/or MySQL gotcha named (§6, §9); a `safety` callout on any write.
- [ ] A drawing/SVG wherever a picture teaches faster (§13).
- [ ] Exactly 2 "Check yourself" questions, one interviewer-style (often predict-the-result).
- [ ] Components used so it's never a plain-text wall; one red element per view.
- [ ] Illustration prompt + diagrams + media slots + (if useful) one `go-deeper` reference.
- [ ] CampusLens state consistent; `[A]` lessons give plan + criteria, not finished SQL
      (except 88).

---

## 17. The capstone — CampusLens (full spec)

**Name:** **CampusLens — the University Reporting Layer.**
**Tagline:** *one query pack, the whole campus at a glance.*

CampusLens is the single artifact the whole course builds: a growing, documented set of SQL
**views and queries** on the `university` database that a real registrar, dean or placement
cell could run to make decisions. It begins in lesson 59 as one saved view and ends in lesson
88 as one complete, heavily-commented `.sql` file. We never switch databases; every `[A]`
lesson adds one piece to the *same* pack (build order in §10b).

### 17.1 What CampusLens delivers (the finished behaviour)
A catalogue of named views/queries answering the university's real questions:
- **Admissions funnel** — enquiry → application → admission conversion, drop-off per stage and
  per source.
- **Cohort / retention** — each intake tracked across semesters, dropout curve, current status.
- **Fee health** — collection by month (running total), overdue/pending exposure, per-program
  and per-cohort.
- **Academic performance** — department scorecard (avg marks, pass rate), backlog counts and
  trends (`attempt_no > 1`), attendance-vs-performance correlation.
- **Placements** — placement rate and average package per department, top recruiters, alumni
  outcomes (NULL-aware).
- **The at-risk early-warning report** — the centrepiece: a single ranked list combining low
  `attendance_pct`, active backlogs, `pending`/`late` fees, and open `warnings`, so staff can
  intervene before a student drops.
- **A per-student transcript** — all results and SGPA/CGPA in one view.

### 17.2 The shape (how it's organised by lesson 88)
- A set of **views** (`vw_funnel`, `vw_cohort_status`, `vw_fee_monthly`,
  `vw_dept_scorecard`, `vw_backlogs`, `vw_placement_summary`, `vw_at_risk`,
  `vw_transcript`) — each a saved, documented query.
- A **query catalogue** at the top of the file: a commented index mapping each view/query to
  the plain-English question it answers.
- Built with the tools the course taught: `JOIN`s across the schema, `GROUP BY`/`HAVING`,
  window functions (running totals, top-N-per-dept, LAG for month-over-month and semester
  dropout), CTEs for readability, NULL-safe handling (`COALESCE`) for the alumni data, correct
  date bucketing (`DATE_FORMAT`), and the backtick on `` `current_role` ``.

### 17.3 Every view carries its concept comment
The final file comments each piece with the idea and the lesson it came from, so the artifact
reads as a guided tour, e.g.:
```sql
-- WINDOW: RUNNING TOTAL (L53) — fees accumulate month over month.
-- NULL-SAFE (L24) — alumni employer defaults to 'Unknown'.
-- ANTI-JOIN (L36) — enquiries that never became students.
```

### 17.4 Acceptance criteria (what "done" means)
- Runs on MySQL 8 against `university`, **read-only** (views + SELECTs; no writes to real data).
- Every view returns a sensible row shape and correct numbers that respect the data semantics
  (§9) — graduated-only alumni, `attempt_no > 1` backlogs, `paid_at` NULL when pending,
  correct intake lifecycle.
- The at-risk report produces a single ranked list a human could act on.
- The file opens with a catalogue mapping each view to its question; every view is commented
  with its concept + lesson.
- Stays entirely inside the `university` world — no generic `employees`/`orders`.
- Presented via the query-window component with a downloadable `campuslens.sql`, plus
  illustrative result-grids for the headline views.

---

## 18. The section wrapper (every section is bookended this way)

On top of the per-lesson shape, each section is its own folder with a section index page plus
five parts, in order:

- **A. Overview — "What you'll study & why."** What this section covers, *why it exists* (which
  university questions you couldn't answer before and can after), how it hangs off the previous
  section's last hook, and the lesson list with one-line promises. Open with a media hook (§15).
- **B. The lessons**, in order (§10b), one idea each (§4), `university`-only (§8).
- **C. Glossary.** Every new term the section introduced, each a one-line plain-English chip.
- **D. Interview Q&A.** The interviewer questions this section's material produces, each a short
  confident answer **with a query that proves it**, in the accordion. Framing: *"You already
  wrote this — here's how to say it out loud."*
- **E. Exercises — "Your turn: the fun set."** **Exciting, challenge-flavoured** problems, never
  a boring drill — give them stakes (a "can you do this in one query, no subquery?" dare, a
  "this query runs but the number's wrong — find why" rescue, a real dean's question, a new
  CampusLens metric). Always against the `university` DB. Reader attempts first; solutions behind
  a reveal, shown as two versions (beginner vs experienced analyst) with a plain-words "why the
  second is better".

So every section reads: **overview → lessons → glossary → interview Q&A → exercises**, and it
still obeys the chain.

---

## 19. The Interview Vault (all Q&A, grouped by section)

After the course is written, produce one **Interview Vault** page collecting **every interview
question in the course, grouped by section**, in the accordion. For each: the question, a short
confident answer, and a query-window that proves it (with an illustrative result-grid where it
helps). Pull from each lesson's "Check yourself", each section's Q&A block (§18.D), the `[Q]`
lessons (89, 90) and lesson 92's round.

Group headers mirror the 14 sections. Intro in the notes-sharing voice: *"Every one of these you
already wrote. This page is just them, lined up, so you can hear yourself answer."* End with a
**"how to talk about your project"** guide — the two-minute CampusLens walkthrough — and a
mock-interview plan (SELECT/WHERE warm-up → joins → GROUP BY/HAVING → window functions →
"predict this result" → "optimize this slow query" → one schema-design question). This vault,
plus lessons 89–92, is the **"Prepare yourself for the interview"** finale.

---

## 20. The mascot (Sig), illustrations and thumbnails

Every lesson gets a cartoon **illustration** (you write the prompt; an image generator draws it)
and a **thumbnail** — the emotional layer, separate from the teaching diagrams of §13. Same
mascot and brand as the Java course, so the two read as siblings. Palette: monochrome ink,
**exactly one signal-red element = the lesson's idea**, amber only for course branding.

### 20.1 The character — "Sig" (locked design, changing expression)
An **original graphic mascot** built from the design system — **not a human, not based on
anyone.** Identical design every image; change only expression, pose and signal state.
- **Head = a rounded frame/screen** with a thin ink border and a light face panel; **two dot
  eyes + a small mouth** (brows only when needed; no nose).
- **The signal:** **one red (`#f04e2e`) live-dot in the top-left corner of the screen** — his
  defining mark; concentric rings when "live".
- **Body = a simple deep-ink rounded shape** with short stub arms, mitten hands, little feet —
  so he can point, hold, sort, stack.
- **Flat solid fills only** — no gradients, shading, realism, 3D.
- **Never:** hair, skin, beard, a human face, a second saturated colour on his body, or more
  than one red element in a scene. If the scene's hook *is* the red element, turn his dot **off**
  (ink) so total red stays at one.
- **Signal-state = expression:** lit+rings = the big moment (cliffhanger, aha); lit = engaged;
  off = calm / the red is spent on the hook.

### 20.2 Illustration prompt (output one per lesson, verbatim scaffold, four blanks)
Sig should be **doing the SQL concept** with a data prop where possible (sorting rows,
stitching two tables together, holding a magnifier over a grid, pointing at a NULL gap).
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
POSE / ACTION: <Sig DOING the SQL concept — a data/table prop where possible>.
CAMERA: <bust / 3-4 / full body / over-shoulder / low angle>.
SCENE: <the concept in the university-data world, naming the ONE red element — his dot OR an
external mark like a NULL gap, a wrong number, an error strip>.

COLOUR (strict): Sig's body ink, screen light, ONE red dot; background + props + tables +
diagram monochrome ink; EXACTLY ONE red (#f04e2e) total; amber only for course branding, never
on Sig; faint blueprint-grid background; no other colours. 16:9.
]
```
**Build the model sheet first** (front + 3/4 poses, six expressions, the dot lit/lit+rings/off,
a few arm poses) and reference "same character and style as the reference sheet" on every later
prompt. Calibration: L18 NULL — Sig peering into an empty ink cell tagged red `NULL` in an
`alumni` row (dot off). L34 INNER JOIN — Sig zipping two ink tables together on a glowing key,
one matched row lit red (dot off). L37 fan-out — Sig startled as one row multiplies into many,
the doubled `COUNT` marked red (dot off). L76 index — Sig flipping to a red-tabbed edge of a
sorted stack while a full scan trails behind (dot off). L88 — Sig, low angle, dot lit+rings,
beside a tidy stack of labelled ink "views" forming the CampusLens tower.

### 20.3 Thumbnails
One per lesson and per section: a 16:9 that **reads in half a second.** Ink everywhere, **one
signal-red element = the hook**, Sig on-model, lesson number in mono in a corner, title = the
tension not the topic ("Where did 230 students go?" beats "LEFT JOIN"). Recipe by type: `[C]`
big idea + one red mark on the concept that bites; `[A]` a query-window with one clause
highlighted red + kicker `CAMPUSLENS`; `[D]` an urgent red MySQL error or wrong result, Sig
reacting; `[P]` the challenge as title + kicker `PRACTICE · N QUERIES`; `[Q]` the question in
quotes as the hero + a red question mark + kicker `INTERVIEW`.

---

## 21. The real-world closing — "SQL today, and where you go next"

After lesson 92's recall, add a closing orientation page, framed as **"beyond this course"**
(so it doesn't violate the MySQL-on-one-DB scope of the lessons). Keep the honest, notes-sharing
voice. Cover:
- **Where SQL is today** — the most universal data skill there is; every company with data uses
  it; the language has barely changed in decades, which is why it's worth learning deeply.
  *Verify specifics against a fresh source at build time; don't ship stale claims.*
- **Who uses it** — analysts, data engineers, backend developers, data scientists, product
  managers; effectively every data-touching role. *Check current framing when you write it.*
- **What you can do with SQL** — reporting and dashboards, analytics, data pipelines, backing
  application databases, ad-hoc investigation, feeding BI and ML.
- **What SQL is *not*** (be honest) — it isn't a general programming language: complex app logic,
  machine learning, and orchestration live elsewhere (Python and friends). "The right tool changes
  with the job — but almost every data job starts with SQL."
- **Dialects you'll meet** — the same core SQL runs on PostgreSQL, SQLite, SQL Server, and cloud
  warehouses (BigQuery, Snowflake, Redshift); note that functions and quirks differ (our
  `DATE_FORMAT`, backtick quoting and `ONLY_FULL_GROUP_BY` are MySQL-isms).
- **The modern data stack** (named, not taught) — dbt for versioned transformations, BI tools
  (Power BI, Tableau, Metabase, Looker), Python/pandas for what SQL can't do, data warehouses,
  and AI/NL-to-SQL assistants (which still need someone who can *verify* the SQL — you).
- **Career paths** — data analyst → analytics engineer / data engineer → BI or backend, or the
  DBA track; SQL is the on-ramp to all of them.
- **Where SQL runs / hosting** — self-hosted (like this Dockerised MySQL), managed cloud
  databases, and cloud warehouses. Name the shapes; don't write a provisioning tutorial.
- **Projects worth building to get hired** — extend CampusLens; build a dashboard on top of it; a
  cohort/retention analysis; an at-risk model's data layer; then, as you learn the stack, a dbt
  project or a warehouse-backed BI dashboard. Point at a bigger staged roadmap as "your next six
  months."

---

## 22. Schema reference (embed so the course is self-contained)

The database is **`university`** (MySQL 8, InnoDB, `utf8mb4`): 24 tables, ~85,000 rows, all
linked by foreign keys; every table has `created_at`/`updated_at` (second precision);
transactional tables use UUID `txn_id`s; data is fully synthetic (Faker). Lifecycle as of Aug
2026: 2021 & 2022 intakes `graduated` (in `alumni`), 2023 in sem 7, 2024 in sem 5, 2025 in sem 3;
a few `dropped` per active batch; enquiry funnel ~990 → ~550 students.

**Connect (from the VPS host):** `docker exec -it mysql-m6yc-mysql-1 mysql -u root -p university`.
**Adminer GUI:** `http://<vps-ip>:8080/` (System MySQL, Server `mysql-m6yc-mysql-1`, user `root`).
**Reset:** `docker exec -i mysql-m6yc-mysql-1 mysql -u root -pPASSWORD < university.sql`.

**Tables (PK / key FKs / notable columns):**
- **departments** (5: CSE, ECE, ME, CE, IT) — `id`, `code`, `name`, `established_year`.
- **programs** — `id`, `department_id`→departments, `name`, `degree_type`, `duration_years`,
  `total_semesters`.
- **faculty** (~70) — `id`, `faculty_code`, `department_id`→departments, name/email/phone,
  `designation`, `date_of_joining`, `salary`, `status` (active/resigned/retired).
- **enquiries** (~990) — `id`, `enquiry_code`, name/email/phone, `city`, `state`,
  `interested_department_id`→departments, `source`, `status`
  (enquired/applied/admitted/rejected/lost), `enquiry_date`.
- **students** (550, central) — `id`, `student_code` (e.g. `UNIV2021CSE0001`),
  `enquiry_id`→enquiries (nullable), `department_id`→departments, `program_id`→programs, name,
  `gender`, `date_of_birth`, email/phone/address/city/state, guardian fields, `intake_year`,
  `current_semester` (1–8, NULL if graduated), `status` (active/graduated/dropped), `cgpa`.
- **admissions** (550, 1 per student) — `id`, `student_id`→students (unique), `admission_no`,
  `admission_date`, `admission_type` (Merit/Management/Sports Quota), `admitted_semester`.
- **fee_structure** — `id`, `program_id`→programs, `intake_year`, `semester`, `tuition_fee`,
  `other_fee`; unique (program, intake_year, semester).
- **fee_payments** (~7,900, UUID) — `id`, `txn_id`, `student_id`→students, `semester`, `amount`,
  `payment_mode` (UPI/Card/NetBanking/Cash), `payment_status` (paid/late/partial/pending),
  `due_date`, `paid_at` (NULL if pending). 2 instalments per semester per student.
- **subjects** (240, 6 per sem per dept) — `id`, `subject_code`, `department_id`→departments,
  `semester` (1–8), `name`, `credits`.
- **semester_registrations** (~3,950) — `id`, `student_id`→students, `semester`, `academic_year`
  (e.g. `2021-22`), `sgpa`, `status` (completed/ongoing/detained); unique (student, semester).
- **exam_results** (~22,800) — `id`, `student_id`→students, `subject_id`→subjects, `semester`,
  `attempt_no` (>1 = backlog re-sit), `marks` (0–100), `max_marks`, `grade` (O/A+/A/B+/B/C/F),
  `result` (pass/fail), `exam_date`.
- **student_attendance** (~23,000) — `id`, `student_id`→students, `subject_id`→subjects,
  `semester`, `classes_held`, `classes_attended`, `attendance_pct`.
- **faculty_attendance** (~3,000) — `id`, `faculty_id`→faculty, `att_date`, `status`
  (present/absent/leave), `check_in`, `check_out` (NULL if not present); unique (faculty, date).
- **books** (200) — `id`, `isbn`, `title`, `author`, `category`, `total_copies`,
  `available_copies`.
- **library_transactions** (~4,000, UUID) — `id`, `txn_id`, `book_id`→books,
  `student_id`→students, `issued_at`, `due_date`, `returned_at` (NULL if not returned),
  `fine_amount`, `status` (issued/returned/overdue).
- **companies** (40) — `id`, `name`, `industry`, `city`.
- **placements** (~320) — `id`, `student_id`→students, `company_id`→companies, `role`,
  `package_lpa`, `placement_type` (internship/full-time), `offer_date`, `status`
  (offered/accepted/declined).
- **alumni** (~220, many NULLs by design) — `id`, `student_id`→students (unique),
  `graduation_year`, `final_cgpa`, `current_company`, `` `current_role` `` (**reserved word —
  backtick always**), `current_location`, `industry`, `linkedin_url`, `higher_studies` (0/1),
  `higher_studies_institute`, `estimated_salary_lpa`, `last_updated`.
- **events** (45) — `id`, `name`, `category` (Technical/Cultural/Sports), `event_date`, `venue`,
  `organised_by_dept`→departments.
- **event_participation** (~1,750) — `id`, `event_id`→events, `student_id`→students, `role`
  (participant/organiser/winner), `position` (1st/2nd/3rd or NULL), `registered_at`.
- **cafeteria_transactions** (~6,000, UUID) — `id`, `txn_id`, `student_id`→students, `amount`,
  `items`, `payment_mode`, `spent_at`.
- **hostel_allocations** (~250) — `id`, `student_id`→students, `block`, `room_no`,
  `allocated_from`, `allocated_to` (NULL if active), `status` (active/vacated).
- **complaints** (260) — `id`, `complaint_code`, `raised_by_student`→students, `category`
  (Hostel/Academic/Facility/Ragging/Cafeteria/Transport), `description`, `severity`
  (low/medium/high), `status` (open/in_progress/resolved/closed), `raised_at`, `resolved_at`
  (NULL if open).
- **warnings** (200) — `id`, `student_id`→students, `reason` (Low attendance/Misconduct/Fee
  default/…), `severity` (verbal/written/final), `issued_by_faculty`→faculty, `issued_at`.

**Relationship map (nearly everything links to `students.id`):**
```
departments ─┬─< programs
             ├─< faculty ──< faculty_attendance
             ├─< subjects ──< exam_results >── students
             └─< students   subjects ──< student_attendance >── students
enquiries ──< students ──< admissions
students ──< fee_payments · semester_registrations · exam_results · student_attendance
students ──< library_transactions >── books
students ──< placements >── companies
students ──< event_participation >── events
students ──< cafeteria_transactions · hostel_allocations · complaints · warnings(──faculty)
students ──1:1── alumni   (graduated only)
```

---

## 23. Do / Don't (guardrails)

**Do**
- Keep the chain unbroken: resolve the last hook first, set the next hook last, every lesson.
- One idea per lesson; split into focused sub-lessons rather than mingle (§4).
- Keep every query against the `university` DB; real tables/columns; never a banned generic
  schema (§8).
- Teach *correct*, not just runnable — show the wrong→right trap; teach reading results
  critically (§6).
- Put a `safety` callout on every write; SELECT-before-write, transactions, backups (§6).
- Respect the data semantics and MySQL gotchas as teaching moments (§9).
- Draw it with SVG whenever a picture teaches faster (§13).
- Pair queries with result-grids; add one strong external reference where it deepens the idea.
- Let readers attempt practice before revealing solutions; show beginner vs experienced-analyst
  versions.
- Verify real-world names/figures at build time (§21).

**Don't**
- Don't teach another dialect, an ORM, a BI tool, or Python *as course content*; don't leave the
  `university` database (HTML is only the delivery medium).
- Don't mingle topics; don't merge or skip lessons; don't open a lesson without answering the
  previous cliffhanger.
- Don't give practice solutions first; don't write CampusLens for the reader (except lesson 88).
- Don't show a destructive command without the safety framing; don't imply a shown result is a
  live run — it's illustrative, tell the reader to run it.
- Don't dump 200-line queries; don't use a word you haven't defined; don't praise a wrong or
  clumsy query.
- Don't invent media URLs — leave filled-in-later slots with suggested search terms.
- Don't let more than one red element appear in any single view.

Build it all. Start with the shared kit, then Section 0, and go — one idea, one lesson, one real
university question at a time.