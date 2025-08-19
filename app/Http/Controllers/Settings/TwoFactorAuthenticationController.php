<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        /** @var User $user */
        $user = $request->user();

        $confirmed = filled($user->two_factor_confirmed_at);

        return Inertia::render('settings/TwoFactor', [
            'confirmed' => $confirmed,
            'recoveryCodes' => filled($user->two_factor_secret) ? json_decode(decrypt($user->two_factor_recovery_codes)) : [],
            'qrCodeSvg' => !$confirmed && filled($user->two_factor_secret) ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => !$confirmed && filled($user->two_factor_secret) ? decrypt($user->two_factor_secret) : null,
        ]);
    }
}
