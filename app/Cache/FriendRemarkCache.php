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
    const KEY = 'hash:user:friend:remark:cache';

    /**
     * 设置好友备注缓存
     *
     * @param int                           $uid
     * @param int                           $fid
     * @param string                        $remark 好友备注
     * @param null|\Hyperf\Redis\RedisProxy $redis
     */
    public static function set(int $uid, int $fid, string $remark, ?RedisProxy $redis)
    {
        if (is_null($redis)) {
            $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
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
    public static function get(int $uid, int $fid, ?RedisProxy $redis)
    {
        if (is_null($redis)) {
            $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        }
        return $redis->hget(self::KEY, "{$uid}_{$fid}") ? : '';
    }
}
