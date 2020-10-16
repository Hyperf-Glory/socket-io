<?php

declare(strict_types = 1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Logger;
use App\Kernel\Log\AppendRequestIdProcessor;

return [
    'default' => [
        'handlers'   => [
            [
                'class'       => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level'  => Logger::INFO,
                ],
                'formatter'   => [
                    'class'       => Formatter\LineFormatter::class,
                    'constructor' => [
                        'format'                => NULL,
                        'dateFormat'            => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
            [
                'class'       => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                    'level'  => Logger::DEBUG,
                ],
                'formatter'   => [
                    'class'       => Formatter\JsonFormatter::class,
                    'constructor' => [
                        'batchMode'     => Formatter\JsonFormatter::BATCH_MODE_JSON,
                        'appendNewline' => true,
                    ],
                ],
            ],
        ],
        'processors' => [
            [
                'class' => AppendRequestIdProcessor::class,
            ],
        ]
    ],
];
