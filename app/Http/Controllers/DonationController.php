<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\Donations\DonationProvider;
use App\Services\Donations\InitiateDonation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function show(): Response
    {
        $recent = Donation::query()
            ->where('status', 'successful')
            ->where('display_publicly', true)
            ->latest()
            ->limit(20)
            ->get(['donor_name', 'amount_minor', 'currency_code', 'message', 'created_at']);

        return Inertia::render('Donate/Index', [
            'recent' => $recent,
        ]);
    }

    public function initiate(Request $request, DonationProvider $provider): RedirectResponse
    {
        $data = $request->validate([
            'amount_major' => ['required', 'numeric', 'min:1'],
            'currency_code' => ['required', 'string', 'size:3'],
            'donor_name' => ['nullable', 'string', 'max:120'],
            'donor_email' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:500'],
            'display_publicly' => ['sometimes', 'boolean'],
        ]);

        $init = $provider->initiate(new InitiateDonation(
            amountMinor: (int) round($data['amount_major'] * 100),
            currencyCode: strtoupper($data['currency_code']),
            donorEmail: $data['donor_email'],
            donorName: $data['donor_name'] ?? null,
            message: $data['message'] ?? null,
            displayPublicly: $data['display_publicly'] ?? true,
            userId: $request->user()?->id,
            callbackUrl: route('donate.callback'),
        ));

        Donation::create([
            'user_id' => $request->user()?->id,
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'],
            'amount_minor' => (int) round($data['amount_major'] * 100),
            'currency_code' => strtoupper($data['currency_code']),
            'provider' => $provider->name(),
            'provider_reference' => $init->reference,
            'status' => 'pending',
            'message' => $data['message'] ?? null,
            'display_publicly' => $data['display_publicly'] ?? true,
        ]);

        return redirect()->away($init->authorizationUrl);
    }

    public function callback(Request $request, DonationProvider $provider): RedirectResponse
    {
        $reference = $request->string('ref')->toString() ?: $request->string('reference')->toString();
        if (! $reference) {
            return redirect()->route('donate')->with('flash', 'Donation could not be verified.');
        }

        $result = $provider->verify($reference);

        Donation::where('provider_reference', $reference)->update([
            'status' => $result->successful ? 'successful' : 'failed',
        ]);

        return redirect()->route('donate')->with(
            'flash',
            $result->successful ? 'Thank you for keeping OpenGovernment running.' : 'Donation could not be confirmed: '.$result->reason,
        );
    }
}
