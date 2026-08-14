# Payments (Dodo Payments)

Planned: settings page for Dodo API keys (stored via WP options, never
committed), checkout flow, and a REST webhook endpoint that verifies Dodo's
signature and fires the `jsl_payment_confirmed` action for the enrollment
module to consume.

Not yet implemented — needs a research pass against Dodo Payments' current
API/webhook docs before building. See TODO.md section 4.
