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

use App\Component\MessageParser;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class LastMsgCache.
 */
class LastMsgCache
{
    /**
     * 设置好友之间或群聊中发送的最后一条消息缓存.
     *
     * @param array $message 消息内容
     * @param int $receive 接收者
     * @param int $sender 发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     */
    public static function set(array $message, int $receive, $sender = 0, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }

        $redis->hSet(self::_name($sender), self::_key($receive, $sender), MessageParser::serialize($message));
    }

    /**
     * 获取好友之间或群聊中发送的最后一条消息缓存.
     *
     * @param int $receive 接收者
     * @param int $sender 发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     *
     * @return mixed
     */
    public static function get(int $receive, $sender = 0, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $data = $redis->hGet(self::_name($sender), self::_key($receive, $sender));

        return $data ? MessageParser::unserialize($data) : null;
    }

    /**
     * 用户聊天或群聊的最后一条消息hash存储的hash名.
     *
     * @param int $sender
     */
    private static function _name($sender = 0): string
    {
        return $sender === 0 ? 'groups:chat:last.msg' : 'friends:chat:last:msg';
    }

    /**
     * 获取hash key.
     *
     * @param int $receive 接收者
     * @param int $sender 发送者
     *
     * @return string
     */
    private static function _key(int $receive, int $sender)
    {
        return $receive < $sender ? "{$receive}_{$sender}" : "{$sender}_{$receive}";
    }

    /**
     * 获取Redis连接.
     *
     * @return RedisProxy
     */
    private static function redis()
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
