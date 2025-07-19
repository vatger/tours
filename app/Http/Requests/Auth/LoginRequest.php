<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Models\LoginLog;
use App\Models\UserLogin;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Reefki\DeviceDetector\Device;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DeviceDetector\Parser\Client\Browser;

use DeviceDetector\Parser\OperatingSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use ApiResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $device = Device::detectRequest($this);
        $osInfo = $device->getOs();
        $device_type = $device->getDeviceName();
        $brand = $device->getBrandName();
        $model = $device->getModel();
        $osFamily = OperatingSystem::getOsFamily($device->getOs('name'));
        $browserFamily = Browser::getBrowserFamily($device->getClient('name'));
        $deviceInfo = [
            'browser' => $device->getClient('name'),
            'browser_version' => $device->getClient('version'),
            'platform' => $device->getOs('name'),
            'device_type' => $device->getDeviceName(),
            'is_mobile' => $device->isMobile(),
            'is_tablet' => $device->isTablet(),
            'is_desktop' => $device->isDesktop(),
            'osInfo' => $osInfo,
            'brand' => $brand,
            'model' => $model,
            'osFamily' => $osFamily,
            'browserFamily' => $browserFamily,
        ];
        Log::debug("Log device infos", $deviceInfo);

        $loginData = [
            'user_id' => Auth::user()->id,
            'ip_address' => $this->ip(),
            'token_id' => '12565555555', // Placeholder, replace with actual token ID if needed
            'browser' => $device->getClient('name'),
            'browser_version' => $device->getClient('version'),
            'platform' => $device->getOs('name'),
            'device_type' => $device->getDeviceName(),
            'is_mobile' => $device->isMobile(),
            'is_tablet' => $device->isTablet(),
            'is_desktop' => $device->isDesktop(),
        ];
        UserLogin::create($loginData);
        //verificar is_active
        if (!Auth::user()->is_active) {
            LoginLog::create([
                'identifier' => $this->email,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'status' => 'is_not_active',
                'error_message' => 'Usuário não está ativo',
                'user_id' => Auth::user()->id,
            ]);
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Usuário não está ativo',
            ]);
        }
        if (Auth::user()->approved_status !== User::APPROVED_STATUS_APPROVED) {
            LoginLog::create([
                'identifier' => $this->email,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'status' => 'is_not_approved',
                'error_message' => 'Usuário está ' . Auth::user()->approved_status_text,
                'user_id' => Auth::user()->id,
            ]);
            $error = Auth::user()->approved_status_text;
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Usuário está ' . $error,
            ]);
        }
        // Log successful login
        LoginLog::create([
            'identifier' => $this->email,
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'status' => 'success',
            'user_id' => Auth::user()->id,
        ]);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
