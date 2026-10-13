<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions',
        'snap_js_url' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js',
        'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2',
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
        'courier_company' => env('BITESHIP_COURIER_COMPANY', 'jne'),
        'courier_type' => env('BITESHIP_COURIER_TYPE', 'reg'),
        'origin' => [
            'contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME', 'Alexa Fulfillment Center'),
            'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE', '081299887766'),
            'contact_email' => env('BITESHIP_ORIGIN_CONTACT_EMAIL', 'fulfillment@alexa.store'),
            'organization' => env('BITESHIP_ORIGIN_ORGANIZATION', 'Alexa Lifestyle Indonesia'),
            'address' => env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Jenderal Sudirman Kav. 52-53, SCBD'),
            'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '12190'),
            'note' => env('BITESHIP_ORIGIN_NOTE', 'Alexa Fulfillment Warehouse Gate 3'),
        ],
    ],

];
