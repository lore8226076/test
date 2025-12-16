<?php

return [
    // 藍新金流設定
    'newebpay' => [
        'return_url' => env('NEWEBPAY_RETURN_URL', 'https://wow-dragon.com/checkPayment'),
        'notify_url' => env('NEWEBPAY_NOTIFY_URL', 'https://clang_party_dev.wow-dragon.com.tw/api/payment/notify-newebpay'),
        'client_back_url' => env('NEWEBPAY_CLIENT_BACK_URL', 'https://wow-dragon.com/game-store'),
    ],
];
