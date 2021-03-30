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
namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;

/**
 * Class ApplyNumCache.
 */
class ApplyNumCache extends AbstractCache
{
    public const KEY = 'friend:apply:unread:num';

    /**
     * 获取好友未读申请数.
     */
    public function get(int $uid)
    {
        return wait(function () use ($uid)
        {
            return $this->redis()->hGet(self::KEY, (string)$uid);
        }, $this->waitTimeOut);
    }

    /**
     * 设置未读好友申请数（自增加1）.
     */
    public function setInc(int $uid) : int
    {
        return wait(function () use ($uid)
        {
            return $this->redis()->hIncrBy(self::KEY, (string)$uid, 1);
        }, $this->waitTimeOut);
    }

    /**
     * 删除好友申请未读数.
     */
    public function del(int $uid) : void
    {
        wait(function () use ($uid)
        {
            $this->redis()->hDel(self::KEY, (string)$uid);
        }, $this->waitTimeOut);
    }

}
