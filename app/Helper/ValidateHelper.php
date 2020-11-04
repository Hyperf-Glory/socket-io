<?php

namespace App\Helper;

class ValidateHelper
{

    /**
     * 是否整数
     *
     * @param mixed $val
     * @param bool  $bigInt 是否大整数(>PHP_INT_MAX)
     *
     * @return bool
     */
    public static function isInteger($val, $bigInt = false) : bool
    {
        if (!is_numeric($val)) {
            return false;
        }

        //php范围内的整数
        if (!$bigInt && is_float($val + 0) && ($val + 0) > PHP_INT_MAX) {
            return false;
        }

        return preg_match(RegularHelper::$patternInteger, strval($val));
    }

    /**
     * 是否自然数
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isNaturalNum($val) : bool
    {
        return is_numeric($val) && preg_match(RegularHelper::$patternNaturalNum, strval($val));
    }

    /**
     * 是否浮点数
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isFloat($val) : bool
    {
        return is_numeric($val) && is_float($val + 0);
    }

    /**
     * 是否奇数
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isOdd($val) : bool
    {
        return self::isInteger($val) && boolval((intval($val) & 1));
    }

    /**
     * 是否偶数
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isEven($val) : bool
    {
        return self::isInteger($val) && boolval(!(intval($val) & 1));
    }

    /**
     * 是否JSON格式
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isJson(string $val) : bool
    {
        $len = strlen($val);
        if ($len == 0) {
            return false;
        } elseif (($val[0] != '{' || $val[$len - 1] != '}') && ($val[0] != '[' || $val[$len - 1] != ']')) {
            return false;
        }

        @json_decode($val);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 是否二进制数据
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isBinary(string $val) : bool
    {
        return !empty($val) && preg_match(RegularHelper::$patternBinary, $val);
    }

    /**
     * 是否邮箱
     *
     * @param string $val
     * @param int    $minLen 字符串最小长度
     * @param int    $maxLen 字符串最大长度
     *
     * @return bool
     */
    public static function isEmail(string $val, int $minLen = 6, int $maxLen = 40) : bool
    {
        $len = strlen($val);
        return $minLen <= $len && $len <= $maxLen && filter_var($val, FILTER_VALIDATE_EMAIL) && preg_match(RegularHelper::$patternEmail, $val);
    }

    /**
     * 是否中国大陆手机号
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isMobilecn(string $val) : bool
    {
        return !empty($val) && preg_match(RegularHelper::$patternMobilecn, $val);
    }

    /**
     * 是否固定电话或400/800电话
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isTel(string $val) : bool
    {
        return !empty($val) && (preg_match(RegularHelper::$patternTel, $val) || preg_match(RegularHelper::$patternTel4800, $val));
    }

    /**
     * 是否电话号码(手机或固话)
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isPhone(string $val) : bool
    {
        return (self::isMobilecn($val) || self::isTel($val));
    }

    /**
     * 验证登录密码格式
     *
     * @param string $password
     *
     * @return bool
     */
    public static function checkPassword(string $password)
    {
        return (boolean)preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', $password);
    }

    /**
     * 是否URL
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isUrl(string $val) : bool
    {
        return !empty($val) && filter_var($val, FILTER_VALIDATE_URL) && preg_match(RegularHelper::$patternUrl, $val);
    }

    /**
     * 是否中国身份证号码
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isChinaCreditNo(string $val) : bool
    {
        $city = [
            11 => "北京",
            12 => "天津",
            13 => "河北",
            14 => "山西",
            15 => "内蒙古",
            21 => "辽宁",
            22 => "吉林",
            23 => "黑龙江",
            31 => "上海",
            32 => "江苏",
            33 => "浙江",
            34 => "安徽",
            35 => "福建",
            36 => "江西",
            37 => "山东",
            41 => "河南",
            42 => "湖北",
            43 => "湖南",
            44 => "广东",
            45 => "广西",
            46 => "海南",
            50 => "重庆",
            51 => "四川",
            52 => "贵州",
            53 => "云南",
            54 => "西藏",
            61 => "陕西",
            62 => "甘肃",
            63 => "青海",
            64 => "宁夏",
            65 => "新疆",
            71 => "台湾",
            81 => "香港",
            82 => "澳门",
            91 => "国外"
        ];

        //18位或15位
        if (empty($val) || !preg_match(RegularHelper::$patternCnIdNo, $val)) {
            return false;
        }

        //省市代码
        if (!in_array(substr($val, 0, 2), array_keys($city))) {
            return false;
        }

        $len = strlen($val);

        //将15位身份证升级到17位
        if ($len == 15) {
            $val = substr($val, 0, 6) . '19' . substr($val, 6, 9);
        }

        //检查生日
        $birthday = substr($val, 6, 4) . '-' . substr($val, 10, 2) . '-' . substr($val, 12, 2);
        if (date('Y-m-d', strtotime($birthday)) != $birthday) {
            return false;
        }

        //18位身份证需要验证最后一位校验位
        if ($len == 18) {
            //∑(ai×Wi)(mod 11)
            //加权因子
            $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            //校验位对应值
            $parity = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            $sum    = 0;
            for ($i = 0; $i < 17; $i++) {
                $sum += substr($val, $i, 1) * $factor[$i];
            }

            $mod = $sum % 11;
            if (strtoupper(substr($val, 17, 1)) != $parity[$mod]) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查字符串是否是UTF8编码
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isUtf8(string $val) : bool
    {
        if (!empty($val)) {
            return 'UTF-8' === mb_detect_encoding($val, 'UTF-8', true);
        }

        return true;
    }

    /**
     * 字符串是否ASCII编码
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAscii(string $val) : bool
    {
        if (!empty($val)) {
            return 'ASCII' === mb_detect_encoding($val, 'ASCII', true);
        }
        return true;
    }

    /**
     * 是否全是中文字符
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isChinese(string $val) : bool
    {
        return !empty($val) && @preg_match(RegularHelper::$patternAllChinese, $val);
    }

    /**
     * 是否含有中文字符
     *
     * @param string $val
     *
     * @return bool
     */
    public static function hasChinese(string $val) : bool
    {
        return !empty($val) && @preg_match(RegularHelper::$patternHasChinese, $val);
    }

    /**
     * 是否全是字母
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlpha(string $val) : bool
    {
        return !empty($val) && @preg_match(RegularHelper::$patternAlpha, $val);
    }

    /**
     * 是否由(字母或数字)组成
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlphaNum(string $val) : bool
    {
        return $val !== '' && @preg_match(RegularHelper::$patternAlphaNum, $val);
    }

    /**
     * 是否由(字母或数字或下划线)组成
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlphaNumDash(string $val) : bool
    {
        return $val !== '' && @preg_match(RegularHelper::$patternAlphaNumDash, $val);
    }

    /**
     * 是否由(字母或中文)组成
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlphaChinese(string $val) : bool
    {
        return !empty($val) && @preg_match(RegularHelper::$patternAlphaChinese, $val);
    }

    /**
     * 是否由(字母或数字或中文)组成
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlphaNumChinese(string $val) : bool
    {
        return $val !== '' && @preg_match(RegularHelper::$patternAlphaNumChinese, $val);
    }

    /**
     * 是否由(字母或数字或下划线或中文)组成
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isAlphaNumDashChinese(string $val) : bool
    {
        return $val !== '' && @preg_match(RegularHelper::$patternAlphaNumDashChinese, $val);
    }

    /**
     * 是否全是字母
     *
     * @param string $val
     * @param int    $case 是否检查大小写:0忽略大小写,1检查小写,2检查大写
     *
     * @return bool
     */
    public static function isLetter(string $val, int $case = 0) : bool
    {
        if (empty($val)) {
            return false;
        }

        if ($case == 1) { //小写
            $res = ctype_lower($val);
        } elseif ($case == 2) { //大写
            $res = ctype_upper($val);
        } else {
            $res = ctype_alpha($val);
        }

        return $res;
    }

    /**
     * 是否包含字母
     *
     * @param string $val
     *
     * @return bool
     */
    public static function hasLetter(string $val) : bool
    {
        return !empty($val) && @preg_match(RegularHelper::$patternHasLetter, $val);
    }

    /**
     * 是否全部大写字母
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isUpperLetter(string $val) : bool
    {
        return !empty($val) && ctype_upper($val);
    }

    /**
     * 是否全部小写字母
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isLowerLetter(string $val) : bool
    {
        return !empty($val) && ctype_lower($val);
    }

    /**
     * 字符串是否全空格
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isSpace(string $str) : bool
    {
        return $str != '' && preg_match(RegularHelper::$patternSpace, $str);
    }

    /**
     * 字符串是否全空白符
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isWhitespace(string $str) : bool
    {
        return $str != '' && preg_match(RegularHelper::$patternWhitespace, $str);
    }

    /**
     * 字符串是否含有多字节字符
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isMultibyte(string $str) : bool
    {
        return $str != '' && preg_match(RegularHelper::$patternMultibyte, $str);
    }

    /**
     * 是否词语(不以下划线开头的中文、英文、数字、下划线)
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isWord(string $val) : bool
    {
        return !empty($val) && preg_match(RegularHelper::$patternWord, $val);
    }

    /**
     * 检查字符串是否日期格式,并转换为时间戳.
     *
     * @param string $val
     *
     * @return int
     */
    public static function isDate2time(string $val) : int
    {
        /* 匹配
        0000
        0000-00
        0000/00
        0000-00-00
        0000/00/00
        0000-00-00 00
        0000/00/00 00
        0000-00-00 00:00
        0000/00/00 00:00
        0000-00-00 00:00:00
        0000/00/00 00:00:00 */

        $val   = str_replace('/', '-', $val);
        $check = preg_match(RegularHelper::$patternDatetime, $val);
        if (!$check) {
            return 0;
        }

        $val      .= substr('1970-01-01 00:00:01', strlen($val), 19);
        $date     = substr($val, 0, 10);
        $unixTime = strtotime($val);

        //检查日期
        if (!$unixTime || date('Y-m-d', $unixTime) != $date) {
            $unixTime = 0;
        }

        return $unixTime;
    }

    /**
     * 字符串$val是否以$sub为开头
     *
     * @param string $val
     * @param string $sub
     * @param bool   $ignoreCase 是否忽略大小写
     *
     * @return bool
     */
    public static function startsWith(string $val, string $sub, bool $ignoreCase = false) : bool
    {
        if ($val != '' && $sub != '') {
            $pos = $ignoreCase ? mb_stripos($val, $sub) : mb_strpos($val, $sub);
            return $pos === 0;
        }
        return false;
    }

    /**
     * 字符串$val是否以$sub为结尾
     *
     * @param string $val
     * @param string $sub
     * @param bool   $ignoreCase 是否忽略大小写
     *
     * @return bool
     */
    public static function endsWith(string $val, string $sub, bool $ignoreCase = false) : bool
    {
        if ($val != '' && $sub != '') {
            $pos = $ignoreCase ? mb_strripos($val, $sub) : mb_strrpos($val, $sub);
            return (mb_strlen($val) - mb_strlen($sub)) === $pos;
        }

        return false;
    }

    /**
     * 是否iPhone客户端
     *
     * @param string $agent 客户端头信息
     *
     * @return bool
     */
    public static function isIPhoneClient(string $agent) : bool
    {
        return stripos($agent, 'iPhone') !== false;
    }

    /**
     * 是否iPad客户端
     *
     * @param string $agent 客户端头信息
     *
     * @return bool
     */
    public static function isIPadClient(string $agent) : bool
    {
        return stripos($agent, 'iPad') !== false;
    }

    /**
     * 是否iOS设备
     *
     * @param string $agent 客户端头信息
     *
     * @return bool
     */
    public static function isIOSClient(string $agent) : bool
    {
        return self::isIPhoneClient($agent) || self::isIPadClient($agent);
    }

    /**
     * 是否Android设备
     *
     * @param string $agent 客户端头信息
     *
     * @return bool
     */
    public static function isAndroidClient(string $agent) : bool
    {
        return stripos($agent, 'Android') !== false;
    }

    /**
     * 字符串是否base64编码的图片.若否,返回false;若是,返回非空的匹配数组.
     *
     * @param string $val
     *
     * @return bool|array
     */
    public static function isBase64Image(string $val)
    {
        if (empty($val) || !stripos($val, 'base64')) {
            return false;
        }

        if (!preg_match(RegularHelper::$patternBase64Image, $val, $match)) {
            return false;
        }

        /*$match = [
            0 => 'data:img/jpg;base64,',
            1 => 'img',
            2 => 'jpg',
        ];*/

        return $match;
    }

    /**
     * 是否图片文件(根据扩展名)
     *
     * @param string $file 文件路径
     *
     * @return bool
     */
    public static function isImage(string $file) : bool
    {
        $ext = FileHelper::getFileExt($file);
        return in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp']);
    }

    /**
     * 是否可执行文件(根据后缀)
     *
     * @param string $file 文件路径
     *
     * @return bool
     */
    public static function isExecuteFile(string $file) : bool
    {
        $ext = FileHelper::getFileExt($file);
        return in_array($ext, ['php', 'php3', 'php4', 'php5', 'exe', 'sh', 'py']);
    }

    /**
     * 字符串是否IPv4格式
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isIPv4(string $val) : bool
    {
        return !empty($val) && filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == true;
    }

    /**
     * 字符串是否IPv6格式
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isIPv6(string $val) : bool
    {
        return !empty($val) && filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == true;
    }

    /**
     * 是否空对象
     *
     * @param object $val
     *
     * @return bool
     */
    public static function isEmptyObject(object $val) : bool
    {
        return count(get_object_vars($val)) == 0;
    }

    /**
     * 是否内网IP
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isIntranetIp(string $ip) : bool
    {
        if (empty($ip)) {
            return false;
        }

        $arr    = [
            '127.',
            '172.',
            '192.',
        ];
        $prefix = substr($ip, 0, 4);

        if (in_array($prefix, $arr) || stripos($ip, '10.') === 0) {
            return true;
        }

        return false;
    }

    /**
     * 是否QQ号码
     *
     * @param string $val
     *
     * @return bool
     */
    public static function isQQ(string $val) : bool
    {
        return !empty($val) && preg_match(RegularHelper::$patternQQNo, $val);
    }

    /**
     * 是否索引数组
     * 注意:无法判断空数组是索引数组还是关联数组
     *
     * @param mixed|array $arr
     *
     * @return bool
     */
    public static function isIndexArray($arr) : bool
    {
        $res = false;
        if (is_array($arr) && !empty($arr)) {
            $str = implode('', array_keys($arr));
            $str = str_replace('-', '', $str); //处理多个负数索引
            $res = boolval(preg_match(RegularHelper::$patternInteger, $str));
        }

        return $res;
    }

    /**
     * 是否关联数组
     * 注意:无法判断空数组是索引数组还是关联数组
     *
     * @param mixed|array $arr
     *
     * @return bool
     */
    public static function isAssocArray($arr) : bool
    {
        $res = false;
        if (is_array($arr) && !empty($arr)) {
            $res = count(array_filter(array_keys($arr), 'is_string')) > 0;
        }

        return $res;
    }

    /**
     * 检查两个数组是否相等(索引顺序可以不同)
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return bool
     */
    public static function isEqualArray(array $arr1, array $arr2) : bool
    {
        $res = false;
        if (count($arr1) == count($arr2)) {
            ArrayHelper::regularSort($arr1, true);
            ArrayHelper::regularSort($arr2, true);
            $res = serialize($arr1) === serialize($arr2);
        }

        return $res;
    }

    /**
     * 检查是否一维数组
     *
     * @param mixed|array $arr
     *
     * @return bool
     */
    public static function isOneDimensionalArray($arr) : bool
    {
        $res = is_array($arr);
        if ($res) {
            foreach ($arr as $item) {
                if (is_array($item) && !empty($item)) {
                    $res = false;
                    break;
                }
            }
        }

        return $res;
    }

}
