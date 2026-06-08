<?php

namespace App\Services\Identity;

/**
 * Development-only provider. Accepts any non-empty payload, hashes whatever
 * the caller submits as "primary_id" so the rest of the pipeline behaves the
 * same as in production. Never use this in a live deployment.
 */
class StubIdentityProvider implements IdentityVerificationProvider
{
    public function verify(array $payload): IdentityVerificationResult
    {
        $primary = (string) ($payload['primary_id'] ?? '');
        $secondary = (string) ($payload['secondary_id'] ?? '');

        if ($primary === '') {
            return IdentityVerificationResult::failed('primary_id is required');
        }

        return new IdentityVerificationResult(
            verified: true,
            primaryIdHash: hash('sha256', $primary),
            secondaryIdHash: $secondary !== '' ? hash('sha256', $secondary) : null,
            reference: 'stub-'.bin2hex(random_bytes(6)),
        );
    }

    public function name(): string
    {
        return 'stub';
    }
}
