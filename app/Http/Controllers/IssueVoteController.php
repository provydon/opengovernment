<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Services\Civic\IssueRegistrarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class IssueVoteController extends Controller
{
    public function store(Request $request, Issue $issue, IssueRegistrarService $registrar): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'integer', 'in:-1,1'],
        ]);

        try {
            $registrar->vote($request->user(), $issue, $data['value']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['vote' => $e->getMessage()]);
        }

        return back();
    }
}
