<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Personal API token abilities
    |--------------------------------------------------------------------------
    |
    | The only abilities a user may grant to a personal token. Wildcard
    | abilities are never permitted.
    |
    */

    'token_abilities' => [
        'records:read',
        'records:write',
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal API token lifetimes (days)
    |--------------------------------------------------------------------------
    |
    | Tokens always expire. Non-expiring tokens are not offered.
    |
    */

    'token_lifetimes_days' => [7, 30, 90, 365],

    'max_tokens_per_user' => 10,
];
