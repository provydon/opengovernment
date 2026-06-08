<?php

namespace Database\Seeders;

use App\Models\GovernmentOfficial;
use App\Models\Issue;
use App\Models\IssueVote;
use App\Models\LocalGovernment;
use App\Models\SpendingComment;
use App\Models\SpendingRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Generates enough realistic content to exercise every page. Never run in
 * production — only used by `php artisan db:seed` in dev.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $ikeja = LocalGovernment::where('slug', 'ikeja')->first();
        if (! $ikeja) {
            $this->command?->warn('Seed Countries first — Ikeja LGA not found.');
            return;
        }

        $official = GovernmentOfficial::firstOrCreate(
            ['email' => 'demo-official@opengovernment.test'],
            [
                'local_government_id' => $ikeja->id,
                'name' => 'Adaeze Onyema',
                'official_title' => 'Director of Budget, Ikeja LGA',
                'password' => Hash::make('password'),
                'status' => 'approved',
                'approved_at' => now(),
            ],
        );

        $citizen = User::firstOrCreate(
            ['email' => 'demo-citizen@opengovernment.test'],
            [
                'name' => 'Tunde Bakare',
                'password' => Hash::make('password'),
                'country_id' => $ikeja->state->country_id,
                'state_id' => $ikeja->state_id,
                'local_government_id' => $ikeja->id,
                'primary_id_hash' => hash('sha256', 'demo-nin-12345678901'),
                'verification_provider' => 'stub',
                'verification_reference' => 'demo-ref',
                'identity_verified_at' => now(),
            ],
        );

        $spends = [
            ['Allawee road resurfacing', 'infrastructure', 4_200_000, 'JK Construction Ltd', '2026-03-12'],
            ['Boreholes for Ojodu Berger market', 'water', 1_800_000, 'AquaWorks NG', '2026-02-04'],
            ['Refurbishment of Ikeja General Hospital ward 4', 'health', 9_750_000, 'MediBuild', '2026-01-20'],
            ['School feeding programme — Q1', 'education', 6_300_000, 'GreenPlate Catering', '2026-04-02'],
            ['Solar streetlights along Mobolaji Bank Anthony', 'infrastructure', 3_100_000, 'Lumos NG', '2026-03-28'],
        ];

        foreach ($spends as [$title, $category, $major, $vendor, $date]) {
            SpendingRecord::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'local_government_id' => $ikeja->id,
                    'published_by' => $official->id,
                    'title' => $title,
                    'category' => $category,
                    'description' => "Official record for {$title}. Vendor: {$vendor}. Filed under {$category}.",
                    'amount_minor' => $major * 100,
                    'currency_code' => 'NGN',
                    'vendor' => $vendor,
                    'spent_on' => $date,
                    'published_at' => now(),
                ],
            );
        }

        $issues = [
            ['Potholes along Awolowo Way are getting worse', 'roads',
                'Three big potholes between Computer Village and Allen Junction. Cars swerve into oncoming traffic to avoid them.'],
            ['No water supply in Ogba for two weeks', 'water',
                'Households along Ifako-Ogba have not had piped water since the end of last month. The community boreholes are overworked.'],
            ['Streetlights out on Mobolaji Bank Anthony', 'infrastructure',
                'Whole stretch is dark at night, robberies have gone up.'],
            ['Ikeja General Hospital ward 4 still leaking', 'health',
                'After the refurbishment was announced, the leaking roof in ward 4 is still there.'],
        ];

        foreach ($issues as [$title, $category, $body]) {
            $issue = Issue::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'local_government_id' => $ikeja->id,
                    'user_id' => $citizen->id,
                    'title' => $title,
                    'body' => $body,
                    'category' => $category,
                ],
            );

            // Seed a few upvotes from the demo citizen.
            IssueVote::firstOrCreate(
                ['issue_id' => $issue->id, 'user_id' => $citizen->id],
                ['value' => 1],
            );
            $issue->recomputeScore();
        }

        SpendingRecord::where('slug', 'solar-streetlights-along-mobolaji-bank-anthony')
            ->first()
            ?->comments()
            ->firstOrCreate(
                ['user_id' => $citizen->id, 'body' => 'These lights were installed but half of them stopped working within two weeks.'],
            );
    }
}
