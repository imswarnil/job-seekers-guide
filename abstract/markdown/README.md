# Platform Data (markdown)

Machine-readable seed data for the platform. Everything here is intended to be
consumable by `wp jsl seed` so the full experience — orientation through first
switch — is demonstrable before real content is authored.

## Structure

```
markdown/
├── README.md              this file
├── schema.md              the frontmatter contract for every file type
├── paths.md               all learning paths + their targeting rules
├── courses.md             the full course catalogue (codes, categories, meta)
├── lessons-sample.md      worked example lessons, one per lesson type
├── quizzes-sample.md      worked example quizzes
├── roles.md               role reference data used by Module 0 and onboarding
└── onboarding-fields.md   the exact field/option lists for the wizard
```

## Conventions

- **Course codes** are stable identifiers: `AREA-TOPIC-LEVEL`, e.g. `CS-OS-101`,
  `LANG-JAVA-101`, `JOB-102`. Never reused, never renumbered.
- **Durations** are learner-hours, not video minutes.
- **Everything is dummy content** unless marked `status: real`. Dummy lessons
  carry realistic titles and outlines so the UI, the builder, and the analytics
  are exercised honestly.
- **Order is data.** Lesson order lives in the path/course definition, never in
  the lesson itself.

## Loading

```bash
docker compose exec wordpress wp jsl seed --fresh
```

The existing seed command covers a subset (2 paths, 4 courses, 24 lessons — see
`TODO.md` §3). Extending it to cover this folder is tracked in
[../flows/10-admin-path-builder.md](../flows/10-admin-path-builder.md).

## Relationship to the abstract docs

| This folder | Derived from |
|---|---|
| `paths.md` | [../personas/](../personas/) + [../flows/08-path-review.md](../flows/08-path-review.md) |
| `courses.md` | [../curriculum/](../curriculum/) |
| `roles.md` | [../curriculum/00-how-it-works.md](../curriculum/00-how-it-works.md) part 4 |
| `onboarding-fields.md` | [../flows/](../flows/) steps 1–7 |

If a document in `../curriculum/` changes, this folder must change with it.
