<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (HTTP v1)
    |--------------------------------------------------------------------------
    |
    | Download a service account JSON from Firebase Console → Project settings
    | → Service accounts → Generate new private key.
    | Store outside public web root, e.g. storage/app/firebase-credentials.json
    |
    */

    'enabled' => (bool) env('PUSH_ENABLED', false),

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
    ],

    'queue' => env('PUSH_QUEUE', env('QUEUE_CONNECTION', 'sync')),

];
