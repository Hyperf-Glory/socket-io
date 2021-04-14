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

use App\Component\MessageParser;

/**
 * Class LastMsgCache.
 */
class LastMsgCache extends AbstractCache
{

    /**
     * 设置好友之间或群聊中发送的最后一条消息缓存.
     *
     * @param array $message 消息内容
     * @param int   $receive 接收者
     * @param int   $sender  发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     */
    public function set(array $message, int $receive, $sender = 0) : void
    {
        wait(function () use ($message, $receive, $sender)
        {
            $this->redis()->hSet(self::_name($sender), self::_key($receive, $sender), MessageParser::serialize($message));
        }, $this->waitTimeOut);
    }

    /**
     * 获取好友之间或群聊中发送的最后一条消息缓存.
     *
     * @param int $receive 接收者
     * @param int $sender  发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     *
     * @return mixed
     */
    public function get(int $receive, $sender = 0)
    {
        return wait(function () use ($receive, $sender)
        {
            $data = $this->redis()->hGet($this->_name($sender), $this->_key($receive, $sender));
            return $data ? MessageParser::unserializable($data) : null;
        }, $this->waitTimeOut);
    }

    /**
     * 用户聊天或群聊的最后一条消息hash存储的hash名.
     *
     * @param int $sender
     */
    private function _name($sender = 0) : string
    {
        return $sender === 0 ? 'groups:chat:last.msg' : 'friends:chat:last:msg';
    }

    /**
     * 获取hash key.
     *
     * @param int $receive 接收者
     * @param int $sender  发送者
     *
     * @return string
     */
    private function _key(int $receive, int $sender) : string
    {
        return $receive < $sender ? "{$receive}_$sender" : "{$sender}_$receive";
    }

}
