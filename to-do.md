# To-do

What is left. Everything else from the previous pass shipped — see **Done**.

---

## 1. Illustrations — scenes, not shapes

`app/components/illustration/`.

The only substantial item outstanding. The current illustrations are abstract
diagrams — boxes, bars, a head in profile. What was asked for is **drawn vector
scenes**: a person at a desk, a kitchen table, a walk-in queue.

This is an art task rather than an engineering one, and doing it badly is worse
than not doing it: eight mediocre scenes would make the site look cheaper than
the abstract shapes they replaced.

- [ ] **Settle the style first.** Line weight, palette, and whether figures have
      faces. Faces are the fork in the road — they triple the work and they make
      every subsequent scene harder to keep consistent.
- [ ] Draw **one** scene completely and live with it before drawing the rest.
- [ ] Scenes worth having, all from the story: **the kitchen table**, **the
      walk-in queue**, **the interview room**, **the classroom**, **the first
      payslip**, **the airport**.
- [ ] Animate one moment per scene, not the whole thing. A scene where everything
      moves reads as a screensaver.
- [ ] Keep them on the shared timing scale in `app/assets/css/illustration.css`
      so reduced-motion stays one line for the whole library.

## 2. Loose ends

- [ ] **Wandbox is a free service with no SLA.** The Java runner works today and
      results are cached in KV for 30 days, but there is no contract. If it ever
      matters, `PISTON_URL` + `PISTON_TOKEN` on the Worker switches to a
      self-hosted Piston with no site deploy.
- [ ] **First Java run is slow** — around 8 seconds cold, because Wandbox is
      cold. Cached runs are instant. Worth a warmer loading message if it grates.
- [ ] **Series hero has no video yet.** `StoryHero.vue` takes `src` and `poster`;
      until then it falls back to the contour grid, which is deliberate rather
      than broken. Decide Mux vs a small self-hosted loop — Mux bills per minute
      delivered and a looping hero is a lot of minutes.
- [ ] **Ad placeholders are on.** `ads.showPlaceholders` in `app/app.config.ts`.
      Turn it off before anybody who is not you looks at the site.
- [ ] **Topmate link is a guess** — `brand.topmate` in `app/app.config.ts` points
      at `topmate.io/swarnil`. Correct it.
- [ ] **GitHub star count is fetched at build time**, so it goes stale between
      deploys. Fine, but that is the trade.

## Open decisions

1. **Illustration style** — flat vector without faces, or drawn figures? Changes
   the size of §1 by roughly a factor of three.
2. **Series hero video hosting** — Mux again, or a small self-hosted loop?

## Done

- **Java runner deployed and working.** `jsg-runner.jobseekersguide.workers.dev`,
  backed by Wandbox after the public Piston API went whitelist-only in February.
  Compile errors, runtime crashes and successful runs all verified, with line
  numbers preserved.
- **Nav** — Run code and My story removed, search promoted to every width,
  GitHub star, leaderboard ad slot.
- **Ads** — a third state, so the reserved box is drawn even when ads are off.
  New `nav-leaderboard` and `sidebar` slots; placements across every page type.
- **Changelog** — icon-led lines in four categories, reasoning collapsed.
- **Questions** — Ask a question and a booking link, above the answers.
- **Logo** — a start node, a trail and a destination. No plate, transparent.
- **Lesson layout** — full-width hero, content beside a 19rem sidebar, full-width
  pagination band, "edit this page".
- **Subject cards** — tech thumbnails and Netflix-style numbering half-hidden
  behind the card. Collapsible modules on subject pages.
- **About** — mission, ten principles, exclusions, with the story cut to a link.
- **My story** — one hub at `/series` with read and watch given equal weight, a
  video-capable hero, and the salary progression as a log-scale chart.
- **Episode player** — next-episode card with a countdown that any interaction
  cancels.
- **Visualisers** — rows physically moving through the SQL pipeline as dots that
  drop out at each clause, and source travelling through javac into the JVM with
  the JVM drawn as never started when compilation fails.
- **Two real bugs found while testing**: a failed run rendered nothing at all
  because the error alert was nested inside the success branch, and `?lang=`
  was ignored after hydration, which broke every shared run link.
- Deployed to GitHub Pages at `jobseekers.imswarnil.com`. DNS resolves; the
  certificate provisions on GitHub's side.
