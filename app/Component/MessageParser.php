<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Component;

use Hyperf\Utils\Codec\Json;

class MessageParser
{
    /**
     * @param string $data
     *
     * @return array
     */
    public static function decode(string $data) : array
    {
        $data   = sprintf('%s%s%s', pack('N', strlen($data)), $data, "\r\n");
        $strlen = strlen($data);
        return swoole_substr_json_decode($data, 4, $strlen - 6, true);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public static function encode(array $data) : string
    {
        return Json::encode($data);
    }

    /**
     * @param $data
     *
     * @return string
     */
    public static function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    public static function unserialize(string $data)
    {
        $str    = pack('N', strlen($data)) . $data . "\r\n";
        $strlen = strlen($data);
        return swoole_substr_unserialize($str, 4, $strlen);
    }

}
