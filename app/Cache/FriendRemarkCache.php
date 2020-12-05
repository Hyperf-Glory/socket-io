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
namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class FriendRemarkCache.
 */
class FriendRemarkCache
{
    public const KEY = 'hash:user:friend:remark:cache';

    /**
     * 设置好友备注缓存.
     *
     * @param string $remark 好友备注
     */
    public static function set(int $uid, int $fid, string $remark, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $redis->hset(self::KEY, "{$uid}_{$fid}", $remark);
    }

    /**
     * 获取好友备注.
     */
    public static function get(int $uid, int $fid, ?RedisProxy $redis = null): string
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        return $redis->hget(self::KEY, "{$uid}_{$fid}") ?: '';
    }

    /**
     * 获取Redis连接.
     */
    private static function redis(): RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
