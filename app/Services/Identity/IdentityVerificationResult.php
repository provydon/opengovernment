<?php

namespace App\Services\Identity;

readonly class IdentityVerificationResult
{
    public function __construct(
        public bool $verified,
        public ?string $primaryIdHash = null,
        public ?string $secondaryIdHash = null,
        public ?string $reference = null,
        public ?string $reason = null,
    ) {}

    public static function failed(string $reason): self
    {
        return new self(verified: false, reason: $reason);
    }
}
