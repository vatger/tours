<?php

namespace App\Providers;

use League\OAuth2\Client\Provider\GenericProvider;

class ConnectProvider extends GenericProvider
{
    /**
     * Initialize the Provider from configuration
     */
    public function __construct()
    {
        parent::__construct([
            'clientId' => config('connect.id'),
            'clientSecret' => config('connect.secret'),
            'redirectUri' => route('callback'),
            'urlAuthorize' => config('connect.autorize'),
            'urlAccessToken' => config('connect.token'),
            'urlResourceOwnerDetails' => config('connect.user'),
            'scopes' => str_replace(',', ' ', 'name'),
            'scopeSeparator' => ' ',
        ]);
    }
}
