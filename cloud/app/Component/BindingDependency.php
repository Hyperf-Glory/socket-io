<?php
declare(strict_types = 1);

namespace App\Component;

use Hyperf\Redis\RedisFactory;

class BindingDependency
{
    /**
     * @var array roomid => roomid
     */
    public static $bucketsRoom;

    public const HASH_KEY_TO_FD_PREFIX = 'hash.key_to_fd_bind';

    public const HASH_FD_TO_KEY_PREFIX = 'hash.fd_to_key_bind';

    public const HASH_KEY_TO_ROOM_PREFIX = 'hash.key_to_room_bind';

    public const SET_ROOM_FD_PREFIX = 'set.room_fd_bind';

    public const STRING_KEY_TO_ROOM = 'string.key_to_fd_bind';

    public static function put(string $key, int $fd, string $roomId = null)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        //bind key to fd
        $redis->hSet(self::HASH_KEY_TO_FD_PREFIX, $key, $fd);
        $redis->hSet(self::HASH_FD_TO_KEY_PREFIX, $fd, $key);
        if (is_null($roomId)) {
            return;
        }
        //check buckets exist roomid
        if (!isset(self::$bucketsRoom[$roomId])) {
            self::$bucketsRoom[$roomId] = $roomId;
        }
        $redis->set(sprintf('%s.%s', self::STRING_KEY_TO_ROOM, $key), $roomId);
        //add fd to room
        $redis->sAdd(sprintf('%s.%s', self::SET_ROOM_FD_PREFIX, $roomId), $fd);
    }

    public static function del(string $key, int $fd = null, string $roomId = null)
    {
        //del key to fd
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $redis->hDel(self::HASH_KEY_TO_FD_PREFIX, $key);
        $redis->hDel(self::HASH_FD_TO_KEY_PREFIX, $fd);
        if (!is_null($roomId)) {
            //del set room - fd
            $redis->del(sprintf('%s.%s', self::STRING_KEY_TO_ROOM, $key));
            $redis->sRem(sprintf('%s.%s', self::SET_ROOM_FD_PREFIX, $roomId), $fd);
        }
    }

    public static function disconnect(int $fd)
    {

    }

    public static function buckets()
    {
        return self::$bucketsRoom;
    }

    /**
     * @param null|string $roomId
     *
     * @return array
     */
    public static function roomfds(string $roomId = null)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        return $redis->sMembers(sprintf('%s.%s', self::SET_ROOM_FD_PREFIX, $roomId)) ?? [];
    }

    public static function fd(string $key)
    {

    }

    public static function key(int $fd)
    {

    }



}


