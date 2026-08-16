=== Guide WP Theme ===
Contributors: imswarnil
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The companion theme for Guide LMS: a course reader built for people learning on
cheap phones and slow connections.

== Description ==

A classic (non-block) theme, built on Bulma and compiled from SCSS with the
Guide design tokens applied as Bulma variables.

* **A three-column lesson player** — course navigation, the lesson, and a
  sticky contents rail that follows the reader down the page. Below a wide
  screen the rail moves above the lesson and stops being sticky, because a
  sticky panel on a phone is a thing covering the text.
* **Five course header treatments**, all carrying the same information. Only
  the arrangement changes: a page whose one job is helping somebody decide
  whether to start should never trade a fact for a look.
* **Light, dark and automatic**, following the operating system by default.
* **Original artwork**, inlined so it inherits the brand colour and animates
  with CSS — and every animation sits behind `prefers-reduced-motion`.
* **No build step in production.** The minified stylesheet is committed.
* **No jQuery on the front end**, no emoji script, no block library, no oEmbed
  discovery. On a ₹8,000 phone every kilobyte is somebody's data allowance.

== Customizing ==

**Appearance → Customize → Guide theme**

* **Brand colours** — one colour regenerates the whole family, including dark
  mode, because Bulma derives every shade from hue, saturation and lightness.
* **Homepage hero** — eyebrow, headline, opening paragraph, both buttons.
* **Homepage sections** — turn off anything not earning its place.
* **Footer and links** — the footer line and your GitHub, LinkedIn and YouTube
  links. Each link is omitted entirely when blank.

Payments, ads, sponsorship and access control are the plugin's business and
live in **LMS → Settings**.

== Building the CSS ==

    npm install
    npm run build

`npm run build` compiles the front-end stylesheet, the admin console stylesheet
and the sign-in skin. The compiled files are committed, so a deployment never
needs Node.

== Changelog ==

= 1.0.0 =
* First stable release.
* Customizer support: brand colours, hero copy, homepage sections, footer.
* Lesson contents moved into a sticky third column with scroll-spy.
* House sponsorship card for unsold ad slots.
* Course header treatments, animated SVG illustrations, learner profiles.
