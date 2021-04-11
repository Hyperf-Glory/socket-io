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

use App\Exception\Handler\AppExceptionHandler;
use App\Exception\Handler\BusinessExceptionHandler;
use App\Exception\Handler\Http\HttpExceptionHandler;
use App\Exception\Handler\Rpc\RpcExceptionHandler;
use App\Exception\Handler\SocketIO\HandshakeExceptionHandler;
use Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler;
use Hyperf\Validation\ValidationExceptionHandler;

return [
    'handler' => [
        'http'      => [
            //            ValidationExceptionHandler::class,
            BusinessExceptionHandler::class,
            WhoopsExceptionHandler::class,
            HttpExceptionHandler::class,
            AppExceptionHandler::class,
        ],
        'socket-io' => [
            HandshakeExceptionHandler::class
        ],
        'jsonrpc'   => [
            RpcExceptionHandler::class
        ]
    ],
];
