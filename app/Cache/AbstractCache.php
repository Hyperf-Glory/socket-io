<?php
declare(strict_types = 1);

namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;

abstract class AbstractCache
{
    public float $waitTimeOut = 5.0;

    /**
     * @param float $waitTimeOut
     */
    public function setWaitTimeOut(float $waitTimeOut) : void
    {
        $this->waitTimeOut = $waitTimeOut;
    }

    /**
     * 获取Redis连接.
     */
    protected function redis() : RedisProxy
    {
        return ApplicationContext::getContainer()->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
