<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
return [
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
    'ws2' => [
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
