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
namespace App\Component;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;

/**
 * 好友未读消息服务
 *
 * Class UnreadTalkService
 */
class UnreadTalk
{
    public float $waitTimeOut = 5.0;

    public const KEY = 'hash:unread_talk';

    /**
     * 设置用户未读消息(自增加1).
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function setInc(int $uid, int $fid) : bool
    {
        return wait(function () use ($uid, $fid)
        {
            $num = $this->get($uid, $uid) + 1;
            return (bool)$this->redis()->hset($this->_key($uid), (string)$fid, $num);
        }, $this->waitTimeOut);
    }

    /**
     * 获取用户指定好友的未读消息数.
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function get(int $uid, int $fid) : int
    {
        return wait(function () use ($uid, $fid)
        {
            return (int)$this->redis()->hget($this->_key($uid), (string)$fid);
        }, $this->waitTimeOut);
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
        return wait(function () use ($uid)
        {
            return $this->redis()->hgetall($this->_key($uid));
        }, $this->waitTimeOut);
    }

    /**
     * 清除用户指定好友的未读消息.
     *
     * @param int $uid 用户ID
     * @param int $fid 好友ID
     */
    public function del(int $uid, int $fid) : bool
    {
        return wait(function () use ($uid, $fid)
        {
            return (bool)$this->redis()->hdel($this->_key($uid), (string)$fid);
        }, $this->waitTimeOut);
    }

    /**
     * 清除用户所有好友未读数.
     */
    public function delAll(int $uid, ?RedisProxy $redis = null) : bool
    {
        return wait(function () use ($uid)
        {
            return (bool)$this->redis()->del($this->_key($uid));
        }, $this->waitTimeOut);
    }

    /**
     * 获取缓存key.
     *
     * @param int $uid 用户ID
     */
    private function _key(int $uid) : string
    {
        return self::KEY . ":{$uid}";
    }

    /**
     * 获取Redis连接.
     */
    private function redis() : RedisProxy
    {
        return ApplicationContext::getContainer()->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
