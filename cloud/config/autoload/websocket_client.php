<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => (int) env('WEBSOCKET_PORT', 9502),
        'ws' => 'ws://',
        'auto_close' => false,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('WEBSOCKET_MAX_IDLE_TIME', 60),
        ],
    ],
    'ws1' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => (int) env('WEBSOCKET_PORT', 9502),
        'ws' => 'ws://',
        'auto_close' => false,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('WEBSOCKET_MAX_IDLE_TIME', 60),
        ],
    ],
];
