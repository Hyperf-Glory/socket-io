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

/**
 * Class FriendRemarkCache.
 */
class FriendRemarkCache extends AbstractCache
{
    public const KEY = 'hash:user:friend:remark:cache';

    /**
     * 设置好友备注缓存.
     *
     * @param string $remark 好友备注
     */
    public function set(int $uid, int $fid, string $remark)
    {
        return wait(function () use ($uid, $fid, $remark)
        {
            $this->redis()->hSet(self::KEY, "{$uid}_{$fid}", $remark);
        }, $this->waitTimeOut);
    }

    /**
     * 获取好友备注.
     */
    public function get(int $uid, int $fid, ) : string
    {
        return wait(function () use ($uid, $fid)
        {
            return $this->redis()->hGet(self::KEY, "{$uid}_{$fid}") ? : '';
        }, $this->waitTimeOut);
    }

}
