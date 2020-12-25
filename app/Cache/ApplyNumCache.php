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
namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class ApplyNumCache.
 */
class ApplyNumCache
{
    public const KEY = 'friend:apply:unread:num';

    /**
     * 获取好友未读申请数.
     */
    public static function get(int $uid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        return $redis->hGet(self::KEY, (string) $uid);
    }

    /**
     * 设置未读好友申请数（自增加1）.
     */
    public static function setInc(int $uid, ?RedisProxy $redis = null): int
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }

        return $redis->hIncrBy(self::KEY, (string) $uid, 1);
    }

    /**
     * 删除好友申请未读数.
     */
    public static function del(int $uid, ?RedisProxy $redis = null): void
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $redis->hDel(self::KEY, (string) $uid);
    }

    /**
     * 获取Redis连接.
     *
     * @return RedisProxy|
     */
    private static function redis(): RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
