---
title: Audit Logging
# group: Guides              # Bucket this page sits under in the sidebar
order: 4
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

# Audit Logging

Every create, update, delete, and restore is recorded via `AuditLogService` and a `Log` model.

## Components

- **`Auditable` contract**: implemented by every audited model; requires an `auditSnapshot()` method returning the fields that should be recorded.
- **`Log` model**: stores the action, model type, model id, actor, and before/after snapshot.
- **Action constants**: named `ACTION_CREATE_{MODEL}`, `ACTION_UPDATE_{MODEL}`, `ACTION_DELETE_{MODEL}`, `ACTION_RESTORE_{MODEL}` (e.g. `Log::ACTION_CREATE_POST`).
- **`AuditLogService`**: the single place that writes to the `Log` model; called from Creator/Updater/Deleter/Restorer services, never called directly from controllers.

## `auditSnapshot()`

Each model defines exactly what "the interesting fields" are for audit purposes. This is deliberately explicit rather than logging every attribute, so that:

- Sensitive or noisy fields (timestamps that change on every touch, cached counts, etc.) can be excluded
- Relation-derived display fields can be included where useful for a human reading the log later

```php
<?php

namespace App\Models;

use App\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements Auditable
{
    use SoftDeletes;

    /**
     * Get a snapshot of the post's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'title',
            'status',
            'category_id',
        ]);
    }
}
```

The real convention is `$this->only([...])` against a fixed attribute list, as shown above and in the full `Company`/`User` examples in [Example Models](11-example-models.md), rather than manually building the array.

## Flow

```
UserUpdaterService::update($user, $data)
    → $before = $user->auditSnapshot();
    → UpdateResource::execute($user, $data);
    → $after = $user->fresh()->auditSnapshot();
    → AuditLogService::log(Log::ACTION_UPDATE_USER, $user, $before, $after, actor: auth()->user());
```

## Displaying audit trail on the frontend

Show pages compose a shared-shape `{Model}AuditDetails` component (e.g. `UserAuditDetails`, `CompanyAuditDetails`) that renders:

- Creator name (`creator?.name`)
- Updater name (`updater?.name`)
- Deleter name (`deleter?.name`, only shown when trashed)
- Restorer name and `restored_at` (formatted `en-GB`, day/month/year), only shown when the record has been restored

These come from eager-loaded relations (`creator`, `updater`, `deleter`, `restorer`) on the model, not from the `Log` table directly. The `Log` table is the full history; the four relation names are just "who did the most recent version of each action".