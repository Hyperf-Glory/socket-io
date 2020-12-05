<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
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
        $records['context']['trace_id'] = Context::getOrSet(self::TRACE_ID, StringHelper::randSimple(20));
        $records['context'][Log::CONTEXT_KEY] = WsContext::get(Log::CONTEXT_KEY);
        return $records;
    }
}
