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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'urls' => [
        'api_getaway' => env('API_GETAWAY_URL'),
        'media_service' => env('MEDIA_SERVICE_URL'),
        'auth_service' => env('AUTH_SERVICE_URL'),
        'promo_service' => env('PROMO_SERVICE_URL'),
        'game_service' => env('GAME_SERVICE_URL'),
        'telegram_service' => env('TELEGRAM_SERVICE_URL'),
        'notification_service' => env('NOTIFICATION_SERVICE_URL'),
        'web_service' => env('WEB_SERVICE_URL')
    ],
    'constants' => [
        'encouragement_points' => env('ENCOURAGEMENT_POINTS'),
    ],

];
