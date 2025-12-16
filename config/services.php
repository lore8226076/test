<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'photon' => [
        'key' => env('PHOTON_KEY', 'error'),
    ],

    'filesystem' => [
        'disk' => env('FILESYSTEM_DISK', 'r2'), // config('services.filesystem.disk')
        'R2_API_PUBLIC_URL' => env('R2_API_PUBLIC_URL'),     // config('services.filesystem.R2_API_PUBLIC_URL')
    ],

    'SITE_NAME' => env('SITE_NAME'),       // config('services.SITE_NAME')
    'API_KEY' => env('API_KEY'),         // config('services.API_KEY')
    'APP_URL' => env('APP_URL'),         // config('services.APP_URL')
    'API_URL' => env('API_URL'),         // config('services.API_URL')
    'API_PASS_DOMAIN' => env('API_PASS_DOMAIN'), // config('services.API_PASS_DOMAIN')

    'APPLE' => [
        'SHARED_SECRET' => env('APPLE_SHARED_SECRET'), // config('services.APPLE.SHARED_SECRET')
    ],

    'GOOGLE_PLAY' => [
        'PACKAGE_NAME' => env('GOOGLE_PLAY_PACKAGE_NAME'),    // config('services.GOOGLE_PLAY.PACKAGE_NAME')
        'SERVICE_ACCOUNT' => env('GOOGLE_PLAY_SERVICE_ACCOUNT'), // config('services.GOOGLE_PLAY.SERVICE_ACCOUNT')
    ],

    // 藍新金流
    'newebpay' => [
        'MERCHANT_ID' => env('NEWEBPAY_MERCHANT_ID', 'MS157087138'),
        'HASH_KEY' => env('NEWEBPAY_HASH_KEY', 'frTCJC6opiPeatGUfUS8LHFFi5FZ5GR1'),
        'HASH_IV' => env('NEWEBPAY_HASH_IV', 'C3rZRlsMBEQhEBwP'),
        'PAY_GATEWAY' => env('NEWEBPAY_PAY_GATEWAY', 'https://core.newebpay.com/MPG/mpg_gateway'),
        'return_url' => env('NEWEBPAY_RETURN_URL', 'https://wow-dragon.com/checkPayment'),
        'notify_url' => env('NEWEBPAY_NOTIFY_URL', 'https://clang_party.wow-dragon.com.tw/api/payment/notify-newebpay'),
        'client_back_url' => env('NEWEBPAY_CLIENT_BACK_URL', 'https://wow-dragon.com/game-store'),
    ],

    // 綠界金流
    // 'newecpay'        => [
    //     'MERCHANT_ID' => env('NEWEBPAY_MERCHANT_ID', '3002599'),
    //     'HASH_KEY'    => env('NEWEBPAY_HASH_KEY', 'spPjZn66i0OhqJsQ'),
    //     'HASH_IV'     => env('NEWEBPAY_HASH_IV', 'hT5OJckN45isQTTs'),
    // ],
];
