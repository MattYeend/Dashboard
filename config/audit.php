<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integrity key
    |--------------------------------------------------------------------------
    |
    | HMAC key used to seal audit rows. Keep it outside the database, rotate
    | it through your secrets manager, and never log it.
    |
    */

    'hmac_key' => env('AUDIT_LOG_HMAC_KEY'),

    'seal_chunk_size' => 500,

    'verify_chunk_size' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Export limits
    |--------------------------------------------------------------------------
    */

    'export_max_days' => 92,

    'export_max_rows' => 100000,

    /*
    |--------------------------------------------------------------------------
    | Sensitive keys
    |--------------------------------------------------------------------------
    |
    | Any array key containing one of these fragments (case insensitive) has
    | its value replaced before it is stored, displayed or exported.
    |
    */

    'sensitive_keys' => [
        'password',
        'secret',
        'token',
        'recovery_codes',
        'two_factor',
        'api_key',
        'authorization',
        'cvv',
        'card_number',
    ],
];
