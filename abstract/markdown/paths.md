# Learning Paths

Every path is an ordered list of courses and standalone items, plus targeting
rules that let onboarding assign it automatically. Schema:
[schema.md](schema.md).

Highest-scoring matching path wins; `default-path` (priority 0) always exists as
a fallback.

---

## 1. `unplaced-cs-graduate` — "CS Graduate → Your First Job"

```yaml
targeting: { level: [2,3], branch: [cse, it], goal: [first-job], priority: 20 }
duration_weeks: 26
```

```
 1  CS-000            How the IT Industry Works        (level-2 variant)
 2  CS-OS-101         Operating Systems
 3  CS-NET-101        Computer Networks
 4  CS-DB-101         DBMS
 5  DB-201            Databases in Practice
 6  LANG-JAVA-101     Java  ⟲ parallel from week 3
 7  CS-OOP-101        OOP
 8  CS-DSA-101        Data Structures & Algorithms
 9  CS-SE-101         Software Engineering
10  CS-WEB-101        Web Technologies
11  WEB-201/202/203   Frontend · Backend · Shipping It
12  CS-SYS-101        System Design
13  PROJ-101 → 104    Projects 1–4
14  SOFT-101          English & Communication  ⟲ parallel throughout
15  JOB-101           Your Resume
16  ▪ rejection-is-data
17  JOB-102           Finding the Job
18  JOB-103           Using AI to Get a Job
19  INT-101 · INT-102 Interviews
20  ▪ the-first-offer-is-a-door
21  JOB-201           Offer, Joining & First Switch
```

---

## 2. `non-cs-to-web-developer` — "Any Branch → Web Developer"

```yaml
targeting: { level: [1,2], branch: [non-cs], goal: [first-job], target_role: [web-developer, full-stack, any], priority: 20 }
duration_weeks: 32
```

Same spine as path 1, with:

- `CS-000` at **full length** (level-1 variant), not condensed
- `▪ why-your-branch-doesnt-matter` inserted at position 2
- Foundations at full depth with no assumed prior CS
- `CS-TOC-101` dropped
- `▪ telling-your-career-change-story` before `JOB-101`

---

## 3. `clueless-starter` — "Start From Zero"

```yaml
targeting: { level: [1], goal: [first-job, dont-know], priority: 25 }
duration_weeks: 38
```

```
 1  CS-000            How the IT Industry Works  (extended, all 24 lessons)
 2  ▪ role-reality-check                          (quiz → suggests a track)
 3  SOFT-101          English & Communication  ⟲ starts week 1, parallel
 4  CS-OS-101 · CS-NET-101 · CS-DB-101 · DB-201
 5  LANG-JAVA-101     Java, from zero  ⟲ parallel
 6  CS-OOP-101 · CS-DSA-101
 7  CS-SE-101 · CS-WEB-101
 8  WEB-201/202/203
 9  PROJ-101 → 104
10  CS-SYS-101 · CS-AI-101
11  JOB-101 · JOB-102 · JOB-103
12  INT-101 · INT-102
13  ▪ the-first-offer-is-a-door
14  JOB-201
```

Gentlest pacing. Highest sidebar-explanation density. Shortest lessons.

---

## 4. `career-changer` — "Another Career → IT"

```yaml
targeting: { level: [1,2], goal: [switch-into-it], priority: 30 }
duration_weeks: 28
```

- `CS-000` full, but at compressed pace (they read fast)
- Foundations trimmed to the load-bearing subset: OS, DBMS, Networks, OOP, Web,
  SE — DSA reduced, ToC dropped
- Language chosen by target role (Python weighted for data/analyst tracks)
- Projects 1–3, domain-flavoured to their old industry
- `▪ telling-your-career-change-story` before `JOB-101`
- Full career module — this persona's leverage is in positioning

---

## 5. `role-targeted-data-engineer` — "→ Data Engineer"

```yaml
targeting: { target_role: [data-engineer, data-analyst], priority: 35 }
duration_weeks: 28
```

```
CS-000 → ▪ role-reality-check → CS-DB-101 → DB-201 → LANG-PY-101 ⟲ →
CS-OS-101 → CS-NET-101 → CS-OOP-101 → DATA-201 → DATA-202 → DATA-203 →
CS-SYS-101 → PROJ (data variants 1–4) → SOFT-101 ⟲ → career module
```

DSA weighted down, DBMS/OS/Networks weighted up. Same pattern for
`role-targeted-frontend`, `-devops`, `-qa`, `-salesforce`, `-mobile`.

---

## 6. `early-career-switcher` — "Your Second Job"

```yaml
targeting: { level: [3,4], goal: [better-job], priority: 30 }
duration_weeks: 20
```

```
Foundations (revision mode) → depth in current language → CS-SYS-101 →
PROJ-102 → 104 → JOB-101 (experienced variant) → JOB-102 (referral-weighted) →
JOB-103 → INT-102 → JOB-201 (expanded: negotiation, notice, counter-offers)
```

Part-time-realistic pacing (6–8 hrs/week default). `CS-000` condensed to parts
4–5 only.

---

## 7. `default-path` — "The Complete Path"

```yaml
targeting: { priority: 0 }
duration_weeks: 30
```

The full curriculum, in the order documented in
[../curriculum/README.md](../curriculum/README.md), Java + Web track. Assigned
to anyone no other path matches. **Must always exist and always be published.**

---

## Assignment algorithm

```
candidates = published_paths.filter(p => all of p.targeting matches user answers,
                                          treating "any"/absent as a wildcard)
chosen     = candidates.maxBy(p => p.targeting.priority)
           ?? default-path
```

Ties break toward the more specific path (more non-wildcard targeting fields).
The chosen path is a **starting point** — the learner can edit it on
[step 8](../flows/08-path-review.md) and any time afterwards from Settings.
