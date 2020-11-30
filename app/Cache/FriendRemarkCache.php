<?php

namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class FriendRemarkCache
 * @package App\Cache
 */
class FriendRemarkCache
{
    public const KEY = 'hash:user:friend:remark:cache';

    /**
     * 设置好友备注缓存
     *
     * @param int                           $uid
     * @param int                           $fid
     * @param string                        $remark 好友备注
     * @param null|\Hyperf\Redis\RedisProxy $redis
     */
    public static function set(int $uid, int $fid, string $remark, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $redis->hset(self::KEY, "{$uid}_{$fid}", $remark);
    }

    /**
     * 获取好友备注
     *
     * @param int                           $uid
     * @param int                           $fid
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return string
     */
    public static function get(int $uid, int $fid, ?RedisProxy $redis = null) : string
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        return $redis->hget(self::KEY, "{$uid}_{$fid}") ? : '';
    }

    /**
     * 获取Redis连接
     *
     * @return RedisProxy
     */
    private static function redis() : RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
