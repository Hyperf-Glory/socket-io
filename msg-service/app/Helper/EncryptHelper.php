<?php
declare(strict_types = 1);

namespace App\Helper;

class EncryptHelper
{

    /**
     * url安全的base64_encode
     *
     * @param string $data
     *
     * @return string
     */
    public static function base64UrlEncode(string $data) : string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * url安全的base64_decode
     *
     * @param string $data
     *
     * @return string
     */
    public static function base64UrlDecode(string $data) : string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * 授权码生成及解码.返回结果为数组,分别是加密/解密的字符串和有效期时间戳.
     *
     * @param string $data   数据
     * @param string $key    密钥
     * @param bool   $encode 操作:true时为加密,false时为解密
     * @param int    $expiry 有效期/秒,0为不限制
     *
     * @return array
     */
    public static function authcode(string $data, string $key, bool $encode = true, int $expiry = 0) : array
    {
        if ($data == '') {
            return ['', 0];
        } elseif (!$encode && strlen($data) < Consts::DYNAMIC_KEY_LEN) {
            return ['', 0];
        }

        $now = time();

        //密钥
        $key = md5($key);
        // 密钥a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密钥b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密钥c用于变化生成的密文
        $keyc      = $encode ? substr(md5(microtime()), -Consts::DYNAMIC_KEY_LEN) : substr($data, 0, Consts::DYNAMIC_KEY_LEN);
        $keyd      = md5($keya . $keyc);
        $cryptkey  = $keya . $keyd;
        $keyLength = strlen($cryptkey);

        if ($encode) {
            if ($expiry != 0) {
                $expiry = $expiry + $now;
            }
            $expMd5 = substr(md5($data . $keyb), 0, 16);
            $data   = sprintf('%010d', $expiry) . $expMd5 . $data;
        } else {
            $data = self::base64UrlDecode(substr($data, Consts::DYNAMIC_KEY_LEN));
        }

        $dataLen = strlen($data);
        $res     = '';
        $box     = range(0, 255);
        $rndkey  = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $dataLen; $i++) {
            $a       = ($a + 1) % 256;
            $j       = ($j + $box[$a]) % 256;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $res     .= chr(ord($data[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($encode) {
            $res = $keyc . self::base64UrlEncode($res);
            return [$res, $expiry];
        } else {
            if (strlen($res) > 26) {
                $expTime = intval(substr($res, 0, 10));
                if (($expTime == 0 || $expTime - $now > 0) && substr($res, 10, 16) == substr(md5(substr($res, 26) . $keyb), 0, 16)) {
                    return [substr($res, 26), $expTime];
                }
            }
            return ['', 0];
        }
    }

    /**
     * 简单加密
     *
     * @param string $data 数据
     * @param string $key  密钥
     *
     * @return string
     */
    public static function easyEncrypt(string $data, string $key) : string
    {
        if ($data == '') {
            return '';
        }

        $key     = md5($key);
        $dataLen = strlen($data);
        $keyLen  = strlen($key);
        $x       = 0;
        $str     = $char = '';
        for ($i = 0; $i < $dataLen; $i++) {
            if ($x == $keyLen) {
                $x = 0;
            }

            $str .= chr(ord($data[$i]) + (ord($key[$x])) % 256);
            $x++;
        }

        return substr($key, 0, Consts::DYNAMIC_KEY_LEN) . self::base64UrlEncode($str);
    }

    /**
     * 简单解密
     *
     * @param string $data 数据
     * @param string $key  密钥
     *
     * @return string
     */
    public static function easyDecrypt(string $data, string $key) : string
    {
        if (strlen($data) < Consts::DYNAMIC_KEY_LEN) {
            return '';
        }

        $key = md5($key);
        if (substr($key, 0, Consts::DYNAMIC_KEY_LEN) != substr($data, 0, Consts::DYNAMIC_KEY_LEN)) {
            return '';
        }

        $data = self::base64UrlDecode(substr($data, Consts::DYNAMIC_KEY_LEN));
        if (empty($data)) {
            return '';
        }

        $dataLen = strlen($data);
        $keyLen  = strlen($key);
        $x       = 0;
        $str     = $char = '';
        for ($i = 0; $i < $dataLen; $i++) {
            if ($x == $keyLen) {
                $x = 0;
            }

            $c = ord($data[$i]);
            $k = ord($key[$x]);
            if ($c < $k) {
                $str .= chr(($c + 256) - $k);
            } else {
                $str .= chr($c - $k);
            }

            $x++;
        }

        return $str;
    }

    /**
     * MurmurHash3算法函数
     *
     * @param string $data   要哈希的数据
     * @param int    $seed   随机种子(仅素数)
     * @param bool   $unsign 是否返回无符号值;为true时返回11位无符号整数,为false时返回10位有符号整数
     *
     * @return float|int
     */
    public static function murmurhash3Int(string $data, int $seed = 3, bool $unsign = true)
    {
        $key  = array_values(unpack('C*', $data));
        $klen = count($key);
        $h1   = abs($seed);
        for ($i = 0, $bytes = $klen - ($remainder = $klen & 3); $i < $bytes;) {
            $k1 = $key[$i] | ($key[++$i] << 8) | ($key[++$i] << 16) | ($key[++$i] << 24);
            ++$i;
            $k1  = (((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16))) & 0xffffffff;
            $k1  = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
            $k1  = (((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16))) & 0xffffffff;
            $h1  ^= $k1;
            $h1  = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7fffffff) >> 19) | 0x1000);
            $h1b = (((($h1 & 0xffff) * 5) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 5) & 0xffff) << 16))) & 0xffffffff;
            $h1  = ((($h1b & 0xffff) + 0x6b64) + ((((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7fffffff) >> 16) | 0x8000)) + 0xe654) & 0xffff) << 16));
        }
        $k1 = 0;
        switch ($remainder) {
            case 3:
                $k1 ^= $key[$i + 2] << 16;
                break;
            case 2:
                $k1 ^= $key[$i + 1] << 8;
                break;
            case 1:
                $k1 ^= $key[$i];
                $k1 = ((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16)) & 0xffffffff;
                $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
                $k1 = ((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16)) & 0xffffffff;
                $h1 ^= $k1;
                break;
        }
        $h1 ^= $klen;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
        $h1 = ((($h1 & 0xffff) * 0x85ebca6b) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 13 : (($h1 & 0x7fffffff) >> 13) | 0x40000);
        $h1 = (((($h1 & 0xffff) * 0xc2b2ae35) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);

        if ($unsign) {
            $h1 = ($h1 >= 0) ? bcadd('1' . str_repeat('0', 10), $h1) : abs($h1);
        }

        return $h1;
    }

}
