<?php

return [
    'id' => env('CONNECT_ID'),
    'secret' => env('CONNECT_SECRET'),
    'test_vatsim_id' => env('CONNECT_TEST_VATSIM_ID'),
    'autorize' => env('CONNECT_AUTORIZE'),
    'token' => env('CONNECT_TOKEN'),
    'user' => env('CONNECT_USER'),
    'delete_token' => env('CONNECT_DELETE_TOKEN'),
    'scopes' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CONNECT_SCOPES', 'name,teams')),
    ))),
    // Comma-separated SSO team/role names, e.g. "tour-admin,staff".
    'admin_allowed_roles' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_ALLOWED_ROLES', '')),
    ))),
];
