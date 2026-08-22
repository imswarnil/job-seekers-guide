# Next steps

Written 22 August 2026, at commit `e2a6875`. Everything below was found while
getting icons, AdSense and the legal pages shipped. Nothing here is in progress:
it is all either waiting on a decision from you, or small enough that it never
justified interrupting the work in front of it.

Ordered by what costs you most to leave alone.

---

## 1. Broken now

### The footer GitHub button goes to github.com

`app/components/AppFooter.vue:90` hardcodes `to="https://github.com"`. That is
GitHub's homepage, not the repository. Every reader who clicks the icon in the
footer lands nowhere useful, and the whole argument that this site is
correctable by anybody depends on that link working.

The value it should use already exists: `repoUrl` in `app/utils/links.ts`, which
is `https://github.com/imswarnil/job-seekers-guide`. It is what the header star
and every "edit this page" link already use.

```
- to="https://github.com"
+ :to="repoUrl"
```

### `brand.github` is a placeholder nothing reads

`app/app.config.ts:75` sets `github: 'https://github.com'`, described as "the
repository the content is authored in". Nothing in the codebase reads it. So it
is a dead setting holding a wrong value, sitting in the file Studio surfaces as
editable controls.

Either point it at `repoUrl` and make the footer read it, or delete it. Leaving
a field that looks configurable but is ignored is the worse option.

### A hydration mismatch on lessons with a `::code-trace`

The dev server logs this on `/orientation/how-this-course-works/how-to-read-these-lessons`:

```
Hydration children mismatch on {}
Server rendered element contains more child nodes than client vdom.
  at <DgmFigure ... > at <CodeTrace ... >
```

It is not fatal and the page looks right, but a hydration mismatch means Vue
throws away the server-rendered subtree and re-renders it on the client. On a
component as heavy as a code walkthrough that is wasted work on every lesson
that uses one, and mismatches have a habit of turning into visible bugs later.

Reproduce with `pnpm dev` and open that lesson. The suspect is `CodeTrace`
rendering something on the server it does not render on the client, or a
`<ClientOnly>` boundary in the wrong place.

---

## 2. AdSense follow-through

Ads are live and filling. Publisher `ca-pub-1291242080282540`. Three slots run
(`in-article`, `lesson-footer`, `sidebar`) plus the repeating `in-feed` one.

### Check `ads.txt` was picked up

`https://jobseekers.imswarnil.com/ads.txt` serves correctly, but Google crawls it
on its own schedule. Look in AdSense for "earnings at risk" clearing within a day
or two. If it has not cleared after a week, the file is being served but not
matched, and the publisher id in it has drifted from `ads.client`.

### Watch for a policy warning on ad density

An ad every three paragraphs is a real choice, not a neutral default. The guards
in `app/utils/autoAds.ts` cap it at four per page and skip anything under six
paragraphs, which is what keeps a long lesson from becoming a stack of adverts.

If AdSense ever flags "ad-heavy pages", the dial is `ads.autoInsert` in
`app/app.config.ts` — lower `max`, or raise `afterParagraphs`. Do not reach for
`max: 0` unless you have decided to accept the risk; that removes the ceiling.

### Two slots are configured but switched off

`rail-bottom` and `path-parallax` have unit ids and are one boolean away in
`ads.slots`. They are off for the reasons written beside them in
`app/utils/ads.ts`: the rail is navigation, and the parallax band is the single
most likely thing to make the site feel cheap. If you turn either on, look at it
on a phone before you push.

### Debugging a slot that renders nothing

`ads.showPlaceholders` is now `false`, so a slot with a missing or wrong unit id
renders nothing at all rather than announcing itself. Set it back to `true`
locally and the slot draws a labelled dashed box with its dimensions, which is
how you tell "no unit id" apart from "no ad inventory".

---

## 3. Decisions only you can make

### The licence

`/terms` says the material is free to read, share with attribution, and not to
resell — in plain English, and it says outright that this is a statement of
intent rather than a formal licence.

If you want it enforceable, add a `LICENSE` file to the repository and link it
from that page. **CC BY-NC-SA 4.0** matches what is currently written. This is
worth doing before somebody repackages the course, not after.

### Governing law

`/terms` deliberately does not name a country. You live in Europe, the site is
aimed at India, and it is hosted in the US. Guessing would have been worse than
silence, but it is a real gap if a dispute ever matters.

### `/start` header alignment

The header now runs edge to edge on `/start`, but that page's content is still
in a container, so the logo sits outside the content column. On lessons the two
line up, because the lesson shell is genuinely wide.

It is defensible as-is — full-width header over contained content is a common
pattern. But if it bothers you, the fix is widening the `UContainer` in
`app/pages/start.vue`. That changes the reading measure of the whole page, which
is why I did not do it unasked.

### The login and signup pages

`/login` and `/signup` are Nuxt UI scaffolding. They validate a form and then
call `console.log`. Nothing is sent anywhere, which the privacy page says out
loud because a form that looks like it collects an email should not be left
ambiguous.

They are disallowed in `robots.txt`, so they are not an SEO problem. They are a
trust problem: a reader who fills one in gets nothing and no explanation beyond
the note in the description. Either build accounts or delete the pages.

### The newsletter

`newsletter.action` is empty, so the footer form renders disabled. Deliberate,
and better than quietly collecting addresses for a list that does not exist. The
privacy page promises it will name the provider before it takes a single
address — keep that promise if you switch it on.

---

## 4. Upstream problems worth revisiting

### The `@nuxt/icon` bug is worked around, not fixed

`@nuxt/icon` 2.5.0 hands Iconify `useRequestFetch().native`, which is `undefined`
on the server under Nuxt 4.5. Every icon failed to load server-side because of
it.

The site no longer cares, because icons are scanned and bundled at build time and
nothing is fetched at runtime — which is the right architecture for a static site
anyway. But the underlying bug is still there. If you upgrade `@nuxt/icon` and
want to know whether it is fixed, remove the `icon.clientBundle.scan` block in
`nuxt.config.ts` and watch for `[Icon] failed to load` in the dev log.

Worth reporting upstream if nobody has.

### `@nuxtjs/mdc` is a direct dependency for a reason

Nothing imports it. It is in `package.json` because it asks Vite to pre-bundle
ten of its own dependencies by name, and pnpm's node_modules only exposes what
you depend on directly. The comment at the top of `nuxt.config.ts` explains it.

**Keep the version in step with whatever `@nuxt/content` pulls in.** Currently
both are on 0.22.2. If they drift you get two copies and a confusing class of
bug.

### Two copies of `@nuxtjs/mdc` are installed

`nuxt-studio` wants `^0.21.1` and `@nuxt/content` wants `^0.22.2`, and those
ranges cannot merge. It works, it just costs disk and install time. It resolves
itself when `nuxt-studio` catches up.

### Icon scan globs are a maintenance edge

`nuxt.config.ts` scans `app/`, `modules/` and `content/` for icon names. If you
add a new top-level source directory with icons in it, add it to that list or
those icons silently do not render. `.navigation.yml` needs its own line because
the scanner skips dotfiles.

Same shape of edge in `ui.prose.codeIcon` in `app/app.config.ts`: a code block
whose filename has an extension not in that map renders a blank square, because
Nuxt UI's defaults all point at a `vscode-icons` collection this site does not
install.

---

## 5. Housekeeping in your working tree

Both predate this session and neither was mine to decide:

- **`to-do.md`** is deleted but not committed. `git restore to-do.md` brings it
  back if that was accidental.
- **`scripts/new-lesson.mjs`** is untracked. It looks deliberate — commit it or
  add it to `.gitignore`, but leaving it untracked means it is one `git clean`
  from gone.

---

## 6. Before you ship anything

The deploy workflow runs exactly these, and all three run locally:

```bash
pnpm lint
pnpm typecheck
pnpm generate
```

Two things this session taught the hard way, both worth keeping:

**Check the generated HTML, not the dev server.** The first version of the
repeating ads worked perfectly in dev and shipped a literal `<Ad>` tag that
nothing rendered, because Nuxt Content only bundles MDC components it can see
used in markdown. `grep` the output in `.output/public/` before you believe a
build.

**`pnpm typecheck` clobbers a running `pnpm dev`.** It regenerates `.nuxt` under
the dev server and every page starts 404ing. Nothing is broken; restart the dev
server.

Useful checks against a finished build:

```bash
# no icons falling back to a network fetch
grep -rl "api.iconify.design" .output/public --include='*.html'

# ads actually rendered, and no unresolved component tags
grep -rho 'ad-auto' .output/public --include='*.html' | wc -l
grep -rho '<Ad ' .output/public --include='*.html' | wc -l

# the crawler reached the whole path
find .output/public -name index.html | wc -l
```

The workflow already fails the deploy if fewer than 20 pages prerender, which is
the guard against a broken content query shipping an empty site. It was 91 pages
at the time of writing.
