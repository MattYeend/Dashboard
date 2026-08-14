---
title: "Configure API CORS origins from environment settings"
labels: ["priority: low", "configuration", "api"]
---

## Problem

`config/cors.php` hard-codes local development origins, so production API clients require a source change to be allowed.

## Affected code

- `config/cors.php:22`

## Change

Replace the literal origins with an environment-driven list:

```php
'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', ''))),
```

Add a production configuration value:

```dotenv
CORS_ALLOWED_ORIGINS=https://app.example.com
```

## Acceptance criteria

- Origins can differ between environments without source edits.
- Only explicitly allowed origins can call the API from a browser.
