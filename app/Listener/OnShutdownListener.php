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
namespace App\Listener;

use Carbon\Carbon;
use Codedungeon\PHPCliColors\Color;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnManagerStop;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Framework\Event\OnWorkerStop;

class OnShutdownListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            OnWorkerStop::class,
            OnManagerStop::class,
            OnShutdown::class,
        ];
    }

    public function process(object $event): void
    {
        echo Color::GREEN, sprintf('[%s]', Carbon::now()->toDateTimeString()), ' ',Color::RED, '
        Hyperf-Chat 
        SOCKET-IO
        ENDFINISH',
        Color::RESET, PHP_EOL;
    }
}
