<?php

return [

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
    'kipapp' => [
    'token'  => env('KIPAPP_BEARER_TOKEN'),
    'api_key' => env('KIPAPP_API_KEY'),
    'origin' => env('KIPAPP_ORIGIN', 'https://jateng.web.bps.go.id'),
    'url'    => env('KIPAPP_URL', 'https://kipapp.bps.go.id/api/v3'),
    ],
    'bps_sso' => [
    'client_id'     => env('BPS_SSO_CLIENT_ID'),
    'client_secret' => env('BPS_SSO_CLIENT_SECRET'),
    ],
];
