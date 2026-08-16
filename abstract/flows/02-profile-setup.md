# Steps 1–2 — Profile Setup

## Step 1 — About you

| Field | Type | Required | Why we ask |
|---|---|---|---|
| Full name | text | ✅ | Certificates, community, addressing the learner by name |
| Photo | image upload | — | Makes the account feel like *theirs*; used on profile and success stories |
| Date of birth | date | ✅ | Age band tunes pacing and tone; never shown publicly |
| Place / City | text + autocomplete | ✅ | Local job market context, cohorts, meetups later |

**Sidebar copy:**
> We're not collecting data for its own sake. Your name goes on your
> certificate, your city tells us which job market you're in, and your photo is
> yours alone — you can skip it.

## Step 2 — Your education

| Field | Type | Required | Why we ask |
|---|---|---|---|
| College / University | text + autocomplete | — | Peer context; never used to judge |
| Degree | select | ✅ | B.E./B.Tech, B.Sc., B.C.A., B.Com., B.A., M.C.A., M.Tech, Diploma, Other |
| Branch / Stream | select (dependent) | ✅ | CSE, IT, ECE, EEE, Mechanical, Civil, Physics, Maths, Commerce, Arts, Medical, Other |
| Year of graduation | select | ✅ | Fresher vs experienced framing |
| Currently | select | ✅ | Studying / Graduated & searching / Working (non-IT) / Working (IT) |

**Sidebar copy:**
> Your branch does not decide your future. We ask because a Mechanical graduate
> and a CS graduate need different starting points — not different ceilings.
> Plenty of people in this industry came from Civil, from Commerce, from
> medicine.

## What these two steps produce

The **persona inference**, which is a hint, not a verdict — the user's own
answers in steps 3 and 4 always override it.

| Signals | Inferred persona |
|---|---|
| CSE/IT + graduated + searching | [Unplaced CS Graduate](../personas/01-unplaced-cs-graduate.md) |
| Non-CS engineering / B.Sc. + searching | [Non-CS Graduate](../personas/02-non-cs-graduate.md) |
| Non-technical degree + "don't know" at step 3 | [Clueless Starter](../personas/03-clueless-starter.md) |
| Working (non-IT) + grad year ≥ 3 years ago | [Career Changer](../personas/04-career-changer.md) |
| Working (IT), 1–3 years | [Early-Career Switcher](../personas/06-early-career-switcher.md) |

## Design notes

- **Two screens, not one.** Long forms kill completion; two short screens with a
  progress indicator feel achievable.
- **No CGPA field.** Ever. It's a filter used against people, and it is not
  relevant to what we teach.
- **"Other" always available**, with a free-text field, on degree and branch.
- Autocomplete on college and city, with free-text fallback — small-town
  colleges must not be un-enterable.
