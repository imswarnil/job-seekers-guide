# university-management-content.md — the complete "University Management" job-seekers guide

**Read this once, then build the whole course.** This is the single, self-contained brief
for producing an end-to-end, job-focused course that takes someone from **knowing nothing**
to a **hireable software/IT engineer**, taught by building one real product: a complete
**University Management System** called **CampusOS**.

The course does three things, **in this order** (the order is a hard rule — see §3):
1. **Foundation & history first** — what a computer is, where it all came from, and how to
   think like a programmer. Nothing is magic.
2. **Core computer-science engineering next** — the timeless fundamentals (programming, data
   structures, algorithms, systems, databases) that make you an engineer, not a code-copier.
3. **Then the technologies of the IT world** — web technologies in depth (building the
   University Management System), a tour of the wider IT landscape, and **AI later**, once the
   foundation is solid.

This is the umbrella course for the whole family. Two sibling courses build modules that live
*inside* CampusOS: "Java for Job Seekers" builds **AdmitDesk** (the admissions console), and
"SQL for Job Seekers" builds **CampusLens** (the reporting layer). This course builds the rest —
the interface, the website, the CRM, the ERP, the dashboards — and the foundation underneath it
all.

Your job (Claude Code): produce the entire course as **interactive HTML lessons** (plus a shared
kit, an interview vault, and the complete CampusOS app), part by part, in order, following §17.
Do not ask which lesson to write — write them all.

---

## 1. The promise (the north star — reread before every lesson)

This course is for **someone who wants an IT job and is starting from zero**. By the end they
must be able to:
1. **Understand computing from first principles** — the history, the machine, and how software
   really works, so nothing they meet later is magic.
2. **Have the core CS engineering fundamentals** — programming, data structures, algorithms,
   operating systems, networks, and databases — the things interviews test and real work needs.
3. **Build and ship a real web application** — the University Management System, front to back,
   deployed to the internet.
4. **Be familiar with the whole IT world** — web technologies, the wider tech landscape (cloud,
   DevOps, security, mobile, data), and AI — enough to hold a conversation, choose a direction,
   and keep learning.

If something you're about to write doesn't move a beginner toward those four, cut it or rewrite
it.

---

## 2. What this course is

- **One course → parts → lessons.** A part is a stage; a lesson is one interactive blog post —
  the unit the reader consumes.
- **One theme, forever:** the **university**, and one product, **CampusOS** — a complete
  University Management System (public website, admission CRM, student/teacher/admin portals,
  ERP-style modules, and data-visualization dashboards). Every example builds a piece of it, or
  builds the foundation to build it. This is a hard rule (§9).
- **Foundation and core CS come first; technologies come after.** History and fundamentals are
  not "boring theory to skip" — they're the reason everything later is easy.

---

## 3. The sequence rule (enforce this — it's the whole point of the rewrite)

Teach in this order, always. Do not jump ahead to a technology before its foundation:

**Foundation & history → Core CS engineering → Web technologies → The wider IT world → AI →
Career & the complete build.**

- **Never open with a framework or a tool.** The reader earns the web stack by first understanding
  the machine, programming, data, and systems.
- **AI comes later, on purpose.** The reader will *use* an AI assistant as a helper once they can
  code, and *learn AI as a subject* only after the foundation and the core are in place — so they
  can judge what AI produces instead of trusting it blindly. Do not front-load AI terminology.
- **"Familiar," not "expert," for the wider IT world.** Web technologies are built deeply (that's
  the product); the broader landscape (cloud, DevOps, security, mobile, data) is a guided tour so
  the reader knows it exists and can choose a lane.

---

## 4. The voice, the chain, and the lesson shape (same family as the sibling courses)

- **Voice:** a **job seeker sharing the notes that got them hired** — first person, warm,
  confident, honest, never fake praise. Beginner-first. Define every new word the first time.
  Warn before hard parts (objects, async, backend, deployment). Thesis: *"Computing is just a
  stack of small, learnable ideas built in order — and once you can build one real system end to
  end, you can build anything, because the pieces repeat."*
- **The cliffhanger chain (the rule that matters most):** every lesson ends on a cliffhanger; the
  next lesson's first line answers it. It's a chain, not a list — today's lesson is the thing
  yesterday's made the reader *need*. Open by resolving the last hook; close by setting the next.
  Non-negotiable, every lesson.
- **One idea per lesson — never mingle.** A CSS-grid lesson teaches grid, not JavaScript "while
  we're here." A methods lesson teaches methods, not objects. Split into focused sub-lessons if
  one idea would carry two. Each build lesson adds one real piece to CampusOS.
- **The 11-beat lesson shape:** (1) resolve last hook, (2) bridge, (3) why we need this / what
  breaks without it, (4) idea with an everyday analogy first, (5) a small runnable example — show
  the **broken version first** where it helps, (6) what confuses people here, (7) how you'd use it
  in the real university system, (8) what you can now build + the visible result, (9) your turn,
  (10) exactly 2 "Check yourself" questions, one interviewer-style, (11) the cliffhanger. Open
  (resolve) and close (hook) appear every time.

---

## 5. AI in this course (a helper early, a subject later)

The honest rule, stated once: **AI writes code and boilerplate fast, so the value moved to
understanding, judgment, architecture, debugging, and knowing whether what it gave you is
correct, accessible, and secure. AI raises the bar on fundamentals — it doesn't remove them. You
cannot supervise what you don't understand.**

Because of that:
- **As a helper:** once the reader can code (after the foundation), they may use an AI assistant
  to scaffold and explain — always reviewing, never trusting blindly.
- **As a subject:** AI and machine learning are taught **later** (Part E), after the foundation
  and core, and only then do you build real AI *features* into CampusOS.

Do not introduce AI terminology up front. First, foundation and history.

---

## 6. Correctness you can see

Every lesson pairs code with its result — a printed output for the foundation/core parts, a live
rendered preview or a live chart for the web parts (§12). Teach the habit of *checking*: does it
run? is it correct? is it responsive and accessible? A thing that runs is not a thing that's done.
Show the broken version first where it helps (the error, the collapsed layout, the failing fetch),
then the fix, so the reader learns to debug instead of guess.

---

## 7. Tools (learn each by using it to build CampusOS)

Introduce each tool when the build needs it. Foundation tools first, web tools later:
- **Linux & the command line, Git & GitHub** — from the foundation, day one.
- **Figma** — designing the UI (web part).
- **npm, Vite, ESLint + Prettier, TypeScript** — the web project toolchain.
- **React** (primary), with a survey of Vue/Svelte/Angular/Lit.
- **Chart.js and D3.js** — data visualization.
- **Node.js + Express, a database, an ORM** — the backend.
- **Jest + Cypress/Playwright** — testing.
- **Docker, a cloud host, CI/CD (GitHub Actions)** — deployment.
- **An AI/LLM API** — the AI part (later).

---

## 8. Scope — foundation + core CS + the technologies of IT

**In scope, in order:** the history and fundamentals of computing; programming; data structures
and algorithms; computer architecture, operating systems, and networks; databases and SQL; the
full web-technologies stack (design, HTML, CSS, JavaScript, data visualization, tooling,
TypeScript, frameworks, web components, backend, PWAs, testing, accessibility, performance,
security, SEO, DevOps); a familiarization tour of the wider IT world (cloud, DevOps, cybersecurity,
mobile, data, and awareness of other fields); and AI/ML (later). Delivery is interactive HTML; the
subject is computing — never teach the HTML delivery layer as if it were the course.

**Kept lighter (familiarize, don't fully build):** native mobile SDKs, heavy data engineering,
infrastructure-at-scale, and niche/hype fields — introduced as landscape awareness in Part D, not
built deeply.

---

## 9. The university rule (strict — enforce it everywhere)

**Every example, dataset, component, page, and exercise belongs to the university world and, once
we can build, to CampusOS. No exceptions.** Never a generic to-do app, blog, shape/animal demo, or
`foo`/`bar`. Foundation examples use university situations (a student's marks, an applicant, a
program); web examples build real CampusOS parts (the admission form, the fee dashboard, the
at-risk chart).

> Enforcement line: **"If this isn't about the university (and, once we're building, part of
> CampusOS), rewrite it until it is."**

The CampusOS surface (built across the web part): a **public website**, the **admission CRM**,
**student/teacher/admin portals**, **ERP modules** (fees, library, HR/faculty, placements), the
**data-visualization dashboards**, and (in the AI part) an **AI layer**.

---

## 10. What a CRM and an ERP are (teach the terms, then build them)

Demystify the buzzwords so the reader is familiar with IT-world language:
- **CRM (Customer Relationship Management)** — software that tracks relationships and a pipeline.
  Here, the **admission CRM**: each prospective student is a lead moving through stages
  (enquiry → applied → under review → shortlisted → admitted / rejected), with notes, follow-ups,
  and conversion metrics.
- **ERP (Enterprise Resource Planning)** — software that runs an organization's internal
  operations as connected modules on shared data. Here, the CampusOS **ERP**: fees, library,
  HR/faculty, and placements, all linked to the same records. Teach that a real CRM/ERP is
  well-designed CRUD + workflows + dashboards on a shared database — then build one.

---

## 11. The full map (in order): Parts A → F

Build in this order. Within each part, write focused lessons (one idea each, 11-beat shape,
cliffhanger-chained, university-only). Each entry lists what it covers, why, and what it builds
toward CampusOS. Split any entry into as many lessons/sub-lessons as one-idea-per-lesson requires.

---

### PART A — Foundation & History (first, always)
*Why: you cannot build well on a machine you think is magic. Understand where computing came from
and how it works, and set up to build.*

- **The Story of Computing** *(history)* — from Babbage and Ada Lovelace to Turing; the
  transistor and the chip; the birth of programming languages; Unix and C; the internet and the
  web; the smartphone; and, much later in the story, modern AI. The people and problems behind
  each leap. *Why:* every concept was invented to solve a real problem; knowing why makes it
  stick, and proves the whole field is understandable, not magic.
- **How a Computer Really Works** — bits and bytes, binary and hex, boolean logic and gates, the
  CPU, memory, storage, the fetch-execute cycle; a peek at assembly. *Why:* the mental model
  everything rests on.
- **How Software & the Internet Work** — a survey: operating systems, files, processes; what
  happens end to end when you load a web page; client and server; the layers of the internet.
  *Why:* the map before the deep dives.
- **The Developer's Workshop** *(tools)* — Linux, the command line, files and permissions, a real
  dev setup, touch typing, and **Git & GitHub** from your first commit. *Why:* your workshop for
  the whole course.
- **Introduce Yourself & Communicate** *(professional, early on purpose)* — the 30-second
  self-introduction, a resume that gets read, a professional profile, and clear writing (commit
  messages, docs, emails). *Why:* the skill you use in every interview and team; start it now.

*Part A cliffhanger:* you understand the machine and you're set up to build — but a computer only
does what it's *told*, exactly. How do you tell it anything?

---

### PART B — Core CS Engineering (the timeless fundamentals)
*Why: these make you an engineer instead of a code-copier, and they're exactly what interviews
test. Learn them before any framework.*

- **Programming Fundamentals** — variables, types, control flow, functions, arrays and strings;
  taught in **C first** (so you meet memory and pointers before frameworks hide them), then a
  productive language. *(A deep, project-based Java course fits here and builds **AdmitDesk**, the
  admissions console.)* *History:* the story of programming languages.
- **Problem Solving & Computational Thinking** — breaking a problem into steps, pseudocode before
  syntax, recognizing patterns, reasoning about correctness. *Why:* interviews test problem
  solving, not memorization.
- **Object-Oriented Programming** — classes, objects, encapsulation, inheritance, polymorphism,
  interfaces. *Why:* almost all large software is built this way. *History:* Simula → Smalltalk →
  Java.
- **Data Structures** — arrays, linked lists, stacks, queues, hash tables, trees, heaps, graphs —
  implemented by hand. *Why:* the heart of the degree and of interviews.
- **Algorithms & Complexity** — Big-O, sorting and searching, recursion, greedy, divide-and-
  conquer, dynamic programming, graph algorithms. *Why:* the most-tested interview subject.
  *History:* Dijkstra, Knuth.
- **Mathematics for Computing** — discrete math (logic, sets, proofs, combinatorics, graphs), and
  the essentials of linear algebra and probability/statistics. *Why:* the language under all of CS,
  and the groundwork for data and AI later.
- **Computer Architecture** — CPU, memory hierarchy, caches, why code is fast or slow. *Why:* to
  reason about performance for real.
- **Operating Systems** — processes, threads, scheduling, memory, concurrency, file systems.
  *Why:* everything you write runs on top of this; heavily interviewed.
- **Computer Networks** — TCP/IP, HTTP, DNS, sockets; how a request travels and returns. *Why:*
  everything is networked; you can't build or debug real systems without it.
- **Databases & SQL** — the relational model, SQL in depth, schema design and normalization,
  transactions, indexing. *(A project-based SQL course fits here and builds **CampusLens**, the
  reporting layer.)* *History:* Edgar Codd and the relational model.
- **Spreadsheets & Data Literacy (Excel)** *(professional)* — formulas, pivots, charts, cleaning
  data, and the bridge from spreadsheets into databases. *Why:* the world's data starts in
  spreadsheets, and "thinking in tables" is exactly how databases work.

*Part B cliffhanger:* you can write real programs and store real data — but everything you've built
runs in a black console window nobody outside a terminal would ever use. How do you build something
a *person* can see and click?

---

### PART C — Web Technologies (build the University Management System)
*Why: this is the most employable application layer, and where the University Management System
becomes a real thing people use. It covers the full web-skills roadmap
(https://andreasbm.github.io/web-skills/).*

- **Design & UX** *(Figma)* — UX vs UI, color, typography, spacing, design systems, accessibility
  principles. *Build:* wireframe CampusOS and a small design system.
- **HTML** — semantic structure, forms, media, tables, accessibility and SEO basics. *Build:* the
  admission form, the student directory, the portal skeletons.
- **CSS** — the box model, selectors and specificity, **Flexbox**, **Grid**, positioning,
  **responsive design**, **custom properties (variables)**, transitions and animations, Sass, BEM,
  a note on utility CSS (Tailwind). *Build:* style CampusOS — a responsive dashboard grid, themeable
  light/dark, a sidebar nav.
- **JavaScript** — fundamentals, the DOM, events, form validation, **fetch/AJAX**, ES6+,
  **async/await and promises**, error handling, `localStorage`. *Build:* live form validation,
  student search, fetching data.
- **Data Visualization** *(headline skill — SVG, Canvas, **Chart.js**, **D3.js**)* — which chart for
  which question, scales, axes, accessible charts. *Build:* the CampusOS analytics dashboards — the
  admissions funnel, fee-collection trend, placements by department, attendance-vs-performance
  scatter, at-risk heatmap — fed by CampusLens data.
- **Tooling** — npm/package managers, **Vite** (bundling), **ESLint + Prettier**, npm scripts, the
  anatomy of a real project. *Build:* turn CampusOS into a real, linted, bundled project.
- **TypeScript** — static typing, interfaces, typing an API response. *Build:* type CampusOS's
  data models and API contract.
- **Frameworks** *(React first; a survey of Vue, Svelte, Angular, Lit)* — the component model,
  props/state, hooks, routing, fetching in components. *Build:* rebuild the CampusOS UI as a
  component-based app — reusable cards, tables, charts, forms; the admission CRM and teacher
  dashboard as routed pages.
- **Web Components** — custom elements, the shadow DOM, templates/slots, Lit. *Build:* reusable,
  framework-agnostic CampusOS widgets.
- **The Backend** *(Node.js + Express, a database, an ORM)* — REST APIs, status codes,
  **authentication** (sessions and JWT), **authorization** (student/teacher/admin roles),
  **WebSockets** for real-time, caching, a GraphQL note. *Build:* the CampusOS API, login and roles,
  live notifications.
- **CRM & ERP: the real product** — CRUD at scale, workflows, dashboards (with the concepts from
  §10). *Build:* the **admission CRM** (the enquiry→admission pipeline), the **teacher portal**
  (gradebook, attendance), the **ERP modules** (fees, library, HR/faculty, placements), and the
  **admin console** — all on shared data, each with its dashboard.
- **Progressive Web Apps** — service workers, the manifest, offline caching, push notifications,
  `IndexedDB`, installability. *Build:* make the student portal an installable, offline-capable PWA.
- **Testing** *(Jest, Cypress/Playwright)* — unit, component, and end-to-end tests. *Build:* test the
  admission flow and the fee API; break the code and watch a test catch it.
- **Accessibility (a11y)** — ARIA, keyboard navigation, screen readers, contrast, accessible forms
  and charts, Lighthouse. *Build:* make all of CampusOS usable by everyone.
- **Performance** — Core Web Vitals, lazy loading, code splitting, caching, CDNs, image
  optimization. *Build:* make CampusOS fast — lazy-load charts, split the bundle.
- **Security** — HTTPS, CORS, XSS, CSRF, CSP, input validation, secure auth, secrets/environment
  variables, the OWASP basics. *Build:* secure CampusOS and protect student data.
- **SEO & the public site** — meta tags, structured data, `sitemap.xml`, performance-as-SEO.
  *Build:* the public, findable university website — the front door.
- **DevOps: deploy & operate** *(Docker, a cloud host, CI/CD with GitHub Actions)* — hosting, static
  vs server vs serverless, environment config, logging/monitoring basics. *Build:* deploy CampusOS
  to a real URL with a pipeline that tests and ships on every push.

*Part C cliffhanger:* CampusOS is live and real — a website, a CRM, portals, dashboards, all
deployed. But web technology is only one corner of IT. What else is out there, and where could you
go?

---

### PART D — The Wider IT World (familiarization, not deep build)
*Why: the reader should know the landscape, speak the language, and choose a direction. This is a
guided tour, not a full build — awareness and vocabulary.*

- **Cloud Computing** — what "the cloud" is, IaaS/PaaS/SaaS, why companies rent instead of own,
  the major providers at a survey level.
- **DevOps & SRE** — containers, CI/CD, infrastructure as code, monitoring, and the culture of
  "you build it, you run it" — what these roles do.
- **Cybersecurity** — the field beyond the security you built: attackers vs defenders, common
  threats, and why every engineer needs security literacy.
- **Mobile Development** — native (iOS/Android) vs cross-platform vs PWA, and how CampusOS could
  reach phones.
- **Data & Big Data** — data engineering, warehouses, pipelines, analytics, and how CampusLens
  reporting scales up in the real world.
- **A landscape survey** — a fair, honest map of other paths (systems/low-level, game development,
  embedded, and a brief, non-hyped note on emerging fields) so the reader knows they exist.

*Part D cliffhanger:* there's one technology reshaping every field on that map right now — and now
that your foundation is solid, you're finally ready to understand it instead of fearing it.

---

### PART E — Artificial Intelligence (now — after the foundation)
*Why: AI is introduced deliberately late, once the reader can code, handle data, and reason — so
they learn it as engineers who can judge it, not as beginners who trust it blindly.*

- **AI & Machine Learning, in plain words** — the terms explained honestly (model, training, data,
  neural network, LLM), what AI can and can't do, and its history (the early neural nets, the "AI
  winters," deep learning, and large language models — why it finally worked).
- **Machine Learning Fundamentals** — supervised learning, features, evaluation, overfitting,
  resting on the math from Part B.
- **Building AI into CampusOS** — real features: an **at-risk-student predictor**, an **AI
  assistant** for staff, and **natural-language search** over the university data (built on the SQL
  the reader already knows). Visualize the AI's outputs honestly on the dashboard.
- **Responsible AI** — privacy, fairness, bias, and guardrails, because CampusOS holds real
  student data. *Why:* building responsibly is a job skill, not a footnote.

*Part E cliffhanger:* you now have the foundation, the core, the technologies, and AI — every piece.
There's one thing left to do with them.

---

### PART F — Career & Putting It All Together
*Why: turn everything into a job — and assemble CampusOS into one finished system.*

- **Teamwork & Collaboration** *(professional)* — real roles, standups, code review as a culture,
  handling disagreement, hitting deadlines. *Why:* software is a team sport; companies test for it.
- **Presentation & Storytelling (PowerPoint)** *(professional)* — structuring a talk, clean slides,
  demoing live software, presenting to non-technical people. *Build:* present a working CampusOS in
  five minutes.
- **Interview Prep & Career Readiness** *(professional)* — coding interviews, system-design
  interviews, behavioral rounds, a sharp resume and portfolio, mock interviews, negotiation, and the
  two-minute CampusOS walkthrough.
- **The Complete University Management System — Putting It All Together** — assemble everything into
  one complete, documented, deployed University Management System: the public website, the admission
  CRM, the student/teacher/admin portals, the ERP modules, the data-visualization dashboards, and the
  AI layer — tested, accessible, fast, secure, and live. Present it end to end as a guided tour, with
  a full recall across the whole course and an honest map of what to learn next.

*Part F close:* none — this is the end. Close the loop and point the reader at their next mountain
(the real-world closing, §21).

---

### The through-line: CampusOS build order
| Stage | What CampusOS becomes |
|-------|-----------------------|
| Part B | Console foundations — AdmitDesk (Java) and the CampusLens data/reporting (SQL). |
| Part C (design→CSS) | A designed, styled, responsive front end. |
| Part C (JS→data viz) | Interactive, with analytics dashboards. |
| Part C (tooling→React→components) | A real, typed, component-based app. |
| Part C (backend→CRM/ERP) | A backend, the admission CRM, the teacher portal, ERP modules. |
| Part C (PWA→testing→a11y→perf→security→SEO→deploy) | Installable, tested, accessible, fast, secure, findable, and live. |
| Part E | An AI layer (assistant, smart search, at-risk prediction). |
| Part F | The complete University Management System, assembled and presented. |

Keep a running mental model of what CampusOS contains; never contradict an earlier lesson. Every
build lesson hands over the **plan and acceptance criteria** — not the finished code — except the
final "Putting It All Together" lesson, which presents the complete system.

---

## 12. The interactive component library (build this first)

Never a wall of plain text. Build a shared kit (`assets/styles.css`, `assets/components.js`) — same
brand as the sibling courses, vanilla HTML/CSS/JS, no build step, in-page state only. Components:
`code-window` (multi-language, filename tab, line numbers, syntax roles, a red-highlightable "line
that matters"); a **result block** (printed output for foundation/core; a **live rendered preview**
or **live chart** for web parts — the reader must *see* it); a **broken → fixed toggle** (the error /
collapsed layout / failing fetch, then the fix); a **diagram (inline SVG)** (§13); **reveal-answer**;
**interview accordion**; **check-yourself quiz** (2 questions, one interviewer-style); **glossary
chip**; **callouts** (`note`, `trap`, `a11y`, `security`, `perf`, `real-world`, `go-deeper`);
**cliffhanger bar**; **video/photo slots**; **progress/part rail**. Every lesson pairs code with its
result; a chart lesson pairs code with a live chart. A component every screen or two.

---

## 13. Draw it — SVG/diagrams when a picture teaches faster

Standing habit; monochrome ink, one red element = the idea, every part labelled, one idea per
diagram. Draw at least: how a computer runs a program; binary/gates; the request lifecycle
(browser→DNS→server→response); stack vs heap; the call stack; a data structure's shape; the box
model; Flexbox/Grid axes; the DOM tree and event bubbling; the event loop; the React component tree;
client→server→database; auth (login→token→protected route); the CRM pipeline; a chart's anatomy; and
(in Part E) a simple neural network. Keep teaching diagrams distinct from the mascot art (§20).

---

## 14. Media & references

Reference the web-skills roadmap (https://andreasbm.github.io/web-skills/) in Part C's overview as
the map that part covers, and cite canonical docs by name in `go-deeper` callouts (MDN for the web,
the official docs for each tool) — max two per lesson. Video/photo hooks are placeholder slots the
owner fills — never invent URLs:
```
[VIDEO HOOK: <what it should show> | suggested: <search terms> | fill: data-src=""]
[PHOTO: <what it should show> | suggested: <search terms> | fill: src="" credit=""]
```
Good hooks: computing history / Turing / Berners-Lee at Part A; a Flexbox/Grid explainer and a D3
showcase in Part C; a plain-English "how do LLMs work" video in Part E; screenshots of the running
CampusOS at the milestones.

---

## 15. The job-readiness layer (runs under the course)

This is a job-seekers guide, so keep the professional thread visible throughout: clear READMEs and
docs, a portfolio that leads with the deployed CampusOS, presenting your work, and — in Part F —
resume, mock interviews, and "how to talk about your project." The deployed CampusOS is the
portfolio centerpiece that gets the reader hired.

---

## 16. Interview questions (baked in, not bolted on)

Every lesson's "Check yourself" includes one interviewer-style question; every part ends with an
interview Q&A block (with code that proves the answer); and all of it is aggregated into one
**Interview Vault grouped by part** at the end (§19). Make sure the classics land across foundation
(how a computer works, memory), core CS (DSA, OS, networks, SQL, OOP), web (box model, Flexbox vs
Grid, `==` vs `===`, async/await, the event loop, REST, auth, XSS vs CSRF, SSR vs CSR, which chart
for which question), and AI (what an LLM is, overfitting, responsible AI).

---

## 17. Build order (how you produce all this)

1. **The shared kit first:** `assets/styles.css` + `assets/components.js` (with the live-preview and
   chart-demo blocks), the Sig model-sheet prompt (§20), a course home page / table of contents that
   shows the A→F sequence.
2. **Part by part, A → F.** For each part: the **overview** (§18.A) → the **lessons** (in order, one
   idea each, chain-linked, university-only, 11-beat shape) → the **glossary** (§18.C) → the
   **interview Q&A** (§18.D) → the **exercise set** (§18.E). Keep CampusOS's running state consistent.
3. **Per lesson:** the result/preview/chart, any diagrams, the illustration prompt, a thumbnail spec,
   media/`go-deeper` slots.
4. **The complete system** presented in Part F.
5. **The Interview Vault (§19)** and **the real-world closing (§21).**

Suggested layout:
```
/index.html
/assets/styles.css
/assets/components.js
/part-a-foundation/…  /part-b-core-cs/…  /part-c-web/…  /part-d-it-world/…  /part-e-ai/…  /part-f-career/…
/campusos/   (the complete system: /public-site, /crm, /portals, /erp, /dashboards, /api, /ai)
/interview-vault/index.html
/real-world/index.html
```

**Per-lesson checklist:** resolves the previous hook + sets the next; exactly one idea; university /
CampusOS example (no generic demo); foundation → core → web → IT world → AI order respected; code
paired with its result (or live preview/chart); broken-first where useful; the relevant trap/a11y/
security/perf note; a diagram when a picture teaches faster; exactly 2 "Check yourself" (one
interviewer-style); components used so it's never a plain-text wall; illustration + media slots;
CampusOS state consistent; build lessons give plan + acceptance criteria, not finished code (except
Part F). **And: the word "capstone" never appears.**

---

## 18. The part wrapper (every part is bookended)

Each part is its own folder with an index page plus: **A. Overview** ("what you'll learn/build &
why", opening with a media hook, and how it follows the previous part's last hook) → **B. Lessons**
(in order) → **C. Glossary** (this part's new terms as chips) → **D. Interview Q&A** (with code that
proves it) → **E. Exercises** ("your turn: the fun set" — real university/CampusOS problems; reader
attempts first; solutions behind a reveal, shown beginner vs experienced-dev with a plain-words "why
the second is better"). Every part still obeys the chain.

---

## 19. The Interview Vault (all Q&A, grouped by part)

After the course, produce one **Interview Vault** page collecting every interview question, grouped
by part (Foundation, Core CS, Web, IT World, AI, Career), in the accordion — question, short
confident answer, and a code-window (with a live preview where it helps) that proves it. Intro in
the notes-sharing voice. End with the "how to talk about CampusOS in two minutes" guide and a mock-
interview plan.

---

## 20. The mascot (Sig), illustrations & thumbnails

Same **Sig** mascot and brand as the sibling courses: an original graphic screen-headed mascot,
**not a human**; one red live-dot in the top-left of his screen; flat solid fills; a monochrome world
with **exactly one signal-red element per view**; amber only for course branding. Output one
`[ILLUSTRATION PROMPT]` per lesson (Sig *doing* the concept — holding a labelled box for variables,
stitching a layout together, holding a chart, wiring a form to a server, peering at a simple neural
net), using the verbatim character/style/color scaffold from the sibling specs, and reference a model
sheet built first. Thumbnails: 16:9, read in half a second, one red element = the hook, Sig on-model,
title = the tension not the topic; kickers like `HISTORY`, `CORE CS`, `WEB`, `DATA VIZ`, `IT WORLD`,
`AI`, `INTERVIEW`.

---

## 21. The real-world closing — "The IT world today, and where you go next"

After Part F, a closing orientation page (framed as "beyond this course"): where software/IT jobs are
today; who hires (effectively every company with software); what you can build; the honest tradeoffs
of each path; the ecosystems to learn next (meta-frameworks, cloud/DevOps depth, a data or AI
specialization, mobile); where to host; the career paths (frontend, backend, full-stack, data,
DevOps, security, AI/ML) and how one deep foundation opens all of them; and portfolio projects to
build next — starting by extending CampusOS. *Verify names/figures at build time; don't ship stale
specifics.*

---

## 22. Do / Don't (guardrails)

**Do**
- Teach in order: foundation & history → core CS → web technologies → the wider IT world → AI →
  career.
- Keep the chain unbroken; one idea per lesson; split rather than mingle.
- Keep everything in the university world / CampusOS (no generic demos).
- Pair every lesson's code with its result (printed output, live preview, or live chart).
- Introduce AI as a helper only after the reader can code, and as a subject only in Part E.
- Teach CRM/ERP and the wider IT world as familiarity, then build the web parts deeply.
- Draw with SVG where a picture teaches faster; add one strong reference where it deepens the idea.
- Let readers attempt before revealing solutions; show beginner vs experienced-dev versions.

**Don't**
- **Don't ever use the word "capstone"** — say "the complete system" / "putting it all together".
- Don't front-load AI, frameworks, or tools before their foundation.
- Don't merge/skip lessons or open one without answering the previous cliffhanger.
- Don't give practice solutions first; don't write CampusOS's code for the reader (except Part F).
- Don't ship a code sample without its visible result; don't use a word you haven't defined; don't
  fake praise.
- Don't invent media URLs; don't let more than one red element appear in any single view.

Build it all, in order. Start with the shared kit, then Part A (foundation & history), and go — one
idea, one lesson, one piece of the University Management System at a time.