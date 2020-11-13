<?php

namespace App\Cache;

use App\Component\MessageParser;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

/**
 * Class LastMsgCache
 * @package App\Cache
 */
class LastMsgCache
{

    /**
     * 用户聊天或群聊的最后一条消息hash存储的hash名
     *
     * @param int $sender
     *
     * @return string
     */
    private static function _name($sender = 0)
    {
        return $sender == 0 ? 'groups:chat:last.msg' : 'friends:chat:last:msg';
    }

    /**
     * 获取hash key
     *
     * @param int $receive 接收者
     * @param int $sender  发送者
     *
     * @return string
     */
    private static function _key(int $receive, int $sender)
    {
        return $receive < $sender ? "{$receive}_{$sender}" : "{$sender}_{$receive}";
    }

    /**
     * 设置好友之间或群聊中发送的最后一条消息缓存
     *
     * @param array                         $message 消息内容
     * @param int                           $receive 接收者
     * @param int                           $sender  发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     * @param null|\Hyperf\Redis\RedisProxy $redis
     */
    public static function set(array $message, int $receive, $sender = 0, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }

        $redis->hset(self::_name($sender), self::_key($receive, $sender), MessageParser::serialize($message));
    }

    /**
     * 获取好友之间或群聊中发送的最后一条消息缓存
     *
     * @param int                           $receive 接收者
     * @param int                           $sender  发送者(注：若聊天消息类型为群聊消息 $sender 应设置为0)
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return mixed
     */
    public static function get(int $receive, $sender = 0, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = self::redis();
        }
        $data = $redis->hget(self::_name($sender), self::_key($receive, $sender));

        return $data ? MessageParser::unserialize($data) : null;
    }

    /**
     * 获取Redis连接
     *
     * @return RedisProxy
     */
    private static function redis()
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }
}
