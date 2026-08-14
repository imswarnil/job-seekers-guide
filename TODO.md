# TODO — Job Seekers Guide (Custom WordPress LMS)

Custom, open-source LMS for job seekers: structured learning paths, in-browser
code practice, Dodo Payments checkout, a from-scratch token-based theme, and
JSON-LD/SEO built in. No Tutor LMS, no WooCommerce — see
`.claude/plans` history for the architecture writeup.

## 1. VPS & Infrastructure — done
- [x] VPS provisioned, Docker installed, SSH key auth set up
- [x] `job-seekers-lms` Docker stack live (`wordpress` + `db`, wp-content bind-mounted)
- [x] Domain `jobseekers.imswarnil.com` on Traefik + Let's Encrypt HTTPS
- [x] GitHub Actions deploy pipeline (push to `main` → SSH → `git pull` → restart)
- [ ] `wp db export`/`import` scripts for staging↔prod DB parity when needed

## 2. Design System & Theme (`wp-content/themes/job-seekers-theme`)
- [x] `theme.json` token set: neutral scale, accent color, spacing/type/radius/shadow/motion tokens
- [ ] Light/dark style variation + FOUC-safe toggle script (`prefers-color-scheme` default, `localStorage` override)
- [ ] Base block templates: front page, single post, archive, 404
- [ ] Custom PHP templates for LMS screens: course landing, lesson/course-player, learning-path overview, pricing, user dashboard
- [ ] Accessible, keyboard-navigable nav + course-card / path-step block patterns

## 3. Core LMS Plugin (`wp-content/plugins/job-seekers-lms`)
- [x] Plugin bootstrap, activation/deactivation hooks, capability + rewrite flush
- [x] CPTs: `Course`, `Lesson`, `Learning Path` + `Module` taxonomy
- [x] Custom DB tables: `wp_jsl_enrollments`, `wp_jsl_progress` (schema only)
- [ ] Internal PHP API (`JSL\Course::get_path()`, `JSL\Progress::for_user()`, ...) theme templates consume
- [ ] Enrollment + progress logic (mark lesson complete, compute path %, certificates)
- [ ] Admin UI: manage courses/lessons/paths, reorder lessons within a course

## 4. Payments — Dodo Payments (`includes/payments`)
- [ ] Research Dodo Payments' current API/webhook contract (do not assume shape)
- [ ] Settings page: API key stored via WP options (never committed/hardcoded)
- [ ] Checkout flow (pricing block/page → Dodo checkout)
- [ ] Webhook REST endpoint: verify signature, fire `jsl_payment_confirmed` → auto-enroll
- [ ] Pricing table block/pattern

## 5. Code Practice (`includes/code-practice`)
- [ ] JS sandbox: editor block (Monaco/CodeMirror) + sandboxed iframe execution — client-side only
- [ ] SQL sandbox: `sql.js` (SQLite/WASM) — client-side only, seed schema per exercise
- [ ] Java sandbox — **deferred**; needs a safe execution strategy (WASM JVM or external sandboxed judge like self-hosted Judge0) before building, not a default/required feature

## 6. SEO & Search (`includes/schema`, `includes/search`)
- [ ] JSON-LD: `Course` schema on course pages, `BreadcrumbList` sitewide, `Organization` on homepage
- [ ] Meta tags (title/description/OG) per template
- [ ] Sitemap hooks for custom post types
- [ ] Enhanced native search: relevance weighting (title/tag boost) across courses/lessons

## 7. Security (`includes/security`, cross-cutting)
- [ ] Standing rule across all modules: `$wpdb->prepare()` for all queries, `esc_*()` on all output, nonce + capability checks on all state-changing actions
- [ ] Security headers, disabled XML-RPC, login rate limiting
- [ ] Secrets only via WP options / environment, never hardcoded or committed

## 8. Content & Launch
- [ ] Plan initial learning path(s) + course outline for job seekers
- [ ] Draft starter courses/lessons
- [ ] QA: full flow — browse path → enroll (free + paid via Dodo) → complete lessons → progress/certificate
- [ ] Cross-browser/device QA, Lighthouse pass (perf/SEO/accessibility)

## 9. Open-Source Packaging
- [ ] GPL-2.0-or-later license on theme + plugin (required for WP distribution)
- [ ] `README.md`/`readme.txt` with clear self-host install steps (clone → `docker compose up` → activate theme/plugin)
- [ ] Document required env vars / WP options for a fresh install (no assumption of our specific VPS/Dodo account)

## 10. Backups & Monitoring
- [ ] Automate DB + `wp-content` backups (cron + offsite storage)
- [ ] Uptime monitoring
- [ ] Docker log rotation
