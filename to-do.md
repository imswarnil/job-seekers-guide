# To-do

Everything outstanding, in the order it makes sense to do it. Each item says what
exists now and what "done" looks like, so any of it can be picked up cold.

Ordered by blocking-ness, not by size. **§1 is the only thing that is currently
broken rather than merely missing.**

---

## 1. Make the Java runner actually work

Right now `/run` in Java mode says *"The Java runner is not configured for this
deployment, so nothing was compiled or run."* That message is accurate — the
Worker exists in `workers/runner/` but has never been deployed.

Nothing in the code needs changing. This is deployment.

- [ ] **Fix the allowed origins before deploying.** `workers/runner/wrangler.jsonc:19`
      still says `https://jobseekersguide.in,http://localhost:3000`. It must be
      `https://jobseekers.imswarnil.com,http://localhost:3000`. Same for
      `DEFAULT_ORIGINS` in `workers/runner/src/index.ts`. **If this is wrong the
      Worker deploys fine and every request from the live site is blocked by
      CORS**, which looks like a broken runner rather than a config typo.
- [ ] Create the KV namespace and paste the id into `wrangler.jsonc`:
      ```bash
      cd workers/runner
      npx wrangler kv namespace create RUNNER_CACHE
      ```
- [ ] Deploy: `npx wrangler deploy`. Note the URL it prints.
- [ ] Set the repo variable so the site picks it up — the workflow already reads it:
      ```bash
      gh variable set RUNNER_URL --body "https://<worker>.workers.dev/run"
      ```
- [ ] Re-run the deploy workflow (`gh workflow run deploy.yml`).
- [ ] **Verify two things on `/run`, not one:** a program that compiles and prints,
      and a program with a deliberate syntax error. The second is the real test —
      the JVM stage must draw as *never started* and the error must carry a line
      number.

Optional, later: point `PISTON_URL`/`PISTON_TOKEN` at a self-hosted Piston. Public
Piston is rate-limited globally and has no SLA. Not urgent until traffic exists.

---

## 2. Navigation

`app/utils/links.ts`, `app/components/AppHeader.vue`.

- [ ] **Remove "Run code"** from the nav. Keep the `/run` page; link it from
      lessons that use `::runner` and from the components showcase instead.
- [ ] **Remove "My story"** from the nav — it moves under the series (§4).
- [ ] **Add a search trigger.** `UContentSearchButton` currently only shows below
      `lg` (`AppHeader.vue:52`). Show it at every width as a proper trigger with
      the `⌘K` hint visible.
- [ ] **Add "Star on GitHub"** — icon + live star count, linking to the repo.
      Fetch the count at build time rather than per visit; a nav element that
      waits on api.github.com is a nav element that is sometimes empty.
- [ ] **Leaderboard ad slot in the navbar.** Add `nav-leaderboard` to
      `app/utils/ads.ts` and render it in the header. Needs care: the header is
      sticky, so the slot has to reserve its height or every page jumps on load.

Resulting nav: **Start here · Series · About · Questions · Changelog** + search,
GitHub star, theme, Continue.

---

## 3. About and mission

`content/2.about.md`, `app/pages/about.vue`.

- [ ] Restructure around **what this is and why it exists**: the mission, the ten
      principles, what is deliberately excluded, who it is for.
- [ ] Add a **short** personal section — three or four sentences, not the full
      story — with a link out to the series. The long version lives in one place
      only.
- [ ] Delete `app/pages/my-story.vue` and `content/2.my-story.md` once §4 has a
      home for the content. Add `/my-story` → the new location in
      `app/middleware/legacy.global.ts` and `public/_redirects`.

---

## 4. Series — "My story", read or watch

The biggest block. `app/pages/series/`, `app/components/Episode*.vue`,
`content/5.series/`.

### Landing

- [ ] Rename the section so it reads as **My story** rather than a generic
      "series". Route stays `/series`.
- [ ] **Video background** on the hero: autoplay, muted, loop, `playsinline`,
      with a gradient overlay and a blend mode over it. Must have a poster frame
      and must not play under `prefers-reduced-motion` — fall back to the poster.
      A hero video that autoplays on a phone on mobile data is a cost decision as
      much as a design one; keep it short and heavily compressed.
- [ ] **Two entry points, given equal weight: "Read the story" and "Watch the
      story."** This is the main navigational decision on the page.

### Watch

- [ ] Proper Netflix-style player. `EpisodePlayer.vue` currently wraps
      `<mux-player>` and stops there. Needs: **next-episode card on completion**
      with a countdown, resume-where-you-left-off, an episode drawer accessible
      from inside the player, and keyboard control.
- [ ] Episode progress in `localStorage`, alongside lesson progress in
      `app/composables/useProgress.ts`.

### Read

- [ ] A **book-like reading experience** — chaptered, paged or scroll-snapped,
      narrated in Swarnil's voice, with the reveal animation belonging to the
      chapter rather than the paragraph. Not the current single long page.
- [ ] Carry over the chapter timeline from `app/components/StoryTimeline.vue`,
      which already works and already tracks position.

---

## 5. Start here — layout and pagination

`app/pages/start.vue`, `app/pages/[...slug].vue`, `app/components/SubjectOverview.vue`,
`ModuleOverview.vue`, `LessonPlayer.vue`.

The lesson layout is currently rail + content + narrow TOC, all inside one
column. Target shape:

```
┌──────────────────────────────────────────────┐
│ Hero header — FULL WIDTH                     │
├───────────────────────────┬──────────────────┤
│ Content                   │ TOC (wide)       │
│                           │ Ads              │
│                           │ Edit this page   │
├───────────────────────────┴──────────────────┤
│ Pagination — FULL WIDTH                      │
└──────────────────────────────────────────────┘
```

- [ ] Full-width hero header per section.
- [ ] Two columns below it: content, and a **wider** sidebar carrying TOC + ads +
      page actions. The current TOC is `w-56` and cramped.
- [ ] Full-width pagination band at the bottom — bigger than the current
      prev/next cards, and clearly the end of the page.
- [ ] **"Edit this page"** and "last updated" in the sidebar, linking to the file
      on GitHub. The content is markdown in a public repo; nothing currently says so.
- [ ] Use the horizontal space properly. On a wide screen the content column
      should not be the same width it is on a laptop.

### Subject cards

`app/components/SubjectCard.vue`, `app/pages/start.vue`.

- [ ] **Thumbnails.** Subject cards have no artwork at all right now. Use the
      tech tiles (`app/utils/tech.ts`) or a per-subject illustration.
- [ ] **Netflix-style numbering** — a large `1 2 3 4` half-hidden behind each
      card, bleeding off the edge. The order of the path is its whole point and
      is currently a small badge nobody will read.

### Modules

- [ ] **Collapsible module sections** in `SubjectOverview.vue`. The rail
      (`player/RailSubject.vue`) already collapses per subject; the overview page
      does not, and a long subject is a wall.

---

## 6. Visualisation — make it visual

The `/run` numbers are real and correct, and they are presented as bars and
tables. The ask is to **see what happens**, animated.

`app/components/visualiser/`.

- [ ] **SQL:** animate rows physically moving through the pipeline — rows leaving
      the table, being rejected by `WHERE`, collapsing into groups at `GROUP BY`,
      reordering at `ORDER BY`. Drive it from the numbers already measured in
      `app/utils/runners/sql-analyse.ts`; the data is there, only the rendering
      is text.
- [ ] **Java:** animate the source becoming bytecode becoming output — the file
      travelling into `javac`, the class file into the JVM, lines appearing on a
      console. When compilation fails, show it visibly stopping at that stage.
- [ ] Keep the honesty rule from `app/utils/runners/visualise.ts`: **every figure
      stays measured, never invented.** An animation must not smooth over a
      number that was not obtained. Animate the real values or animate nothing.

---

## 7. Illustrations — scenes, not shapes

`app/components/illustration/`.

Current illustrations are abstract diagrams — boxes, bars, a head in profile.
The ask is **drawn vector scenes**: a person at a desk, a kitchen table, a
walk-in queue, an interview room, a train to Bangalore.

- [ ] Establish a drawing style first — line weight, palette, whether figures
      have faces. Get one scene right before drawing eight.
- [ ] Scenes worth having, all from the story: **the kitchen table**, **the
      walk-in queue**, **the interview room**, **the classroom**, **the first
      payslip**, **the airport**.
- [ ] Animate a moment within each scene rather than the whole thing. A scene
      where everything moves reads as a screensaver.
- [ ] Keep them on the shared timing scale in `app/assets/css/illustration.css`
      so reduced-motion stays one line for the whole library.

---

## 8. My story — show the facts, don't narrate them

Currently ~2,000 words of prose with four stat tiles at the top.

- [ ] Break it into **visual sections**: the salary progression as a chart
      (₹13k → ₹3.6L → ₹5.5L → ₹15.5L → ₹32L), the timeline as a real timeline,
      the four offers as a comparison, rejections as a counter.
- [ ] Keep the writing, cut its length. The facts should be legible to somebody
      who scrolls without reading — the numbers are the argument.
- [ ] `content.config.ts` already has `chapters` and `stats` on the `pages`
      collection; extend the schema rather than hardcoding figures in the page.

---

## 9. Ads — show the placeholder even when disabled

`app/components/AdSlot.vue:58` renders nothing when `ads.enabled` is false, so
there is currently no visible placeholder anywhere.

- [ ] Add a third state: **disabled but reserved** — a dashed box labelled
      "Advertisement" at the real slot dimensions. Makes the layout honest during
      development and proves the reservation works before real ads arrive.
- [ ] Gate it on an app-config flag (`ads.showPlaceholders`) so the placeholder
      never reaches production while ads are off.
- [ ] Add the AdSense script path behind `ads.provider === 'adsense'`, still
      loading only when a slot scrolls near the viewport.
- [ ] New slots needed: `nav-leaderboard` (§2), `sidebar` (§5).

---

## 10. Changelog — icons, categories, less text

`app/pages/changelog/index.vue`, `content/4.changelog/*`.

- [ ] Entries become **short, categorised, icon-led** lines instead of prose:
      **Feature · Fix · Content · Other**, each with its own icon and colour.
- [ ] Add a `changes` array to the `versions` schema in `content.config.ts`:
      `{ type, text }`. Render as a list, not paragraphs.
- [ ] Rewrite the four existing entries to match. They are currently essays.

---

## 11. Questions — let people actually ask

`app/pages/faq.vue`, `content/3.faq.md`.

- [ ] **"Ask a question"** call to action at the top of the page.
- [ ] **Topmate link** to book a call, given real prominence rather than a
      footnote — it is the one place on the site offering direct help.
- [ ] Decide where unanswered questions go: an email link, a GitHub Discussion,
      or a form. A button that opens nothing is worse than no button.

---

## 12. Logo — refine the trail

`app/utils/logo.ts`, `app/components/AppLogo.vue`, `public/favicon.svg`,
`app/components/OgImage/Guide.takumi.vue`.

The trail mark is right in concept. It needs a drawing pass:

- [ ] Balance the curve — the trail currently reads slightly thin against the
      wordmark at 28px.
- [ ] Check optical alignment of the destination node against the cap height of
      "Job Seekers Guide".
- [ ] Verify at 16px. The favicon already drops the two waypoints; confirm that
      is still the right call after any changes.
- [ ] Keep all four files in step — geometry lives in `logo.ts` and is drawn by
      the component, the favicon and the OG renderer.

---

## Open decisions

Things worth settling before starting the block they belong to:

1. **Where does the long story live** — `/series` as a combined read-and-watch
   hub, or `/series` for video with a separate reading route? §4 assumes one hub.
2. **Illustration style** — flat vector with no faces, or drawn figures? Changes
   how long §7 takes by roughly a factor of three.
3. **GitHub star count** — build-time only means it goes stale between deploys.
   Acceptable, or worth a client fetch?
4. **Video hosting for the series hero** — Mux again, or a small self-hosted
   loop? Mux bills per minute delivered and a looping hero is a lot of minutes.

---

## Done

Kept for context on what already exists.

- One path at root-level URLs, `/start`, player with cross-subject prev/next
- Teaching component library (`::flow`, `::code-trace`, `::memory`, `::compare`,
  `::pros-cons`, `::real-life`, `::persona`, `::feature-list`, `::timeline`,
  `::ai-prompt`, `::youtube`, `::runner`, `::ad`, `::illustration`, `::tech`)
- Code runners: HTML, CSS, JavaScript, SQL, Python in-browser; Java via Worker
- `/run` with **measured** SQL query plan and per-clause row counts
- Ten episode scripts, Mux player scaffolding, poster fallbacks
- SEO: sitemap, robots, canonicals, JSON-LD, OG images
- Deployed to GitHub Pages at `jobseekers.imswarnil.com` — **DNS still needs
  pointing at `imswarnil.github.io`**
