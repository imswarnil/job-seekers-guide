# Roadmap

What is coming, roughly in order, and what is deliberately not.

This is a plan, not a promise. It is maintained by one person around a
full-time job, so the ordering is by *what unblocks the most learners per hour
of work*, not by what is most interesting to build.

---

## Now — in progress

### The flagship course content
The platform runs; the curriculum in [`../abstract/curriculum/`](../abstract/curriculum/)
is mostly outlines. The single highest-value work left is writing the real
thing, starting at the foundational beginning — *What is Software / How the IT
Industry Works* — and building forward in order, each course assuming the one
before it. One running university-admissions example throughout, so a table
introduced in DBMS is the same table used in the Java course.

**Why first:** every other feature is scaffolding around content that does not
exist yet.

### Learner comments
Discussion on lessons and stories. Moderated, one level of nesting, rate
limited, no HTML from learners.

**Why:** the single loneliest part of self-teaching is being stuck at 11pm with
nobody to ask. The abstract calls accountability a feature; this is most of it.

### Community resource library
`/resources/` — books, courses, blogs and videos submitted by learners,
published after approval, with filters and search. Free/paid clearly marked,
because a job seeker with no income needs that before anything else.

---

## Next

### "My Story" page
The founder's full story as a designed long-form page — the 2017 fog, the
institute, 33 rejections, ₹13,000 a month, Accenture three months later. It is
the argument the whole platform rests on and it currently exists only as a
paragraph on the homepage.

### The onboarding wizard
The eight screens in [`../abstract/flows/`](../abstract/flows/) — who you are,
where you are now, where you want to reach, subjects, language, track — ending
in an assembled path. The design is written; none of it is built.

**Why not sooner:** a wizard that assembles a path from courses that are mostly
outlines would be an elaborate way to deliver an empty shelf.

### Rich text and images in learner submissions
Stories are plain fields today. The console's own editor already does this
well; the front-end version needs the same care plus much stricter sanitising,
since it accepts input from the public.

### Aptitude and written-test preparation
Service companies test it, so the curriculum has a gap. Aptitude, logical
reasoning, and the written-English round — as a short, honest module rather
than a thousand practice questions.

### Certificates
On course and path completion. Verifiable, with a public check URL — a
certificate nobody can verify is decoration.

---

## Later

- **Cohorts** — a start date and a group moving together. The strongest thing a
  training institute has that this does not.
- **Application tracker as a product feature**, not just Project 3.
- **Mentor answers** — let people who got hired answer questions from people
  who have not.
- **Offline-first PWA** for lesson text, for learners on unreliable data.
- **In-browser code practice** — the plugin has a stub for it.
- **Multi-language** — Hindi first. The audience is not uniformly comfortable
  reading technical English, and pretending otherwise excludes people the
  project is explicitly for.

---

## Deliberately not doing

Saying no in public is how a small project stays finishable.

| Not building | Why |
|---|---|
| A job board | We teach the hunt. Brokering roles is a different business with different incentives. |
| Placement guarantees | Cannot be honestly promised. The whole pitch is that we do not lie about this. |
| Per-course pricing | Removed in 0.8.0, not coming back. One decision, made once. |
| An interview-question dump | Patterns and understanding, not 500 MCQs. |
| A social feed | Engagement mechanics compete with the thing people came to do. |
| Video hosting | YouTube and Vimeo already do it. Curation is the product. |
| A mobile app | The PWA covers it at a fraction of the cost. |
| AI-generated course content | The whole premise is that a person who has done this is sequencing it. Generated filler would make the platform exactly what it was built against. |

---

## How to influence this

Open an issue on
[GitHub](https://github.com/imswarnil/job-seekers-guide). The most useful thing
is not a feature request — it is telling us where you got stuck, and what you
went looking for that was not there.
