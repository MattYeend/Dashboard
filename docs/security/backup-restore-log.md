# npm audit exceptions

## braces (GHSA-vfj7-8cjw-p6xm)

- Severity: High (4 findings in one dependency chain: braces, micromatch, fast-glob, @vue/eslint-config-typescript)
- Installed versions: braces 3.0.3, micromatch 4.0.8, fast-glob 3.3.3, @vue/eslint-config-typescript 14.9.0
- Dependency path: @vue/eslint-config-typescript > fast-glob > micromatch > braces
- Scope: Development only (`npm audit --omit=dev` reports 0 vulnerabilities)
- Patched version: None. The advisory affects all versions and 3.0.3 is the latest published release.
- Attempted remediation: Pinning @vue/eslint-config-typescript to 14.0.1 (the last version outside the chain) fails with an ERESOLVE peer conflict, because it requires eslint-plugin-vue ^9.28.0 and the project uses ^10.11.0. Resolving it would require downgrading two lint packages by a major version, which was rejected as disproportionate to the risk.
- Justification: The vulnerable code only parses glob patterns supplied by repository configuration, never untrusted input, so the stack-exhaustion denial of service cannot be triggered by an external party. The packages run only on developer machines and CI, and do not ship to users.
- Logged: 2026-10-16
- Review date: 2026-11-16
- Review action: Re-run `npm audit` and check for a patched `braces` release or a new @vue/eslint-config-typescript release that drops the `fast-glob` dependency.
