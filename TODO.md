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
- [x] Hand-rolled token system (`tokens.css`) now compiled through Tailwind CSS v4 (`src/app.css` → committed `assets/css/app.css`, rebuild with `npm run build`)
- [x] Dark mode: FOUC-safe inline bootstrap + toggle, overrides the semantic token layer only
- [x] Classic PHP templates: `front-page`, `single`, `page`, `archive`, `404`, `index`
- [x] LMS templates: `single-course` (dark hero, sticky enroll card, accordion curriculum with duration/preview/completion state), `single-lesson` (full lesson player: video embed, progress sidebar, mark-complete, prev/next), `single-learning_path` (milestone steps)
- [x] Homepage redesign: hero + stats, numbered learning-path sections with course cards + lesson peeks, CTA band, logged-in "Jump back in" resume strip
- [x] Lesson player v2: full-width app shell (fixed course sidebar w/ progress + durations, mobile slide-over drawer, sticky toolbar with Lesson N of M + mark-complete, prev/next bar)
- [x] "My Learning" learner dashboard (`page-templates/my-learning.php` + `/my-learning/` page): stats, streak, resume cards, 14-day activity chart
- [x] Material Design 3 token system (tonal palettes → system roles → compat aliases), shared by theme, console, wp-admin skin and login
- [x] Phosphor icons baked to `inc/icons.php` by `npm run icons` — only what's used, inline, offline-safe
- [x] New brand mark (magnifier + briefcase); Plus Jakarta Sans / Inter pairing
- [x] Course-player app bar (`header-player.php`) separate from the marketing header
- [x] Asset cache-busting by file mtime
- [ ] Accessible keyboard-nav pass (current nav/focus states are baseline, not audited)

## 3. Core LMS Plugin (`wp-content/plugins/job-seekers-lms`)
- [x] Plugin bootstrap, activation/deactivation hooks, capability + rewrite flush
- [x] CPTs: `Course`, `Lesson`, `Learning Path` + `Module` taxonomy
- [x] Custom DB tables: `wp_jsl_enrollments`, `wp_jsl_progress`, `wp_jsl_modules`
- [x] `Course_Api::get_modules()`/`get_path_courses()` — theme's read API
- [x] `Enrollment::enroll()`/`is_enrolled()` — free-course enrollment writes, verified end-to-end locally
- [x] Visual course builder v2 (`wp-admin → Courses → Course Builder`): course picker cards, module drag-reorder, lesson drag within/between modules with drop indicator, inline rename (contenteditable), inline delete confirm, live save-state + toasts — verified persists across reload
- [x] Progress tracking: `Progress` class + `POST/DELETE jsl/v1/lessons/{id}/complete`, lesson player updates sidebar/progress bar in place — verified end-to-end locally
- [x] Lesson meta (video URL, duration, free-preview flag) + metabox and YouTube/Vimeo/mp4 embed helper
- [x] LMS Console (`wp-admin → LMS`): single-page admin app — Dashboard (analytics: learners/enrollments/completions, 14-day chart, course performance, activity feed), Courses (create/publish/excerpt), Course editor (builder + lesson writing in a TinyMCE drawer incl. video/duration/preview meta), Learners (list + per-user profile with course progress and activity)
- [x] Analytics backend (`includes/analytics/class-analytics.php`) + admin REST routes under `jsl/v1/analytics/*`
- [x] wp-admin reskin (`admin/class-admin-theme.php`): design-system colors across admin menu/bar/buttons/forms/tables + branded login screen
- [x] Design tokens v2 "Night Iris": night-indigo / frost / iris-violet / lime palette, Space Grotesk display + Manrope body, applied across theme, console, admin skin, and login
- [x] LMS-only WordPress: Posts + Comments removed everywhere, wp-admin Dashboard redirects to the LMS console, learners (subscribers) redirected to /my-learning, hardcoded LMS nav, classic-editor plugin removed
- [x] Security hardening (`includes/security/`): XML-RPC dead (403), security headers, user-enumeration blocked (?author= + wp/v2/users), generic login errors, DISALLOW_FILE_EDIT, version/meta-tag leaks stripped, comments fully off
- [x] In-house lesson editor in the console (no TinyMCE dependency): custom rich-text toolbar, media-library image insert, lesson type selector (article/video/quiz)
- [x] Quiz engine (`includes/quiz/class-quiz.php`): builder UI in the drawer, answers stored server-side only, public questions route, server-graded submissions that auto-complete the lesson on pass
- [x] Video clip ranges (start/end) + custom click-to-play facade player (youtube-nocookie, modestbranding, poster + brand play button)
- [x] Course codes (`jsl_course_code`) + `course_category` taxonomy, editable in the console
- [x] Dynamic JSON-LD (`includes/schema/class-json-ld.php`): Course with lesson ItemList/hasPart, LearningResource + VideoObject on lessons, ItemList on paths, WebSite/Organization on home
- [x] Generated SVG placeholder card art (`includes/media/class-placeholder.php`) with course code, wrapped title, lesson count; brand SVGs (logo, favicon, empty-state) in theme assets
- [x] Realistic seed curriculum (`wp jsl seed --fresh`): 2 paths, 4 coded courses, 24 lessons incl. video-with-clip and 4 quizzes
- [x] Nested lesson permalinks `/courses/{course}/{lesson}/` — one URL per lesson, old/wrong URLs 301
- [x] LMS-only wp-admin: Course/Lesson/Path menus hidden, native editors redirect into the console
- [x] Visual learning-path builder: ordered steps mixing courses with standalone articles/videos/quizzes, authored in the console
- [x] Google sign-in/sign-up (OAuth 2.0 + PKCE, verified-email linking only)
- [x] PWA: generated manifest, network-first service worker, offline page, install support
- [x] Auto-complete the current lesson when the learner clicks Next
- [x] One tabbed LMS → Settings screen (Payments / Subscription / Google / SEO / PWA)
- [ ] Certificates

## 4. Payments & access — Dodo Payments (`includes/payments`, `includes/access`) — untested against a live account
- [x] Researched Dodo's actual API/webhook contract (Bearer auth, `POST /checkouts`, Standard Webhooks HMAC-SHA256 signing)
- [x] Settings page (`Settings → Dodo Payments`): API key, mode, webhook secret via WP options
- [x] Course pricing meta box: free/paid, Dodo Product ID, display price label
- [x] `Checkout::create_session()` — builds and sends the real `wp_remote_post` request
- [x] `Webhook` REST route — verifies `webhook-id`/`webhook-signature`/`webhook-timestamp` per spec, enrolls on `payment.succeeded`
- [ ] End-to-end test against a real Dodo test-mode product (needs your Dodo dashboard account — nothing more to build until then)
- [x] Platform subscription (`class-subscription.php`): one plan unlocks every course, time-boxed grant renewed/cancelled by webhook
- [x] Buying a course unlocks all of its lessons (no per-lesson purchase)
- [x] Homepage pricing section + "Included in your plan" state on course pages

## 5. Code Practice (`includes/code-practice`) — not started
- [ ] JS sandbox: editor block (Monaco/CodeMirror) + sandboxed iframe execution — client-side only
- [ ] SQL sandbox: `sql.js` (SQLite/WASM) — client-side only, seed schema per exercise
- [ ] Java sandbox — **deferred**; needs a safe execution strategy (WASM JVM or external sandboxed judge like self-hosted Judge0) before building, not a default/required feature

## 6. SEO & Search (`includes/seo`, `includes/schema`) — done except search
- [x] JSON-LD: `Course` + lesson ItemList, `LearningResource`/`VideoObject`, `ItemList` on paths, `WebSite`/`Organization` on home
- [x] `BreadcrumbList` reflecting the nested `/courses/{course}/{lesson}/` hierarchy
- [x] Meta tags (title/description/OG/Twitter) per template; canonical taken over from core so there is exactly one
- [x] Locked lesson bodies are never used as a meta description — paid content can't leak into a snippet
- [x] CPT sitemaps (native WP sitemaps pick up courses/lessons/paths)
- [x] SEO settings tab (default description, social image, Twitter handle, organization name)
- [ ] Enhanced native search: relevance weighting (title/tag boost) across courses/lessons

## 7. Security (`includes/security`, `includes/access`)
- [x] Standing rule applied so far: `$wpdb->prepare()` on all queries, nonce + `current_user_can()` checks on all builder/pricing writes, webhook authenticity via HMAC (not a WP capability, since it's an external caller)
- [x] Security headers, XML-RPC disabled, user-enumeration blocked, generic login errors
- [x] Unified access layer (`includes/access/class-access.php`): one gatekeeper consulted by templates, progress REST, quiz REST and `wp/v2` lesson content
- [x] Webhook replay protection (delivery-id transient) + 5-minute timestamp tolerance; provider error bodies no longer echoed to the browser
- [ ] Login rate limiting — not yet built
- [x] Secrets only via WP options / environment — verified nothing hardcoded or committed (also cleaned up two accidental token pastes into tracked files during this project)

## 8. Content & Launch
- [x] Demo content seeder (`wp jsl seed`) — 1 learning path, 3 courses (1 free, 2 paid demo pricing), modules + real (non-lorem-ipsum) lesson content — used to preview the design
- [ ] Real course content (seed data is for design preview only, not launch content)
- [ ] QA: full flow — browse path → enroll (free ✅ verified; paid via Dodo — blocked on a real Dodo product) → complete lessons → progress/certificate
- [ ] Cross-browser/device QA, Lighthouse pass (perf/SEO/accessibility)

## 9. Open-Source Packaging
- [x] GPL-2.0-or-later declared in theme/plugin headers
- [x] End-to-end setup + operating guide (`SETUP.md`): install, curriculum authoring, pricing, Google OAuth, SEO, PWA, troubleshooting
- [ ] Top-level `README.md`/`readme.txt` short install blurb pointing at SETUP.md

## 10. Backups & Monitoring
- [ ] Automate DB + `wp-content` backups (cron + offsite storage)
- [ ] Uptime monitoring
- [ ] Docker log rotation
