# Setup

Everything on this site runs without configuration except four things, and all
four need a secret you have to create yourself. This is the list, in the order
worth doing them.

Nothing here is committed. `.env` is gitignored — check with `git check-ignore .env`
if you ever doubt it. **Never paste a secret into `nuxt.config.ts`,
`app.config.ts`, or any file under `content/`.** Those are all public.

```bash
cp .env.example .env
```

---

## 1. Nuxt Studio — editing content in a browser

Studio is the visual editor for everything in `content/`. It lives at
`/_studio` on your own site — it is not a hosted service, so nothing leaves your
machine unless you publish.

> **The docs will send you wrong.** The setup page says
> `NUXT_STUDIO_AUTH_GITHUB_CLIENT_ID`. The module does not read that. The names
> below are the ones taken from the module source, and they are what works.

There are two ways in. Pick one.

### Option A — a personal access token (easiest, and right for you)

One person editing their own repo. No OAuth app, no callback URLs, two minutes.

1. Go to **[github.com/settings/personal-access-tokens](https://github.com/settings/personal-access-tokens)**
   → **Generate new token** → *Fine-grained token*.
2. Fill it in:

   | Field | Value |
   |---|---|
   | Token name | `job-seekers-guide-studio` |
   | Expiration | 90 days (you will be reminded; that is the point) |
   | Repository access | **Only select repositories** → `imswarnil/job-seekers-guide` |

3. Under **Repository permissions**, set **Contents** to **Read and write**.
   That is the only permission Studio needs — it reads your markdown and commits
   changes back. Leave everything else on *No access*.
4. **Generate token**, copy it. GitHub shows it once.
5. Put it in `.env`:

   ```
   STUDIO_GITHUB_TOKEN=github_pat_xxxxxxxxxxxx
   ```

That token is equivalent to your write access on this repo. If it leaks, revoke
it on the same page immediately — that is the whole remedy, and it works.

### Option B — a GitHub OAuth app

Do this instead if more than one person will ever edit, or if you later move to
a host that can run Studio in production. Each editor signs in as themselves and
commits under their own name.

1. Go to **[github.com/settings/developers](https://github.com/settings/developers)**
   → **New OAuth App**.
2. Fill it in:

   | Field | Value |
   |---|---|
   | Application name | `Job Seekers Guide Studio` |
   | Homepage URL | `http://localhost:4321` |
   | Authorization callback URL | `http://localhost:4321/__nuxt_studio/auth/github` |

   The callback path matters exactly. If you later run Studio on a real domain,
   add a second OAuth app with that domain in both fields.
3. **Register application**, then **Generate a new client secret**.
4. Put both in `.env`:

   ```
   STUDIO_GITHUB_CLIENT_ID=Iv1.xxxxxxxxxxxx
   STUDIO_GITHUB_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxx
   ```

### Opening it

```bash
pnpm dev
```

Then `http://localhost:4321/_studio`, or press `Cmd + .` from any page.

Edits write straight to the files in `content/` on your disk. They are ordinary
file changes — review them with `git diff` and commit them like anything else.

**If it says "No authentication provider found"**, neither of the above is set,
or the dev server was started before you edited `.env`. Restart it.

### Why it only works locally

Studio needs a server to run OAuth and to commit on request. This site is
prerendered to static files and served by GitHub Pages, which has no server —
so `/_studio` works on `localhost` and 404s on `jobseekers.imswarnil.com`.

That is not a bug to fix in config. Editing from the live site means moving the
host to something that runs a server. Cloudflare Workers would be the natural
choice, since the DNS is already there and `public/_redirects` is written in
Cloudflare's format.

---

## 2. The newsletter — currently inert

The sign-up form in the footer renders disabled and posts nowhere. It is
deliberately a dead control rather than a box that quietly swallows addresses.

To switch it on, put a provider's form endpoint in `app/app.config.ts`:

```ts
newsletter: {
  action: 'https://…'   // Buttondown, ConvertKit, Formspark, a Worker — anything that accepts a POST
}
```

It posts `{ email }` as JSON. This one goes in `app.config.ts` rather than
`.env` because a form endpoint is a public URL, not a secret.

---

## 3. Advertising — off

Ads are off site-wide (`ads.enabled: false` in `app/app.config.ts`), and the
reserved boxes still draw as labelled dashed placeholders so the layout stays
honest while you decide.

- To hide the boxes as well: set `ads.showPlaceholders: false`.

Which slots exist, and why each one is allowed to, is documented in
`app/utils/ads.ts`.

### Turning AdSense on

The code is written and does nothing until you supply the ids. In
`app/app.config.ts`:

1. `ads.client` — your publisher id, the `ca-pub-…` string. One per account.
2. `ads.slotIds` — the `data-ad-slot` number of an ad unit, per placement.
   These are **not** the publisher id and there is a different one for every
   unit, so create one unit in AdSense per placement you want filled. A
   placement left empty stays empty, which is how you run ads on some slots and
   not others.
3. `ads.provider: 'adsense'`.
4. `ads.enabled: true`.

Any slot missing an id draws the dashed placeholder instead of an empty box, so
a forgotten unit is visible rather than silent.

Two things Google requires before an account is approved, neither of which is
code:

- **A privacy policy** reachable from every page, saying that third parties set
  cookies and how a reader opts out. This site does not have one yet, and its
  absence is a common rejection reason.
- **Ownership of the domain**, verified through AdSense against
  `jobseekers.imswarnil.com`.

The script is requested lazily, only once a real unit is about to be filled, so
a reader who never scrolls to an ad never downloads it.

---

## 4. Deployment

Pushing to `main` builds and publishes automatically — see
`.github/workflows/deploy.yml`. No secrets are needed; GitHub Pages is wired
through the repository's own permissions.

The workflow runs four things, and **all four run locally with the same
commands**. Run them before pushing:

```bash
pnpm lint        # eslint over the whole project, nuxt.config.ts included
pnpm typecheck
pnpm generate
```

It then refuses to publish a build with fewer than 20 pages in it, which is the
guard against a broken content query shipping an empty site.

> Run `pnpm lint`, not `eslint app/`. The config file has its own rules — a
> deploy has already failed once on `nuxt/nuxt-config-keys-order` in
> `nuxt.config.ts`, which a narrowed lint does not see.

---

## Quick reference

| Secret | Where | Needed for |
|---|---|---|
| `STUDIO_GITHUB_TOKEN` | `.env` | Studio, option A |
| `STUDIO_GITHUB_CLIENT_ID` | `.env` | Studio, option B |
| `STUDIO_GITHUB_CLIENT_SECRET` | `.env` | Studio, option B |
| `newsletter.action` | `app/app.config.ts` | The sign-up form |
| `ads.client` | `app/app.config.ts` | AdSense publisher id |
| `ads.slotIds` | `app/app.config.ts` | AdSense, one unit id per placement |

`NUXT_PUBLIC_SITE_URL` is set by the deploy workflow and drives canonicals, the
sitemap and absolute social-card URLs. Override it for a preview environment
rather than editing `nuxt.config.ts`.
