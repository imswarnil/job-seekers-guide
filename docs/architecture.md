# Architecture

How the two packages fit together, for anyone extending them.

## The split

```
guide-lms      (plugin)   behaviour, data, access, money
guide-wp-theme (theme)    presentation only
```

The rule: **the theme never decides anything.** It asks the plugin and renders
the answer. A template that computes access, pricing, or progress is a bug — it
means the same rule now exists in two places and they will diverge.

```php
// Right
$locked = \Guide\Access\Access::is_locked( $lesson_id );

// Wrong — re-derives a rule the plugin owns
$locked = $is_premium && ! $has_subscription;
```

This is why the plugin can be installed with any theme, and why the theme
degrades gracefully (every call site is wrapped in `class_exists`).

## Plugin layout

```
includes/
  access/       Access — the single gatekeeper for "may this user see this?"
  account/      Account — /account/ routing and profile REST
  ads/          Ads — AdSense placement rules
  analytics/    Console dashboard figures
  api/          Course_Api — the theme's read API
  auth/         Google sign-in (OAuth 2.0 + PKCE)
  billing/      Billing — the payments log behind receipts
  builder/      Course and path builder REST + tables
  cli/          wp jsl seed
  enrollment/   Grants and the enrollment/progress tables
  media/        Generated placeholder card art
  payments/     Course_Access, Checkout, Subscription, Webhook, Settings
  post-types/   CPTs, meta, permalinks
  progress/     Completion tracking
  pwa/          Manifest + service worker
  quiz/         Quiz engine (answers never leave the server)
  schema/       JSON-LD
  security/     Hardening
  seo/          Meta tags
  success/      Learner stories
admin/          The console, settings, wp-admin skin
```

## Key invariants

**One gatekeeper.** `Access::lesson_denial_reason()` and
`Access::course_denial_reason()` are the only places access is decided. The
lesson template, the REST progress writes, the quiz route, JSON-LD and search
all call them.

**Content is stripped at the source.** A locked lesson's body is removed by a
`the_content` filter *and* in the REST response — not merely hidden by the
template. A template bug must not be able to leak a members-only lesson.

**Quiz answers are server-side only.** The public questions route omits them;
grading happens on the server.

**The database is the stable interface.** Table names (`wp_jsl_*`) and meta
keys (`jsl_*`) survived the rename from Job Seekers LMS to Guide LMS on
purpose. Renaming them buys tidiness and costs a migration on every live site.

## Front-end build

```
src/scss/
  _tokens.scss      Guide design tokens — the rationale is in the file
  app.scss          Front end: Bulma imported piece by piece + the Guide layer
  admin.scss        Console: Bulma's theme layer only + console components
  admin-skin.scss   wp-admin colour skin (no resets — loads everywhere)
  login.scss        Login screen
```

Bulma is **not** imported wholesale. The full framework is 662 KB minified,
most of it the colour-helper matrix and layout components the theme replaces.
`app.scss` documents every exclusion.

Components read `--bulma-*` custom properties rather than SCSS colour values,
so light and dark work from one set of rules. Bands that are dark *by design*
(course hero, CTA, code blocks) use fixed `--guide-inverse-*` tokens instead —
pointing them at `scheme-invert` renders dark-on-dark once the page is dark.

The console stylesheet is scoped to `.guide-admin` by PostCSS. Without that,
Bulma's reset would restyle the admin menu and every other plugin's pages.

Compiled CSS is committed, so production needs no Node.

## REST surface

All routes under `guide/v1`:

| Route | Purpose |
|---|---|
| `POST /enroll` | Enroll in a course the user may take |
| `POST /subscribe` | Start a subscription checkout |
| `POST /dodo-webhook` | Payment provider callback |
| `POST\|DELETE /lessons/{id}/complete` | Progress |
| `GET /lessons/{id}/quiz` | Questions, without answers |
| `POST /lessons/{id}/quiz/grade` | Server-side grading |
| `POST /stories` | Submit a success story (always pending) |
| `POST /account/profile` | Update own display name and bio |
| `GET /analytics/*` | Console figures (admin only) |

## Adding a feature

1. Behaviour and data go in the plugin, in their own `includes/` folder.
2. Register it in `guide-lms.php` — requires, then boot.
3. If it needs a table, add it to both the activation hook and the upgrade
   routine, or existing installs never get it.
4. Presentation goes in the theme, reading from the plugin.
5. Styles go in a partial under `src/scss/`, wired into `app.scss`.
6. Run `npm run build` and commit the compiled CSS.
