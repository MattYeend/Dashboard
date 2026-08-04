---
title: Frontend Vue Patterns
# group: Guides              # Bucket this page sits under in the sidebar
order: 7
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

# Frontend: Vue Patterns

## Component composition

Every page keeps as little markup as possible in the top-level page component; pages compose smaller domain components. For a `Users` module:

```
resources/js/pages/Users/
    Index.vue
    Create.vue
    Edit.vue
    Show.vue
    components/
        UserForm.vue              (used by both Create.vue and Edit.vue)
        UserBasicDetailsForm.vue
        UserRoleDetailsForm.vue
        UserDateDetailsForm.vue
        UserBasicDetails.vue      (used by Show.vue)
        UserRoleDetails.vue
        UserDateDetails.vue
```

- `Create.vue` and `Edit.vue` both render `UserForm.vue`. They differ only in the data passed in and the submit target, not in markup.
- `UserForm.vue` itself is a thin composer of `UserBasicDetailsForm.vue`, `UserRoleDetailsForm.vue`, and `UserDateDetailsForm.vue`, each handling one logical group of fields.
- `Show.vue` similarly composes `UserBasicDetails.vue`, `UserRoleDetails.vue`, `UserDateDetails.vue`, plus the shared `{Model}AuditDetails` component (see [Audit Logging](04-audit-logging.md)).

This pattern applies to every module: Tasks, Orders, Contacts, Companies, Posts, etc.

## Shared components (used across all modules)

| Component | Purpose |
|---|---|
| `ResourceTable.vue` | Generic (`T extends { id }`) table with `columns`, `selectable`, `v-model:selected`, named `cell-{key}` slots, `bulk-actions`/`actions` slots |
| `ConfirmDialog.vue` | Replaces native `confirm()` for delete/restore/force-delete confirmations |
| `FilterBar.vue` | Search, trashed filter, `sort_by`/`sort_direction` fields |
| `IndexHeader.vue` | Title, create href, create label, `can-create` flag |
| `Pagination.vue` | Renders `meta`/`links` plus a resource label |
| `InputError.vue` | Renders a form field's validation error |

## Form conventions

- Built from shadcn/ui primitives: `Input`, `Label`, `Select`/`SelectContent`/`SelectItem`/`SelectTrigger`/`SelectValue`, `Textarea`.
- One `defineModel` **per field**, kebab-case, rather than a single object model:

```vue
<script setup lang="ts">
const name = defineModel<string>('name');
const industryId = defineModel<number | null>('industry-id');
</script>
```

- Form-only interfaces (e.g. `CompanyFormData`) are declared **locally inside the form component**, not added to `types/index.ts` (see [Frontend Types](08-frontend-types.md) for why).
- Before submit, values pass through `@/lib/forms` helpers in `form.transform()`:

```ts
import { nullIfBlank, numberOrNull } from '@/lib/forms';

form.transform((data) => ({
    ...data,
    industry_id: numberOrNull(data.industry_id),
    notes: nullIfBlank(data.notes),
}));
```

## Show page conventions

Detail cards use a `dl`/`dt`/`dd` grid layout, not tables. Dark theme only:

- Text: `text-gray-300` / `text-gray-400`
- Dividers: `border-gray-500`
- **No background-colour utility classes** on any Vue file

## Slugs

Slug fields are **not** manual inputs on Create/Edit forms. A shared `SlugService::generateUnique()` auto-generates a unique slug server-side, and the slug is displayed read-only on Show/Index pages only.

## Route helpers in components

Import per-resource, typed Wayfinder helpers rather than `route()`:

```ts
import { index as companiesIndex } from '@/routes/companies';
import { bulkDelete as companiesBulkDelete } from '@/routes/companies/bulk';
```

## Prop casing

Props passed from Inertia controllers to Vue pages are always camelCase. This is the enforced convention between PHP and Vue, even though PHP itself uses snake_case internally.