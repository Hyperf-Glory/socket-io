<?php

namespace App\Services\Common;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * 好友未读消息服务
 *
 * Class UnreadTalkService
 * @package App\Services
 */
class UnreadTalk
{
    const KEY = 'hash:unread_talk';

    /**
     * 设置用户未读消息(自增加1)
     *
     * @param int                           $uid 用户ID
     * @param int                           $fid 好友ID
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return bool
     */
    public function setInc(int $uid, int $fid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        $num = $this->get($uid, $uid) + 1;

        return (bool)$redis->hset($this->_key($uid), $fid, $num);
    }

    /**
     * 获取用户指定好友的未读消息数
     *
     * @param int                           $uid 用户ID
     * @param int                           $fid 好友ID
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return int
     */
    public function get(int $uid, int $fid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }

        return (int)$redis->hget($this->_key($uid), $fid);
    }

    /**
     * 获取用户未读消息列表
     *
     * @param int                           $uid 用户ID
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return mixed
     */
    public function getAll(int $uid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }

        return $redis->hgetall($this->_key($uid));
    }

    /**
     * 清除用户指定好友的未读消息
     *
     * @param int                           $uid 用户ID
     * @param int                           $fid 好友ID
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return bool
     */
    public function del(int $uid, int $fid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        return (bool)$redis->hdel($this->_key($uid), $fid);
    }

    /**
     * 清除用户所有好友未读数
     *
     * @param int                           $uid
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return bool
     */
    public function delAll(int $uid, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }

        return (bool)$redis->del($this->_key($uid));
    }

    /**
     * 获取缓存key
     *
     * @param int                           $uid 用户ID
     *
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return string
     */
    private function _key(int $uid, ?RedisProxy $redis = null)
    {
        return self::KEY . ":{$uid}";
    }

    /**
     * 获取Redis连接
     *
     * @return RedisProxy
     */
    private function redis()
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
