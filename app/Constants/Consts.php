<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
namespace App\Constants;

class Consts
{
    //整数相关
    const INT_3B_MAX = 255; //3位最大整数值,tinyint(3)

    const INT_5B_MAX = 65535; //5位最大整数值,smallint(5)

    const INT_8B_MAX = 16777215; //8位最大整数值,mediumint(8)

    const INT_10B_MAX = 4294967295; //10位最大整数值,int(10)

    //有效期时间
    const TTL_LONG = 0; //永久

    const TTL_DEFAULT = 1800; //默认缓存时间30分钟

    const TTL_ONE_MONTH = 2592000; //缓存时间1月

    const TTL_HALF_MONTH = 1296000; //缓存时间0.5月

    const TTL_ONE_WEEK = 604800; //缓存时间1周

    const TTL_ONE_DAY = 86400; //缓存时间1天

    const TTL_HALF_DAY = 43200; //缓存时间0.5天

    const TTL_ONE_HOUR = 3600; //缓存时间1小时

    const TTL_HALF_HOUR = 1800; //缓存时间半小时

    const TTL_FIF_MINUTE = 900; //缓存时间15分钟

    const TTL_TEN_MINUTE = 600; //缓存时间10分钟

    const TTL_FIV_MINUTE = 300; //缓存时间5分钟

    const TTL_TWO_MINUTE = 120; //缓存时间2分钟

    const TTL_ONE_MINUTE = 60; //缓存时间1分钟

    const UNKNOWN = 'Unknown'; //未知字符串

    const DELIMITER = '$@#KSYS#@$'; //本库自定义分隔符

    const PAAMAYIM_NEKUDOTAYIM = '::'; //范围解析操作符-双冒号

    const DYNAMIC_KEY_LEN = 8; //authcode动态密钥长度,须<32
}
