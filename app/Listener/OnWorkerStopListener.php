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
namespace App\Listener;

use Carbon\Carbon;
use Codedungeon\PHPCliColors\Color;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerStop;

class OnWorkerStopListener extends AbstractProcessListener implements ListenerInterface
{
    public function listen() : array
    {
        return [
            OnWorkerStop::class,
        ];
    }

    public function process(object $event) : void
    {
        if ($event instanceof OnWorkerStop) {
            if ($event->server->taskworker) {
                echo Color::GREEN, sprintf('[%s]', Carbon::now()->toDateTimeString()), ' ', Color::CYAN,
                "TaskWorker#{$event->workerId} stopped.", PHP_EOL;
            } else {
                echo Color::GREEN, sprintf('[%s]', Carbon::now()->toDateTimeString()), ' ', Color::CYAN,
                "Worker#{$event->workerId} stopped.", PHP_EOL;
            }
        }
    }
}
