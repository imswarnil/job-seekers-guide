=== Guide LMS ===
Contributors: imswarnil
Tags: lms, courses, education, learning, sponsorship
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A course platform for teaching people to get their first software job: courses, learning paths, progress, subscriptions, company guides and sponsorship.

== Description ==

Guide LMS is the engine behind a platform built for one audience: graduates who
cannot get a first software job, and who have been sold expensive training they
did not need.

It is opinionated in ways that follow from that.

* **Courses, sections and lessons are loosely coupled.** A section can appear in
  several courses; a lesson can be borrowed by another course or a learning
  path without being duplicated. Serving a new kind of learner should mean
  composing a new path, never a deployment.
* **One subscription, no per-course prices.** Courses are free or included.
  Being asked to make a purchasing decision at every step of a path is the
  paralysis the platform exists to remove.
* **Access control lives in one place.** Every surface — template, REST, feed —
  asks the same gatekeeper, so they cannot disagree about who may read what.
  Quiz answers never leave the server.
* **Sponsorship as a first-class module,** with a review and payment workflow,
  per-slot analytics, and creative that locks on approval.
* **Ads that behave.** Never inside lesson content, never on the account or
  sign-in pages, never for subscribers or staff, always labelled — and a paying
  sponsor always outranks the ad network for the same space.

= Also included =

* Learning paths with their own player
* Company guides ("how to get a job at X") with structured data
* Success stories with moderation and salary bands
* Help centre, changelog and a roadmap with voting
* Branded transactional email
* Brute-force and bot protection on sign-in, without a third-party CAPTCHA

== Installation ==

1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Install a compatible theme. Guide WP Theme is the companion.
3. Open **LMS → Settings** for payments, ads and sign-in.

Course and lesson URLs need pretty permalinks. If they 404 after activation,
visit **Settings → Permalinks** and save once.

== Frequently Asked Questions ==

= Does it require the companion theme? =

No. The plugin renders through whatever theme is active and falls back to plain
output when a template is missing. The companion theme provides the course
reader, the lesson player and the sponsorship layout.

= Can I sell individual courses? =

Deliberately not. There is one subscription. See the description.

= Does it phone home? =

No. The only outbound requests are the payment provider you configure, the
AdSense script when you enable it, and fetching a company logo when you ask it
to.

== Changelog ==

= 1.0.0 =
* First stable release.
* Starter content: the courses ship with the plugin and are planted on upgrade,
  never overwriting anything an operator has edited.
* House ad for unsold sponsorship slots, and an AdSense default unit so one
  responsive slot ID is enough to start.
* Branded email for the learner journey, stories and sponsorship.
* Learner profiles: picture, links, and resetting your own progress.
* Sign-in hardening: honeypot, form timing, per-IP throttling with a growing
  lockout, and application passwords disabled.
