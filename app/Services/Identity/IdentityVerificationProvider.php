<?php

namespace App\Services\Identity;

/**
 * Implemented per country / per provider. The driver is selected via
 * config('opengovernment.identity.driver') and bound in AppServiceProvider.
 *
 * The contract is intentionally small: each driver decides what fields it
 * needs (NIN+BVN for NG-Dojah, national ID for KE, etc.) and returns a result
 * that the citizen registration controller stores against the user.
 */
interface IdentityVerificationProvider
{
    /**
     * @param  array<string, scalar>  $payload  Raw identity fields supplied by the user.
     *                                          Drivers must hash anything sensitive before
     *                                          returning it; raw IDs must never leave this method.
     */
    public function verify(array $payload): IdentityVerificationResult;

    /**
     * Machine-readable name of the driver (e.g. "ng-dojah"). Logged against
     * verified users for audit.
     */
    public function name(): string;
}
