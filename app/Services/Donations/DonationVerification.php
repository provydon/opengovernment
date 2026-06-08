<?php

namespace App\Services\Donations;

readonly class DonationVerification
{
    public function __construct(
        public bool $successful,
        public int $amountMinor,
        public string $currencyCode,
        public string $reference,
        public ?string $reason = null,
    ) {}
}
