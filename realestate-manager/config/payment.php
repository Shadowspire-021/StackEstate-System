<?php

return [
    'jazzcash' => [
        'enabled'        => env('JAZZCASH_ENABLED', false),
        'merchant_id'    => env('JAZZCASH_MERCHANT_ID'),
        'password'       => env('JAZZCASH_PASSWORD'),
        'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
        'sandbox'        => env('JAZZCASH_SANDBOX', true),
    ],
    'easypaisa' => [
        'enabled'     => env('EASYPAISA_ENABLED', false),
        'merchant_id' => env('EASYPAISA_MERCHANT_ID'),
        'secret_key'  => env('EASYPAISA_SECRET_KEY'),
        'sandbox'     => env('EASYPAISA_SANDBOX', true),
    ],
];
