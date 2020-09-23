<?php
declare(strict_types = 1);

return [
    'default' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => (int)env('WEBSOCKET_PORT', 9502),
        'ws'   => 'ws://',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout'    => 3.0,
            'heartbeat'       => -1,
            'max_idle_time'   => (float)env('WEBSOCKET_MAX_IDLE_TIME', 60),
        ],
    ],
    'ws1'     => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => (int)env('WEBSOCKET_PORT', 9502),
        'ws'   => 'ws://',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout'    => 3.0,
            'heartbeat'       => -1,
            'max_idle_time'   => (float)env('WEBSOCKET_MAX_IDLE_TIME', 60),
        ],
    ]
];


