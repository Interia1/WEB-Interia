<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gdpr_consent' => ['accepted'],
            'terms_accepted' => ['accepted'],
            'marketing_consent' => ['nullable', 'boolean'],
        ], [
            'gdpr_consent.accepted' => 'Na registráciu je potrebný súhlas so spracovaním osobných údajov.',
            'terms_accepted.accepted' => 'Na registráciu je potrebný súhlas s obchodnými podmienkami.',
        ]);

        $marketingConsent = (bool) ($validated['marketing_consent'] ?? false);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gdpr_consent_at' => now(),
            'gdpr_consent_ip' => $request->ip(),
            'terms_accepted_at' => now(),
            'terms_accepted_ip' => $request->ip(),
            'marketing_consent' => $marketingConsent,
            'marketing_consent_at' => $marketingConsent ? now() : null,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', 'Registrácia bola úspešná. Skontrolujte e-mail a potvrďte overovací odkaz.');
    }
}
