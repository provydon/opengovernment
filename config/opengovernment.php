<?php

/*
|--------------------------------------------------------------------------
| OpenGovernment deployment config
|--------------------------------------------------------------------------
|
| OpenGovernment is built to be white-labelled. Each country (or even each
| sub-national jurisdiction) that wants to run an instance can fork the repo
| and tune this file plus the .env to match their context: their currency,
| their administrative tier names, and the identity provider they trust to
| confirm a citizen is a real, unique person in their country.
|
*/

return [

    'brand' => [
        // Public-facing name. "OpenGovernment" by default; deployments are
        // encouraged to keep the prefix (e.g. "OpenGovernment NG", "OpenGovernment KE")
        // so the network of instances is recognisable.
        'name' => env('OG_BRAND_NAME', 'OpenGovernment'),
        'tagline' => env('OG_BRAND_TAGLINE', 'Public spending, public scrutiny.'),
        'support_email' => env('OG_SUPPORT_EMAIL', 'hello@opengovernment.org'),
    ],

    // The deployment's "home" country — the one this instance primarily serves.
    // Used to pick sensible defaults when an unauthenticated visitor lands.
    'default_country_iso2' => env('OG_DEFAULT_COUNTRY', 'NG'),

    'identity' => [
        // Which IdentityVerificationProvider implementation to use. Pluggable
        // per deployment; see app/Services/Identity/.
        //
        // Built-in drivers:
        //   - "stub"     : accepts anything (dev only)
        //   - "ng-dojah" : Nigerian NIN/BVN via Dojah (needs DOJAH_* env)
        //   - "ng-verifyme" : Nigerian NIN/BVN via VerifyMe (needs VERIFYME_* env)
        //
        // Implement your own driver by adding a class that implements
        // App\Services\Identity\IdentityVerificationProvider and binding it
        // in AppServiceProvider.
        'driver' => env('OG_IDENTITY_DRIVER', 'stub'),
    ],

    'donations' => [
        // Payment provider for keeping the lights on. Defaults to Paystack
        // (works across most of Africa). Swap to Stripe / Flutterwave / etc.
        // by implementing App\Services\Donations\DonationProvider.
        'driver' => env('OG_DONATIONS_DRIVER', 'paystack'),
        'paystack' => [
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        ],
    ],

    'ai' => [
        // The agentic chat uses laravel/ai. Pick any provider that ships with
        // it (OpenAI, Anthropic, Gemini, Ollama for self-hosted).
        'driver' => env('OG_AI_DRIVER', 'openai'),
        'model' => env('OG_AI_MODEL', 'gpt-4o-mini'),

        // When the citizen describes a problem, the agent calls
        // search_similar_issues and treats any hit above this cosine-similarity
        // threshold as "likely a duplicate" — it'll ask the user to confirm
        // before creating a new issue.
        'duplicate_similarity_threshold' => (float) env('OG_AI_DUPE_THRESHOLD', 0.78),
    ],

    'moderation' => [
        // Minimum citizen actions before identity verification kicks in. We
        // want low friction to *read* and *upvote*, but a verified identity to
        // *post* an issue or *comment* on spending — to limit brigading.
        'require_verification_to_post' => env('OG_REQUIRE_VERIFICATION_TO_POST', true),
    ],
];
