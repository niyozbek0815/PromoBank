<?php
return [
    'default'  => env('FIREBASE_PROJECT', 'app'),

'credentials' => [
    'file' => env('FIREBASE_CREDENTIALS', base_path('storage/firebase/credentials.json')),
],

    'web'      => [
        'api_key'             => env('FIREBASE_WEB_API_KEY'),
        'auth_domain'         => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'project_id'          => env('FIREBASE_WEB_PROJECT_ID'),
        'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'app_id'              => env('FIREBASE_WEB_APP_ID'),
        'vapid_key'           => env('FIREBASE_WEB_VAPID_KEY'),
    ],

];
