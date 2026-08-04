---
title: Plans Module
# group: Guides              # Bucket this page sits under in the sidebar
order: 10
# description: A short summary used for <meta> tags and SEO
# slug: custom-url           # Override the URL slug (defaults to the file path)
# hidden: true               # Hide from the sidebar and listings
# badge: New                 # Small label shown next to the title in the sidebar
# icon: book                 # Icon name (consumed by your views/macros)
# tags: [intro, basics]      # Free-form tags
# updated_at: 2026-01-01     # Shown in the page footer when set
# author: Jane Doe
# layout: docs               # Override the Blade layout used to render this page
# image: /img/social.png     # Social/OG image
# redirect: /docs/other      # Permanent redirect to another URL
---

# Plans Module (Stripe / Cashier)
 
## Overview
 
Subscription billing built on Laravel Cashier, integrated with Stripe. The billable entity is `User` (via the `Billable` trait on the `User` model, see [Example Models](11-example-models.md)), not `Company`.
 
## Components
 
- **`Plan` model**: full CRUD, standard resource scaffold plus `PlanSeeder`.
- **`Subscription` model**: extends Cashier's base `Subscription` model, allowing project-specific relations/accessors to be added without touching Cashier internals.
- **`StripeService`**: wraps outbound calls to the Stripe API (creating customers, checkout sessions, updating subscriptions).
- **`SubscriptionCreatorService`**: creates the subscription record; the billable entity passed to Cashier is `User`, not `Company`. Any code that assumes `Company` is billable by default is working against the grain of the current model setup.
- **`StripeWebhookController`**: receives and processes Stripe webhook events.
## Webhook handling: JSON, not Inertia
 
Stripe webhook requests are plain HTTP POSTs from Stripe's servers, not browser requests, so they do not go through Inertia. The global exception handler was previously rendering Inertia HTML error pages for webhook failures, which Stripe's webhook consumer can't parse, surfacing as unexplained 500s. The webhook route is excluded from Inertia's response handling so failures return plain JSON, which Stripe can log and retry against correctly.
 
## Seeder idempotency
 
`PlanSeeder` seeds real plan data (see [CLI Reference](09-cli-reference.md), no `fake()`), and is written to be **idempotent**: re-running it should not create duplicate Stripe products/prices or duplicate local `Plan` rows. Use `updateOrCreate()` keyed on a stable external identifier (e.g. Stripe price ID) rather than `create()`.
 
## Boolean casting for the Stripe API
 
Stripe's API expects specific boolean representations in some fields; casting a PHP `bool` naively when building the request payload has previously caused silent acceptance/rejection issues. When sending booleans to Stripe, cast explicitly at the point of building the API payload rather than relying on implicit casting through the model.
 
## Where this diverges from the standard module pattern
 
- Billable entity is `User`, and this should be explicit everywhere in the Plans module; never assume `Company`.
- No soft-delete/restore semantics apply to `Subscription` the way they do to standard resources. Subscription lifecycle (active, cancelled, on grace period) is managed by Cashier and Stripe status, not local `deleted_at`.
- Webhook routes are the one place in the app that intentionally bypass the standard Inertia response flow.
 