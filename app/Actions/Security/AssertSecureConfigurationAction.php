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
        $this->assertCorsOriginsAreSecure();
        $this->assertStripeWebhookSecretIsConfigured();
        $this->assertSessionCookieIsSecure();
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

    /**
     * Cashier skips signature verification when the secret is blank.
     */
    private function assertStripeWebhookSecretIsConfigured(): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

        if (blank(config('cashier.webhook.secret'))) {
            throw new RuntimeException(
                'STRIPE_WEBHOOK_SECRET must be set outside local and testing.'
            );
        }
    }

    /**
     * Production session cookies must only be sent over HTTPS.
     */
    private function assertSessionCookieIsSecure(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        if (config('session.secure') !== true) {
            throw new RuntimeException(
                'SESSION_SECURE_COOKIE must be true in production.'
            );
        }
    }
}
