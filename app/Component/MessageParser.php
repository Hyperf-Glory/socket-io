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
    public static function decode(string $data) : array
    {
        $data   = sprintf('%s%s%s', pack('N', strlen($data)), $data, "\r\n");
        $strlen = strlen($data);
        return swoole_substr_json_decode($data, 4, $strlen - 6, true);
    }

    public static function encode(array $data) : string
    {
        return Json::encode($data);
    }
}
