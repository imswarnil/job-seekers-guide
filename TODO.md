# TODO — Job Seekers Guide (Custom WordPress LMS)

Custom, open-source LMS for job seekers: structured learning paths, a visual
course builder, in-browser code practice, Dodo Payments checkout, and a
hand-rolled design-token system (no block theme, no theme.json — see
`assets/css/tokens.css`). No Tutor LMS, no WooCommerce.

## 1. VPS & Infrastructure — done
- [x] VPS provisioned, Docker installed, SSH key auth set up
- [x] `job-seekers-lms` Docker stack live (`wordpress` + `db`, wp-content bind-mounted)
- [x] Domain `jobseekers.imswarnil.com` on Traefik + Let's Encrypt HTTPS
- [x] GitHub Actions deploy pipeline (push to `main` → SSH → `git pull` → restart)
- [ ] `wp db export`/`import` scripts for staging↔prod DB parity when needed

## 2. Design System & Theme (`wp-content/themes/job-seekers-theme`) — done
- [x] Hand-rolled token system: `tokens.css` → `base.css` → `typography.css` → `components.css`
- [x] Dark mode: FOUC-safe inline bootstrap + toggle, overrides the semantic token layer only
- [x] Classic PHP templates: `front-page`, `single`, `page`, `archive`, `404`, `index`
- [x] LMS templates: `single-course` (hero + pricing box + enroll button + module/lesson list), `single-lesson` (content + sidebar nav), `single-learning_path` (ordered course grid)
- [ ] Accessible keyboard-nav pass (current nav/focus states are baseline, not audited)

## 3. Core LMS Plugin (`wp-content/plugins/job-seekers-lms`)
- [x] Plugin bootstrap, activation/deactivation hooks, capability + rewrite flush
- [x] CPTs: `Course`, `Lesson`, `Learning Path` + `Module` taxonomy
- [x] Custom DB tables: `wp_jsl_enrollments`, `wp_jsl_progress`, `wp_jsl_modules`
- [x] `Course_Api::get_modules()`/`get_path_courses()` — theme's read API
- [x] `Enrollment::enroll()`/`is_enrolled()` — free-course enrollment writes, verified end-to-end locally
- [x] Visual course builder (`wp-admin → Courses → Course Builder`): drag-and-drop reorder, add/rename module + lesson, REST-backed (`jsl/v1/modules`, `jsl/v1/lessons`), verified persists across reload
- [ ] Progress tracking (mark lesson complete) — tables exist, no write path yet
- [ ] Certificates

## 4. Payments — Dodo Payments (`includes/payments`) — real implementation, untested against a live account
- [x] Researched Dodo's actual API/webhook contract (Bearer auth, `POST /checkouts`, Standard Webhooks HMAC-SHA256 signing)
- [x] Settings page (`Settings → Dodo Payments`): API key, mode, webhook secret via WP options
- [x] Course pricing meta box: free/paid, Dodo Product ID, display price label
- [x] `Checkout::create_session()` — builds and sends the real `wp_remote_post` request
- [x] `Webhook` REST route — verifies `webhook-id`/`webhook-signature`/`webhook-timestamp` per spec, enrolls on `payment.succeeded`
- [ ] End-to-end test against a real Dodo test-mode product (needs your Dodo dashboard account — nothing more to build until then)
- [ ] Pricing table block/pattern (currently just the single price box on the course page)

## 5. Code Practice (`includes/code-practice`) — not started
- [ ] JS sandbox: editor block (Monaco/CodeMirror) + sandboxed iframe execution — client-side only
- [ ] SQL sandbox: `sql.js` (SQLite/WASM) — client-side only, seed schema per exercise
- [ ] Java sandbox — **deferred**; needs a safe execution strategy (WASM JVM or external sandboxed judge like self-hosted Judge0) before building, not a default/required feature

## 6. SEO & Search (`includes/schema`, `includes/search`) — not started
- [ ] JSON-LD: `Course` schema on course pages, `BreadcrumbList` sitewide, `Organization` on homepage
- [ ] Meta tags (title/description/OG) per template
- [ ] Sitemap hooks for custom post types
- [ ] Enhanced native search: relevance weighting (title/tag boost) across courses/lessons

## 7. Security (`includes/security`)
- [x] Standing rule applied so far: `$wpdb->prepare()` on all queries, nonce + `current_user_can()` checks on all builder/pricing writes, webhook authenticity via HMAC (not a WP capability, since it's an external caller)
- [ ] Security headers, disabled XML-RPC, login rate limiting — not yet built
- [x] Secrets only via WP options / environment — verified nothing hardcoded or committed (also cleaned up two accidental token pastes into tracked files during this project)

## 8. Content & Launch
- [x] Demo content seeder (`wp jsl seed`) — 1 learning path, 3 courses (1 free, 2 paid demo pricing), modules + real (non-lorem-ipsum) lesson content — used to preview the design
- [ ] Real course content (seed data is for design preview only, not launch content)
- [ ] QA: full flow — browse path → enroll (free ✅ verified; paid via Dodo — blocked on a real Dodo product) → complete lessons → progress/certificate
- [ ] Cross-browser/device QA, Lighthouse pass (perf/SEO/accessibility)

## 9. Open-Source Packaging
- [x] GPL-2.0-or-later declared in theme/plugin headers
- [ ] Top-level `README.md`/`readme.txt` self-host install steps (clone → `docker compose up` → activate theme/plugin → `wp jsl seed` optionally)
- [ ] Document required WP options for a fresh install (Dodo keys, etc. — no assumption of our specific VPS/Dodo account)

## 10. Backups & Monitoring
- [ ] Automate DB + `wp-content` backups (cron + offsite storage)
- [ ] Uptime monitoring
- [ ] Docker log rotation
