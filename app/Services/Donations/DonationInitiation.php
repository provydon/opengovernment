<?php

namespace App\Services\Donations;

readonly class DonationInitiation
{
    public function __construct(
        public string $reference,
        public string $authorizationUrl,
    ) {}
}
