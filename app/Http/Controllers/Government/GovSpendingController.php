<?php

namespace App\Http\Controllers\Government;

use App\Http\Controllers\Controller;
use App\Models\SpendingRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GovSpendingController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Government/Spending/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\GovernmentOfficial $official */
        $official = $request->user('government');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:10', 'max:10000'],
            'amount_major' => ['required', 'numeric', 'min:0'], // in major units; we convert
            'currency_code' => ['required', 'string', 'size:3'],
            'vendor' => ['nullable', 'string', 'max:200'],
            'spent_on' => ['required', 'date'],
            'source_document_url' => ['nullable', 'url'],
            'publish_now' => ['sometimes', 'boolean'],
        ]);

        $slug = $this->uniqueSlug($data['title']);

        $record = SpendingRecord::create([
            'local_government_id' => $official->local_government_id,
            'published_by' => $official->id,
            'title' => $data['title'],
            'slug' => $slug,
            'category' => $data['category'] ?? null,
            'description' => $data['description'],
            'amount_minor' => (int) round($data['amount_major'] * 100),
            'currency_code' => strtoupper($data['currency_code']),
            'vendor' => $data['vendor'] ?? null,
            'spent_on' => $data['spent_on'],
            'source_document_url' => $data['source_document_url'] ?? null,
            'published_at' => ($data['publish_now'] ?? true) ? now() : null,
        ]);

        return redirect()->route('government.dashboard')->with('flash', "Published “{$record->title}”.");
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'record';
        $slug = $base;
        $i = 2;

        while (SpendingRecord::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
