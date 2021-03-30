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

use App\Model\Users;
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

    /**
     * @param $data
     *
     * @return string
     */
    public static function serialize($data) : string
    {
        return serialize($data);
    }

    /**
     * @return mixed
     */
    public static function unserializable(string $data)
    {
        $str    = pack('N', strlen($data)) . $data . "\r\n";
        $strlen = strlen($data);
        return swoole_substr_unserialize($str, 4, $strlen);
    }

    /**
     * 格式化对话的消息体.
     *
     * @param array $data 对话的消息
     */
    public static function formatTalkMsg(array $data) : array
    {
        // 缓存优化
        if (!isset($data['nickname'], $data['avatar']) || empty($data['nickname']) || empty($data['avatar'])) {
            if (isset($data['user_id']) && !empty($data['user_id'])) {
                /**
                 * @var Users $info
                 */
                $info = Users::where('id', $data['user_id'])->first(['nickname', 'avatar']);
                if ($info) {
                    $data['nickname'] = $info->nickname;
                    $data['avatar']   = $info->avatar;
                }
            }
        }

        $arr = [
            'id'         => 0,
            'source'     => 1,
            'msg_type'   => 1,
            'user_id'    => 0,
            'receive_id' => 0,
            'content'    => '',
            'is_revoke'  => 0,

            // 发送消息人的信息
            'nickname'   => '',
            'avatar'     => '',

            // 不同的消息类型
            'file'       => [],
            'code_block' => [],
            'forward'    => [],
            'invite'     => [],

            'created_at' => '',
        ];

        return array_merge($arr, array_intersect_key($data, $arr));
    }
}
