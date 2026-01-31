<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit API Key
    |--------------------------------------------------------------------------
    |
    | Your Xendit secret API key. Get this from your Xendit dashboard.
    | Use test key for development: xnd_development_...
    | Use live key for production: xnd_production_...
    |
    */
    'secret_key' => env('XENDIT_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Xendit Webhook Verification Token
    |--------------------------------------------------------------------------
    |
    | Token used to verify webhook callbacks from Xendit.
    | Get this from your Xendit dashboard under Webhooks settings.
    |
    */
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        // Invoice expiry in seconds (default: 24 hours)
        'expiry_seconds' => env('XENDIT_INVOICE_EXPIRY', 86400),

        // Success redirect URL after payment
        'success_url' => env('XENDIT_SUCCESS_URL', '/orders'),

        // Failure redirect URL if payment fails
        'failure_url' => env('XENDIT_FAILURE_URL', '/orders'),
    ],
];
