<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Kernel\Log;

use App\Constants\Log;
use App\Helper\StringHelper;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    const TRACE_ID = 'log.trace.id';

    public function __invoke(array $records)
    {
        $records['context']['trace_id']       = Context::getOrSet(self::TRACE_ID, StringHelper::randSimple(20));
        $records['context'][Log::CONTEXT_KEY] = WsContext::get(Log::CONTEXT_KEY);
        return $records;
    }
}
