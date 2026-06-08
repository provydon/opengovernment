<?php

namespace App\Services\Donations;

/**
 * Dev-only provider: returns a deterministic "authorization URL" pointing at
 * an in-app sandbox page that lets you mark the donation as successful. Real
 * deployments must bind a provider like PaystackDonationProvider.
 */
class StubDonationProvider implements DonationProvider
{
    public function initiate(InitiateDonation $request): DonationInitiation
    {
        $ref = 'stub-'.bin2hex(random_bytes(8));

        return new DonationInitiation(
            reference: $ref,
            authorizationUrl: url('/donate/sandbox?ref='.$ref),
        );
    }

    public function verify(string $reference): DonationVerification
    {
        return new DonationVerification(
            successful: true,
            amountMinor: 0,
            currencyCode: 'NGN',
            reference: $reference,
            reason: 'stub-verified',
        );
    }

    public function name(): string
    {
        return 'stub';
    }
}
