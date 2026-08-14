# Dashboard code review

The following are separate GitHub-ready issues for the `dashboard/` repository. Each includes the affected code and a concrete change.

| Priority | Finding | Issue |
| --- | --- | --- |
| Critical | Plaintext passwords are retained for welcome emails. | [Open issue](github/issues/critical-remove-plaintext-password-email.md) |
| High | API login bypasses the web email-verification gate. | [Open issue](github/issues/high-enforce-email-verification-for-api-login.md) |
| Medium | Privileged role and audit fields are mass assignable. | [Open issue](github/issues/medium-guard-privileged-user-fields.md) |
| Medium | API logout does not invalidate a browser session or rotate CSRF state. | [Open issue](github/issues/medium-invalidate-api-session-on-logout.md) |
| Low | API CORS origins are hard-coded for local development. | [Open issue](github/issues/low-configure-cors-by-environment.md) |

Validation completed: `php artisan test --compact`, `npx vue-tsc --noEmit`, and `npx eslint .`.
