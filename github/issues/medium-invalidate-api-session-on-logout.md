---
title: "Invalidate web sessions during API logout"
labels: ["priority: medium", "security", "api"]
---

## Problem

The SPA API login flow creates a web session, but API logout only logs out the guard. It does not invalidate the session or rotate the CSRF token.

## Affected code

- `app/Http/Controllers/Api/AuthController.php:58-67`

## Change

Replace the logout tail with:

```php
if ($token = $request->user()?->currentAccessToken()) {
    $token->delete();
}

Auth::guard('web')->logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

return response()->json(['message' => 'Logged out']);
```

## Acceptance criteria

- The old browser session cannot access authenticated routes.
- Token clients revoke only their current token.
