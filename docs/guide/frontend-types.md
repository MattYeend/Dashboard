---
title: Frontend Types
# group: Guides              # Bucket this page sits under in the sidebar
order: 8
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

# Frontend: TypeScript Types

## `types/index.ts`

Re-exports domain groupings and declares the **model** interfaces shared across the app:

```ts
export * from './auth';
export * from './navigation';
export * from './ui';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    role: 'user' | 'admin' | 'super_admin';
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
```

## What belongs in `types/index.ts`

- The **main model shape** as it comes back from the backend (matches the model's public/API-visible attributes plus standard audit relations: `creator`, `updater`, `deleter`, `restorer`).
- Small **option** shapes used for dropdowns (e.g. `UserOption { id: number; name: string }`).
- Cross-cutting shared shapes: `Pagination`, `PermissionsMeta`, `AuthUser`.

## What does **not** belong in `types/index.ts`

- **Form data.** `{Model}FormData` interfaces are declared locally inside the relevant form component (see [Frontend: Vue Patterns](07-frontend-vue-patterns.md)), because form shape is a frontend-only concern that often diverges from the persisted model shape (e.g. a form field might be a string that gets transformed to `number | null` before submit). Keeping it local avoids `types/index.ts` becoming a dumping ground for every form variant across every module.

## Standard audit fields

Every audited model's interface includes the same block:

```ts
created_by: number | null;
updated_by: number | null;
deleted_by: number | null;
restored_by: number | null;
restored_at: string | null;
created_at: string;
updated_at: string;
deleted_at: string | null;
creator?: { name: string };
updater?: { name: string };
deleter?: { name: string };
restorer?: { name: string };
```

This mirrors the backend's `Auditable` contract (see [Audit Logging](04-audit-logging.md)): if a model implements `auditSnapshot()`, its frontend interface should include this block.

## Polymorphic relation shapes

Polymorphic models (e.g. `Contact`, which can belong to a `User` or a `Company` via `contactable`) expose the morph columns directly on the interface:

```ts
export interface Contact {
    id: number;
    contactable_id: number;
    contactable_type: string;
    // ...
}
```