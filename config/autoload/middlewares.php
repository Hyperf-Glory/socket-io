<?php

declare(strict_types = 1);

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

use App\Middleware\CorsMiddleware;
use App\Middleware\SocketIOAuthMiddleware;
use Han\Utils\Middleware\DebugMiddleware;

return [
    'http'      => [
        DebugMiddleware::class,
        CorsMiddleware::class,
    ],
    'socket-io' => [
        SocketIOAuthMiddleware::class,
    ],
];
