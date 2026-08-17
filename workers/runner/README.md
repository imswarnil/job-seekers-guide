# The Java runner

The site itself is static files with no origin. This Worker is the one
deliberate exception, and it exists for exactly one component: `::runner{lang="java"}`.

Everything else runs in the reader's tab. HTML, CSS and JavaScript go into a
sandboxed iframe; Python and SQL are WebAssembly loaded from a CDN at click time.
Java cannot: the browser JVMs are multiple megabytes and none of them give you
`javac`, and half the teaching value of a Java runner is the compile error.

If this Worker is not deployed, the Java runner shows a clear message and the
rest of every lesson keeps working.

## Why a Worker and not the public API directly

Calling `emkc.org/api/v2/piston` from the page would be less code and worse in
four specific ways:

- The public instance is rate-limited globally. One learner in a loop gets every
  learner blocked, and there is nothing you can do about it from a static site.
- A private backend needs a key, and a key in a static bundle is not a key.
- Readers' IP addresses go to a third party on every run.
- No caching. Every reader of a lesson runs the same starter code, so the cache
  hit rate here is enormous — this is the single biggest reason the Worker pays
  for itself.

## Deploy

```bash
cd workers/runner
npx wrangler kv namespace create RUNNER_CACHE   # paste the id into wrangler.jsonc
npx wrangler deploy
```

Point `ENDPOINT` in `app/utils/runners/java.ts` at the deployed URL.

To move off public Piston later — the day rate limits or latency start to
matter — set `PISTON_URL` and `PISTON_TOKEN` and redeploy the Worker. The site
does not change.

```bash
npx wrangler secret put PISTON_TOKEN
```

## What it does

| | |
| --- | --- |
| CORS | Locked to `ALLOWED_ORIGINS`. |
| Rate limit | 20 executions per IP per minute, fixed window in KV. Cache hits are not counted — there is nothing to ration about a KV read. |
| Cache | `SHA-256(language + version + source + stdin)` → result, 30 days. |
| Size limit | 24 KB of source. |
| Timeouts | 10s compile, 5s run. |
| Compile errors | Returned as `stderr` with `ok: false`, not as a 500. They are the lesson. |
