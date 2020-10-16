<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
ini_set('memory_limit', '1G');
Swoole\Coroutine\run(function () {
    for ($i = 0; $i < 1000000; ++$i) {
        \Swoole\Coroutine::create(function () {
            Swoole\Coroutine::sleep(1);
        });
    }
});
