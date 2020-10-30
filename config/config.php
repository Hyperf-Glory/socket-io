<?php

declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;
use Hyperf\Utils\Codec\Json;

return [
    'app_name'                   => env('APP_NAME', 'skeleton'),
    'app_env'                    => env('APP_ENV', 'dev'),
    'websocket_server_ips'       => value(function ()
    {
        return Json::decode(env('WEBSOCKET_SERVER_IPS'), true) ?? [];
    }),
    'scan_cacheable'             => env('SCAN_CACHEABLE', true),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
//            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
];
