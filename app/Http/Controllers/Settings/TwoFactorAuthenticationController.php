<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        $confirmed = filled($user->two_factor_confirmed_at);
        $hasSecret = filled($user->two_factor_secret);
        $showSetup = ! $confirmed && $hasSecret;

        $recoveryCodes = $hasSecret && filled($user->two_factor_recovery_codes)
            ? (json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [])
            : [];

        return Inertia::render('settings/TwoFactor', [
            'confirmed' => $confirmed,
            'recoveryCodes' => $recoveryCodes,
            'qrCodeSvg' => $showSetup ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => $showSetup ? decrypt($user->two_factor_secret) : null,
        ]);
    }
}
