<?php

namespace App\Providers;

use App\Services\Donations\DonationProvider;
use App\Services\Donations\StubDonationProvider;
use App\Services\Identity\IdentityVerificationProvider;
use App\Services\Identity\StubIdentityProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IdentityVerificationProvider::class, function () {
            return match (config('opengovernment.identity.driver')) {
                'stub' => new StubIdentityProvider,
                // Real drivers should be wired here. Each one is a separate
                // class implementing IdentityVerificationProvider so a
                // deployment can fork and add their country's driver in one
                // place without touching the rest of the codebase.
                default => new StubIdentityProvider,
            };
        });

        $this->app->singleton(DonationProvider::class, function () {
            return match (config('opengovernment.donations.driver')) {
                'paystack' => new StubDonationProvider, // TODO: PaystackDonationProvider
                default => new StubDonationProvider,
            };
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
