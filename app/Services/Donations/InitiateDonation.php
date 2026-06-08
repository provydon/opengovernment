<?php

namespace App\Services\Donations;

readonly class InitiateDonation
{
    public function __construct(
        public int $amountMinor,
        public string $currencyCode,
        public string $donorEmail,
        public ?string $donorName = null,
        public ?string $message = null,
        public bool $displayPublicly = true,
        public ?int $userId = null,
        public ?string $callbackUrl = null,
    ) {}
}
