<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Concerns\ConfirmsTwoFactorAuthentication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{
    use ConfirmsTwoFactorAuthentication;

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'Two factor authentication is disabled.');
        }

        $this->validateTwoFactorAuthenticationState($request);

        return Inertia::render('settings/TwoFactor', [
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
        ]);
    }
}
