<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\LoginLog;
use App\Models\UserLogin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Reefki\DeviceDetector\Device;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Auth\LoginRequest;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Http\Requests\Api\V1\Auth\UserLoginSanctumRequest;

class AuthenticatedSessionController extends Controller
{
    use ApiResponse;
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function loginNew(UserLoginSanctumRequest $request)
    {
        try {


            $user = User::where('email', $request->identifier)
                ->orWhere('nickname', $request->identifier)
                ->first();

            $logData = [
                'identifier' => $request->identifier,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success'
            ];

            if (!$user || (!Hash::check($request->password, $user->password) &&
                !Hash::check($request->pin_code, $user->pin_code))) {
                $logData['status'] = 'invalid_credentials';
                $logData['error_message'] = 'Credenciais inválidas';
                LoginLog::create($logData);

                throw ValidationException::withMessages([
                    'identifier' => ['As credenciais fornecidas estão incorretas.'],
                ]);
            }

            $logData['user_id'] = $user->id;

            //verificar is_active e

            // Verificar o tipo de login do usuário
            if ($user->is_active !== true) {
                $logData['status'] = 'is_not_active';
                $logData['error_message'] = 'Usuário não está ativo';
                LoginLog::create($logData);

                throw ValidationException::withMessages([
                    'identifier' => ['Usuário inativado.'],
                ]);
            }
            if ($user->approved_status !== User::APPROVED_STATUS_APPROVED) {
                $logData['status'] = 'is_not_approved';
                $logData['error_message'] = 'Usuário está bloqueado';
                LoginLog::create($logData);

                throw ValidationException::withMessages([
                    'identifier' => ['Usuário bloqueado.'],
                ]);
            }



            $device = Device::detectRequest($request);
            $osInfo = $device->getOs();
            $device_type = $device->getDeviceName();
            $brand = $device->getBrandName();
            $model = $device->getModel();
            $osFamily = OperatingSystem::getOsFamily($device->getOs('name'));
            $browserFamily = Browser::getBrowserFamily($device->getClient('name'));

            $deviceInfoTest = [
                'browser' => $device->getClient('name'),
                'browser_version' => $device->getClient('version'),
                'platform' => $device->getOs('name'),
                'device_type' => $device->getDeviceName(),
                'is_mobile' => $device->isMobile(),
                'is_tablet' => $device->isTablet(),
                'is_desktop' => $device->isDesktop(),
                'osInfo' => $device->getOs(),
                'brand' => $device->getBrandName(),
                'model' => $device->getModel(),
                'osFamily' => OperatingSystem::getOsFamily($device->getOs('name')),
                'browserFamily' => Browser::getBrowserFamily($device->getClient('name')),


            ];
            Log::debug("Log device infos", $deviceInfoTest);
            $deviceInfo = [
                'browser' => $device->getClient('name'),
                'browser_version' => $device->getClient('version'),
                'platform' => $device->getOs('name'),
                'device_type' => $device->getDeviceName(),
                'is_mobile' => $device->isMobile(),
                'is_tablet' => $device->isTablet(),
                'is_desktop' => $device->isDesktop(),
            ];


            $token = $user->createToken(json_encode($deviceInfo), expiresAt: $expiresAt);
            $loginData = [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'token_id' => $token->accessToken->id,
                'browser' => $device->getClient('name'),
                'browser_version' => $device->getClient('version'),
                'platform' => $device->getOs('name'),
                'device_type' => $device->getDeviceName(),
                'is_mobile' => $device->isMobile(),
                'is_tablet' => $device->isTablet(),
                'is_desktop' => $device->isDesktop(),
            ];
            UserLogin::create($loginData);
            LoginLog::create($logData);
            $success['token'] =  $token->plainTextToken;
            $success['expires_at'] = null;
            $success['user'] =  new UserResource($user);
            return $this->sendResponse($success, __('User login successfully'));

            // return response()->json([
            //     'token' => $token->plainTextToken,
            //     'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null
            // ]);
        } catch (\Exception $e) {
            LoginLog::create([
                'identifier' => $request->identifier,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }





    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
