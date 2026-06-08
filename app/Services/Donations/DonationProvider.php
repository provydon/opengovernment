<?php

namespace App\Services\Donations;

/**
 * Donations keep the lights on. Swap providers per deployment: Paystack works
 * across most of Africa; Stripe for global; Flutterwave is another option.
 */
interface DonationProvider
{
    /**
     * Begin a donation. Returns an authorization URL that the donor's browser
     * should be redirected to.
     */
    public function initiate(InitiateDonation $request): DonationInitiation;

    /**
     * Verify a callback from the provider. Throws on tampering.
     */
    public function verify(string $reference): DonationVerification;

    public function name(): string;
}
