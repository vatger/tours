<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\ConnectProvider;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class ConnectController extends Controller
{
    private string $state_session_key = 'connect.state';

    private ConnectProvider $provider;

    public function __construct()
    {
        $this->provider = new ConnectProvider;
    }

    public function login(Request $request): RedirectResponse
    {
        if (App::environment('local') && App::isLocal()) {
            $user = User::where('id', 1450775)->first();
            Auth::login($user);

            return Redirect::route('tours')->with('success', 'Logged in successfully');
        }
        $authenticationUrl = $this->provider->getAuthorizationUrl();
        $request->session()->put($this->state_session_key, $this->provider->getState());

        return redirect()->away($authenticationUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->input('state') != session()->pull($this->state_session_key)) {
            $request->session()->invalidate();

            return redirect()->route('home')->withErrors('Invalid state');
        }
        try {
            $response = $this->processCallback($request);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('home')
                ->withErrors('Error processing login');
        }

        return $response;
    }

    /**
     * Check that all required data is received from the VATSIM Connect authentication system
     *
     * @throws Exception|GuzzleException
     */
    private function processCallback(Request $request): RedirectResponse
    {
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->input('code'),
        ]);
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));
        if (
            ! isset($resourceOwner->id) ||
            ! isset($resourceOwner->firstname) ||
            ! isset($resourceOwner->lastname)
        ) {
            throw new Exception('missing data');
        }

        $user = User::where('id', $resourceOwner->id)->first();
        if (! $user) {
            $user = new User;
        }
        $user->id = $resourceOwner->id;
        $user->firstname = $resourceOwner->firstname;
        $user->lastname = $resourceOwner->lastname;
        $user->save();

        Auth::login($user);

        return Redirect::route('tours')->with('success', 'Logged in successfully');
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();

        return Redirect::route('home')->with('success', 'Logged out successfully');
    }
}
