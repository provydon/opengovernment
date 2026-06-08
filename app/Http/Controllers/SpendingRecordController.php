<?php

namespace App\Http\Controllers;

use App\Models\LocalGovernment;
use App\Models\SpendingRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpendingRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $records = SpendingRecord::query()
            ->whereNotNull('published_at')
            ->with(['localGovernment.state.country', 'publisher:id,name,official_title'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('vendor', 'like', "%{$term}%");
                });
            })
            ->when($request->integer('lga'), fn ($q, $id) => $q->where('local_government_id', $id))
            ->latest('spent_on')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Spending/Index', [
            'records' => $records,
            'filters' => $request->only(['q', 'lga']),
            'lgas' => LocalGovernment::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(SpendingRecord $spendingRecord): Response
    {
        $spendingRecord->load([
            'localGovernment.state.country',
            'publisher:id,name,official_title',
            'comments' => fn ($q) => $q->where('is_hidden', false)->latest()->with('user:id,name'),
        ]);

        return Inertia::render('Spending/Show', ['record' => $spendingRecord]);
    }
}
