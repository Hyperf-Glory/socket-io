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
namespace App\Helper;

class RegularHelper
{
    /**
     * 正则模式-整数.
     * @var string
     */
    public static $patternInteger = '/^((:?+|-)?[0-9]+)$/';

    /**
     * 正则模式-自然数.
     * @var string
     */
    public static $patternNaturalNum = '/^(?:0|[1-9][0-9]*)$/';

    /**
     * 正则模式-字母.
     * @var string
     */
    public static $patternAlpha = '/^[A-Za-z]+$/';

    /**
     * 正则模式-字母或数字.
     * @var string
     */
    public static $patternAlphaNum = '/^[A-Za-z0-9]+$/';

    /**
     * 正则模式-字母或数字或下划线
     * @var string
     */
    public static $patternAlphaNumDash = '/^[A-Za-z0-9\_]+$/';

    /**
     * 正则模式-字母或中文.
     * @var string
     */
    public static $patternAlphaChinese = '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u';

    /**
     * 正则模式-字母或数字或中文.
     * @var string
     */
    public static $patternAlphaNumChinese = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u';

    /**
     * 正则模式-字母或数字或下划线或中文.
     * @var string
     */
    public static $patternAlphaNumDashChinese = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_]+$/u';

    /**
     * 正则模式-二进制.
     * @var string
     */
    public static $patternBinary = "/[^\x20-\x7E\t\r\n]/";

    /**
     * 正则模式-邮箱.
     * @var string
     */
    public static $patternEmail = '/^[\\w\\-\\.]+@[\\w\\-\\.]+(\\.\\w+)+$/';

    /**
     * 正则模式-中国大陆手机号.
     * @var string
     */
    public static $patternMobilecn = '/^1[3456789]\\d{9}$/';

    /**
     * 正则模式-座机号.
     * @var string
     */
    public static $patternTel = '/^(010|02\\d{1}|0[3-9]\\d{2})-\\d{7,9}(-\\d+)?$/';

    /**
     * 正则模式-座机号400/800.
     * @var string
     */
    public static $patternTel4800 = '/^[48]00\\d?(-?\\d{3,4}){2}$/';

    /**
     * 正则模式-URL.
     * @var string
     */
    public static $patternUrl = '/^http[s]?:\/\/' . '(([0-9]{1,3}\.){3}[0-9]{1,3}' . // IP形式的URL- 199.194.52.184
    '|' . // 允许IP和DOMAIN（域名）
    '([0-9a-z_!~*\'()-]+\.)*' . // 三级域验证- www.
    '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.' . // 二级域验证
    '[a-z]{2,6})' .  // 顶级域验证.com or .museum
    '(:[0-9]{1,4})?' .  // 端口- :80
    '((\/\?)|' .  // 如果含有文件对文件部分进行校验
    '(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/';

    /**
     * 正则模式-中国大陆身份证号码
     * @var string
     */
    public static $patternCnIdNo = '/^([\\d]{17}[xX\\d]|[\\d]{15})$/';

    /**
     * 正则模式-QQ号码
     * @var string
     */
    public static $patternQQNo = '/^[1-9][0-9]{5,16}$/';

    /**
     * 正则模式-中文字符.
     * @var string
     */
    public static $patternChineseChar = '/[\\x{4e00}-\\x{9fa5}]+/u';

    /**
     * 正则模式-全中文.
     * @var string
     */
    public static $patternAllChinese = '/^[\\x{4e00}-\\x{9fa5}]+$/u';

    /**
     * 正则模式-含中文.
     * @var string
     */
    public static $patternHasChinese = '/[\\x{4e00}-\\x{9fa5}]/u';

    /**
     * 正则模式-宽字节(双字节)字符.
     * @var string
     */
    public static $patternWidthChar = '/[^\\x{00}-\\x{ff}]/u';

    /**
     * 正则模式-全英文-小写.
     * @var string
     */
    public static $patternAllLetterLower = '/^[a-z]+$/';

    /**
     * 正则模式-全英文-大写.
     * @var string
     */
    public static $patternAllLetterUpper = '/^[A-Z]+$/';

    /**
     * 正则模式-全英文-忽略大小写.
     * @var string
     */
    public static $patternAllLetter = '/^[a-z]+$/i';

    /**
     * 正则模式-含英文.
     * @var string
     */
    public static $patternHasLetter = '/[a-z]+/i';

    /**
     * 正则模式-词语,不以下划线开头的中文、英文、数字、下划线
     * @var string
     */
    public static $patternWord = '/^(?!_)[\\x{4e00}-\\x{9fa5}a-zA-Z0-9_]+$/u';

    /**
     * 正则模式-日期时间.
     * @var string
     */
    public static $patternDatetime = '/^[0-9]{4}(|\\-[0-9]{2}(|\\-[0-9]{2}(|\\s+[0-9]{2}(|:[0-9]{2}(|:[0-9]{2})))))$/';

    /**
     * 正则模式-base64编码图片.
     * @var string
     */
    public static $patternBase64Image = '/^data:\\s*(image|img)\\/(\\w+);base64,/i';

    /**
     * 正则模式-IPv4.
     * @var string
     */
    public static $patternIPv4 = '/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$/';

    /**
     * 正则模式-连续的"//"或"\\"或"\/"或"/\".
     * @var string
     */
    public static $patternDoubleSlash = '/[\/\\\\]{2,}/';

    /**
     * 正则模式-全空格(包括半角/全角空格和TAB).
     * @var string
     */
    public static $patternSpace = '/^[(\xc2\xa0)|[:blank:]]+$/u';

    /**
     * 正则模式-全空白符.
     * @var string
     */
    public static $patternWhitespace = '/^[(\xc2\xa0)|\s]+$/u';

    /**
     * 正则模式-多字节字符.
     * @var string
     */
    public static $patternMultibyte = '/[^\x00-\x7F]/u';

    /**
     * 正则模式-连续的空白符.
     * @var string
     */
    public static $patternWhitespaceDuplicate = '/[(\xc2\xa0)|\s]{2,}/u';
}
