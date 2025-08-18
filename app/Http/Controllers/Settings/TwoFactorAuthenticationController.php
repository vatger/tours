<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $confirmed = ! is_null($user->two_factor_confirmed_at);

        return Inertia::render('settings/TwoFactor', [
            'confirmed' => $confirmed,
            'recoveryCodes' => ! is_null($user->two_factor_secret) ? json_decode(decrypt($user->two_factor_recovery_codes)) : [],
        ]);
    }
}
