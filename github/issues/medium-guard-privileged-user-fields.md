---
title: "Guard role and audit attributes from mass assignment"
labels: ["priority: medium", "security", "authorization"]
---

## Problem

`User` marks `role`, `meta`, and lifecycle audit fields as fillable. A future use of unfiltered request data could allow privilege changes or forged audit metadata.

## Affected code

- `app/Models/User.php:56-70`

## Change

Replace the fillable declaration with ordinary account fields only:

```php
#[Fillable([
    'name',
    'email',
    'password',
])]
```

Set privileged fields only in an authorized service:

```php
$user->forceFill([
    'role' => $role,
    'updated_by' => $actor->id,
])->save();
```

## Acceptance criteria

- Request payloads cannot update role or audit columns.
- An explicit authorized action can update roles.
