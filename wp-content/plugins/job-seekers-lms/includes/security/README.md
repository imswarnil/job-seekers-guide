# Security

Standing rules applied across every module (not a bolt-on):
- All DB access via `$wpdb->prepare()`
- All output escaped (`esc_html()`, `esc_attr()`, etc.)
- Nonce + capability checks on every state-changing action
- Secrets only via WP options / environment, never hardcoded or committed

Planned additions: security headers, disabled XML-RPC, login attempt rate
limiting. See TODO.md section 7.
