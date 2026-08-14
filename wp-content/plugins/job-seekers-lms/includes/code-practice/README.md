# Code Practice

Planned: in-browser code sandboxes embedded in lessons.

- **JS** — sandboxed iframe execution, client-side only.
- **SQL** — `sql.js` (SQLite compiled to WASM), client-side only.
- **Java** — deferred. Safe arbitrary Java execution needs either a WASM JVM
  or real server-side sandboxing (isolated containers, timeouts, resource
  limits); building that casually would work against the project's security
  goals. Revisit as an optional pluggable integration later.

See TODO.md section 5.
