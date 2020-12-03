<?php

namespace App\Cache;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class ApplyNumCache
 * @package App\Cache
 */
class ApplyNumCache
{

    const KEY = 'friend:apply:unread:num';

    /**
     * 获取好友未读申请数
     *
     * @param int                           $uid
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return string
     */
    public static function get(int $uid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        return $redis->hget(self::KEY, (string)$uid);
    }

    /**
     * 设置未读好友申请数（自增加1）
     *
     * @param int                           $uid
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return int
     */
    public static function setInc(int $uid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }

        return $redis->hincrby(self::KEY, (string)$uid, 1);
    }

    /**
     * 删除好友申请未读数
     *
     * @param int                           $uid
     * @param null|\Hyperf\Redis\RedisProxy $redis
     */
    public static function del(int $uid, ?RedisProxy $redis = null) : void
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $redis->hDel(self::KEY, (string)$uid);
    }

    /**
     * 获取Redis连接
     *
     * @return RedisProxy|
     */
    private static function redis() : RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
