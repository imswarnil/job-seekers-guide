# Sample Quizzes

Schema in [schema.md](schema.md). Answers live server-side only — the browser
never receives the `answer` or `explain` fields until after submission. Already
enforced by the quiz engine (`TODO.md` §3).

---

## `CS-000` — The vocabulary quiz

The only quiz on the platform that tests words rather than concepts, because
vocabulary is the actual barrier for the [Clueless Starter](../personas/03-clueless-starter.md).

```yaml
type: quiz
slug: it-vocabulary-quiz
title: "How the IT Industry Works — vocabulary check"
course: CS-000
pass_percent: 70
completes_lesson: true
questions:
  - q: "A colleague says 'I'll raise a PR after standup.' What are they doing?"
    options:
      - "Submitting their code for review after the morning team sync"
      - "Filing a public relations request"
      - "Booking a meeting room"
      - "Reporting a production issue"
    answer: 0
    explain: "PR = pull request. Standup = the short daily team sync. You'll hear both every day."

  - q: "What is an MVP?"
    options:
      - "The smallest version of a product that's still useful to a real user"
      - "The most valuable person on the team"
      - "A senior developer title"
      - "A type of database"
    answer: 0
    explain: "Minimum Viable Product. It exists to test an idea before building everything."

  - q: "Code is working on your laptop. Where does it go before production?"
    options:
      - "Staging"
      - "Straight to production"
      - "The backlog"
      - "Version control only"
    answer: 0
    explain: "Dev → staging → production. Staging is production's rehearsal, with fake data."

  - q: "Which team would you talk to about a slow deployment pipeline?"
    options:
      - "DevOps"
      - "QA"
      - "Product"
      - "Sales"
    answer: 0
    explain: "DevOps owns build, deploy, and infrastructure."

  - q: "What does a service company primarily sell?"
    options:
      - "Engineering work delivered to other companies' projects"
      - "Its own product, to many customers"
      - "Hardware"
      - "Training courses"
    answer: 0
    explain: "TCS, Infosys, Accenture build for clients. Google and Zoho build their own products. Different work, different hiring, different pay curves."
```

---

## `CS-OS-101` — Checkpoint 1

```yaml
type: quiz
slug: os-quiz-1
title: "Operating Systems — checkpoint 1"
course: CS-OS-101
pass_percent: 70
completes_lesson: true
questions:
  - q: "A context switch saves..."
    options:
      - "The process's registers and program counter"
      - "The entire contents of RAM"
      - "The program's source code"
      - "Nothing — it starts fresh"
    answer: 0
    explain: "Only the execution state needs saving. That's what makes it fast — and why it still isn't free."

  - q: "Two threads of the same process share..."
    options:
      - "Memory (the heap), but each has its own stack"
      - "Nothing"
      - "Everything, including stacks"
      - "Only the CPU"
    answer: 0
    explain: "Shared heap is exactly why threads are fast and exactly why they corrupt each other's data."

  - q: "Which is NOT one of the four conditions required for a deadlock?"
    options:
      - "High CPU usage"
      - "Mutual exclusion"
      - "Hold and wait"
      - "Circular wait"
    answer: 0
    explain: "Mutual exclusion, hold-and-wait, no-preemption, circular-wait. Break any one and the deadlock cannot occur."

  - q: "Virtual memory lets a program..."
    options:
      - "Use more memory than physically exists, with the OS paging to disk"
      - "Run faster than physical memory allows"
      - "Skip the operating system"
      - "Share memory with any other process freely"
    answer: 0
    explain: "It's also why your machine crawls when it starts swapping heavily."
```

---

## `JOB-101` — Resume self-audit

Not scored as pass/fail — a **checklist quiz** whose output is a personalised
list of fixes rather than a mark.

```yaml
type: quiz
slug: resume-self-audit
title: "Resume self-audit — 25 checks"
course: JOB-101
pass_percent: 0
mode: checklist
completes_lesson: true
questions:
  - q: "Is your resume one page?"
    options: ["Yes", "No"]
    answer: 0
    explain: "As a fresher, two pages signals padding. One page, always."
  - q: "Is it a single-column layout, saved as PDF?"
    options: ["Yes", "No"]
    answer: 0
    explain: "Two-column templates confuse ATS parsers. Beautiful, and discarded before a human sees it."
  - q: "Does every project bullet name a technology AND an outcome?"
    options: ["Yes", "No"]
    answer: 0
    explain: "'Built a blog using React and Node, deployed to a live URL with 40 users' beats 'Made a blog'."
  - q: "Can you defend every skill you listed, in an interview, today?"
    options: ["Yes", "No"]
    answer: 0
    explain: "Every skill on that page is a question you've invited. Remove anything you can't answer for."
  - q: "Is your filename in the form Name_Role.pdf?"
    options: ["Yes", "No"]
    answer: 0
    explain: "'resume_final_v3(1).pdf' is the first impression you didn't mean to make."
```

---

## Quiz design rules

1. **Every question explains itself** after submission — a wrong answer must
   teach, not just deduct.
2. **No trick questions.** We are not filtering candidates; we are teaching them.
3. **Distractors are real misconceptions**, drawn from what learners actually
   get wrong.
4. **Questions mirror interview phrasing** where possible, so the format itself
   is practice.
5. **Retakes are unlimited**, and the pass auto-completes the lesson.
6. **Checklist mode** exists for career lessons where "score" is the wrong idea
   and "here's your fix list" is the right one.
