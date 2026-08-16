# Module 1 — Computer Science Foundations

**Duration:** ~20 weeks at regular pace, spread across five semesters.

> These are the same subjects on the curriculum at the IITs and at every good
> engineering college. The syllabus was never the secret. What's missing at most
> colleges is a teacher who connects it to the job — that's what these courses
> are.

## Semester 1 — The Machine

### Operating Systems · `CS-OS-101` · 4 weeks
Processes and threads · scheduling · context switching · memory management,
paging, virtual memory · deadlocks (detect, prevent, break) · concurrency,
synchronization, mutex vs semaphore · file systems · Linux basics you'll use
daily.

**Why it matters:** why your server ran out of memory, why the app froze, why
two requests corrupted the same row. Asked in nearly every technical interview.

### Computer Networks · `CS-NET-101` · 3 weeks
The layered model, practically · IP, ports, DNS · TCP vs UDP · HTTP/HTTPS,
methods, status codes, headers · what happens when you type a URL and press
enter · REST · latency, bandwidth, caching · basic security: TLS, certificates,
CORS.

**Why it matters:** you cannot debug a web application without it. "What happens
when you type google.com" is the single most-asked interview question in the
industry.

## Semester 2 — The Data

### DBMS · `CS-DB-101` · 3 weeks
What a database is and why files aren't enough · relational model · ER diagrams
and schema design · normalisation (1NF→3NF, and when to stop) · keys and
constraints · SQL: select, joins, group by, subqueries · indexes and why queries
are slow · transactions and ACID · SQL vs NoSQL, honestly.

**Why it matters:** every application has data. This subject appears in the job
on day one and in the interview in every round.

## Semester 3 — The Code

### Object-Oriented Programming · `CS-OOP-101` · 3 weeks
Classes and objects · encapsulation, inheritance, polymorphism, abstraction —
with real reasons, not definitions · interfaces vs abstract classes ·
composition over inheritance · SOLID principles · the design patterns that
actually appear (Singleton, Factory, Strategy, Observer, MVC).

Taught **in your chosen language**, alongside Module 2.

**Why it matters:** every real codebase is structured this way. Being unable to
read someone else's code is the fastest way to fail probation.

### Data Structures & Algorithms · `CS-DSA-101` · 6 weeks
Complexity — Big-O, honestly and briefly · arrays and strings · linked lists ·
stacks and queues · hash maps (the most useful structure in real work) · trees
and BSTs · heaps · graphs, BFS/DFS · sorting and searching · recursion ·
patterns: two pointers, sliding window, greedy, dynamic programming basics.

**Approach:** patterns over problem count. 100 problems understood beats 500
solved by copying. Curated problem sets, not a firehose.

**Why it matters:** it's a real interview round almost everywhere, and it is
genuine problem-solving skill. It is also **not the whole game** — a warning
this course states out loud.

## Semester 4 — The Craft

### Software Engineering · `CS-SE-101` · 2 weeks
SDLC models · Agile and Scrum in practice · requirements and user stories ·
version control with Git, properly (branching, PRs, merge conflicts, rebasing) ·
code review etiquette · testing: unit, integration, regression · bug lifecycle ·
documentation · estimation · working in a team.

**Why it matters:** this is what your actual working day is made of. Freshers
who know Git and sprints are productive in week one instead of week six.

### Web Technologies · `CS-WEB-101` · 2 weeks
Client–server, properly · HTML semantics · CSS layout (flexbox, grid,
responsive) · JavaScript fundamentals and the DOM · HTTP in the browser ·
cookies, sessions, tokens, auth · REST APIs, JSON · browser dev tools ·
rendering, and where performance goes.

**Why it matters:** almost every job touches the web, whatever your title.

## Semester 5 — The Scale

### System Design · `CS-SYS-101` · 3 weeks
Scaling: vertical vs horizontal · load balancers · caching strategies · database
replication and sharding · queues and async processing · microservices vs
monolith, honestly · CAP theorem · designing for failure · how to *talk through*
a design in an interview.

**Why it matters:** this subject moves your salary band more than any other. It
separates a 3-year engineer from a 6-year one.

### Artificial Intelligence — overview · `CS-AI-101` · 1 week
What AI/ML actually is · supervised vs unsupervised, in plain language · what
LLMs are and are not · what's hype and what's real · how AI shows up in ordinary
products · how to build on top of AI APIs without becoming a researcher · what
it means for entry-level jobs, said honestly.

**Why it matters:** every company is asking about it. Being the fresher who can
speak about it sensibly is an advantage.

### Theory of Computation / Compiler Design · `CS-TOC-101` · 1 week
Finite automata and regular expressions (the useful half) · grammars ·
tokenising, parsing, compiling · interpreted vs compiled · what your language
runtime is doing.

**Kept deliberately light**, and marked optional for most tracks. It explains
*how* your code becomes something that runs, which is worth a week — not more,
for a job seeker.

## What is not here

**Mathematics.** No calculus, no discrete maths, no engineering maths. It is not
a prerequisite for a software job. If you go into ML research or graphics later,
you will learn what you need then, with motivation and context.

Being bad at maths has never stopped anyone from becoming a good engineer.
Believing that it would has stopped plenty of people from trying.
