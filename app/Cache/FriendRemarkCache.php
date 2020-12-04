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
