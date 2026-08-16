# Help Centre — running the platform

The operator's manual. Written for whoever is running the site, which for now
is one person doing it around a full-time job, so it favours "here is the one
way that works" over exhaustive options.

---

## 1. Where everything lives

| I want to… | Go to |
|---|---|
| Write or restructure a course | **LMS → Dashboard → Courses** |
| Write a lesson | Open the course, click a lesson title |
| Build a learning path | **LMS → Learning Paths** |
| Approve a learner's story | **LMS → Stories** |
| See who is learning what | **LMS → Learners** |
| Change payments, ads, sign-in, SEO | **LMS → Settings** |
| Check the site is healthy | **Tools → Site Health** |
| Install updates | **LMS → Updates** (only appears when there are some) |

Posts and Comments are removed from the admin menu — this is an LMS, not a
blog. Everything authored here is a course, a lesson, or a path.

---

## 2. Creating a course

1. **LMS → Courses → "New course title…" → Create course.** It starts as a
   draft, so nothing is public until you say so.
2. On the course screen, set the **course code** (e.g. `CS-OS-101`). It shows
   on cards, on the generated placeholder art, and in structured data.
3. Add **modules**, then **lessons** inside them. Drag to reorder; lessons can
   be dragged between modules.
4. Click **Edit details & description** for the image, level, "what you'll
   learn", requirements, and the access tier.
5. **Publish** when it is ready.

### Access tier

Every course is one of two things:

- **Free** — open to everyone, signed in or not.
- **Members** — included in the platform subscription.

There is no per-course price and there is no way to set one. The platform sells
exactly one thing: a subscription. This is deliberate — see
[`../abstract/01-abstract.md`](../abstract/01-abstract.md). A learner being
asked to make a purchasing decision at every step of a path is the paralysis
the whole project exists to remove.

**Keep the core path free.** Orientation, foundations, one language, projects
and the job-search module are the promise. Premium is for what goes beyond it.

---

## 3. Writing a lesson

Click a lesson title in the builder to open the writing drawer.

- **Article** — the default. The editor is a small rich-text field: headings,
  bold, italic, lists, quotes, links, code, and images from the media library.
- **Video** — paste a YouTube/Vimeo/mp4 URL. You can set a **start and end
  time** to clip it, which is how curated lessons stay tight. Nothing loads
  from the video host until the learner clicks play.
- **Quiz** — add questions and options, mark the correct one, set a pass mark.
  Answers are stored server-side and are never sent to the browser, so a
  learner cannot read them out of the page source.

Also on each lesson:

- **Duration** — used for the "57 min total" figures and the sidebar.
- **Free preview** — opens this one lesson even inside a members course. Use it
  as the sample chapter.

### House style

See [`../abstract/curriculum/README.md`](../abstract/curriculum/README.md). In
short: why before what, show before you explain, say plainly what to skip, end
with a check, and credit anything you curate.

---

## 4. Learning paths

A path is an ordered list of milestones. Each is either a whole course or a
standalone article/video/quiz.

Build it in **LMS → Learning Paths**: drag from the library into the path,
reorder, publish. A learner sees it as a numbered timeline.

Paths are data, not code — serving a new kind of learner should mean composing
a new path, never a deployment.

---

## 5. Subscriptions and payments

**LMS → Settings → Payments** and **→ Subscription**.

1. Create a **recurring product** in Dodo Payments.
2. Paste its product ID into the Subscription tab, set the price label and
   period, and enable it.
3. In the Payments tab, add the API key and webhook secret, and register the
   webhook URL shown on that screen in Dodo → Developer → Webhooks.
4. Subscribe to `payment.succeeded` and every `subscription.*` event.

Start in **Test** mode and take a real test checkout end to end before
switching to Live.

### What a learner sees

- **/account/** — plan status, billing history, editable profile.
- **/account/receipt/{id}/** — a printable receipt for each payment.

Receipts are the site's own record. The payment provider's invoice is the
formal document, and every receipt carries the provider's reference so support
questions can be answered without guessing.

If the provider does not send an amount with an event, the receipt shows `—`
rather than inventing `0.00`.

---

## 6. Ads

**LMS → Settings → Ads.**

Ads are how the free tier stays free. They are also the thing most likely to
make a learning site feel cheap, so the rules are strict and enforced in code:

- Subscribers and staff **never** see one, and the AdSense script is not even
  loaded for them.
- Never inside lesson content. Slots sit below it.
- Never on the account, sign-in, or checkout pages.
- Always labelled "Advertisement".

Setup: enable, paste your `ca-pub-…` publisher ID, and add the two slot IDs.
Ads stay off until the publisher ID is filled in.

**Use test mode while checking placement.** Clicking your own live ads will get
the AdSense account banned.

---

## 7. Stories

Learners submit at `/success/`. Every story arrives as **pending** and appears
publicly only after you approve it in **LMS → Stories**.

Read them properly before approving. A story that skips the rejections is less
useful than one that includes them — the whole point of the wall is that
somebody at rejection number twelve reads it and keeps going.

---

## 8. Routine maintenance

| How often | Do this |
|---|---|
| Weekly | Clear the Stories queue |
| Weekly | Install updates (**LMS → Updates**) |
| Monthly | **Tools → Site Health** — resolve anything critical |
| Monthly | Check **LMS → Dashboard** for courses nobody finishes |
| Before any big change | Take a database backup |

### Backups

```bash
docker compose -f docker/docker-compose.yml exec db \
  mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" wordpress > backup-$(date +%F).sql
```

`wp-content/uploads` needs backing up too — it is not in git.

---

## 9. Troubleshooting

**A page 404s that should not.**
Settings → Permalinks → Save. That flushes rewrite rules. Most "the URL
stopped working" reports are this.

**Styles look broken after a deploy.**
The compiled CSS is committed. If you changed SCSS without running
`npm run build`, the change is not in the file the site serves.

**The console is unstyled.**
Its stylesheet is scoped to `.guide-admin`. If that wrapper is missing from the
markup, nothing applies.

**A learner says they paid but has no access.**
Check **LMS → Learners** for their grant, then their `/account/` billing
history. If the payment is in the provider but not in the billing history, the
webhook did not arrive — check the webhook secret and the registered URL.

**Someone is stuck in a redirect loop on wp-admin.**
Learners (subscribers) are deliberately sent to `/my-learning/`. If it is
happening to an author, their role lost the `edit_posts` capability.

---

## 10. Security

Already enforced by the plugin, listed so you know not to undo it:

- XML-RPC returns 403
- User enumeration blocked (`?author=` and `wp/v2/users`)
- Generic login errors — no "that username exists"
- File editing disabled in wp-admin
- Version strings stripped from the markup
- Security headers sent on every response
- Quiz answers never leave the server
- Lesson content stripped server-side for anyone without access, in the
  template *and* over REST

Your part:

- Keep WordPress and PHP current
- Use a password manager for the admin account
- Never paste the Dodo API key anywhere but the settings screen
- Prefer `GUIDE_GOOGLE_CLIENT_SECRET` in `wp-config.php` over storing the
  Google secret in the database
