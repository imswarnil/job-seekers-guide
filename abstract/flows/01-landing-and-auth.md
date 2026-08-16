# Landing + Sign up / Sign in

## Landing page

The landing page's only job is to make a stranger believe two things:

1. **This is for me** — "you graduated and didn't get a job" said out loud.
2. **This is free, and here's why that's credible** — because the content was
   always free; what we add is the order.

Sections, in order:

| Section | Content |
|---|---|
| Hero | The promise, one line, plus "Start free — no card, no fees" |
| The problem | The ₹80,000 institute selling free YouTube content |
| The story | 33 rejections → ₹1.8 LPA → Accenture in 3 months (short form) |
| The path | The 10 modules, visually, end to end |
| Who it's for | The four personas, as cards |
| Wall of success | Real learner stories (already built) |
| FAQ | "Is it really free?" "I'm not from CS." "I'm 30." "How long?" |
| CTA | Create your free account |

## Sign up

Two routes, both first-class:

- **Google** — one tap, verified email only (already implemented)
- **Email + password** — for users without a Google account

Asked at sign-up: **email, password, and nothing else.** Name, photo, and
education come in the wizard, where there's context for why we're asking.

## Sign in

Standard, plus:
- Sign-in redirects to **the dashboard** if onboarding is complete,
  **to the wizard step they abandoned** if not.
- Generic error messages (no user enumeration) — already hardened.

## The sidebar, from the very first authenticated screen

The moment a user is signed in, the persistent sidebar appears and stays for the
rest of the product. On the wizard it explains the flow; on the dashboard it
becomes navigation.

Sidebar content during onboarding:

```
┌─────────────────────────────┐
│  HOW THIS PLATFORM WORKS    │
│                             │
│  ● 1 About you              │
│  ● 2 Your education         │
│  ▶ 3 Where you are now      │  ← current, expanded
│      We ask so we know      │
│      where to start you.    │
│      Nothing here is a      │
│      test. You can change   │
│      it any time.           │
│  ○ 4 Where you want to go   │
│  ○ 5 Foundation subjects    │
│  ○ 6 Your language          │
│  ○ 7 Your track             │
│  ○ 8 Your path              │
│                             │
│  ── YOUR PATH SO FAR ──     │
│  0 How IT Works             │
│  1 Foundations              │
│  …                          │
└─────────────────────────────┘
```

## Guest access

Course listings, the curriculum outline, and success stories are readable
without an account. **A path cannot be assembled without signing in** — that's
the value exchange, and it's a fair one.
