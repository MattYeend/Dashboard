---
title: "Stop retaining and emailing plaintext user passwords"
labels: ["priority: critical", "security", "authentication"]
---

## Problem

`User::$plainPassword` retains passwords so they can be included in welcome emails. Email is not a safe password-delivery channel.

## Affected code

- `app/Models/User.php:89-98`

## Change

Delete:

```php
public ?string $plainPassword = null;
```

Do not include any password in a notification. Send an expiring password-reset link instead:

```php
use Illuminate\Support\Facades\Password;

Password::sendResetLink(['email' => $user->email]);
```

## Acceptance criteria

- No notification, log, queue payload, or model field contains a plaintext password.
- A new user receives an expiring set-password link.
