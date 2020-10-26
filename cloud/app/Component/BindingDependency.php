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

    public const HASH_KEY_TO_IP = 'string.key_to_ip_bind';

    public const ZSET_IP_TO_KEY = 'zset.ip_to_key_bind';

    public static function put(string $key, int $fd, string $ip = null)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        //bind key to fd
        $redis->hSet(self::HASH_KEY_TO_FD_PREFIX, $key, $fd);
        $redis->hSet(self::HASH_FD_TO_KEY_PREFIX, $fd, $key);
        if (is_null($ip)) {
            return;
        }
        if (!isset(self::$bucketsRoom[$ip])) {
            self::$bucketsRoom[$ip] = $ip;
        }
        $redis->hSet(self::HASH_KEY_TO_IP, $key, $ip);
        $redis->sAdd(sprintf('%s.%s', self::ZSET_IP_TO_KEY, $ip), $key);
    }

    public static function del(string $key, int $fd = null, string $ip = null)
    {
        //del key to fd
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $redis->hDel(self::HASH_KEY_TO_FD_PREFIX, $key);
        $redis->hDel(self::HASH_FD_TO_KEY_PREFIX, $fd);
        if (!is_null($ip)) {
            //del set ip - fd
            $redis->hDel(self::HASH_KEY_TO_IP, $key);
            $redis->sRem(sprintf('%s.%s', self::ZSET_IP_TO_KEY, $ip), $key);
        }
    }

    public static function disconnect(int $fd)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $key   = $redis->hGet(self::HASH_FD_TO_KEY_PREFIX, $fd);
        if (empty($key)) {
            return;
        }
        $ip = $redis->hGet(self::HASH_KEY_TO_IP, $key);
        self::del($key, $fd, $ip);
    }

    public static function buckets()
    {
        return self::$bucketsRoom;
    }

    /**
     * @param string $key
     *
     * @return null|int
     */
    public static function fd(string $key)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        return (int)$redis->hGet(self::HASH_KEY_TO_FD_PREFIX, $key) ?? null;
    }

    public static function key(int $fd)
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        return $redis->hGet(self::HASH_FD_TO_KEY_PREFIX, $fd) ?? null;
    }

}


