<?php

$appUrl = rtrim((string) env('APP_URL', ''), '/');

return [

    /*
    |--------------------------------------------------------------------------
    | PayMob username and password
    |--------------------------------------------------------------------------
    |
    | This is your PayMob username and password to make auth request.
    |
    */

    'username' => env('PayMob_Username'),
    'password' => env('PayMob_Password'),

    /*
    |--------------------------------------------------------------------------
    | PayMob integration id
    |--------------------------------------------------------------------------
    |
    | This is your PayMob integration id.
    |
    */

    'integration_id' => env('PayMob_Integration_Id'),
    'wallet_integration_id' => env('PAYMOB_WALLET_INTEGRATION_ID'),

    /*
    |--------------------------------------------------------------------------
    | Payment Intention + wallet flow (accept.paymob.com)
    |--------------------------------------------------------------------------
    */

    'secret_key' => env('PAYMOB_SECRET_KEY'),

    'currency' => env('PAYMOB_CURRENCY', 'EGP'),

    'intention_notification_url' => env('PAYMOB_NOTIFICATION_URL', $appUrl !== '' ? $appUrl . '/api/checkout/processed' : ''),

    'intention_redirection_url' => env('PAYMOB_REDIRECTION_URL', $appUrl !== '' ? $appUrl . '/en/checkout/response' : ''),

    'intention_url' => env('PAYMOB_INTENTION_URL', 'https://accept.paymob.com/v1/intention'),

    'wallet_pay_url' => env('PAYMOB_WALLET_PAY_URL', 'https://accept.paymob.com/api/acceptance/payments/pay'),
];
