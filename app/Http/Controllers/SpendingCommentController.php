<?php

namespace App\Http\Controllers;

use App\Models\SpendingComment;
use App\Models\SpendingRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SpendingCommentController extends Controller
{
    public function store(Request $request, SpendingRecord $spendingRecord): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        if (config('opengovernment.moderation.require_verification_to_post') && ! $user->isVerified()) {
            throw ValidationException::withMessages([
                'body' => 'Verify your identity before commenting on public spending.',
            ]);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        SpendingComment::create([
            'spending_record_id' => $spendingRecord->id,
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        return back();
    }
}
