# How a lesson is written

## The shape

Every lesson on this platform follows the same three beats, and the first and
last are not optional:

1. **Why this matters in a job.** Two or three sentences, concrete, naming the
   situation where not knowing this costs you something. Not "databases are
   important" — "the query that worked on your laptop takes forty seconds in
   production, and this is why".
2. **The thing itself.** Explained honestly, including the parts that are
   genuinely confusing. Say when something is confusing.
3. **The interview question this answers.** The actual question, phrased the way
   an interviewer phrases it, followed by what a good answer contains.

A `::real-life` block somewhere in the middle is the single highest-value thing
you can add.

## Front matter

```yaml
---
title: How a program actually runs
description: One sentence. It appears on cards, in search results and on the
  social image, so write it for a stranger.
minutes: 14
kind: lesson        # lesson | practice | project | quiz | reading
---
```

`minutes` drives the reading estimates on the subject page and the whole path.
Guess honestly — 200 words a minute, plus a minute for every diagram.

## Voice

The reader has been rejected more than once and is short on time and confidence.
Write like somebody who has done this job and is on their side.

- **Say the number.** "Four weeks", "about 40%", "nine seconds". Vague
  encouragement is what the institutes sell.
- **Say what to skip.** A lesson that says "you can skip this on a first pass,
  here is what you lose" is as valuable as one that teaches. Skipping
  deliberately is studying; skipping because you got lost is drifting.
- **No hype, no exclamation marks, no "amazing".** No "simply" or "just" — if it
  were simple they would not be reading.
- **Never claim outcomes.** No placement percentages, no guaranteed packages.
- **Second person.** "You", not "the student" or "one".
- **British spelling**, to match the rest of the site.

## Things to avoid

- Starting with a dictionary definition.
- A wall of prose where a `::compare` or a `::memory` would do it in one glance.
- Teaching a tool without teaching the thing underneath it.
- Pretending a trade-off does not exist. Use `::pros-cons`.
- More than one accent colour in a single diagram.
