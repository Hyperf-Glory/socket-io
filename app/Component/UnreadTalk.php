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
namespace App\Component;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * 好友未读消息服务
 *
 * Class UnreadTalkService
 */
class UnreadTalk
{
    const KEY = 'hash:unread_talk';

    /**
     * 设置用户未读消息(自增加1).
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function setInc(int $uid, int $fid, ?RedisProxy $redis = null): bool
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        $num = $this->get($uid, $uid) + 1;

        return (bool) $redis->hset($this->_key($uid), (string) $fid, $num);
    }

    /**
     * 获取用户指定好友的未读消息数.
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function get(int $uid, int $fid, ?RedisProxy $redis = null): int
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }

        return (int) $redis->hget($this->_key($uid), (string) $fid);
    }

    /**
     * 获取用户未读消息列表.
     *
     * @param int $uid 用户ID
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
     * 清除用户指定好友的未读消息.
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function del(int $uid, int $fid, ?RedisProxy $redis = null): bool
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        return (bool) $redis->hdel($this->_key($uid), (string) $fid);
    }

    /**
     * 清除用户所有好友未读数.
     */
    public function delAll(int $uid, ?RedisProxy $redis = null): bool
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }

        return (bool) $redis->del($this->_key($uid));
    }

    /**
     * 获取缓存key.
     *
     * @param int $uid 用户ID
     */
    private function _key(int $uid, ?RedisProxy $redis = null): string
    {
        return self::KEY . ":{$uid}";
    }

    /**
     * 获取Redis连接.
     */
    private function redis(): RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
