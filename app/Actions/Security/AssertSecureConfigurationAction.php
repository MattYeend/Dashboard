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
        $this->assertAuditKeyIsConfigured();
    }

    /**
     * The audit HMAC key must be a 64 character hexadecimal string outside
     * local and testing, otherwise signatures are effectively unauthenticated.
     */
    private function assertAuditKeyIsConfigured(): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

        $key = config('audit.hmac_key');

        if (
            ! is_string($key)
            || strlen($key) !== self::AUDIT_KEY_LENGTH
            || ! ctype_xdigit($key)
        ) {
            throw new RuntimeException(
                'AUDIT_LOG_HMAC_KEY must be set to a 64 character hexadecimal value.'
            );
        }
    }
}
