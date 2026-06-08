<?php

namespace App\Services\Civic;

use App\Models\Issue;
use App\Models\IssueVote;
use App\Models\LocalGovernment;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class IssueRegistrarService
{
    /**
     * Create a new issue on behalf of a verified citizen.
     */
    public function create(User $user, LocalGovernment $lga, string $title, string $body, ?string $category = null): Issue
    {
        $this->ensureCanPost($user);

        return Issue::create([
            'local_government_id' => $lga->id,
            'user_id' => $user->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'body' => $body,
            'category' => $category,
        ]);
    }

    /**
     * Add (or flip) a vote on an existing issue. Returns the updated issue.
     */
    public function vote(User $user, Issue $issue, int $value): Issue
    {
        $this->ensureCanPost($user);

        if ($value !== 1 && $value !== -1) {
            throw new RuntimeException('Vote value must be 1 or -1.');
        }

        IssueVote::updateOrCreate(
            ['issue_id' => $issue->id, 'user_id' => $user->id],
            ['value' => $value],
        );

        $issue->recomputeScore();

        return $issue->fresh();
    }

    private function ensureCanPost(User $user): void
    {
        if ($user->is_banned) {
            throw new RuntimeException('Account is suspended.');
        }

        if (config('opengovernment.moderation.require_verification_to_post') && ! $user->isVerified()) {
            throw new RuntimeException('Identity verification is required before posting or voting.');
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'issue';
        $slug = $base;
        $i = 2;

        while (Issue::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
