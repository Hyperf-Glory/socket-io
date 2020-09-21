<?php
declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
        return Json::decode($data, true);
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
}
