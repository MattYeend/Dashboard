---
title: "Enforce verified email before issuing API sessions or tokens"
labels: ["priority: high", "security", "api"]
---

## Problem

`routes/web.php` protects application pages with `verified`, but `Api\AuthController::login()` creates a Sanctum token or web session immediately after `Auth::attempt()`. Unverified accounts can bypass the web verification gate through `/api/login`.

## Affected code

- `app/Http/Controllers/Api/AuthController.php:31-48`

## Change

Add this after `Auth::attempt()` and before `createToken()` or session regeneration:

```php
/** @var User $user */
$user = Auth::user();

if (! $user->hasVerifiedEmail()) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    throw ValidationException::withMessages([
        'email' => [__('Please verify your email address before signing in.')],
    ]);
}
```

## Acceptance criteria

- Unverified users receive neither a token nor an authenticated session.
- Verified users can use token and SPA login flows.
