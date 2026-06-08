<?php

namespace App\Http\Controllers\Government;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\SpendingRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GovDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\GovernmentOfficial $official */
        $official = $request->user('government');
        $lga = $official->localGovernment;

        $topIssues = Issue::query()
            ->where('local_government_id', $lga->id)
            ->whereIn('status', ['open', 'acknowledged', 'in_progress'])
            ->orderByDesc('score')
            ->limit(20)
            ->get(['id', 'title', 'slug', 'category', 'upvotes', 'downvotes', 'score', 'status', 'created_at']);

        $recentSpend = SpendingRecord::query()
            ->where('local_government_id', $lga->id)
            ->latest('spent_on')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'amount_minor', 'currency_code', 'spent_on', 'published_at']);

        return Inertia::render('Government/Dashboard', [
            'official' => $official->only(['id', 'name', 'official_title']),
            'lga' => $lga->only(['id', 'name', 'slug']),
            'topIssues' => $topIssues,
            'recentSpend' => $recentSpend,
        ]);
    }
}
