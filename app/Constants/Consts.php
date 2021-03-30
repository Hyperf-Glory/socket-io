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
namespace App\Constants;

class Consts
{
    //整数相关
    public const INT_3B_MAX = 255; //3位最大整数值,tinyint(3)

    public const INT_5B_MAX = 65535; //5位最大整数值,smallint(5)

    public const INT_8B_MAX = 16777215; //8位最大整数值,mediumint(8)

    public const INT_10B_MAX = 4294967295; //10位最大整数值,int(10)

    //有效期时间
    public const TTL_LONG = 0; //永久

    public const TTL_DEFAULT = 1800; //默认缓存时间30分钟

    public const TTL_ONE_MONTH = 2592000; //缓存时间1月

    public const TTL_HALF_MONTH = 1296000; //缓存时间0.5月

    public const TTL_ONE_WEEK = 604800; //缓存时间1周

    public const TTL_ONE_DAY = 86400; //缓存时间1天

    public const TTL_HALF_DAY = 43200; //缓存时间0.5天

    public const TTL_ONE_HOUR = 3600; //缓存时间1小时

    public const TTL_HALF_HOUR = 1800; //缓存时间半小时

    public const TTL_FIF_MINUTE = 900; //缓存时间15分钟

    public const TTL_TEN_MINUTE = 600; //缓存时间10分钟

    public const TTL_FIV_MINUTE = 300; //缓存时间5分钟

    public const TTL_TWO_MINUTE = 120; //缓存时间2分钟

    public const TTL_ONE_MINUTE = 60; //缓存时间1分钟

    public const UNKNOWN = 'Unknown'; //未知字符串

    public const DELIMITER = '$@#KSYS#@$'; //本库自定义分隔符

    public const PAAMAYIM_NEKUDOTAYIM = '::'; //范围解析操作符-双冒号

    public const DYNAMIC_KEY_LEN = 8; //authcode动态密钥长度,须<32
}
