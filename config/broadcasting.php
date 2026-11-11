<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that your application will
    | use to broadcast events. You may use the "pusher" (or "reverb")
    | options to fully manage your web sockets, or you may use the
    | "ably" option to fully manage your web sockets via Ably.
    |
    | Supported: "pusher", "reverb", "ably", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Connections
    |--------------------------------------------------------------------------
    |
    | These connections are used by the Reverb server to broadcast events.
    | You may define multiple connections for different environments.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
            'cluster' => null,
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'host' => env('PUSHER_HOST'),
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'cluster' => env('PUSHER_CLUSTER'),
                'useTLS' => env('PUSHER_USE_TLS', true),
                'http_proxy_host' => env('PUSHER_PROXY_HOST'),
                'http_proxy_port' => env('PUSHER_PROXY_PORT'),
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

    ],

];
