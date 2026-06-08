<?php

namespace App\Services\Civic;

use App\Models\Issue;
use App\Models\LocalGovernment;
use Illuminate\Support\Collection;

class IssueSearchService
{
    /**
     * Find issues in the given LGA that look similar to the user's description.
     *
     * MVP: lexical similarity using PHP's similar_text. This is intentionally
     * simple — it lets the agent ask the user "is one of these the same
     * problem?" without depending on an embeddings provider being configured.
     *
     * Production deployments should swap this for embedding-based retrieval
     * (e.g. pgvector + Laravel\Ai embeddings) by overriding the binding in
     * AppServiceProvider. The agent flow does not change.
     *
     * @return Collection<int, array{issue: Issue, similarity: float}>
     */
    public function findSimilar(string $description, LocalGovernment $lga, int $limit = 5): Collection
    {
        $candidates = Issue::query()
            ->where('local_government_id', $lga->id)
            ->whereIn('status', ['open', 'acknowledged', 'in_progress'])
            ->latest('score')
            ->take(200)
            ->get(['id', 'title', 'body', 'slug', 'upvotes', 'score', 'status', 'created_at']);

        return $candidates
            ->map(fn (Issue $issue) => [
                'issue' => $issue,
                'similarity' => $this->similarity($description, $issue->title.' '.$issue->body),
            ])
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();
    }

    private function similarity(string $a, string $b): float
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return round($percent / 100, 4);
    }
}
