<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\LocalGovernment;
use App\Services\Civic\IssueRegistrarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class IssueController extends Controller
{
    public function index(Request $request): Response
    {
        $issues = Issue::query()
            ->with(['localGovernment:id,name', 'user:id,name'])
            ->when($request->integer('lga'), fn ($q, $id) => $q->where('local_government_id', $id))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('score')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Issues/Index', [
            'issues' => $issues,
            'filters' => $request->only(['lga', 'status']),
            'lgas' => LocalGovernment::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Issue $issue): Response
    {
        $issue->load(['localGovernment:id,name,slug', 'user:id,name']);

        return Inertia::render('Issues/Show', [
            'issue' => $issue,
            'userVote' => request()->user()
                ? optional($issue->votes()->where('user_id', request()->user()->id)->first())->value
                : null,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Issues/Create', [
            'lgas' => LocalGovernment::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, IssueRegistrarService $registrar): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'category' => ['nullable', 'string', 'max:50'],
            'local_government_id' => ['required', 'integer', 'exists:local_governments,id'],
        ]);

        try {
            $issue = $registrar->create(
                user: $request->user(),
                lga: LocalGovernment::findOrFail($data['local_government_id']),
                title: $data['title'],
                body: $data['body'],
                category: $data['category'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['body' => $e->getMessage()])->withInput();
        }

        return redirect()->route('issues.show', $issue);
    }
}
