# Changelog

Notable changes to Guide LMS and Guide WP Theme.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions are shared between the plugin and the theme — they ship together.

---

## [Unreleased]

### Added
- Learner comments on lessons and stories
- Community resource library with submission and moderation
- "My Story" long-form page
- Aptitude and written-test preparation in the curriculum

---

## [0.8.0] — 2026-08-16

The pricing model changed, and the money surfaces a learner needs came with it.

### Changed
- **Courses no longer have individual prices.** The platform sells exactly one
  thing: a subscription. `Course_Pricing` (free/paid + product ID + price
  label) is replaced by `Course_Access` (free/premium). Per-course checkout is
  removed.

  A purchase decision at every step of a learning path is the paralysis the
  project exists to remove.
- The LMS console's course drawer now offers **Free / Members**, not a price.
- `POST guide/v1/enroll` no longer returns a checkout URL. For a members-only
  course it answers `402` with `needsSubscription`, and the browser hands over
  to the subscribe flow.
- wp-admin keeps **Tools → Site Health** and **Export**, which were previously
  removed along with the rest of the Tools menu. On a self-hosted site those
  are the two screens you least want hidden.
- Pending updates now appear as **LMS → Updates**, because the Dashboard —
  where an admin would normally notice them — redirects to the console.

### Added
- **Ads.** AdSense units for visitors without a subscription. Subscribers and
  staff never see one and never load the script. Never inside lesson content,
  never on account or sign-in pages, always labelled. Configured under
  **LMS → Settings → Ads**, with a test-unit toggle.
- **Account area** at `/account/` — plan status, billing history, editable
  profile (display name and bio).
- **Receipts** at `/account/receipt/{id}/` — printable, scoped to their owner.
- **`wp_jsl_payments`** table. The enrollments table records what access
  someone has and says nothing about what they were charged, so receipts had
  nothing real to show. The webhook writes a row before granting, keyed
  uniquely on the provider's payment id so a redelivered webhook cannot
  produce two receipts.
- `docs/` — help centre, changelog, roadmap.

### Fixed
- **The LMS console rendered unstyled.** Its stylesheet had been written
  against class names the console app does not use. It now covers all 128
  classes the console actually emits.
- Console toasts, drawers and scrims were appended to `<body>`, outside the
  scoped stylesheet, so they rendered unstyled too. They now mount inside the
  scope root.

### Compatibility
- No data migration. The meta key stays `jsl_pricing_type`; the legacy value
  `paid` reads as `premium`.
- Course-scoped grants from the old per-course model are still honoured, so
  nobody loses access to something they bought.
- The webhook's per-course branch is kept, so a payment started before the
  change still grants on arrival.

---

## [0.7.0] — 2026-08-16

A rename, a new UI framework, and the project's thinking written down.

### Changed
- **Renamed for distribution.** `job-seekers-theme` → `guide-wp-theme`,
  `job-seekers-lms` → `guide-lms`. Namespace `JSL\` → `Guide\`, constants,
  text domains, REST namespace `jsl/v1` → `guide/v1`, JS globals.

  The database deliberately did **not** move: the `wp_jsl_*` tables and all 90
  `jsl_*` meta and option keys are untouched, so an existing site upgrades with
  zero migration risk.
- **Rebuilt the UI on Bulma 1.x.** Tailwind v4 and the Material 3 layer are
  gone. Bulma is imported piece by piece — the full framework is 662 KB
  minified and most of it is unused — and the build ships **28 KB brotli**.

  Design tokens feed Bulma's SCSS configuration, and components read the
  generated `--bulma-*` custom properties, so light and dark work from one set
  of rules off Bulma's native `[data-theme]` switch.
- The admin console gets real Bulma, scoped under `.guide-admin` by PostCSS so
  it cannot reach the admin menu, core settings screens, or other plugins'
  pages. wp-admin and the login screen get colour-and-type skins only.
- All 20 theme templates rewritten.

### Added
- **`abstract/`** — the project's thinking layer. Problem, motive, founder
  journey, ten principles, seven personas, the eight-screen onboarding flow,
  the ten-module curriculum, and machine-readable seed data.
- **Course catalogue filter rail** — subject tree with progressive disclosure
  (pick "Programming Languages", get the languages), plus level, price and
  search. Implemented as GET parameters on the main query, so every filtered
  view has a shareable URL and works without JavaScript.
- `ui.js` replacing `md3.js`: snackbar with undo, dropdowns, drawers, tabs.

### Fixed
- Icons rendered at their full SVG size once the utility classes that sized
  them were removed.
- "Sign in" appeared twice in the header.
- Dark heroes and code blocks rendered dark-on-dark in dark mode — they used
  theme-following surfaces where they needed fixed inverse tokens.

### Compatibility
- `JSL_GOOGLE_CLIENT_SECRET` in `wp-config.php` is still honoured, so sign-in
  does not break on upgrade. `GUIDE_GOOGLE_CLIENT_SECRET` is the current name.

---

## [0.6.0] and earlier

Built before this changelog started. In summary: the custom LMS itself —
courses, lessons, learning paths, the visual course builder, enrollment and
progress tracking, the quiz engine, Google sign-in, the PWA, success stories,
the leaderboard, JSON-LD, and the security hardening pass.

See `TODO.md` for the full picture of what exists.
