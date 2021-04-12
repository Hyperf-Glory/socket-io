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
namespace App\Kernel\Log;

use App\Constants\Log;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Context as WsContext;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const TRACE_ID = 'log.trace.id';

    public function __invoke(array $record) : array
    {
        $record['context']['trace_id']       = Context::getOrSet(self::TRACE_ID, uniqid('SocketIO', true));
        $record['context'][Log::CONTEXT_KEY] = WsContext::get(Log::CONTEXT_KEY);
        $record['context']['coroutine_id']   = Coroutine::id();
        return $record;
    }
}
