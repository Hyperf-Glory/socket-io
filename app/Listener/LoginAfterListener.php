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
namespace App\Listener;

use App\Event\LoginAfterEvent;
use Hyperf\Event\Contract\ListenerInterface;

class LoginAfterListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            LoginAfterEvent::class,
        ];
    }

    public function process(object $event)
    {
        // TODO: Implement process() method.
    }
}
