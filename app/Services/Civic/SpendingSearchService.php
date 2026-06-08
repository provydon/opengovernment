<?php

namespace App\Services\Civic;

use App\Models\LocalGovernment;
use App\Models\SpendingRecord;
use Illuminate\Database\Eloquent\Collection;

class SpendingSearchService
{
    /**
     * @return Collection<int, SpendingRecord>
     */
    public function search(string $query, ?LocalGovernment $lga = null, int $limit = 10): Collection
    {
        $q = SpendingRecord::query()
            ->whereNotNull('published_at')
            ->when($lga, fn ($builder) => $builder->where('local_government_id', $lga->id))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($w) use ($query) {
                    $w->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('vendor', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%");
                });
            })
            ->latest('spent_on')
            ->take($limit);

        return $q->get();
    }

    public function lgaSummary(LocalGovernment $lga, int $months = 12): array
    {
        $since = now()->subMonths($months)->startOfMonth();

        $records = SpendingRecord::query()
            ->where('local_government_id', $lga->id)
            ->where('spent_on', '>=', $since)
            ->whereNotNull('published_at')
            ->get();

        return [
            'lga' => $lga->only(['id', 'name', 'slug']),
            'period_months' => $months,
            'record_count' => $records->count(),
            'total_minor' => (int) $records->sum('amount_minor'),
            'by_category' => $records
                ->groupBy(fn ($r) => $r->category ?? 'uncategorised')
                ->map(fn ($group) => [
                    'count' => $group->count(),
                    'total_minor' => (int) $group->sum('amount_minor'),
                ])
                ->all(),
        ];
    }
}
