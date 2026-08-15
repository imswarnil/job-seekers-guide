# Job Seekers Guide — setup and operating guide

Everything below is done from **wp-admin → LMS**. You should never need the
classic editor, the block editor, or the Courses/Lessons post-type screens —
they are deliberately hidden, and visiting their URLs redirects into the LMS
console.

---

## 1. First run

### 1.1 Start the stack

```bash
cd docker
cp .env.example .env        # fill in DB credentials + WORDPRESS_* values
docker compose -f docker-compose.local.yml up -d
```

The site comes up at <http://localhost:8080>. `wp-content/` is bind-mounted,
so edits to the theme and plugin are live without a rebuild.

### 1.2 Activate

In **Appearance → Themes** activate **Job Seekers Theme**, and in **Plugins**
activate **Job Seekers LMS**. Activation creates the custom tables
(`wp_jsl_enrollments`, `wp_jsl_progress`, `wp_jsl_modules`,
`wp_jsl_path_steps`) and flushes permalinks.

### 1.3 Permalinks

Settings → Permalinks must be anything other than "Plain". The plugin
registers its own rules on top:

| URL | What it is |
| --- | --- |
| `/courses/` | course archive |
| `/courses/{course}/` | a course |
| `/courses/{course}/{lesson}/` | a lesson — **the only URL a lesson has** |
| `/library/{slug}/` | a standalone article/video that belongs to no course |
| `/learning-paths/{path}/` | a learning path |
| `/my-learning/` | the learner dashboard |

Old `/lessons/{slug}/` URLs and wrong-course URLs 301 to the canonical one, so
nothing that was already linked or indexed breaks.

> If lesson URLs 404 after an upgrade, re-save Settings → Permalinks once.

### 1.4 Optional demo content

```bash
docker exec docker-wordpress-1 wp --allow-root --path=/var/www/html jsl seed --fresh
```

This is design-preview content, not launch content — replace it before going
live.

---

## 2. Build the curriculum

### 2.1 Create a course

**LMS → Courses → Create course.** It starts as a draft. Click it to open the
course editor.

### 2.2 Structure it

In the course editor:

1. **Add module** — the chapters of the course.
2. **Add lesson** inside a module.
3. Drag modules and lessons by their grip handle to reorder; lessons can be
   dragged between modules. Ordering saves as you drop it.
4. Click a lesson title to open the **writing drawer**: rich text, images from
   the media library, and the lesson's settings.

### 2.3 Lesson settings (in the drawer)

| Setting | Effect |
| --- | --- |
| Type: Article / Video / Quiz | which player the lesson renders |
| Video URL | YouTube, Vimeo or a direct `.mp4` |
| Clip start / end | plays only that range |
| Duration (minutes) | shown in the sidebar and course stats |
| Free preview | this lesson stays open even in a paid course |

Mark one or two lessons per paid course as **free preview** — that is the
sample chapter, and it is the only content a non-buyer can read.

### 2.4 Build a learning path

**LMS → Learning Paths → Create path**, then open it. A path is an ordered
list of steps, and a step is either:

- **an existing course** — pick it from the dropdown and *Add course*; or
- **a standalone article, video or quiz** — choose the type, give it a title,
  *Add step*. It is created immediately and lives at `/library/{slug}/`.

Drag steps to reorder. Click the title of a standalone step to rename it in
place; click the pencil to write it. Toggle **Published** when it is ready.

Removing a step removes it from the path only — the course or lesson itself is
never deleted.

---

## 3. Charging for it

Configured at **LMS → Settings**.

### 3.1 Payments tab (Dodo Payments)

1. In your Dodo dashboard create a product per paid course.
2. Paste the **API key** and **Webhook secret** here, and set Mode to `Test`
   while you are testing.
3. Copy the **Webhook URL** shown on the page into
   Dodo → Developer → Webhooks, subscribed to `payment.succeeded` and, if you
   sell a subscription, the `subscription.*` events.

### 3.2 Price a course

Open the course in the console and set it to **Paid**, then paste the Dodo
**Product ID** and a display price label (e.g. `$49`).

**One purchase unlocks the whole course.** There is no per-lesson buying.

### 3.3 Platform subscription (optional)

**LMS → Settings → Subscription.** Create a *recurring* product in Dodo, tick
"Offer a subscription that unlocks every course", paste the product ID, and
give it a price label.

Once enabled:

- a pricing section appears on the homepage,
- paid course pages offer "unlock everything" alongside buying that course,
- a subscriber sees **Included in your plan** instead of a paywall.

The grant is time-boxed. Renewals push the expiry out; a cancellation ends
access at the next check but **never deletes progress**.

> Leave the subscription toggle **off** until the Dodo product really exists —
> otherwise the homepage advertises a plan whose checkout fails.

### 3.4 How access is decided

One class, `JSL\Access\Access`, answers "can this person open this lesson?" for
every surface — the template, the progress API, the quiz API, and the REST
lesson content. In order:

1. Anyone who can edit posts (you) sees everything.
2. A free-preview lesson is open to everyone.
3. A free course is open to everyone.
4. A paid course opens for someone who bought **that course**.
5. An active subscription opens **every** course.

---

## 4. Sign-in with Google

**LMS → Settings → Google Sign-In.**

1. Google Cloud Console → **APIs & Services → Credentials → Create
   credentials → OAuth client ID → Web application**.
2. Under **Authorized redirect URIs** paste the exact URI shown on the
   settings page — `https://yoursite/auth/google/callback`. It must match
   character for character, including `https`.
3. Copy the **Client ID** into the settings page.
4. For the **Client secret**, prefer putting it in `wp-config.php`:

   ```php
   define( 'JSL_GOOGLE_CLIENT_SECRET', 'your-secret-here' );
   ```

   The constant wins over the database field, which keeps the secret out of
   your database and out of any DB backup.
5. Tick **Google sign-in**, and decide whether first-time Google users may
   create an account.

A "Continue with Google" button then appears on the login and register forms.

**What the flow does:** authorization code with PKCE (S256); a random `state`
bound to the browser by both an HttpOnly cookie and a single-use transient;
the ID token read from the back channel and checked for issuer, audience,
expiry and nonce. An account is only ever linked to an existing user when
Google reports the email as **verified**.

---

## 5. SEO

**LMS → Settings → SEO** sets the default description, the fallback social
image (1200×630), your Twitter/X handle, and the organization name used as the
course provider in structured data.

Emitted automatically, per template:

- one canonical URL (the plugin takes this over from core, so there is never a
  duplicate),
- meta description — from the page's excerpt, else its content; **a locked
  lesson's body is never used**, so paid content cannot leak into a search
  snippet,
- Open Graph + Twitter card tags,
- `BreadcrumbList` JSON-LD following the real hierarchy
  (Home › Courses › Course › Lesson),
- `Course` with its lesson list, `LearningResource`/`VideoObject` on lessons,
  `ItemList` on paths, `WebSite`/`Organization` on the homepage,
- WordPress's native sitemap picks up courses, lessons and paths.

---

## 6. Installable app (PWA)

**LMS → Settings → App / PWA** — tick "Installable app", set the app name,
short name (keep it under ~12 characters) and theme colour.

That serves `/manifest.webmanifest` and `/sw.js`, adds the iOS meta tags, and
gives you an offline fallback at `/offline/`. Nothing to upload.

Caching is deliberately conservative because this site has paid content:

- **HTML is network-first** — the cache is only a fallback for being offline.
  A cache-first strategy could show one learner a page rendered for another,
  or a "buy this" page to someone who just bought it.
- **Assets** (CSS/JS/fonts/images) are stale-while-revalidate.
- **Never cached:** `/wp-admin`, `/wp-login.php`, `/wp-json`, `/auth/*`,
  anything that is not a GET, and any response carrying `Set-Cookie` or
  `Vary: Cookie`.

After changing service-worker logic, bump `Pwa::SW_VERSION` so browsers drop
their old caches.

---

## 7. Day-to-day: what a learner sees

1. Lands on a path, works down the steps.
2. Opens a lesson at `/courses/{course}/{lesson}/` in the player: curriculum
   sidebar with live progress, video or article, quiz if it is one.
3. **Clicking "Next" marks the current lesson complete and moves on** — no
   separate button to remember. "Mark complete" is still there for anyone who
   wants it, and toggles back off.
4. Progress, streak and resume links live at `/my-learning/`.
5. Quizzes are graded on the server; answers are never sent to the browser.
   Passing auto-completes the lesson.

---

## 8. Design system

Tokens live in **one place per side**:

- front end: `wp-content/themes/job-seekers-theme/assets/css/tokens.css`
- admin: `wp-content/plugins/job-seekers-lms/admin/assets/css/md3-tokens.css`

Both are Material 3: tonal reference palettes → system roles
(`--md-sys-primary`, `--md-sys-surface-container-*`, `--md-sys-outline`, …) →
compatibility aliases. Dark mode swaps *tones*, not roles, so nothing
downstream changes. Retinting the whole product — site, console, wp-admin,
login — means editing the palettes in those two files.

After editing CSS:

```bash
cd wp-content/themes/job-seekers-theme
npm run build        # compiles src/app.css -> assets/css/app.css (committed)
```

Icons are Phosphor, baked into `inc/icons.php`:

```bash
npm run icons        # after adding a name to tools/build-icons.mjs
```

Only the icons actually used are included, inline — no icon font, no request,
works offline.

---

## 9. Security posture

Already in place:

- XML-RPC disabled; security headers; user-enumeration blocked
  (`?author=` and `wp/v2/users`); generic login errors; `DISALLOW_FILE_EDIT`;
  version strings stripped; comments off site-wide.
- Every REST write checks a capability, and every query goes through
  `$wpdb->prepare()`.
- Webhooks are authenticated by HMAC signature, reject timestamps outside a
  five-minute window, and ignore replayed delivery IDs.
- Secrets live in options or `wp-config.php` constants — nothing is hardcoded
  or committed.
- Paid lesson bodies are stripped server-side, so a template bug cannot leak
  them, and they are blanked in the REST API too.

Still worth doing before a public launch:

- [ ] HTTPS everywhere (the OAuth cookie only gets the `Secure` flag under TLS)
- [ ] Login rate limiting / a WAF in front
- [ ] Automated DB + `wp-content` backups, off-site
- [ ] Test one real Dodo checkout end to end in test mode, then switch to Live

---

## 10. Troubleshooting

| Symptom | Cause and fix |
| --- | --- |
| Lesson URLs 404 | Re-save Settings → Permalinks. |
| "My Learning" goes to the homepage | That page is set as the static front page. Settings → Reading → set "Your homepage displays" to **Your latest posts**; `front-page.php` still renders `/`. |
| Design looks stale after a deploy | Run `npm run build` in the theme. Asset URLs are versioned by file mtime, so a rebuilt file busts the cache automatically. |
| Google sign-in says "could not be verified" | The redirect URI in Google Cloud does not match `/auth/google/callback` exactly, or the sign-in took longer than 10 minutes. |
| Subscription never activates after payment | The webhook is not reaching the site, or is not subscribed to the `subscription.*` events. Check the Webhook URL on the Payments tab. |
| A new DB column is missing after an upgrade | Load any wp-admin page once — the schema upgrade runs on `admin_init` when the plugin version changes. |
