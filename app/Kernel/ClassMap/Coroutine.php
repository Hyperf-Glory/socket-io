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
namespace Hyperf\Utils;

use App\Kernel\Context\Coroutine as Go;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;

class Coroutine
{
    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable): void
    {
        Co::defer($callable);
    }

    public static function sleep(float $seconds): void
    {
        usleep((int) ($seconds * 1000 * 1000));
    }

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     *
     * @param null|int $coroutineId
     *
     * @return int
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * @param callable $callable
     *
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        return di()->get(Go::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }
}
