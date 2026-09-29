<?php

namespace App\Actions\Security;

use RuntimeException;

class AssertSecureConfigurationAction
{
    /**
     * Length of a 32 byte key encoded as hexadecimal.
     */
    private const AUDIT_KEY_LENGTH = 64;

    /**
     * Refuse to boot when security critical configuration is missing.
     */
    public function execute(): void
    {
        $this->assertCorsOriginsAreSecure();
    }

    /**
     * Production CORS origins must be explicit and HTTPS only.
     */
    private function assertCorsOriginsAreSecure(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        foreach ((array) config('cors.allowed_origins', []) as $origin) {
            if (! is_string($origin) || ! str_starts_with($origin, 'https://')) {
                throw new RuntimeException(
                    'CORS_ALLOWED_ORIGINS must contain explicit HTTPS origins only in production.'
                );
            }
        }
    }
}
