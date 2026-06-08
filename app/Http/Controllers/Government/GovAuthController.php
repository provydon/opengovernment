<?php

namespace App\Http\Controllers\Government;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Government officials log in through their own form, against their own
 * guard, and end up in their own dashboard. They are never able to log in via
 * the citizen flow — and citizens cannot log in here. The separation is
 * deliberate: the writing privilege on this platform is the right to publish
 * spending records, so that role is held behind a different door.
 */
class GovAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Government/Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('government')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Those credentials do not match our records.']);
        }

        /** @var \App\Models\GovernmentOfficial $official */
        $official = Auth::guard('government')->user();

        if (! $official->isApproved()) {
            Auth::guard('government')->logout();
            throw ValidationException::withMessages([
                'email' => match ($official->status) {
                    'pending' => 'This account is awaiting platform admin approval.',
                    'suspended' => 'This account has been suspended.',
                    default => 'This account cannot publish.',
                },
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('government.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('government')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('government.login');
    }
}
