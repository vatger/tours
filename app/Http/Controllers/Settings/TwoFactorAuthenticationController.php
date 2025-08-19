<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $confirmed = ! is_null($user->two_factor_confirmed_at);
        $hasTwoFactorSecret = ! is_null($user->two_factor_secret);

        return Inertia::render('settings/TwoFactor', [
            'confirmed' => $confirmed,
            'recoveryCodes' => $hasTwoFactorSecret ? json_decode(decrypt($user->two_factor_recovery_codes)) : [],
            'qrCodeSvg' => !$confirmed && $hasTwoFactorSecret ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => !$confirmed && $hasTwoFactorSecret ? decrypt($user->two_factor_secret) : null,
        ]);
    }
}
