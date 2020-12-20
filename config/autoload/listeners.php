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
use App\Listener\OnShutdownListener;
use App\Listener\OnStartListener;
use App\Listener\OnWorkerStopListener;

return [
    OnStartListener::class,
    OnWorkerStopListener::class,
    OnShutdownListener::class,
];
